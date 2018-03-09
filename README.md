# Oxrun

[![Build Status](https://travis-ci.org/marcharding/oxrun.svg)](https://travis-ci.org/marcharding/oxrun)
[![Coverage Status](https://coveralls.io/repos/github/marcharding/oxrun/badge.svg?branch=master)](https://coveralls.io/github/marcharding/oxrun?branch=master)

Oxrun provides a cli toolset for the OXID eShop Community Edition.

Thanks to the [netz98 magerun](https://github.com/netz98/n98-magerun) project which heavily inspired oxrun.

## Installation

__Disclaimer:__ This fork is intended for __usage with OXID 6.x__ and up, it will not be compatible with older shop versions. Legacy commands will be removed, e.g. 
"install:shop" which is now handled via Composer.


PHP 5.6 is required, PHP 7 or newer is recommended.

If you are using composer (which you probably are), just add `"smxsm/oxrun": "dev-develop"` to your composer.json and run composer install.

You can then use oxrun by calling `vendor/bin/oxrun` or add `vendor/bin` to your $PATH to be able to just call `oxrun`.

# Usage

To use oxrun just execute `php oxrun.phar` or `./vendor/bin/oxrun` (see above).

Execute oxrun inside your OXID eShop base directory (or subdirectory) if you want to interact with an existing shop. It will automatically try to find the oxid boostrap.php and load it.

If you want to __run it from a different directory__, you have to add the option `"--shopDir=/path/to/your/shop"`.

You can use it e.g. to help you with an OXID 6 installation or deployment, e.g.:

```json
  "scripts": {
    "post-update-cmd": [
      "Incenteev\\ParameterHandler\\ScriptHandler::buildParameters",
      "@oe:ide-helper:generate",
      "@oxrun:activate-modules",
      "@oxrun:set-config"
    ],
    "oxrun:activate-modules": [
      "./vendor/bin/oxrun module:multiactivate configs/modules.yml -c --shopDir=./source"
    ],
    "oxrun:set-config": [
      "./vendor/bin/oxrun config:multiset configs/malls.yml --shopDir=./source"
    ],

```

This could activate some modules in different subshops and set a bunch of config variables e.g.

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

config:multiset
----------

* Description: Sets multiple config values for multiple subshops, defined in a yaml file
* Usage: `config:multiset configfile`

### Arguments:

**configfile:**

The file containing the config values, see malls.yml.dist. The file path is relative to the shop root.

```yaml
config:
  1:
    blReverseProxyActive: 
      variableType: bool
      variableValue: false
    # simple string type
    sMallShopURL: http://myshop.dev.local
    sMallSSLShopURL: http://myshop.dev.local
    myMultiVal:
      variableType: aarr
      variableValue:
        - /foo/bar/
        - /bar/foo/
      # optional module id
      moduleId: my_module
  2:
    blReverseProxyActive: 
...
```

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

config:export
---------------

* Description: Exports all config values as yaml files, interacts with the [Modules Config](https://github.com/OXIDprojects/oxid_modules_config/) module
* Usage: `config:export`

### Arguments:

### Options:

**no-debug:**

* Name: `--no-debug`
* Is value required: no
* Description: No debug ouput

**env:**

* Name: `--env`
* Is value required: no
* Description: set specific environment, corresponds to a specific folder for the yaml files
* Example: `--env=stage`

**force-cleanup:**

* Name: `--force-cleanup`
* Is value required: no
* Description: Force cleanup on error

config:import
---------------

* Description: Imports all config values from yaml files, interacts with the [Modules Config](https://github.com/OXIDprojects/oxid_modules_config/) module
* Usage: `config:import`

### Arguments:

### Options:

**no-debug:**

* Name: `--no-debug`
* Is value required: no
* Description: No debug ouput

**env:**

* Name: `--env`
* Is value required: no
* Description: set specific environment, corresponds to a specific folder for the yaml files
* Example: `--env=stage`

**force-cleanup:**

* Name: `--force-cleanup`
* Is value required: no
* Description: Force cleanup on error

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

**shopId:**

* Name: `--shopId`
* Is value required: no
* Description: <none>

module:deactivate
-----------------

* Description: Deactivates a module
* Usage: `module:deactivate module`

### Arguments:

**module:**

* Name: module
* Description: Module name

### Options:

**shopId:**

* Name: `--shopId`
* Is value required: no
* Description: <none>

module:multiactivate
---------------

* Description: Activates multiple modules, based on a YAML file
* Usage: `module:multiactivate <yamlfile.yml>`

### Arguments:

**modulefile:**

* Name: modulefile
* Description: Module definition file name, e.g. "modules.yml", relative to the shop base dir.

Example:

```yaml
whitelist:
  1:
    - ocb_cleartmp
    - moduleinternals
    #- ddoevisualcms
    #- ddoewysiwyg
  2:
    - ocb_cleartmp
```

Supports either a __"whitelist"__ or a __"blacklist"__ entry with multiple shop ids and the desired module ids to activate (whitelist) or to exclude from activation (blacklist).

### Options:

**shopId:**

* Name: `--shopId`
* Is value required: no
* Description: The subshop id

**skipDeactivation:**

* Name: `--skipDeactivation` or `-s`
* Is value required: no
* Description: skip deactivation, only activate the modules

**skipClear:**

* Name: `--skipClear` or `-c`
* Is value required: no
* Description: skip cache clearing between deactivation and activation

module:fix
----------

* Description: Fixes a module
* Usage: `module:fix module`
* __NOT IMPLEMENTED YET!__

### Arguments:

**module:**

* Name: module
* Description: Module name

### Options:

**shopId:**

* Name: `--shopId`
* Is value required: no
* Description: <none>

module:generate
---------------

* Description: Generates a module skeleton
* Usage: `module:generate module`
* __NOT IMPLEMENTED YET!__

### Arguments:

**module:**

* Name: module
* Description: Module name

### Options:

**shopId:**

* Name: `--shopId`
* Is value required: no
* Description: <none>

module:list
-----------

* Description: Lists all modules
* Usage: `module:list`

### Options:

**shopId:**

* Name: `--shopId`
* Is value required: no
* Description: <none>

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
