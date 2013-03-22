<?php

//use Symfony\Component\DependencyInjection\ContainerBuilder;
//use Symfony\Component\DependencyInjection\Reference;

$container = new ehough_iconic_ContainerBuilder();
$container->
    register('foo', 'FooClass')->
    addArgument(new ehough_iconic_Reference('bar'))
;
$container->
    register('bar', 'BarClass')
;
$container->compile();

return $container;
