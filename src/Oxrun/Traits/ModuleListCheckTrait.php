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
     * 
     * @param string $shopId The shop id.
     * 
     * @return null
     */
    protected function checkModulelist($shopId = null)
    {
        $oConfig = \oxRegistry::getConfig();
        if ($shopId != null) {
            $oConfig->setShopId($shopId);
            \oxRegistry::set('oxConfig', $oConfig);
        }
        // we must call this once, otherwise there are no modules visible in a fresh shop
        $oModuleList = oxNew("oxModuleList");
        $oModuleList->getModulesFromDir($oConfig->getModulesDir());
    }
}
