# Oxrun

[![Build Status](https://travis-ci.org/OXIDprojects/oxrun.svg?branch=master)](https://travis-ci.org/OXIDprojects/oxrun)
[![Coverage Status](https://coveralls.io/repos/github/OXIDprojects/oxrun/badge.svg?branch=master)](https://coveralls.io/github/OXIDprojects/oxrun?branch=master)

Oxrun provides a cli toolset for the OXID eShop Community Edition.

Thanks to the [netz98 magerun](https://github.com/netz98/n98-magerun) project which heavily inspired oxrun.

Copyright (c) 2015 Marc Harding http://www.marcharding.de (https://github.com/marcharding/oxrun)

Copyright (c) 2018 Tobias Matthaiou http://www.tobimat.eu

Copyright (c) 2018 Stefan Moises https://www.rent-a-hero.de/

## Installation

PHP 7.0 is required.  
OXID v6 is required.

##### 1. As a separate command `oxrun`

Here is a bash snippet which automatically downloads the latest release from github:

    curl -LOk `curl --silent https://api.github.com/repos/OXIDprojects/oxrun/releases/latest | awk '/browser_download_url/ { print $2 }' | sed 's/"//g'`

You can oxrun now via `php oxrun.phar`

Alternatively you can also make the phar itself executable and copy it to your /usr/local/bin/ directory for global usage.

    chmod +x oxrun.phar
    sudo mv oxrun.phar /usr/local/bin/oxrun

You can then run oxrun by just calling `oxrun`

##### 2. As part of the OXID eShop installation `vendor/bin/oxrun`
 
`composer require oxidprojects/oxrun`.

You can then use oxrun by calling `vendor/bin/oxrun` or add `vendor/bin` to your $PATH to be able to just call `oxrun`.

You can also install oxrun by simply downloading the phar file from the release tab.


# Usage

To use oxrun just execute `php oxrun.phar` or `oxrun` (see above).

Execute oxrun inside your OXID eShop base directory (or subdirectory) if you want to interact with an existing shop. It will automatically try to find the oxid boostrap.php and load it.

# Defining your own command

It is possible to adding new console command via `services.yaml` file. For this you have to deposit in your repo a `/services.yaml` file
and install it with composer.

That's how she looks

```yaml
    services:
      company_name.project.command.hello_world:
        class: OxidEsales\DemoComponent\Command\HelloWorldCommand
        tags:
          - { name: 'console.command' }
```

[Template for your command](https://github.com/OXIDprojects/oxrun/blob/composer-command-collector/tests/Oxrun/CommandCollection/testData/HelloWorldCommand.php)

Example: https://github.com/OXIDprojects/oxid-module-internals/blob/master/services.yaml

# Available commands

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

### Options:

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

db:anonymize
------------

* Description: Anonymize relevant OXID db tables
* Usage:

  * `db:anonymize [--debug] [-d|--domain [DOMAIN]] [-k|--keepdomain [KEEPDOMAIN]]`

Anonymizes user relevant data in the OXID database.
Relevant tables are:
Array
(
    [0] => oxnewssubscribed
    [1] => oxuser
    [2] => oxvouchers
    [3] => oxaddress
    [4] => oxorder
)


### Options:

**debug:**

* Name: `--debug`
* Accept value: no
* Is value required: no
* Description: Debug SQL queries generated
* Default: `false`

**domain:**

* Name: `--domain`
* Shortcut: `-d`
* Is value required: no
* Description: Domain to use for all anonymized usernames /email addresses, default is "@oxrun.com"

**keepdomain:**

* Name: `--keepdomain`
* Shortcut: `-k`
* Is value required: no
* Description: Domain which should NOT be anonymized, default is "@foobar.com". Data with this domain in the email address will NOT be anonymized.

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

### Options:

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

db:dump
-------

* Description: Create a dump, with mysqldump
* Usage:

  * `db:dump [--file FILE] [-t|--table TABLE] [-i|--ignoreViews] [-a|--anonymous] [-w|--withoutTableData WITHOUTTABLEDATA]`

Create a dump from the current database.

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
    
## System requirement:

    * php
    * MySQL CLI tools.


### Options:

**file:**

* Name: `--file`
* Is value required: yes
* Description: Save dump at this location.

**table:**

* Name: `--table`
* Shortcut: `-t`
* Is value required: yes
* Description: Only names of tables are dumped. Default all tables. Use comma separated list and or pattern e.g. %voucher%

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
* Description: Export not table with person related data.
* Default: `false`

**withoutTableData:**

* Name: `--withoutTableData`
* Shortcut: `-w`
* Is value required: yes
* Description: Export tables only with their CREATE statement. So without content. Use comma separated list and or pattern e.g. %voucher%

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

config:get
----------

* Description: Gets a config value
* Usage:

  * `config:get [--moduleId [MODULEID]] [--] <variableName>`

Gets a config value

### Arguments:

**variableName:**

* Name: variableName
* Description: Variable name

### Options:

**moduleId:**

* Name: `--moduleId`
* Is value required: no
* Description: <none>

config:shop:set
---------------

* Description: Sets a shop config value
* Usage:

  * `config:shop:set <variableName> <variableValue>`

Sets a shop config value

### Arguments:

**variableName:**

* Name: variableName
* Description: Variable name

**variableValue:**

* Name: variableValue
* Description: Variable value

### Options:

config:set
----------

* Description: Sets a config value
* Usage:

  * `config:set [--variableType VARIABLETYPE] [--moduleId [MODULEID]] [--] <variableName> <variableValue>`

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

**moduleId:**

* Name: `--moduleId`
* Is value required: no
* Description: <none>

config:multiset
---------------

* Description: Sets multiple config values from yaml file
* Usage:

  * `config:multiset <configfile>`

YAML example:
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
[Example: malls.yml.dist](example/malls.yml.dist)

If you want, you can also specify __a YAML string on the command line instead of a file__, e.g.:

```bash
../vendor/bin/oxrun module:multiset $'config:
  1:
    foobar: barfoo
' --shopId=1
```    

### Arguments:

**configfile:**

* Name: configfile
* Description: The file containing the config values, see example/malls.yml.dist. The file path is relative to the shop installation_root_path/oxrun_config/. You can also pass a YAML string on the command line.

### Options:

config:shop:get
---------------

* Description: Sets a shop config value
* Usage:

  * `config:shop:get <variableName>`

Sets a shop config value

### Arguments:

**variableName:**

* Name: variableName
* Description: Variable name

### Options:

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

module:multiactivate
--------------------

* Description: Activates multiple modules, based on a YAML file
* Usage:

  * `module:multiactivate [-s|--skipDeactivation] [-c|--skipClear] [--] <module>`

usage:
oxrun module:multiactivate configs/modules.yml
- to activate all modules defined in the YAML file. [Example: modules.yml.dist](example/modules.yml.dist) based
on a white- or blacklist

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

If you want, you can also specify __a YAML string on the command line instead of a file__, e.g.:

```bash
../vendor/bin/oxrun module:multiactivate $'whitelist:
  1:
    - oepaypal
' --shopId=1
```

### Arguments:

**module:**

* Name: module
* Description: YAML module list filename or YAML string. The file path is relative to the shop installation_root_path/oxrun_config/

### Options:

**skipDeactivation:**

* Name: `--skipDeactivation`
* Shortcut: `-s`
* Accept value: no
* Is value required: no
* Description: Skip deactivation of modules, only activate.
* Default: `false`

**skipClear:**

* Name: `--skipClear`
* Shortcut: `-c`
* Accept value: no
* Is value required: no
* Description: Skip cache clearing.
* Default: `false`

module:list
-----------

* Description: Lists all modules
* Usage:

  * `module:list`

Lists all modules

### Options:

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

### Options:

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

### Options:

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

oxid:shops
----------

* Description: Lists the shops
* Usage:

  * `oxid:shops`

Lists the shops

### Options:

user:create
-----------

* Description: Creates a new user
* Usage:

  * `user:create`

Creates a new user

### Options:

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

### Options:

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

log:exceptionlog
----------------

* Description: Read EXCEPTION_LOG.txt and display entries.
* Usage:

  * `log:exceptionlog [-l|--lines [LINES]] [-f|--filter [FILTER]] [-r|--raw] [-t|--tail]`

Read EXCEPTION_LOG.txt and display entries.

### Options:

**lines:**

* Name: `--lines`
* Shortcut: `-l`
* Is value required: no
* Description: Number of lines to show

**filter:**

* Name: `--filter`
* Shortcut: `-f`
* Is value required: no
* Description: Filter string to search for

**raw:**

* Name: `--raw`
* Shortcut: `-r`
* Accept value: no
* Is value required: no
* Description: Show raw text, no table
* Default: `false`

**tail:**

* Name: `--tail`
* Shortcut: `-t`
* Accept value: no
* Is value required: no
* Description: Show last lines first
* Default: `false`

views:update
------------

* Description: Updates the views
* Usage:

  * `views:update`

Updates the views

### Options:

