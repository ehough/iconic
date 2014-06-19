<?php

$container = new ehough_iconic_ContainerBuilder();
$container
    ->register('foo', '%foo.class%')
;

return $container;
