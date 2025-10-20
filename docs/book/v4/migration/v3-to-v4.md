# Migration from Version 3 to 4

The ServiceManager 4 introduce new interfaces and updated the factory class structure.

We have [Rector Rules](https://github.com/laminas/laminas-servicemanager-migration) migrations that cover the following changes:

## Rename Interfaces

Below is a quick reference table of interface renames from ServiceManager v3 to v4. These are applied by the migration rules (see the linked Rector rule).

| Before (v3)                                         | After (v4)                                                 |
| --------------------------------------------------- | ---------------------------------------------------------- |
| `Interop\Container\ContainerInterface`              | `Psr\Container\ContainerInterface`                         |
| `Laminas\ServiceManager\AbstractFactoryInterface`   | `Laminas\ServiceManager\Factory\AbstractFactoryInterface`  |
| `Laminas\ServiceManager\FactoryInterface`           | `Laminas\ServiceManager\Factory\FactoryInterface`          |
| `Laminas\ServiceManager\DelegatorFactoryInterface`  | `Laminas\ServiceManager\Factory\DelegatorFactoryInterface` |
| `Laminas\ServiceManager\InitializerInterface`       | `Laminas\ServiceManager\Initializer\InitializerInterface`  |


## Update Factory structure

### Before

```php
use Interop\Container\ContainerInterface;

class ServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {

    }
}
```

### After

```php
use Psr\Container\ContainerInterface;

class ServiceFactory
{
    public function __invoke(ContainerInterface $container)
    {

    }
}
```
