<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class CheckReferenceValidityPassTest extends PHPUnit_Framework_TestCase
{
    public function testProcessIgnoresScopeWideningIfNonStrictReference()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->register('a')->addArgument(new ehough_iconic_Reference('b', ehough_iconic_ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, false));
        $container->register('b')->setScope('prototype');

        $this->process($container);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testProcessDetectsScopeWidening()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->register('a')->addArgument(new ehough_iconic_Reference('b'));
        $container->register('b')->setScope('prototype');

        $this->process($container);
    }

    public function testProcessIgnoresCrossScopeHierarchyReferenceIfNotStrict()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->addScope(new ehough_iconic_Scope('a'));
        $container->addScope(new ehough_iconic_Scope('b'));

        $container->register('a')->setScope('a')->addArgument(new ehough_iconic_Reference('b', ehough_iconic_ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, false));
        $container->register('b')->setScope('b');

        $this->process($container);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testProcessDetectsCrossScopeHierarchyReference()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->addScope(new ehough_iconic_Scope('a'));
        $container->addScope(new ehough_iconic_Scope('b'));

        $container->register('a')->setScope('a')->addArgument(new ehough_iconic_Reference('b'));
        $container->register('b')->setScope('b');

        $this->process($container);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testProcessDetectsReferenceToAbstractDefinition()
    {
        $container = new ehough_iconic_ContainerBuilder();

        $container->register('a')->setAbstract(true);
        $container->register('b')->addArgument(new ehough_iconic_Reference('a'));

        $this->process($container);
    }

    public function testProcess()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->register('a')->addArgument(new ehough_iconic_Reference('b'));
        $container->register('b');

        $this->process($container);
    }

    protected function process(ehough_iconic_ContainerBuilder $container)
    {
        $pass = new ehough_iconic_compiler_CheckReferenceValidityPass();
        $pass->process($container);
    }
}
