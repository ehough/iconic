<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class YamlDumperTest extends PHPUnit_Framework_TestCase
{
    protected static $fixturesPath;

    protected function setUp()
    {
        if (!class_exists('Symfony\Component\Yaml\Yaml')) {
            $this->markTestSkipped('The "Yaml" component is not available');
        }
    }

    public static function setUpBeforeClass()
    {
        self::$fixturesPath = realpath(dirname(__FILE__).'/../Fixtures/');
    }

    public function testDump()
    {
        $dumper = new ehough_iconic_dumper_YamlDumper($container = new ehough_iconic_ContainerBuilder());

        $this->assertStringEqualsFile(self::$fixturesPath.'/yaml/services1.yml', $dumper->dump(), '->dump() dumps an empty container as an empty YAML file');

        $container = new ehough_iconic_ContainerBuilder();
        $dumper = new ehough_iconic_dumper_YamlDumper($container);
    }

    public function testAddParameters()
    {
        $container = include self::$fixturesPath.'/containers/container8.php';
        $dumper = new ehough_iconic_dumper_YamlDumper($container);
        $this->assertStringEqualsFile(self::$fixturesPath.'/yaml/services8.yml', $dumper->dump(), '->dump() dumps parameters');
    }

    public function testAddService()
    {
        $container = include self::$fixturesPath.'/containers/container9.php';
        $dumper = new ehough_iconic_dumper_YamlDumper($container);
        $this->assertEquals(str_replace('%path%', self::$fixturesPath.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR, file_get_contents(self::$fixturesPath.'/yaml/services9.yml')), $dumper->dump(), '->dump() dumps services');

        $dumper = new ehough_iconic_dumper_YamlDumper($container = new ehough_iconic_ContainerBuilder());
        $container->register('foo', 'FooClass')->addArgument(new stdClass());
        try {
            $dumper->dump();
            $this->fail('->dump() throws a RuntimeException if the container to be dumped has reference to objects or resources');
        } catch (Exception $e) {
            $this->assertInstanceOf('RuntimeException', $e, '->dump() throws a RuntimeException if the container to be dumped has reference to objects or resources');
            $this->assertEquals('Unable to dump a service container if a parameter is an object or a resource.', $e->getMessage(), '->dump() throws a RuntimeException if the container to be dumped has reference to objects or resources');
        }
    }
}
