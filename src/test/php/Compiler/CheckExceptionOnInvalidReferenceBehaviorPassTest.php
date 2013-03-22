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

//use Symfony\Component\DependencyInjection\Definition;

//use Symfony\Component\DependencyInjection\Compiler\CheckExceptionOnInvalidReferenceBehaviorPass;
//use Symfony\Component\DependencyInjection\Reference;
//use Symfony\Component\DependencyInjection\ContainerBuilder;

class CheckExceptionOnInvalidReferenceBehaviorPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new ehough_iconic_ContainerBuilder();

        $container
            ->register('a', '\stdClass')
            ->addArgument(new ehough_iconic_Reference('b'))
        ;
        $container->register('b', '\stdClass');
    }

    /**
     * @expectedException ehough_iconic_exception_ServiceNotFoundException
     */
    public function testProcessThrowsExceptionOnInvalidReference()
    {
        $container = new ehough_iconic_ContainerBuilder();

        $container
            ->register('a', '\stdClass')
            ->addArgument(new ehough_iconic_Reference('b'))
        ;

        $this->process($container);
    }

    /**
     * @expectedException ehough_iconic_exception_ServiceNotFoundException
     */
    public function testProcessThrowsExceptionOnInvalidReferenceFromInlinedDefinition()
    {
        $container = new ehough_iconic_ContainerBuilder();

        $def = new ehough_iconic_Definition();
        $def->addArgument(new ehough_iconic_Reference('b'));

        $container
            ->register('a', '\stdClass')
            ->addArgument($def)
        ;

        $this->process($container);
    }

    private function process(ehough_iconic_ContainerBuilder $container)
    {
        $pass = new ehough_iconic_compiler_CheckExceptionOnInvalidReferenceBehaviorPass();
        $pass->process($container);
    }
}
