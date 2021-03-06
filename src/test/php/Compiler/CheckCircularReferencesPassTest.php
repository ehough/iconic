<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class CheckCircularReferencesPassTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException ehough_iconic_exception_ServiceCircularReferenceException
     */
    public function testProcess()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->register('a')->addArgument(new ehough_iconic_Reference('b'));
        $container->register('b')->addArgument(new ehough_iconic_Reference('a'));

        $this->process($container);
    }

    /**
     * @expectedException ehough_iconic_exception_ServiceCircularReferenceException
     */
    public function testProcessWithAliases()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->register('a')->addArgument(new ehough_iconic_Reference('b'));
        $container->setAlias('b', 'c');
        $container->setAlias('c', 'a');

        $this->process($container);
    }

    /**
     * @expectedException ehough_iconic_exception_ServiceCircularReferenceException
     */
    public function testProcessWithFactory()
    {
        $container = new ehough_iconic_ContainerBuilder();

        $container
            ->register('a', 'stdClass')
            ->setFactoryService('b')
            ->setFactoryMethod('getInstance');

        $container
            ->register('b', 'stdClass')
            ->setFactoryService('a')
            ->setFactoryMethod('getInstance');

        $this->process($container);
    }

    /**
     * @expectedException ehough_iconic_exception_ServiceCircularReferenceException
     */
    public function testProcessDetectsIndirectCircularReference()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->register('a')->addArgument(new ehough_iconic_Reference('b'));
        $container->register('b')->addArgument(new ehough_iconic_Reference('c'));
        $container->register('c')->addArgument(new ehough_iconic_Reference('a'));

        $this->process($container);
    }

    /**
     * @expectedException ehough_iconic_exception_ServiceCircularReferenceException
     */
    public function testProcessDetectsIndirectCircularReferenceWithFactory()
    {
        $container = new ehough_iconic_ContainerBuilder();

        $container->register('a')->addArgument(new ehough_iconic_Reference('b'));

        $container
            ->register('b', 'stdClass')
            ->setFactoryService('c')
            ->setFactoryMethod('getInstance');

        $container->register('c')->addArgument(new ehough_iconic_Reference('a'));

        $this->process($container);
    }

    /**
     * @expectedException ehough_iconic_exception_ServiceCircularReferenceException
     */
    public function testDeepCircularReference()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->register('a')->addArgument(new ehough_iconic_Reference('b'));
        $container->register('b')->addArgument(new ehough_iconic_Reference('c'));
        $container->register('c')->addArgument(new ehough_iconic_Reference('b'));

        $this->process($container);
    }

    public function testProcessIgnoresMethodCalls()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->register('a')->addArgument(new ehough_iconic_Reference('b'));
        $container->register('b')->addMethodCall('setA', array(new ehough_iconic_Reference('a')));

        $this->process($container);
    }



    protected function process(ehough_iconic_ContainerBuilder $container)
    {
        $compiler = new ehough_iconic_compiler_Compiler();
        $passConfig = $compiler->getPassConfig();
        $passConfig->setOptimizationPasses(array(
            new ehough_iconic_compiler_AnalyzeServiceReferencesPass(true),
            new ehough_iconic_compiler_CheckCircularReferencesPass(),
        ));
        $passConfig->setRemovingPasses(array());

        $compiler->compile($container);
    }
}
