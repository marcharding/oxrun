<?php

namespace Oxrun\Command\Misc;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class PhpstormMetadataCommand
 *
 * @package Oxrun\Command\Misc
 */
class PhpstormMetadataCommand extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('misc:phpstorm:metadata')->setDescription(
                'Generate a PhpStorm metadata file for auto-completion.'
            )->addOption(
                'output-dir',
                'o',
                InputOption::VALUE_REQUIRED,
                'Writes the metadata for PhpStorm to the specified directory.'
            );
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $searchDirs = [
            OX_BASE_PATH . '../vendor/oxid-esales/oxideshop-ce/source/Application/Component',
            OX_BASE_PATH . '../vendor/oxid-esales/oxideshop-ce/source/Application/Controller',
            OX_BASE_PATH . '../vendor/oxid-esales/oxideshop-ce/source/Application/Model',
            OX_BASE_PATH . '../vendor/oxid-esales/oxideshop-ce/source/Core',
            OX_BASE_PATH . '/modules',
        ];
        $finder     = new Finder();
        $finder
            ->name('*.php')
            ->notPath('tcpdf')
            ->notPath('smarty')
            ->notPath('wysiwigpro')
            ->notPath('phpmailer')
            ->notPath('adodblite')
            ->notName('*_lang.php')
            ->in($searchDirs)
            ->files();

        try {
            $extendedOxidClasses = \OxidEsales\Eshop\Core\Registry::get('oxModuleInstaller')->getModulesWithExtendedClass();
        } catch (\oxSystemComponentException $e) {
            $extendedOxidClasses = \OxidEsales\Eshop\Core\Registry::getConfig()->getAllModules();
        }

        $classes = array();

        $classExtensions = [];

        foreach ($finder as $file) {
            $output->writeln("Processing {$file}...");
            $fileContents = file_get_contents($file);
            if (false === strpos($fileContents, 'class ')) {
                continue;
            }
            $tokens      = token_get_all($fileContents);
            $class_token = false;
            foreach ($tokens as $token) {
                if (is_array($token)) {
                    if ($token[0] == T_CLASS) {
                        $class_token = true;
                    } else {
                        if ($class_token && $token[0] == T_STRING) {
                            $className           = $token[1];
                            $classes[$className] = $className;
                            $normalizedClassName = strtolower($className);

                            if (isset($extendedOxidClasses[$normalizedClassName])) {
                                $oldExtendedClass = $className;
                                foreach ($extendedOxidClasses[$normalizedClassName] as $moduleClass) {
                                    $extendedClassName   = basename($moduleClass);
                                    $classExtensions[]   =
                                        "    class {$extendedClassName}_parent extends \\{$oldExtendedClass} {}";
                                    $oldExtendedClass    = $extendedClassName;
                                    $classes[$className] = $oldExtendedClass;
                                }
                            }

                            $class_token = false;
                        }
                    }
                }
            }
        }

        $STATIC_METHOD_TYPES = '';

        foreach ($classes as $baseClass => $class) {
            $classLowerCase = strtolower($baseClass);
            $STATIC_METHOD_TYPES .= "                '{$classLowerCase}' => \\{$class}::class,\n";
        }

        $metaContent = <<<'EOT'
<?php

/**
 * Used by PhpStorm to map factory methods to classes for code completion, source code analysis, etc.
 *
 * The code is not ever actually executed and it only needed during development when coding with PhpStorm.
 *
 * @see http://confluence.jetbrains.com/display/PhpStorm/PhpStorm+Advanced+Metadata
 * @see http://blog.jetbrains.com/webide/2013/04/phpstorm-6-0-1-eap-build-129-177/
 */

namespace PHPSTORM_META {
    override(
        \oxNew(0),
        map(
            [
                CLASSES_PLACEHOLDER
            ]
        )
    );
    override(
        \OxidEsales\Eshop\Core\Registry::get(0),
        map(
            [
                CLASSES_PLACEHOLDER
            ]
        )
    );
}

namespace {

EXTENDS_PLACEHOLDER

}
EOT;

        $metaContent = str_replace('CLASSES_PLACEHOLDER', trim($STATIC_METHOD_TYPES), $metaContent);
        $metaContent = str_replace('EXTENDS_PLACEHOLDER', implode("\n\n", $classExtensions), $metaContent);

        if ($input->hasOption('output-dir') && (null !== ($outputDir = $input->getOption('output-dir')))) {
            $phpstormMetaDir = "{$outputDir}/.phpstorm.meta.php";
            $phpstormMetaFile = "{$phpstormMetaDir}/oxid.meta.php";

            if (is_file($phpstormMetaDir)) {
                unlink($phpstormMetaDir);
            }

            if (!is_dir($phpstormMetaDir)) {
                mkdir($phpstormMetaDir, 0777, true);
            }

            file_put_contents($phpstormMetaFile, $metaContent);
            return;
        }

        $output->writeln($metaContent);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getApplication()->bootstrapOxid();
    }
}
