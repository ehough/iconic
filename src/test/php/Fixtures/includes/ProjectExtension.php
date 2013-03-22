<?php

//use Symfony\Component\DependencyInjection\ContainerBuilder;
//use Symfony\Component\DependencyInjection\Definition;
//use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class ProjectExtension implements ehough_iconic_extension_ExtensionInterface
{
    public function load(array $configs, ehough_iconic_ContainerBuilder $configuration)
    {
        $config = call_user_func_array('array_merge', $configs);

        $configuration->setDefinition('project.service.bar', new ehough_iconic_Definition('FooClass'));
        $configuration->setParameter('project.parameter.bar', isset($config['foo']) ? $config['foo'] : 'foobar');

        $configuration->setDefinition('project.service.foo', new ehough_iconic_Definition('FooClass'));
        $configuration->setParameter('project.parameter.foo', isset($config['foo']) ? $config['foo'] : 'foobar');

        return $configuration;
    }

    public function getXsdValidationBasePath()
    {
        return false;
    }

    public function getNamespace()
    {
        return 'http://www.example.com/schema/project';
    }

    public function getAlias()
    {
        return 'project';
    }

    public function getConfiguration(array $config, ehough_iconic_ContainerBuilder $container)
    {
        return null;
    }
}
