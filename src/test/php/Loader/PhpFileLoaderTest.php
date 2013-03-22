<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//namespace Symfony\Component\DependencyInjection\Tests\Loader;

//use Symfony\Component\DependencyInjection\ContainerBuilder;
//use Symfony\Component\Config\Loader\Loader;
//use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
//use Symfony\Component\Config\FileLocator;

class PhpFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony\Component\Config\Loader\Loader')) {
            $this->markTestSkipped('The "Config" component is not available');
        }
    }

    /**
     * @covers ehough_iconic_loader_PhpFileLoader::supports
     */
    public function testSupports()
    {
        $loader = new ehough_iconic_loader_PhpFileLoader(new ehough_iconic_ContainerBuilder(), new \Symfony\Component\Config\FileLocator());

        $this->assertTrue($loader->supports('foo.php'), '->supports() returns true if the resource is loadable');
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns true if the resource is loadable');
    }

    /**
     * @covers ehough_iconic_loader_PhpFileLoader::load
     */
    public function testLoad()
    {
        $loader = new ehough_iconic_loader_PhpFileLoader($container = new ehough_iconic_ContainerBuilder(), new \Symfony\Component\Config\FileLocator());

        $loader->load(__DIR__.'/../Fixtures/php/simple.php');

        $this->assertEquals('foo', $container->getParameter('foo'), '->load() loads a PHP file resource');
    }
}
