# Oxrun

[![Build Status](https://travis-ci.org/marcharding/oxrun.svg)](https://travis-ci.org/marcharding/oxrun)
[![Coverage Status](https://coveralls.io/repos/github/marcharding/oxrun/badge.svg?branch=master)](https://coveralls.io/github/marcharding/oxrun?branch=master)

Oxrun provides a cli toolset for the OXID eShop Community Edition.

Thanks to the [netz98 magerun](https://github.com/netz98/n98-magerun) project which heavily inspired oxrun.

## Installation

PHP 5.6 is required.

If you are using composer (which you probably are), just add `"marcharding/oxrun": "dev-master"` to your composer.json and run composer install.

You can then use oxrun by calling `vendor/bin/oxrun` or add `vendor/bin` to your $PATH to be able to just call `oxrun`.

You can also install oxrun by simply downloading the phar file from the release tab.

Here is a bash snippet which automatically downloads the latest release from github:

    curl -LOk `curl --silent https://api.github.com/repos/marcharding/oxrun/releases/latest | awk '/browser_download_url/ { print $2 }' | sed 's/"//g'`

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
* Usage: `cache:clear`

### Options:

cms:update
----------

* Description: Updates a cms page
* Usage: `cms:update [--title[="..."]] [--content[="..."]] [--language="..."] [--active="..."] ident`

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
* Usage: `config:get [--shopId[="..."]] [--moduleId[="..."]] variableName`

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
* Usage: `config:set [--variableType="..."] [--shopId[="..."]] [--moduleId[="..."]] variableName variableValue`

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
* Usage: `config:shop:get [--shopId[="..."]] variableName`

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
* Usage: `config:shop:set [--shopId[="..."]] variableName variableValue`

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
* Usage: `db:dump [--file="..."]`

Dumps the the current shop database.

Requires php exec and MySQL CLI tools installed on your system.

### Options:

**file:**

* Name: `--file`
* Is value required: yes
* Description: Dump sql in to this file

db:import
---------

* Description: Import a sql file
* Usage: `db:import file`

Imports an SQL file on the current shop database.

Requires php exec and MySQL CLI tools installed on your system.

### Arguments:

**file:**

* Name: file
* Description: The sql file which is to be imported

### Options:

db:query
--------

* Description: Executes a query
* Usage: `db:query [--raw] query`

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
* Usage: `install:shop [--oxidVersion[="..."]] [--installationFolder[="..."]] [--dbHost="..."] [--dbUser="..."] [--dbPwd="..."] [--dbName="..."] [--dbPort[="..."]] [--installSampleData[="..."]] [--shopURL="..."] [--adminUser="..."] [--adminPassword="..."]`

### Options:

**oxidVersion:**

* Name: `--oxidVersion`
* Is value required: no
* Description: Oxid version
* Default: `'v4.9.5'`

**installationFolder:**

* Name: `--installationFolder`
* Is value required: no
* Description: Installation folder
* Default: `'/vagrant/web/oxrun'`

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

**adminPassword:**

* Name: `--adminPassword`
* Is value required: yes
* Description: Admin password

misc:generate:documentation
---------------------------

* Description: Generate a raw command documentation of the available commands
* Usage: `misc:generate:documentation`

### Arguments:

**command:**

* Name: command
* Description: The command to execute

### Options:

misc:phpstorm:metadata
----------------------

* Description: Generate a PhpStorm metadata file for autocompletion
* Usage: `misc:phpstorm:metadata`

### Options:

**output-dir:**

* Name: `--output-dir`, `-o`
* Accept value: yes
* Is value required: yes
* Description: Writes the metadata for PhpStorm to the specified directory.

module:activate
---------------

* Description: Activates a module
* Usage: `module:activate module`

### Arguments:

**module:**

* Name: module
* Description: Module name

### Options:

module:deactivate
-----------------

* Description: Deactivates a module
* Usage: `module:deactivate module`

### Arguments:

**module:**

* Name: module
* Description: Module name

### Options:

module:fix
----------

* Description: Fixes a module
* Usage: `module:fix module`

### Arguments:

**module:**

* Name: module
* Description: Module name

### Options:

module:generate
---------------

* Description: Generates a module skeleton
* Usage: `module:generate module`

### Arguments:

**module:**

* Name: module
* Description: Module name

### Options:

module:list
-----------

* Description: Lists all modules
* Usage: `module:list`

### Options:

user:password
-------------

* Description: Sets a new password
* Usage: `user:password username password`

### Arguments:

**username:**

* Name: username
* Description: Username

**password:**

* Name: password
* Description: New password

### Options:

views:update
------------

* Description: Updates the views
* Usage: `views:update`
