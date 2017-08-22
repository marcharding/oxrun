<?php
/**
 * Created for oxrun
 * Author: Tobias Matthaiou <matthaiou@tobimat.eu>
 */


namespace Oxrun\Traits;

/**
 * Class ModuleListCheck
 * @package OxrunTrait
 */
trait ModuleListCheckTrait
{

    /**
     * Check if we have Modulelist, else create new.
     */
    protected function checkModulelist()
    {
        // we must call this once, otherwise there are no modules visible in a fresh shop
        $oModuleList = oxNew("oxModuleList");
        $oModuleList->getModulesFromDir(\oxRegistry::getConfig()->getModulesDir());
    }
}
