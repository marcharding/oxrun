<?php

/**
 * Class OxidFileConfig
 *
 * This Class can read the config.inc.php without to load OXID Framework
 */
class OxidFileConfig
{
    /**
     * oxConfigFileReader constructor.
     */
    public function __construct()
    {
        $ds = DIRECTORY_SEPARATOR;
        $oxid_base_dir = __DIR__ . "$ds..$ds..$ds";
        // _loadConfig
        include $oxid_base_dir . 'config.inc.php';
        // _loadCustomConfig
        if (file_exists($oxid_base_dir.'cust_config.inc.php')) {
            include $oxid_base_dir . 'cust_config.inc.php';
        }
    }
}
