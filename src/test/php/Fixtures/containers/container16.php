<?php

$container = new ehough_iconic_ContainerBuilder();
$container
    ->register('foo', 'FooClass\\Foo')
    ->setDecoratedService('bar')
;

return $container;
