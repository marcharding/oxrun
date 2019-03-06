<?php
    // Database connection information
    $this->dbType = 'pdo_mysql';
    $this->dbHost = '127.0.0.1'; // database host name
    $this->dbPort  = 3306; // tcp port to which the database is bound
    $this->dbName = 'oxid'; // database name
    $this->dbUser = 'root'; // database user name
    $this->dbPwd  = ''; // database user password
    $this->sShopURL     = 'http://localhost'; // eShop base url, required
    $this->sSSLShopURL  = null;            // eShop SSL url, optional
    $this->sAdminSSLURL = null;            // eShop Admin SSL url, optional
    $this->sShopDir     = __DIR__.'/../oxid-esale/source';
    $this->sCompileDir  = __DIR__.'/../oxid-esale/source/tmp';

