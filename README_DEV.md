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

    docker-compose exec oxid_php70 vendor/bin/phpunit
    
Start `oxrun`

    docker-compose exec oxid_php70 oxrun list

In `docker-compose.yml` you could change the OXID version.
can be found at [github.com](https://github.com/OXID-eSales/oxideshop_project/branches).

    docker-compose.yml: services->oxid_php70->environment->OXID_SHOP_VERSION = 'b-6.1-ce'
