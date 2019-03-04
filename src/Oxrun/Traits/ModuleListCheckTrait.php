<?php
/**
 * Created for oxrun
 * Author: Tobias Matthaiou <matthaiou@tobimat.eu>
 */


namespace Oxrun\Traits;

use OxidEsales\Eshop\Core\Registry;
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
     * @return void
     */
    protected function checkModulelist($shopId = null)
    {
        $oConfig = Registry::getConfig();
        if ($shopId != null) {
            $oConfig->setShopId($shopId);
            $oConfig->reinitialize();
        }
        // we must call this once, otherwise there are no modules visible in a fresh shop
        $oModuleList = oxNew(\OxidEsales\Eshop\Core\Module\ModuleList::class);
        $oModuleList->getModulesFromDir($oConfig->getModulesDir());
    }
}
