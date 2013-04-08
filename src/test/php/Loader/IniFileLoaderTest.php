<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class IniFileLoaderTest extends PHPUnit_Framework_TestCase
{
    protected static $fixturesPath;

    protected $container;
    protected $loader;

    public static function setUpBeforeClass()
    {
        self::$fixturesPath = realpath(__DIR__.'/../Fixtures/');
    }

    protected function setUp()
    {
        if (!class_exists('Symfony\Component\Config\Loader\Loader')) {
            $this->markTestSkipped('The "Config" component is not available');
        }

        $this->container = new ehough_iconic_ContainerBuilder();
        $this->loader    = new ehough_iconic_loader_IniFileLoader($this->container, $this->_buildFileLocator(self::$fixturesPath.'/ini'));
    }

    /**
     * @covers ehough_iconic_loader_IniFileLoader::__construct
     * @covers ehough_iconic_loader_IniFileLoader::load
     */
    public function testIniFileCanBeLoaded()
    {
        $this->loader->load('parameters.ini');
        $this->assertEquals(array('foo' => 'bar', 'bar' => '%foo%'), $this->container->getParameterBag()->all(), '->load() takes a single file name as its first argument');
    }

    /**
     * @covers ehough_iconic_loader_IniFileLoader::__construct
     * @covers ehough_iconic_loader_IniFileLoader::load
     */
    public function testExceptionIsRaisedWhenIniFileDoesNotExist()
    {
        try {
            $this->loader->load('foo.ini');
            $this->fail('->load() throws an InvalidArgumentException if the loaded file does not exist');
        } catch (Exception $e) {
            $this->assertInstanceOf('\InvalidArgumentException', $e, '->load() throws an InvalidArgumentException if the loaded file does not exist');
            $this->assertStringStartsWith('The file "foo.ini" does not exist (in: ', $e->getMessage(), '->load() throws an InvalidArgumentException if the loaded file does not exist');
        }
    }

    /**
     * @covers ehough_iconic_loader_IniFileLoader::__construct
     * @covers ehough_iconic_loader_IniFileLoader::load
     */
    public function testExceptionIsRaisedWhenIniFileCannotBeParsed()
    {
        try {
            @$this->loader->load('nonvalid.ini');
            $this->fail('->load() throws an InvalidArgumentException if the loaded file is not parseable');
        } catch (Exception $e) {
            $this->assertInstanceOf('\InvalidArgumentException', $e, '->load() throws an InvalidArgumentException if the loaded file is not parseable');
            $this->assertEquals('The "nonvalid.ini" file is not valid.', $e->getMessage(), '->load() throws an InvalidArgumentException if the loaded file is not parseable');
        }
    }

    /**
     * @covers ehough_iconic_loader_IniFileLoader::supports
     */
    public function testSupports()
    {
        $loader = new ehough_iconic_loader_IniFileLoader(new ehough_iconic_ContainerBuilder(), $this->_buildFileLocator());

        $this->assertTrue($loader->supports('foo.ini'), '->supports() returns true if the resource is loadable');
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns true if the resource is loadable');
    }

    private function _buildFileLocator($path = null)
    {
        $ref = new ReflectionClass('\Symfony\Component\Config\FileLocator');

        if ($path) {

            return $ref->newInstanceArgs(array($path));

        } else {

            return $ref->newInstance();
        }
    }
}
