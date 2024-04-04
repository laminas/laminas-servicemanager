# Reflection Factory

## Service Auto Wiring

Writing a factory class for each and every service that has dependencies can be tedious, particularly in early development as you are still sorting out dependencies.

To alleviate this issue, laminas-servicemanager ships with the `ReflectionBasedAbstractFactory`, which provides a [reflection-based approach](https://www.php.net/manual/intro.reflection.php) to instantiation, resolving constructor dependencies to the relevant services.

The factory operates with the following features/constraints:

- A parameter named `$config` type-hinted as an array will receive the application "config" service (i.e., the merged configuration).
- Parameters type-hinted against array, but not named `$config`, will be injected with an empty array.
- Scalar parameters will result in the factory raising an exception, unless a default value is present; if it is, that value will be used.
- If a service cannot be found for a given typehint, the factory will raise an exception detailing this.

WARNING: `$options` passed to the factory are ignored in all cases, as we cannot make assumptions about which argument(s) they might replace.

## Usage

The factory may be used as either an abstract factory or mapped to specific service names as a factory.
The following sections detail how to configure the `ReflectionBasedAbstractFactory` for each use case.

### Usage as an Abstract Factory

To use the `ReflectionBasedAbstractFactory` as an [abstract factory](configuring-the-service-manager.md#abstract-factories), add it to the `abstract_factories` configuration:

```php
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;

return [
    // …
    'service_manager' => [
        'abstract_factories' => [
            ReflectionBasedAbstractFactory::class,
        ],
    ],
    // …
];
```

With this configuration, any service that **cannot be found** in the service manager will be created using the `ReflectionBasedAbstractFactory`.

### Usage as a Factory

To use the `ReflectionBasedAbstractFactory` as a factory for a specific service, map it to the service name in the `factories` configuration:

```php
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;

return [
    // …
    'service_manager' => [
        'factories' => [
            'MyModule\Model\FooModel' => ReflectionBasedAbstractFactory::class,
        ],
    ],
    // …
];
```

With this configuration, the `ReflectionBasedAbstractFactory` will be used to create the `MyModule\Model\FooModel` service.

## Performance Improvements

Once the dependencies have stabilized and when performance is a requirement, it is recommended writing a dedicated factory, as reflection _can_ introduce performance overhead.
There are two ways to provide dedicated factories for services consuming `ReflectionBasedAbstractFactory`:

1. Usage of the [generate-factory-for-class CLI command](cli-commands/generate-factory-for-class.md) (this will also require to manually modify the configuration)
2. Usage of the [generate-aot-factories CLI command](cli-commands/generate-ahead-of-time-factories.md) which needs an initial project + deployment setup

TIP:
In many applications, the `ReflectionBasedAbstractFactory` can also be used on productive systems without any problems or losses, so there is no need to create or generate dedicated factories.

## Alternatives

It is also possible to use the [Config Abstract Factory](config-abstract-factory.md), which is slightly more flexibility in terms of mapping dependencies:

- If you wanted to map to a specific implementation, choose the `ConfigAbstractFactory`.
- If you need to map to a service that will return a scalar or array (e.g., a subset of the `'config'` service), choose the `ConfigAbstractFactory`.
- If you need a faster factory for production, choose the `ConfigAbstractFactory` or create a custom factory.
