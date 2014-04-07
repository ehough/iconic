<?php

require_once dirname(__FILE__).'/../includes/classes.php';

$container = new ehough_iconic_ContainerBuilder();
$container->
    register('foo', 'FooClass')->
    addTag('foo', array('foo' => 'foo'))->
    addTag('foo', array('bar' => 'bar'))->
    setFactoryClass('FooClass')->
    setFactoryMethod('getInstance')->
    setArguments(array('foo', new ehough_iconic_Reference('foo.baz'), array('%foo%' => 'foo is %foo%', 'foobar' => '%foo%'), true, new ehough_iconic_Reference('service_container')))->
    setProperties(array('foo' => 'bar', 'moo' => new ehough_iconic_Reference('foo.baz'), 'qux' => array('%foo%' => 'foo is %foo%', 'foobar' => '%foo%')))->
    addMethodCall('setBar', array(new ehough_iconic_Reference('bar')))->
    addMethodCall('initialize')->
    setConfigurator('sc_configure')
;
$container->
    register('bar', 'FooClass')->
    setArguments(array('foo', new ehough_iconic_Reference('foo.baz'), new ehough_iconic_Parameter('foo_bar')))->
    setScope('container')->
    setConfigurator(array(new ehough_iconic_Reference('foo.baz'), 'configure'))
;
$container->
    register('foo.baz', '%baz_class%')->
    setFactoryClass('%baz_class%')->
    setFactoryMethod('getInstance')->
    setConfigurator(array('%baz_class%', 'configureStatic1'))
;
$container->
    register('foo_bar', '%foo_class%')->
    setScope('prototype')
;
$container->getParameterBag()->clear();
$container->getParameterBag()->add(array(
    'baz_class' => 'BazClass',
    'foo_class' => 'FooClass',
    'foo' => 'bar',
));
$container->setAlias('alias_for_foo', 'foo');
$container->setAlias('alias_for_alias', 'alias_for_foo');
$def = $container->
    register('method_call1', 'FooClass')->
    setFile(realpath(__DIR__.'/../includes/foo.php'))->
    addMethodCall('setBar', array(new ehough_iconic_Reference('foo')))->
    addMethodCall('setBar', array(new ehough_iconic_Reference('foo2', ehough_iconic_ContainerInterface::NULL_ON_INVALID_REFERENCE)))->
    addMethodCall('setBar', array(new ehough_iconic_Reference('foo3', ehough_iconic_ContainerInterface::IGNORE_ON_INVALID_REFERENCE)))->
    addMethodCall('setBar', array(new ehough_iconic_Reference('foobaz', ehough_iconic_ContainerInterface::IGNORE_ON_INVALID_REFERENCE)));

if (class_exists('\Symfony\Component\ExpressionLanguage\Expression')) {

    $ref        = new ReflectionClass('\Symfony\Component\ExpressionLanguage\Expression');
    $expression = $ref->newInstance('service("foo").foo() ~ parameter("foo")');

    $def->addMethodCall('setBar', array($expression));
};

$container->
    register('factory_service', 'Bar')->
    setFactoryService('foo.baz')->
    setFactoryMethod('getInstance')
;

$container
    ->register('foo_with_inline', 'Foo')
    ->addMethodCall('setBar', array(new ehough_iconic_Reference('inlined')))
;
$container
    ->register('inlined', 'Bar')
    ->setProperty('pub', 'pub')
    ->addMethodCall('setBaz', array(new ehough_iconic_Reference('baz')))
    ->setPublic(false)
;
$container
    ->register('baz', 'Baz')
    ->addMethodCall('setFoo', array(new ehough_iconic_Reference('foo_with_inline')))
;
$container
    ->register('request', 'Request')
    ->setSynthetic(true)
    ->setSynchronized(true)
;
$container
    ->register('depends_on_request', 'stdClass')
    ->addMethodCall('setRequest', array(new ehough_iconic_Reference('request', ehough_iconic_ContainerInterface::NULL_ON_INVALID_REFERENCE, false)))
;
$container
    ->register('configurator_service', 'ConfClass')
    ->setPublic(false)
    ->addMethodCall('setFoo', array(new ehough_iconic_Reference('baz')))
;
$container
    ->register('configured_service', 'stdClass')
    ->setConfigurator(array(new ehough_iconic_Reference('configurator_service'), 'configureStdClass'))
;
$container
    ->register('decorated', 'stdClass')
;
$container
    ->register('decorator_service', 'stdClass')
    ->setDecoratedService('decorated')
;
$container
    ->register('decorator_service_with_name', 'stdClass')
    ->setDecoratedService('decorated', 'decorated.pif-pouf')
;

return $container;
