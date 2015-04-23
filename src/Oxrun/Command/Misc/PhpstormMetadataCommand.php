<?php

namespace Oxrun\Command\Misc;

use Oxrun\Helper\ClassExtractor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PhpstormMetadataCommand
 * @package Oxrun\Command\Misc
 */
class PhpstormMetadataCommand extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('misc:phpstorm:metadata')
            ->setDescription('Generate a PhpStorm metadata file for autocompletion');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $iterator = new ClassExtractor(OX_BASE_PATH);

        $classes = array();

        foreach ($iterator as $file) {

            $tokens = token_get_all(file_get_contents($file));
            $class_token = false;
            foreach ($tokens as $token) {
                if (is_array($token)) {
                    if ($token[0] == T_CLASS) {
                        $class_token = true;
                    } else {
                        if ($class_token && $token[0] == T_STRING) {
                            $classes[$token[1]] = $token[1];
                            $class_token = false;
                        }
                    }
                }
            }
        }

        $STATIC_METHOD_TYPES = '';

        foreach ($classes as $class) {
            $classLowerCase = strtolower($class);
            $STATIC_METHOD_TYPES .= "            '$classLowerCase' instanceof \\$class," . PHP_EOL;
        }

        $phpstorm = <<<'EOT'
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

    /** @noinspection PhpUnusedLocalVariableInspection */
    /** @noinspection PhpIllegalArrayKeyTypeInspection */

    $STATIC_METHOD_TYPES = array(
        \oxNew => array(
            PLACEHOLDER
        )
    );

}
EOT;

        $output->writeln(str_replace('PLACEHOLDER', $STATIC_METHOD_TYPES, $phpstorm));
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getApplication()->bootstrapOxid();
    }
}
