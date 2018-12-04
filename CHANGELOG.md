# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]


## 3.2.0 - 2018-12-02

### Added

- oxrun can share his command with other cli tools like [ps:console](https://github.com/OXIDprojects/oxid-console), [oxid:console](https://github.com/OXID-eSales/oxideshop_ce/tree/b-6.x-introduce_console-OXDEV-1580) 

### Removed

- Remove OXID version switch. Command module:activate and module:deactivate works >v6.x

## 3.1.0 - 2018-11-28

### Added

- It can now take more commands from other packages. With a services.yml

### Changed

- development environment with docker for OXID v6x

### Removed

- Fix Command. Is now write by [oxid-module-internals]https://github.com/OXIDprojects/oxid-module-internals
- Install Command. Because this could only install the v4x


## 3.0.1 - 2018-11-13

### Added

- Registered at packagist.org

### Changed

- Hotfix will be required


## 3.0.0 - 2018-11-13

### Added

- New Command `module:generate`

### Changed

- Oxrun will only be developed for OXID eShop v6.x
- Test change to PHPUnit v6

### Removed

- Ioly Command
