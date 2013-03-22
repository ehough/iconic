<?php

//use Symfony\Component\DependencyInjection\ContainerBuilder;
//use Symfony\Component\DependencyInjection\Definition;

$container = new ehough_iconic_ContainerBuilder();

$factoryDefinition = new ehough_iconic_Definition('BarClassFactory');
$container->setDefinition('barFactory', $factoryDefinition);

$definition = new ehough_iconic_Definition();
$definition->setFactoryService('barFactory');
$definition->setFactoryMethod('createBarClass');
$container->setDefinition('bar', $definition);

return $container;

class BarClass
{
    public $foo;

    public function setBar($foo)
    {
        $this->foo = $foo;
    }
}

class BarClassFactory
{
    public function createBarClass()
    {
        return new BarClass();
    }
}
