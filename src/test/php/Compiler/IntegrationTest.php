<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This class tests the integration of the different compiler passes
 */
class IntegrationTest extends PHPUnit_Framework_TestCase
{
    /**
     * This tests that the following dependencies are correctly processed:
     *
     * A is public, B/C are private
     * A -> C
     * B -> C
     */
    public function testProcessRemovesAndInlinesRecursively()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->setResourceTracking(false);

        $a = $container
            ->register('a', 'stdClass')
            ->addArgument(new ehough_iconic_Reference('c'))
        ;

        $b = $container
            ->register('b', 'stdClass')
            ->addArgument(new ehough_iconic_Reference('c'))
            ->setPublic(false)
        ;

        $c = $container
            ->register('c', 'stdClass')
            ->setPublic(false)
        ;

        $container->compile();

        $this->assertTrue($container->hasDefinition('a'));
        $arguments = $a->getArguments();
        $this->assertSame($c, $arguments[0]);
        $this->assertFalse($container->hasDefinition('b'));
        $this->assertFalse($container->hasDefinition('c'));
    }

    public function testProcessInlinesReferencesToAliases()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->setResourceTracking(false);

        $a = $container
            ->register('a', 'stdClass')
            ->addArgument(new ehough_iconic_Reference('b'))
        ;

        $container->setAlias('b', new ehough_iconic_Alias('c', false));

        $c = $container
            ->register('c', 'stdClass')
            ->setPublic(false)
        ;

        $container->compile();

        $this->assertTrue($container->hasDefinition('a'));
        $arguments = $a->getArguments();
        $this->assertSame($c, $arguments[0]);
        $this->assertFalse($container->hasAlias('b'));
        $this->assertFalse($container->hasDefinition('c'));
    }

    public function testProcessInlinesWhenThereAreMultipleReferencesButFromTheSameDefinition()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->setResourceTracking(false);

        $container
            ->register('a', 'stdClass')
            ->addArgument(new ehough_iconic_Reference('b'))
            ->addMethodCall('setC', array(new ehough_iconic_Reference('c')))
        ;

        $container
            ->register('b', 'stdClass')
            ->addArgument(new ehough_iconic_Reference('c'))
            ->setPublic(false)
        ;

        $container
            ->register('c', 'stdClass')
            ->setPublic(false)
        ;

        $container->compile();

        $this->assertTrue($container->hasDefinition('a'));
        $this->assertFalse($container->hasDefinition('b'));
        $this->assertFalse($container->hasDefinition('c'), 'Service C was not inlined.');
    }
}
