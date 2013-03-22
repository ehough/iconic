<?php

//use Symfony\Component\DependencyInjection\ContainerBuilder;
//use Symfony\Component\DependencyInjection\Definition;

$container = new ehough_iconic_ContainerBuilder();
$container->
    register('foo', 'FooClass\\Foo')->
    addArgument('foo<>&bar')->
    addTag('foo"bar\\bar', array('foo' => 'foo"barřž€'))
;

return $container;
