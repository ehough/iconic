<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RemoveUnusedDefinitionsPassTest extends PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container
            ->register('foo')
            ->setPublic(false)
        ;
        $container
            ->register('bar')
            ->setPublic(false)
        ;
        $container
            ->register('moo')
            ->setArguments(array(new ehough_iconic_Reference('bar')))
        ;

        $this->process($container);

        $this->assertFalse($container->hasDefinition('foo'));
        $this->assertTrue($container->hasDefinition('bar'));
        $this->assertTrue($container->hasDefinition('moo'));
    }

    public function testProcessRemovesUnusedDefinitionsRecursively()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container
            ->register('foo')
            ->setPublic(false)
        ;
        $container
            ->register('bar')
            ->setArguments(array(new ehough_iconic_Reference('foo')))
            ->setPublic(false)
        ;

        $this->process($container);

        $this->assertFalse($container->hasDefinition('foo'));
        $this->assertFalse($container->hasDefinition('bar'));
    }

    public function testProcessWorksWithInlinedDefinitions()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container
            ->register('foo')
            ->setPublic(false)
        ;
        $container
            ->register('bar')
            ->setArguments(array(new ehough_iconic_Definition(null, array(new ehough_iconic_Reference('foo')))))
        ;

        $this->process($container);

        $this->assertTrue($container->hasDefinition('foo'));
        $this->assertTrue($container->hasDefinition('bar'));
    }

    public function testProcessWontRemovePrivateFactory()
    {
        $container = new ehough_iconic_ContainerBuilder();

        $container
            ->register('foo', 'stdClass')
            ->setFactoryClass('stdClass')
            ->setFactoryMethod('getInstance')
            ->setPublic(false);

        $container
            ->register('bar', 'stdClass')
            ->setFactoryService('foo')
            ->setFactoryMethod('getInstance')
            ->setPublic(false);

        $container
            ->register('foobar')
            ->addArgument(new ehough_iconic_Reference('bar'));

        $this->process($container);

        $this->assertTrue($container->hasDefinition('foo'));
        $this->assertTrue($container->hasDefinition('bar'));
        $this->assertTrue($container->hasDefinition('foobar'));
    }

    protected function process(ehough_iconic_ContainerBuilder $container)
    {
        $repeatedPass = new ehough_iconic_compiler_RepeatedPass(array(new ehough_iconic_compiler_AnalyzeServiceReferencesPass(), new ehough_iconic_compiler_RemoveUnusedDefinitionsPass()));
        $repeatedPass->process($container);
    }
}
