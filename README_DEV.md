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

    docker-compose exec oxid_php70 /oxrun/vendor/bin/phpunit -c /oxrun/phpunit.xml
    
Start `oxrun`

    docker-compose exec oxid_php70 oxrun list

In `docker-compose.yml` you could change the OXID version.
can be found at [github.com](https://github.com/OXID-eSales/oxideshop_project/branches).

    docker-compose.yml: services.oxid_php70.environment.OXID_SHOP_VERSION = 'b-6.1-ce'

OXID eShop
==========

The source code of OXID eShop is, outside of container, inside in the folder of `./oxid-esale/`
    
    Container Struckture
    /
    |-- usr
    |   |-- local
    |   |   |-- bin
    |           -- oxrun -> /oxrun/bin/oxrun
    |
    |-- data                # mount volume ./oxid-esale
    |   |-- composer.json
    |   |-- source          # Apache Document root | Container workdir
    |   |-- vendor
    |
    |-- oxrun               # mount volume ./
    |   |-- README.md
    |   |-- phpunit.xml
    |   |-- bin
    |       -- oxrun
    |   |-- src
    |   |-- tests
    |   |-- vendor
    |   |   | -- bin
    |            -- phpunit
