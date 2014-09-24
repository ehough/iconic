<?php

$container = new ehough_iconic_ContainerBuilder();
$container->addScope(new ehough_iconic_Scope('request'));
$container->
    register('foo', 'FooClass')->
    setScope('request')
;
$container->compile();

return $container;
