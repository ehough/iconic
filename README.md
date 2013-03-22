# iconic [![Build Status](https://secure.travis-ci.org/ehough/iconic.png)](http://travis-ci.org/ehough/iconic)

Fork of [Symfony's Dependency Injection component](https://github.com/symfony/DependencyInjection) compatible with PHP 5.2+.

### Differences from [Symfony's Dependency Injection component](https://github.com/symfony/DependencyInjection)

The primary difference is naming conventions of the Symfony classes.
Instead of the `\Symfony\Component\DependencyInjection` namespace (and sub-namespaces), prefix the Symfony class names
with `ehough_iconic` and follow the [PEAR naming convention](http://pear.php.net/manual/en/standards.php)

A few examples of class naming conversions:

    \Symfony\Component\DependencyInjection\ContainerBuilder                     ----->    ehough_iconic_ContainerBuilder
    \Symfony\Component\DependencyInjection\Compiler\Compiler      ----->    ehough_iconic_compiler_Compiler
    \Symfony\Component\DependencyInjection\ParameterBag\ParameterBag  ----->    ehough_iconic_parameterbag_ParameterBag

Other gotchas when using iconic instead of Symfony's Dependency Injection component

* Most of the loaders and dumpers can only be used with PHP 5.3+

### Usage

Here is a simple example that shows how to register services and parameters:

```php
$sc = new ehough_iconic_ContainerBuilder();
$sc
    ->register('foo', '%foo.class%')
    ->addArgument(new Reference('bar'))
;
$sc->setParameter('foo.class', 'Foo');

$sc->get('foo');
```

Method Calls (Setter Injection):

```php
$sc = new ehough_iconic_ContainerBuilder();

$sc
    ->register('bar', '%bar.class%')
    ->addMethodCall('setFoo', array(new Reference('foo')))
;
$sc->setParameter('bar.class', 'Bar');

$sc->get('bar');
```

Factory Class:

If your service is retrieved by calling a static method:

```php
$sc = new ehough_iconic_ContainerBuilder();

$sc
    ->register('bar', '%bar.class%')
    ->setFactoryClass('%bar.class%')
    ->setFactoryMethod('getInstance')
    ->addArgument('Aarrg!!!')
;
$sc->setParameter('bar.class', 'Bar');

$sc->get('bar');
```

File Include:

For some services, especially those that are difficult or impossible to
autoload, you may need the container to include a file before
instantiating your class.

```php
$sc = new ehough_iconic_ContainerBuilder();

$sc
    ->register('bar', '%bar.class%')
    ->setFile('/path/to/file')
    ->addArgument('Aarrg!!!')
;
$sc->setParameter('bar.class', 'Bar');

$sc->get('bar');
```