<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class CheckDefinitionValidityPassTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException ehough_iconic_exception_RuntimeException
     */
    public function testProcessDetectsSyntheticNonPublicDefinitions()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->register('a')->setSynthetic(true)->setPublic(false);

        $this->process($container);
    }

    /**
     * @expectedException ehough_iconic_exception_RuntimeException
     */
    public function testProcessDetectsSyntheticPrototypeDefinitions()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->register('a')->setSynthetic(true)->setScope(ehough_iconic_ContainerInterface::SCOPE_PROTOTYPE);

        $this->process($container);
    }

    /**
     * @expectedException ehough_iconic_exception_RuntimeException
     */
    public function testProcessDetectsNonSyntheticNonAbstractDefinitionWithoutClass()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->register('a')->setSynthetic(false)->setAbstract(false);

        $this->process($container);
    }

    public function testProcess()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->register('a', 'class');
        $container->register('b', 'class')->setSynthetic(true)->setPublic(true);
        $container->register('c', 'class')->setAbstract(true);
        $container->register('d', 'class')->setSynthetic(true);

        $this->process($container);
    }

    public function testValidTags()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->register('a', 'class')->addTag('foo', array('bar' => 'baz'));
        $container->register('b', 'class')->addTag('foo', array('bar' => null));
        $container->register('c', 'class')->addTag('foo', array('bar' => 1));
        $container->register('d', 'class')->addTag('foo', array('bar' => 1.1));

        $this->process($container);
    }

    /**
     * @expectedException ehough_iconic_exception_RuntimeException
     */
    public function testInvalidTags()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->register('a', 'class')->addTag('foo', array('bar' => array('baz' => 'baz')));

        $this->process($container);
    }

    protected function process(ehough_iconic_ContainerBuilder $container)
    {
        $pass = new ehough_iconic_compiler_CheckDefinitionValidityPass();
        $pass->process($container);
    }
}
