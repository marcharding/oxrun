<?php
/**
 * Created by PhpStorm.
 * Autor: Tobias Matthaiou
 * Date: 2019-03-29
 * Time: 07:09
 */

namespace Oxrun\Helper;

use OxidEsales\Eshop\Core\Registry;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Finder\SplFileInfo as SymfonyFileInfo;
use Symfony\Component\Yaml\Yaml;

/**
 * Class MultiSetTranslator
 * @package Oxrun\Helper
 */
class MultiSetTranslator
{
    /**
     * @var SymfonyFileInfo
     */
    private $configPath;

    /**
     * @var string
     */
    private $ymltxt = '';

    /**
     * @param string $filepath
     * @param integer $langId
     *
     * @return $this
     */
    public function configFile($filepath, $langId)
    {
        $this->configPath = new SymfonyFileInfo($filepath, null, null);

        if ($this->configPath->isFile() == false) {
            throw new FileNotFoundException($filepath);
        }

        $yamltxt = $this->configPath->getContents();
        $configs = Yaml::parse($yamltxt);
        if (!isset($configs['config']) || !is_array($configs['config'])) {
            throw new \Exception($this->configPath->getBasename() . ' has not a config section');
        }

        $configs = $configs['config'];

        $language = Registry::getLang();
        $translated = [];

        foreach ($configs as $shopId) {
            foreach ($shopId as $varname => $value) {
                $searchName = "    {$varname}:";

                //Don't translate agean
                if (isset($translated[$searchName])) {
                    continue;
                }

                // Shop
                $trans = 'SHOP_CONFIG_' . $this->trimUngarnNotation($varname);
                $description = $language->translateString($trans, $langId, true);

                // Module Translate
                if ($language->isTranslated() == false) {
                    $trans = 'SHOP_MODULE_' . $varname;
                    $description = $language->translateString($trans, $langId, true);
                }

                // Plain Admin
                if ($language->isTranslated() == false) {
                    $trans = $varname;
                    $description = $language->translateString($trans, $langId, true);
                }

                // Plain Shop
                if ($language->isTranslated() == false) {
                    $trans = $varname;
                    $description = $language->translateString($trans, $langId, false);
                }

                // Nothing found
                if ($language->isTranslated() == false) {
                    $translated[$searchName] = $searchName;
                    continue;
                }

                $translated[$searchName] = "    # {$description}\n{$searchName}" ;
            }
        }

        $this->ymltxt = str_replace(array_keys($translated), array_values($translated), $yamltxt);

        return $this;
    }

    /**
     * Save config file
     */
    public function save()
    {
        if ($this->ymltxt) {
            $filename = $this->configPath->getPathname();
            file_put_contents($filename, $this->ymltxt);
        }
    }

    /**
     * @param $string
     * @return string in Uppercase
     */
    protected function trimUngarnNotation($string)
    {
        if (preg_match('/([A-Z].+)/', $string, $match)) {
            return strtoupper($match[1]);
        }
        return strtoupper($string);
    }
}
