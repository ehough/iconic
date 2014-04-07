<?php

$container = new ehough_iconic_ContainerBuilder();
$container
    ->register('foo', 'FooClass\\Foo')
    ->setDecoratedService('bar', 'bar.woozy')
;

return $container;
