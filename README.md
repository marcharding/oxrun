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

Available commands
------------------

##### cache
  - [cache:clear](#cacheclear)   Clear OXID cache
  