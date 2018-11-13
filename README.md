# Oxrun

[![Build Status](https://travis-ci.org/OXIDprojects/oxrun.svg)](https://travis-ci.org/OXIDprojects/oxrun)
[![Coverage Status](https://coveralls.io/repos/github/OXIDprojects/oxrun/badge.svg?branch=master)](https://coveralls.io/github/OXIDprojects/oxrun?branch=master)

Oxrun provides a cli toolset for the OXID eShop Community Edition.

Thanks to the [netz98 magerun](https://github.com/netz98/n98-magerun) project which heavily inspired oxrun.

## Installation

PHP 7.0 is required.  
OXID v6 is required.

If you are using composer (which you probably are), just add `"OXIDprojects/oxrun": "dev-master"` to your composer.json and run composer install.

You can then use oxrun by calling `vendor/bin/oxrun` or add `vendor/bin` to your $PATH to be able to just call `oxrun`.

You can also install oxrun by simply downloading the phar file from the release tab.

Here is a bash snippet which automatically downloads the latest release from github:

    curl -LOk `curl --silent https://api.github.com/repos/OXIDprojects/oxrun/releases/latest | awk '/browser_download_url/ { print $2 }' | sed 's/"//g'`

You can oxrun now via `php oxrun.phar`

Alternatively you can also make the phar itself executable and copy it to your /usr/local/bin/ directory for global usage.

    chmod +x oxrun.phar
    sudo mv oxrun.phar /usr/local/bin/oxrun

You can then run oxrun by just calling `oxrun`

# Usage

To use oxrun just execute `php oxrun.phar` or `oxrun` (see above).

Execute oxrun inside your OXID eShop base directory (or subdirectory) if you want to interact with an existing shop. It will automatically try to find the oxid boostrap.php and load it.

# Available commands


cache:clear
-----------

* Description: Clears the cache
* Usage:

  * `cache:clear [-f|--force]`

Clears the cache

### Options:

**force:**

* Name: `--force`
* Shortcut: `-f`
* Accept value: no
* Is value required: no
* Description: Try to delete the cache anyway. [danger or permission denied]
* Default: `false`

cms:update
----------

* Description: Updates a cms page
* Usage:

  * `cms:update [--title [TITLE]] [--content [CONTENT]] [--language LANGUAGE] [--active ACTIVE] [--] <ident>`

Updates a cms page

### Arguments:

**ident:**

* Name: ident
* Description: Content ident

### Options:

**title:**

* Name: `--title`
* Is value required: no
* Description: Content title

**content:**

* Name: `--content`
* Is value required: no
* Description: Content body

**language:**

* Name: `--language`
* Is value required: yes
* Description: Content language

**active:**

* Name: `--active`
* Is value required: yes
* Description: Content active

config:get
----------

* Description: Gets a config value
* Usage:

  * `config:get [--shopId [SHOPID]] [--moduleId [MODULEID]] [--] <variableName>`

Gets a config value

### Arguments:

**variableName:**

* Name: variableName
* Description: Variable name

### Options:

**shopId:**

* Name: `--shopId`
* Is value required: no
* Description: <none>

**moduleId:**

* Name: `--moduleId`
* Is value required: no
* Description: <none>

config:set
----------

* Description: Sets a config value
* Usage:

  * `config:set [--variableType VARIABLETYPE] [--shopId [SHOPID]] [--moduleId [MODULEID]] [--] <variableName> <variableValue>`

Sets a config value

### Arguments:

**variableName:**

* Name: variableName
* Description: Variable name

**variableValue:**

* Name: variableValue
* Description: Variable value

### Options:

**variableType:**

* Name: `--variableType`
* Is value required: yes
* Description: Variable type

**shopId:**

* Name: `--shopId`
* Is value required: no
* Description: <none>

**moduleId:**

* Name: `--moduleId`
* Is value required: no
* Description: <none>

config:shop:get
---------------

* Description: Sets a shop config value
* Usage:

  * `config:shop:get [--shopId [SHOPID]] [--] <variableName>`

Sets a shop config value

### Arguments:

**variableName:**

* Name: variableName
* Description: Variable name

### Options:

**shopId:**

* Name: `--shopId`
* Is value required: no
* Description: oxbaseshop
* Default: `'oxbaseshop'`

config:shop:set
---------------

* Description: Sets a shop config value
* Usage:

  * `config:shop:set [--shopId [SHOPID]] [--] <variableName> <variableValue>`

Sets a shop config value

### Arguments:

**variableName:**

* Name: variableName
* Description: Variable name

**variableValue:**

* Name: variableValue
* Description: Variable value

### Options:

**shopId:**

* Name: `--shopId`
* Is value required: no
* Description: oxbaseshop
* Default: `'oxbaseshop'`

db:dump
-------

* Description: Dumps the the current shop database
* Usage:

  * `db:dump [--file FILE] [-t|--table TABLE] [-i|--ignoreViews] [-a|--anonymous] [-w|--withoutTableData WITHOUTTABLEDATA]`

Dump the current shop database.

usage:
    oxrun db:dump --withoutTableData oxseo,oxvou%
    - To dump all Tables, but `oxseo`, `oxvoucher`, and `oxvoucherseries` without data.
      possibilities: oxseo%,oxuser,%logs%
      
    oxrun db:dump --table %user%
    - to dump only those tables `oxuser` `oxuserbasketitems` `oxuserbaskets` `oxuserpayments` 

    oxrun db:dump --anonymous # Perfect for Stage Server
    - Those table without data: `oxseo`, `oxseologs`, `oxseohistory`, `oxuser`, `oxuserbasketitems`, `oxuserbaskets`, `oxuserpayments`, `oxnewssubscribed`, `oxremark`, `oxvouchers`, `oxvoucherseries`, `oxaddress`, `oxorder`, `oxorderarticles`, `oxorderfiles`, `oepaypal_order`, `oepaypal_orderpayments`.
    
    oxrun db:dump -v 
    - With verbose mode you will see the mysqldump command
      (`mysqldump -u 'root' -h 'oxid_db' -p ... `)
      
    oxrun db:dump --file dump.sql 
    - Put the Output into a File
    
** Only existing tables will be exported. No matter what was required.
    
Requires php, exec and MySQL CLI tools installed on your system.

### Options:

**file:**

* Name: `--file`
* Is value required: yes
* Description: Dump sql in to this file

**table:**

* Name: `--table`
* Shortcut: `-t`
* Is value required: yes
* Description: name of table to dump only. Default all tables. Use comma separated list and or pattern e.g. %voucher%

**ignoreViews:**

* Name: `--ignoreViews`
* Shortcut: `-i`
* Accept value: no
* Is value required: no
* Description: Ignore views
* Default: `false`

**anonymous:**

* Name: `--anonymous`
* Shortcut: `-a`
* Accept value: no
* Is value required: no
* Description: Do not export table with person related data.
* Default: `false`

**withoutTableData:**

* Name: `--withoutTableData`
* Shortcut: `-w`
* Is value required: yes
* Description: Table name to dump without data. Use comma separated list and or pattern e.g. %voucher%

db:import
---------

* Description: Import a sql file
* Usage:

  * `db:import <file>`

Imports an SQL file on the current shop database.

Requires php exec and MySQL CLI tools installed on your system.

### Arguments:

**file:**

* Name: file
* Description: The sql file which is to be imported


db:list
-------

* Description: List of all Tables
* Usage:

  * `db:list [-p|--plain] [-t|--pattern PATTERN]`

List Tables

usage:
    oxrun db:list --pattern oxseo%,oxuser
    - To dump all Tables, but `oxseo`, `oxvoucher`, and `oxvoucherseries` without data.
      possibilities: oxseo%,oxuser,%logs%
      


### Options:

**plain:**

* Name: `--plain`
* Shortcut: `-p`
* Accept value: no
* Is value required: no
* Description: print list as comma separated.
* Default: `false`

**pattern:**

* Name: `--pattern`
* Shortcut: `-t`
* Is value required: yes
* Description: table name pattern test. e.g. oxseo%,oxuser

db:query
--------

* Description: Executes a query
* Usage:

  * `db:query [--raw] [--] <query>`

Executes an SQL query on the current shop database. Wrap your SQL in quotes.

If your query produces a result (e.g. a SELECT statement), the output will be returned via the table component. Add the raw option for raw output.

Requires php exec and MySQL CLI tools installed on your system.

### Arguments:

**query:**

* Name: query
* Description: The query which is to be executed

### Options:

**raw:**

* Name: `--raw`
* Accept value: no
* Is value required: no
* Description: Raw output
* Default: `false`

install:shop
------------

* Description: Installs the shop
* Usage:

  * `install:shop [--oxidVersion [OXIDVERSION]] [--installationFolder [INSTALLATIONFOLDER]] [--dbHost DBHOST] [--dbUser DBUSER] [--dbPwd DBPWD] [--dbName DBNAME] [--dbPort [DBPORT]] [--installSampleData [INSTALLSAMPLEDATA]] [--shopURL SHOPURL] [--adminUser ADMINUSER] [--adminPassword ADMINPASSWORD]`

Installs the shop

### Options:

**oxidVersion:**

* Name: `--oxidVersion`
* Is value required: no
* Description: Oxid version

**installationFolder:**

* Name: `--installationFolder`
* Is value required: no
* Description: Installation folder
* Default: `'/var/www/html/source'`

**dbHost:**

* Name: `--dbHost`
* Is value required: yes
* Description: Database host
* Default: `'localhost'`

**dbUser:**

* Name: `--dbUser`
* Is value required: yes
* Description: Database user
* Default: `'oxid'`

**dbPwd:**

* Name: `--dbPwd`
* Is value required: yes
* Description: Database password
* Default: `''`

**dbName:**

* Name: `--dbName`
* Is value required: yes
* Description: Database name
* Default: `'oxid'`

**dbPort:**

* Name: `--dbPort`
* Is value required: no
* Description: Database port
* Default: `3306`

**installSampleData:**

* Name: `--installSampleData`
* Is value required: no
* Description: Install sample data
* Default: `true`

**shopURL:**

* Name: `--shopURL`
* Is value required: yes
* Description: Installation base url

**adminUser:**

* Name: `--adminUser`
* Is value required: yes
* Description: Admin user email/login
* Default: `'admin@example.com'`

**adminPassword:**

* Name: `--adminPassword`
* Is value required: yes
* Description: Admin password
* Default: `'oxid-123456'`

misc:generate:documentation
---------------------------

* Description: Generate a raw command documentation of the available commands
* Usage:

  * `misc:generate:documentation`

Generate a raw command documentation of the available commands

### Arguments:

**command:**

* Name: command
* Description: The command to execute


misc:phpstorm:metadata
----------------------

* Description: Generate a PhpStorm metadata file for auto-completion.
* Usage:

  * `misc:phpstorm:metadata [-o|--output-dir OUTPUT-DIR]`

Generate a PhpStorm metadata file for auto-completion.

### Options:

**output-dir:**

* Name: `--output-dir`
* Shortcut: `-o`
* Is value required: yes
* Description: Writes the metadata for PhpStorm to the specified directory.

module:activate
---------------

* Description: Activates a module
* Usage:

  * `module:activate <module>`

Activates a module

### Arguments:

**module:**

* Name: module
* Description: Module name

module:deactivate
-----------------

* Description: Deactivates a module
* Usage:

  * `module:deactivate <module>`

Deactivates a module

### Arguments:

**module:**

* Name: module
* Description: Module name

module:fix
----------

* Description: Fixes a module
* Usage:

  * `module:fix <module>`

Fixes a module

### Arguments:

**module:**

* Name: module
* Description: Module name

module:generate
---------------

* Description: Generates a module skeleton
* Usage:

  * `module:generate [-s|--skeleton SKELETON] [--name NAME] [--vendor VENDOR] [--description DESCRIPTION] [--author AUTHOR] [--email EMAIL]`

Generates a module skeleton

### Options:

**skeleton:**

* Name: `--skeleton`
* Shortcut: `-s`
* Is value required: yes
* Description: Zip of a Oxid Module Skeleton
* Default: `'https://github.com/OXIDprojects/oxid-module-skeleton/archive/v6_module.zip'`

**name:**

* Name: `--name`
* Is value required: yes
* Description: Module name

**vendor:**

* Name: `--vendor`
* Is value required: yes
* Description: Vendor

**description:**

* Name: `--description`
* Is value required: yes
* Description: Description of your Module: OXID eShop Module ...

**author:**

* Name: `--author`
* Is value required: yes
* Description: Author of Module

**email:**

* Name: `--email`
* Is value required: yes
* Description: Email of Author

module:list
-----------

* Description: Lists all modules
* Usage:

  * `module:list`

Lists all modules

module:reload
-------------

* Description: Deactivate and activate a module
* Usage:

  * `module:reload [-f|--force] [--] <module>`

Deactivate and activate a module

### Arguments:

**module:**

* Name: module
* Description: Module name

### Options:

**force:**

* Name: `--force`
* Shortcut: `-f`
* Accept value: no
* Is value required: no
* Description: Force reload Module
* Default: `false`

route:debug
-----------

* Description: Returns the route. Which controller and parameters are called.
* Usage:

  * `route:debug [-c|--copy] [--] <url>`

Returns the route. Which controller and parameters are called.

### Arguments:

**url:**

* Name: url
* Description: Website URL. Full or Path

### Options:

**copy:**

* Name: `--copy`
* Shortcut: `-c`
* Accept value: no
* Is value required: no
* Description: Copy file path from the class to the clipboard (only MacOS)
* Default: `false`

user:password
-------------

* Description: Sets a new password
* Usage:

  * `user:password <username> <password>`

Sets a new password

### Arguments:

**username:**

* Name: username
* Description: Username

**password:**

* Name: password
* Description: New password

views:update
------------

* Description: Updates the views
* Usage:

  * `views:update`

Updates the views

