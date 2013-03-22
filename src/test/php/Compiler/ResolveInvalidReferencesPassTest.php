<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//namespace Symfony\Component\DependencyInjection\Tests\Compiler;

//use Symfony\Component\DependencyInjection\ContainerInterface;
//use Symfony\Component\DependencyInjection\Reference;
//use Symfony\Component\DependencyInjection\Compiler\ResolveInvalidReferencesPass;
//use Symfony\Component\DependencyInjection\ContainerBuilder;

class ResolveInvalidReferencesPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $def = $container
            ->register('foo')
            ->setArguments(array(new ehough_iconic_Reference('bar', ehough_iconic_ContainerInterface::NULL_ON_INVALID_REFERENCE)))
            ->addMethodCall('foo', array(new ehough_iconic_Reference('moo', ehough_iconic_ContainerInterface::IGNORE_ON_INVALID_REFERENCE)))
        ;

        $this->process($container);

        $arguments = $def->getArguments();
        $this->assertNull($arguments[0]);
        $this->assertCount(0, $def->getMethodCalls());
    }

    public function testProcessIgnoreNonExistentServices()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $def = $container
            ->register('foo')
            ->setArguments(array(new ehough_iconic_Reference('bar')))
        ;

        $this->process($container);

        $arguments = $def->getArguments();
        $this->assertEquals('bar', (string) $arguments[0]);
    }

    public function testProcessRemovesPropertiesOnInvalid()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $def = $container
            ->register('foo')
            ->setProperty('foo', new ehough_iconic_Reference('bar', ehough_iconic_ContainerInterface::IGNORE_ON_INVALID_REFERENCE))
        ;

        $this->process($container);

        $this->assertEquals(array(), $def->getProperties());
    }

    public function testStrictFlagIsPreserved()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->register('bar');
        $def = $container
            ->register('foo')
            ->addArgument(new ehough_iconic_Reference('bar', ehough_iconic_ContainerInterface::NULL_ON_INVALID_REFERENCE, false))
        ;

        $this->process($container);

        $this->assertFalse($def->getArgument(0)->isStrict());
    }

    protected function process(ehough_iconic_ContainerBuilder $container)
    {
        $pass = new ehough_iconic_compiler_ResolveInvalidReferencesPass();
        $pass->process($container);
    }
}
