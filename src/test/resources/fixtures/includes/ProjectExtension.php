<?php
class ehough_iconic_impl_extension_ProjectExtension implements ehough_iconic_api_extension_IExtension
{
    public function load(ehough_iconic_impl_ContainerBuilder $configuration)
    {
        $configuration->setDefinition('project.service.bar', new ehough_iconic_impl_Definition('FooClass'));
        $configuration->setParameter('project.parameter.bar', isset($config['foo']) ? $config['foo'] : 'foobar');

        $configuration->setDefinition('project.service.foo', new ehough_iconic_impl_Definition('FooClass'));
        $configuration->setParameter('project.parameter.foo', isset($config['foo']) ? $config['foo'] : 'foobar');

        return $configuration;
    }

    public function getAlias()
    {
        return 'project';
    }
}