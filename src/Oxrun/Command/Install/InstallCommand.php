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
    protected $oxidVersions = array(
        "4.9.3" => "http://support.oxid-esales.com/versions/CE/index.php?php=5.2&target=4.9.3",
        "4.8.9" => "http://support.oxid-esales.com/versions/CE/index.php?php=5.2&target=4.8.9",
        "4.7.14" => "http://wiki.oxidforge.org/download/OXID_ESHOP_CE_4.7.14.zip"
    );

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('install:shop')
            ->addOption('oxidVersion', null, InputOption::VALUE_OPTIONAL, 'Oxid version', key($this->oxidVersions))
            ->addOption('installationFolder', null, InputOption::VALUE_OPTIONAL, 'Installation folder', getcwd())
            ->addOption('dbHost', null, InputOption::VALUE_REQUIRED, 'Database host', 'localhost')
            ->addOption('dbUser', null, InputOption::VALUE_REQUIRED, 'Database user', 'oxid')
            ->addOption('dbPwd', null, InputOption::VALUE_REQUIRED, 'Database password', '')
            ->addOption('dbName', null, InputOption::VALUE_REQUIRED, 'Database name', 'oxid')
            ->addOption('dbPort', null, InputOption::VALUE_OPTIONAL, 'Database port', 3306)
            ->addOption('installSampleData', null, InputOption::VALUE_OPTIONAL, 'Install sample data', true)
            ->addOption('shopURL', null, InputOption::VALUE_REQUIRED, 'Installation base url')
            ->addOption('adminUser', null, InputOption::VALUE_REQUIRED, 'Admin user email/login')
            ->addOption('adminPassword', null, InputOption::VALUE_REQUIRED, 'Admin password')
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
        $output->writeln("<info>Downloading oxid {$input->getOption('oxidVersion')}</info>");

        $oxidUrl = $this->oxidVersions[$input->getOption('oxidVersion')];

        $archiveFile = $this->downloadOxid($output, $oxidUrl);

        $target = $input->getOption('installationFolder');
        if (!is_dir($target)) {
            mkdir($target, 0755, true);
        }
        $target = realpath($input->getOption('installationFolder'));

        $output->writeLn("<info>Extracting archive</info>");
        $this->extractArchive($output, $archiveFile, $target);


        $output->writeLn("<info>Patching installation</info>");
        $this->patchOxSetup($target);

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
        $oxSetupSession->setSessionParam('setup_lang', 'de');
        $oxSetupSession->setSessionParam('sShopLang', 'de');
        $oxSetupSession->setSessionParam('aSetupConfig', $_POST["aSetupConfig"]);

        $oxSetupController = new \oxSetupController;
        $oxSetupController->dbConnect();
        $oxSetupController->dbCreate();
        $oxSetupController->dirsWrite();
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
    protected function patchOxSetup($target)
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
    }

    /**
     * Downloads the oxid archive
     *
     * @param OutputInterface $output
     * @param $url
     * @return string
     */
    protected function downloadOxid(OutputInterface $output, $url)
    {
        $file = sys_get_temp_dir() . '/oxrun-' . time() . '.zip';

        $progressBar = null;

        $client = new Client();

        try {

            $request = $client->createRequest('GET', $url, array('save_to' => $file));
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
     */
    protected function extractArchive(OutputInterface $output, $file, $target)
    {
        $distill = new Distill();
        $output->writeLn("Extracting");
        $distill->extract($file, $target);
    }

}
