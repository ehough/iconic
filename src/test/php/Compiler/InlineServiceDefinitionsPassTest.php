<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class InlineServiceDefinitionsPassTest extends PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container
            ->register('inlinable.service')
            ->setPublic(false)
        ;

        $container
            ->register('service')
            ->setArguments(array(new ehough_iconic_Reference('inlinable.service')))
        ;

        $this->process($container);

        $arguments = $container->getDefinition('service')->getArguments();
        $this->assertInstanceOf('ehough_iconic_Definition', $arguments[0]);
        $this->assertSame($container->getDefinition('inlinable.service'), $arguments[0]);
    }

    public function testProcessDoesNotInlineWhenAliasedServiceIsNotOfPrototypeScope()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container
            ->register('foo')
            ->setPublic(false)
        ;
        $container->setAlias('moo', 'foo');

        $container
            ->register('service')
            ->setArguments(array($ref = new ehough_iconic_Reference('foo')))
        ;

        $this->process($container);

        $arguments = $container->getDefinition('service')->getArguments();
        $this->assertSame($ref, $arguments[0]);
    }

    public function testProcessDoesInlineServiceOfPrototypeScope()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container
            ->register('foo')
            ->setScope('prototype')
        ;
        $container
            ->register('bar')
            ->setPublic(false)
            ->setScope('prototype')
        ;
        $container->setAlias('moo', 'bar');

        $container
            ->register('service')
            ->setArguments(array(new ehough_iconic_Reference('foo'), $ref = new ehough_iconic_Reference('moo'), new ehough_iconic_Reference('bar')))
        ;

        $this->process($container);

        $arguments = $container->getDefinition('service')->getArguments();
        $this->assertEquals($container->getDefinition('foo'), $arguments[0]);
        $this->assertNotSame($container->getDefinition('foo'), $arguments[0]);
        $this->assertSame($ref, $arguments[1]);
        $this->assertEquals($container->getDefinition('bar'), $arguments[2]);
        $this->assertNotSame($container->getDefinition('bar'), $arguments[2]);
    }

    public function testProcessInlinesIfMultipleReferencesButAllFromTheSameDefinition()
    {
        $container = new ehough_iconic_ContainerBuilder();

        $a = $container->register('a')->setPublic(false);
        $b = $container
            ->register('b')
            ->addArgument(new ehough_iconic_Reference('a'))
            ->addArgument(new ehough_iconic_Definition(null, array(new ehough_iconic_Reference('a'))))
        ;

        $this->process($container);

        $arguments = $b->getArguments();
        $this->assertSame($a, $arguments[0]);

        $inlinedArguments = $arguments[1]->getArguments();
        $this->assertSame($a, $inlinedArguments[0]);
    }

    public function testProcessInlinesOnlyIfSameScope()
    {
        $container = new ehough_iconic_ContainerBuilder();

        $container->addScope(new ehough_iconic_Scope('foo'));
        $a = $container->register('a')->setPublic(false)->setScope('foo');
        $b = $container->register('b')->addArgument(new ehough_iconic_Reference('a'));

        $this->process($container);
        $arguments = $b->getArguments();
        $this->assertEquals(new ehough_iconic_Reference('a'), $arguments[0]);
        $this->assertTrue($container->hasDefinition('a'));
    }

    protected function process(ehough_iconic_ContainerBuilder $container)
    {
        $repeatedPass = new ehough_iconic_compiler_RepeatedPass(array(new ehough_iconic_compiler_AnalyzeServiceReferencesPass(), new ehough_iconic_compiler_InlineServiceDefinitionsPass()));
        $repeatedPass->process($container);
    }
}
