<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class PhpFileLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ehough_iconic_loader_PhpFileLoader::supports
     */
    public function testSupports()
    {
        $loader = new ehough_iconic_loader_PhpFileLoader(new ehough_iconic_ContainerBuilder(), $this->_buildFileLocator());

        $this->assertTrue($loader->supports('foo.php'), '->supports() returns true if the resource is loadable');
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns true if the resource is loadable');
    }

    /**
     * @covers ehough_iconic_loader_PhpFileLoader::load
     */
    public function testLoad()
    {
        $loader = new ehough_iconic_loader_PhpFileLoader($container = new ehough_iconic_ContainerBuilder(), $this->_buildFileLocator());

        $loader->load(dirname(__FILE__).'/../Fixtures/php/simple.php');

        $this->assertEquals('foo', $container->getParameter('foo'), '->load() loads a PHP file resource');
    }

    private function _buildFileLocator()
    {
        $ref = new ReflectionClass('\Symfony\Component\Config\FileLocator');

        return $ref->newInstance();
    }
}
