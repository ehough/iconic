<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ClosureLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ehough_iconic_loader_ClosureLoader::supports
     */
    public function testSupports()
    {
        $loader = new ehough_iconic_loader_ClosureLoader(new ehough_iconic_ContainerBuilder());

        $this->assertTrue($loader->supports(eval('return function ($container) {};')), '->supports() returns true if the resource is loadable');
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns true if the resource is loadable');
    }

    /**
     * @covers ehough_iconic_loader_ClosureLoader::load
     */
    public function testLoad()
    {
        $loader = new ehough_iconic_loader_ClosureLoader($container = new ehough_iconic_ContainerBuilder());

        $loader->load(eval('return function ($container) {
            $container->setParameter(\'foo\', \'foo\');
        };'));

        $this->assertEquals('foo', $container->getParameter('foo'), '->load() loads a \Closure resource');
    }
}
