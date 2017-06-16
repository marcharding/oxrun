<?php

namespace Oxrun\Command\Install;

use Distill\Distill;
use GuzzleHttp\Client;
use GuzzleHttp\Event\ProgressEvent;
use GuzzleHttp\Exception\ClientException;
use Oxrun\PhpParser\OxidSetupNodeVisitor;
use PhpParser;
use PhpParser\Node;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class InstallCommand
 * @package Oxrun\Command\Install
 */
class InstallCommand extends Command
{

    /**
     * Array of available oxid versions
     *
     * @var array
     */
    protected $oxidVersions = array();

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('install:shop')
            ->addOption('oxidVersion', null, InputOption::VALUE_OPTIONAL, 'Oxid version')
            ->addOption('installationFolder', null, InputOption::VALUE_OPTIONAL, 'Installation folder', getcwd())
            ->addOption('dbHost', null, InputOption::VALUE_REQUIRED, 'Database host', 'localhost')
            ->addOption('dbUser', null, InputOption::VALUE_REQUIRED, 'Database user', 'oxid')
            ->addOption('dbPwd', null, InputOption::VALUE_REQUIRED, 'Database password', '')
            ->addOption('dbName', null, InputOption::VALUE_REQUIRED, 'Database name', 'oxid')
            ->addOption('dbPort', null, InputOption::VALUE_OPTIONAL, 'Database port', 3306)
            ->addOption('installSampleData', null, InputOption::VALUE_OPTIONAL, 'Install sample data', true)
            ->addOption('shopURL', null, InputOption::VALUE_REQUIRED, 'Installation base url')
            ->addOption('adminUser', null, InputOption::VALUE_REQUIRED, 'Admin user email/login', 'admin@example.com')
            ->addOption('adminPassword', null, InputOption::VALUE_REQUIRED, 'Admin password', 'oxid-123456')
            ->setDescription('Installs the shop');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->oxidVersions = $this->getOxidVersions();
        if (!isset($this->oxidVersions[$input->getOption('oxidVersion')])) {
            $output->writeln("<error>Oxid {$input->getOption('oxidVersion')} not available</error>");
            return;
        }

        $output->writeln("<info>Downloading oxid {$input->getOption('oxidVersion')}</info>");

        $oxidVersion = $this->oxidVersions[$input->getOption('oxidVersion')];
        $archiveFile = $this->downloadOxid($output, $oxidVersion);

        $target = $input->getOption('installationFolder');
        if (!is_dir($target)) {
            mkdir($target, 0755, true);
        }
        $target = realpath($input->getOption('installationFolder'));

        $output->writeLn("<info>Extracting archive</info>");
        $this->extractArchive($output, $archiveFile, $target, $oxidVersion);

        $output->writeLn("<info>Patching installation</info>");
        $this->patchOxSetup($target, $oxidVersion['tag']);

        $output->writeLn("<info>Installing shop</info>");
        include_once $target . '/setup/oxrunsetup.php';

        // Fake request params...
        $_GET['ow'] = true;
        $_POST = array(
            "aDB" => array(
                'dbHost' => $input->getOption('dbHost'),
                'dbName' => $input->getOption('dbName'),
                'dbUser' => $input->getOption('dbUser'),
                'dbPwd' => $input->getOption('dbPwd'),
                'dbiDemoData' => $input->getOption('installSampleData'),
                'iUtfMode' => 1
            ),
            "aPath" => array(
                'sShopURL' => $input->getOption('shopURL'),
                'sShopDir' => $target,
                'sCompileDir' => $target . '/tmp',
                'sBaseUrlPath' => '/',
            ),
            "aSetupConfig" => array(
                'blDelSetupDir' => 1
            ),
            "aAdminData" => array(
                'sLoginName' => $input->getOption('adminUser'),
                'sPassword' => $input->getOption('adminPassword'),
                'sPasswordConfirm' => $input->getOption('adminPassword')
            )
        );

        $oxSetupSession = new \oxSetupSession;
        $oxSetupSession->setSessionParam('aDB', $_POST["aDB"]);
        $oxSetupSession->setSessionParam('setup_lang', 'en');
        $oxSetupSession->setSessionParam('sShopLang', 'de');
        $oxSetupSession->setSessionParam('aSetupConfig', $_POST["aSetupConfig"]);

        $oxSetupController = new \oxSetupController;
        $oxSetupController->dbConnect();
        $oxSetupController->dbCreate();
        $oxSetupController->dirsWrite();
        $aMessages = $oxSetupController->getView()->getMessages();
        foreach($aMessages as $message) {
            $cleanMessage = str_replace(array('ERROR:','FEHLER:'), '', $message);
            if($cleanMessage !== $message) {
                $cleanMessage = trim($cleanMessage);
                $output->writeln("<error>An error occured during the installation: {$cleanMessage}</error>");
            }
        }
        $oxSetupController->finish();

        $oxSetupView = new \oxSetupView;
        $oxSetupView->isDeletedSetup();
        rmdir($target . '/setup/');
    }

    /**
     * Utility method to show the number of bytes in a readable format.
     * Taken from symfony installer.
     *
     * @param int $bytes The number of bytes to format
     *
     * @return string The human readable string of bytes (e.g. 4.32MB)
     */
    protected function formatSize($bytes)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = $bytes ? floor(log($bytes, 1024)) : 0;
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return number_format($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Patches the oxid install so it works on the command line
     *
     * @param $target
     */
    protected function patchOxSetup($target, $version = null)
    {
        $parser = new \PhpParser\Parser(new PhpParser\Lexer\Emulative);
        $traverser = new \PhpParser\NodeTraverser;
        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard;
        file_put_contents($target . '/setup/oxrunsetup.php', '');
        $traverser->addVisitor(new OxidSetupNodeVisitor($target . '/setup/'));
        try {
            $code = file_get_contents($target . '/setup/oxsetup.php');
            $stmts = $parser->parse($code);
            $stmts = $traverser->traverse($stmts);
            $code = $prettyPrinter->prettyPrintFile($stmts);
            file_put_contents($target . '/setup/oxrunsetup.php', $code, FILE_APPEND);
        } catch (PhpParser\Error $e) {

        }
        // patch version 4.10.2 and above (broken in official installer)
        if(isset($version)) {
            if(version_compare($version, "v4.10.2") >= 0){
                $code = file_get_contents($target . '/setup/oxrunsetup.php');
                $code = str_replace("demodata.sql", "test_demodata.sql", $code);
                file_put_contents($target . '/setup/oxrunsetup.php', $code);
                // they really messed this up, the admin user is missing when the testdata is used.
                $sqlInitialData = file_get_contents($target . '/setup/sql/initial_data.sql');
                $sqlDemoData = file_get_contents($target . '/setup/sql/test_demodata.sql');
                file_put_contents($target . '/setup/sql/test_demodata.sql', $sqlInitialData . PHP_EOL . $sqlDemoData);
            }
        }
    }

    /**
     * @return mixed
     */
    protected function getOxidVersions()
    {
        $client = new Client();
        $githubToken = getenv('GITHUB_TOKEN');
        if( $githubToken ) {
            $tagsArray = $client->get('https://api.github.com/repos/OXID-eSales/oxideshop_ce/tags?per_page=9999&access_token='.$githubToken)->json();
        } else {
            $tagsArray = $client->get('https://api.github.com/repos/OXID-eSales/oxideshop_ce/tags?per_page=9999')->json();
        }
        $tagsArray = array_reduce(
            $tagsArray,
            function ($result, $item) {
                $result[$item['name']] = array(
                    'zip' => $item['zipball_url'],
                    'folder' => 'OXID-eSales-oxideshop_ce-' . substr($item['commit']['sha'], 0, 7),
                    'hash' => 'OXID-eSales-oxideshop_ce-' . $item['commit']['sha'],
                    'sha' => $item['commit']['sha'],
                    'tag' => $item['name'],
                    'versionTag' => substr($item['name'], 1),
                );
                return $result;
            },
            array()
        );
        $tagsArray = array_filter(
            $tagsArray,
            function($tagArray) {
                return preg_match('#^v\d+\.\d+\.\d+$#', $tagArray['tag']);
            }
        );
        return $tagsArray;
    }

    /**
     * Downloads the oxid archive
     *
     * @param OutputInterface $output
     * @param $url
     * @return string
     */
    protected function downloadOxid(OutputInterface $output, $version)
    {
        $file = sys_get_temp_dir() . '/oxrun-' . time() . '.zip';

        $progressBar = null;

        $client = new Client();

        try {

            $githubToken = getenv('GITHUB_TOKEN');
            if( $githubToken ) {
                $request = $client->createRequest('GET', $version['zip'].'?access_token='.$githubToken, array('save_to' => $file));
            } else {
                $request = $client->createRequest('GET', $version['zip'], array('save_to' => $file));
            }

            $request->getEmitter()->on('progress', function (ProgressEvent $e) use (&$progressBar, $output) {

                if (null === $progressBar && $e->downloadSize !== 0) {

                    ProgressBar::setPlaceholderFormatterDefinition('max', function (ProgressBar $bar) {
                        return $this->formatSize($bar->getMaxSteps());
                    });

                    ProgressBar::setPlaceholderFormatterDefinition('current', function (ProgressBar $bar) {
                        return str_pad($this->formatSize($bar->getStep()), 11, ' ', STR_PAD_LEFT);
                    });

                    $progressBar = new ProgressBar($output, $e->downloadSize);
                    $progressBar->setFormat('%current%/%max% %bar%  %percent:3s%%');
                    $progressBar->setRedrawFrequency(max(1, floor($e->downloadSize / 1000)));
                    $progressBar->setBarWidth(60);

                    if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
                        $progressBar->setEmptyBarCharacter('░'); // light shade character \u2591
                        $progressBar->setProgressCharacter('');
                        $progressBar->setBarCharacter('▓'); // dark shade character \u2593
                    }

                    $progressBar->start();
                }
                if ($progressBar) {
                    $progressBar->setProgress($e->downloaded);

                }

            });

            $client->send($request);

        } catch (ClientException $e) {
            throw new \RuntimeException(sprintf(
                "There was an error downloading:\n%s",
                $e->getMessage()
            ), null, $e);
        }

        if (null !== $progressBar) {
            $progressBar->finish();
            $output->writeln("\n");
        }

        return $file;
    }

    /**
     * Extracts the archive
     *
     * @param OutputInterface $output
     * @param $file
     * @param $target
     * @param $oxidVersion
     */
    protected function extractArchive(OutputInterface $output, $file, $target, $oxidVersion)
    {
        $distill = new Distill();
        $output->writeLn("Extracting");
        $distill->extract($file, $target);
        $filesystem = new Filesystem();
        $filesystem->mirror($target . '/' . $oxidVersion['folder'] . '/source', $target);
        $filesystem->remove($target . '/' . $oxidVersion['folder']);
        $oxidVersion['timestamp'] = date('Y-m-d H:i:s');
        $oxidVersion['build'] = preg_replace('/[^\d]+/', '', $oxidVersion['versionTag']);
        $pkgInfo = <<<EOT
[Builder]
build = {$oxidVersion['build']}

[Package info]
revision = "{$oxidVersion['sha']}"
edition = "CE"
version = "{$oxidVersion['versionTag']}"
encoder-version = ""
timestamp = "{$oxidVersion['timestamp']}"
EOT;
        file_put_contents($target . '/pkg.info', $pkgInfo);
        file_put_contents($target . '/pkg.rev', $oxidVersion['sha']);
    }

}
