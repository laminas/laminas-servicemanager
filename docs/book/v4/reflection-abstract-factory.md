# Reflection Factory

- Since 3.2.0.

Writing a factory class for each and every service that has dependencies
can be tedious, particularly in early development as you are still sorting
out dependencies.

laminas-servicemanager ships with `Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory`,
which provides a reflection-based approach to instantiation, resolving
constructor dependencies to the relevant services. The factory may be used as
either an abstract factory, or mapped to specific service names as a factory:

```php
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;

return [
    /* ... */
    'service_manager' => [
        'abstract_factories' => [
            ReflectionBasedAbstractFactory::class,
        ],
        'factories' => [
            'MyModule\Model\FooModel' => ReflectionBasedAbstractFactory::class,
        ],
    ],
    /* ... */
];
```

Mapping services to the factory is more explicit and even more performant than in v3.0 due to the [ahead of time factory generation](ahead-of-time-factories.md).

The factory operates with the following constraints/features:

- A parameter named `$config` typehinted as an array will receive the
  application "config" service (i.e., the merged configuration).
- Parameters typehinted against array, but not named `$config`, will
  be injected with an empty array.
- Scalar parameters will result in the factory raising an exception,
  unless a default value is present; if it is, that value will be used.
- If a service cannot be found for a given typehint, the factory will
  raise an exception detailing this.

`$options` passed to the factory are ignored in all cases, as we cannot
make assumptions about which argument(s) they might replace.

Once your dependencies have stabilized, we recommend providing a dedicated
factory, as reflection introduces a performance overhead.

There are two ways to provide dedicated factories for services consuming `ReflectionBasedAbstractFactory`:

1. Usage of the [generate-factory-for-class console tool](console-tools.md#generate-factory-for-class) (this will also require to manually modify the configuration)
2. Usage of the [generate-aot-factories console tool](console-tools.md#generate-ahead-of-time-factories) which needs an initial project + deployment setup

## Alternatives

You may also use the [Config Abstract Factory](config-abstract-factory.md),
which gives slightly more flexibility in terms of mapping dependencies:

- If you wanted to map to a specific implementation, choose the
  `ConfigAbstractFactory`.
- If you need to map to a service that will return a scalar or array (e.g., a
  subset of the `'config'` service), choose the `ConfigAbstractFactory`.
- If you need a faster factory for production, choose the
  `ConfigAbstractFactory` or create a custom factory.

## References

This feature was inspired by [a blog post by Alexandre Lemaire](http://circlical.com/blog/2016/3/9/preparing-for-zend-f).
