<?php

//use Symfony\Component\DependencyInjection\ContainerBuilder;
//use Symfony\Component\DependencyInjection\Definition;

$container = new ehough_iconic_ContainerBuilder();
$container->
    register('foo', 'FooClass')->
    addArgument(new ehough_iconic_Definition('BarClass', array(new ehough_iconic_Definition('BazClass'))))
;

return $container;
