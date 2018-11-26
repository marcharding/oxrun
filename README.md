# Oxrun

[![Build Status](https://travis-ci.org/OXIDprojects/oxrun.svg)](https://travis-ci.org/OXIDprojects/oxrun)
[![Coverage Status](https://coveralls.io/repos/github/OXIDprojects/oxrun/badge.svg?branch=master)](https://coveralls.io/github/OXIDprojects/oxrun?branch=master)

Oxrun provides a cli toolset for the OXID eShop Community Edition.

Thanks to the [netz98 magerun](https://github.com/netz98/n98-magerun) project which heavily inspired oxrun.

Copyright (c) 2015 Marc Harding http://www.marcharding.de (https://github.com/marcharding/oxrun)

Copyright (c) 2018 Tobias Matthaiou http://www.tobimat.eu

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

views:update
------------

* Description: Updates the views
* Usage:

  * `views:update`

Updates the views

### Options:

