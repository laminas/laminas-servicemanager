# PSR-11 Support

## Standard Support

Version 4.0 of laminas-servicemanager supports version 1.1 and 2 of [PSR-11: Container interface](https://www.php-fig.org/psr/psr-11/), and has update the various factory interfaces and exception implementations to typehint against the PSR-11 interfaces.

## Migrating Code to laminas-servicemanager 4.x Compatibility

To migrate code to be compatible with laminas-servicemanager 4.x, [laminas-servicemanager-migration](https://docs.laminas.dev/laminas-servicemanager-migration/) can be used.
This package provides a set of rules based on Rector that can be used to migrate code.
