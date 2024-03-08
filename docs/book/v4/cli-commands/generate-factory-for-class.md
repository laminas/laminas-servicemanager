# Generate Factory for Class

```bash
$ ./vendor/bin/laminas servicemanager:generate-factory-for-class -h
Description:
  Generates to STDOUT a factory for creating the specified class; this may then be added to your application, and configured as a factory for the class.

Usage:
  servicemanager:generate-factory-for-class <className>

Arguments:
  className                  Name of the class to reflect and for which to generate a factory.

Options:
  -q, --quiet                Do not output any message
```

This utility generates a factory class for the given class, based on the
typehints in its constructor. The factory is emitted to STDOUT, and may be piped
to a file if desired:

```bash
$ ./vendor/bin/laminas servicemanager:generate-factory-for-class \
> "Application\\Model\\AlbumModel" > ./module/Application/src/Model/AlbumModelFactory.php
```

The class generated implements `Laminas\ServiceManager\Factory\FactoryInterface`,
and is generated within the same namespace as the originating class.