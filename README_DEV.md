FOR DEVELOPER
=============

PHPUnit Test are required. The PHPUnit could be started via Docker. 
To be successful, you need a ready-configured OXID with DB connection.
The tool must be located in the source code of the OXID Framework.

Docker
======
   
Docker start. The image installs itself OXID and DB.

    docker-compose up -d
    
Start PHPUnit test

    docker-compose exec php5.6 vendor/bin/phpunit
    
Start `oxrun`

    docker-compose exec php5.6 oxrun list

In `docker-compose.yml` you could change the OXID version.
OXID v6 works not yet currently . A list of versions 
can be found at [github.com](https://github.com/OXID-eSales/oxideshop_ce/tags?per_page=9999).

    docker-compose.yml: services->php5.6->environment->OXID_SHOP_VERSION = 'v4.9.11'
