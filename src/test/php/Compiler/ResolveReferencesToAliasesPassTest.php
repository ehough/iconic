<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ResolveReferencesToAliasesPassTest extends PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->setAlias('bar', 'foo');
        $def = $container
            ->register('moo')
            ->setArguments(array(new ehough_iconic_Reference('bar')))
        ;

        $this->process($container);

        $arguments = $def->getArguments();
        $this->assertEquals('foo', (string) $arguments[0]);
    }

    public function testProcessRecursively()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->setAlias('bar', 'foo');
        $container->setAlias('moo', 'bar');
        $def = $container
            ->register('foobar')
            ->setArguments(array(new ehough_iconic_Reference('moo')))
        ;

        $this->process($container);

        $arguments = $def->getArguments();
        $this->assertEquals('foo', (string) $arguments[0]);
    }

    protected function process(ehough_iconic_ContainerBuilder $container)
    {
        $pass = new ehough_iconic_compiler_ResolveReferencesToAliasesPass();
        $pass->process($container);
    }
}
