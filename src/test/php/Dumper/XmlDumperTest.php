<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class XmlDumperTest extends PHPUnit_Framework_TestCase
{
    protected static $fixturesPath;

    public static function setUpBeforeClass()
    {
        self::$fixturesPath = realpath(dirname(__FILE__).'/../Fixtures/');
    }

    public function testDump()
    {
        $dumper = new ehough_iconic_dumper_XmlDumper($container = new ehough_iconic_ContainerBuilder());

        $this->assertXmlStringEqualsXmlFile(self::$fixturesPath.'/xml/services1.xml', $dumper->dump(), '->dump() dumps an empty container as an empty XML file');

        $container = new ehough_iconic_ContainerBuilder();
        $dumper = new ehough_iconic_dumper_XmlDumper($container);
    }

    public function testExportParameters()
    {
        $container = include self::$fixturesPath.'//containers/container8.php';
        $dumper = new ehough_iconic_dumper_XmlDumper($container);
        $this->assertXmlStringEqualsXmlFile(self::$fixturesPath.'/xml/services8.xml', $dumper->dump(), '->dump() dumps parameters');
    }

    public function testAddParameters()
    {
        $container = include self::$fixturesPath.'//containers/container8.php';
        $dumper = new ehough_iconic_dumper_XmlDumper($container);
        $this->assertXmlStringEqualsXmlFile(self::$fixturesPath.'/xml/services8.xml', $dumper->dump(), '->dump() dumps parameters');
    }

    public function testAddService()
    {
        if (version_compare(PHP_VERSION, '5.3', '>=')) {

            $suffix = '.xml';

        } else {

            $suffix = '-php52.xml';
        }

        $container = include self::$fixturesPath.'/containers/container9.php';
        $dumper = new ehough_iconic_dumper_XmlDumper($container);
        $this->assertEquals(str_replace('%path%', self::$fixturesPath.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR, file_get_contents(self::$fixturesPath.'/xml/services9' . $suffix)), $dumper->dump(), '->dump() dumps services');

        $dumper = new ehough_iconic_dumper_XmlDumper($container = new ehough_iconic_ContainerBuilder());
        $container->register('foo', 'FooClass')->addArgument(new stdClass());
        try {
            $dumper->dump();
            $this->fail('->dump() throws a RuntimeException if the container to be dumped has reference to objects or resources');
        } catch (Exception $e) {
            $this->assertInstanceOf('RuntimeException', $e, '->dump() throws a RuntimeException if the container to be dumped has reference to objects or resources');
            $this->assertEquals('Unable to dump a service container if a parameter is an object or a resource.', $e->getMessage(), '->dump() throws a RuntimeException if the container to be dumped has reference to objects or resources');
        }
    }

    public function testDumpAnonymousServices()
    {
        $container = include self::$fixturesPath.'/containers/container11.php';
        $dumper = new ehough_iconic_dumper_XmlDumper($container);
        $this->assertEquals("<?xml version=\"1.0\" encoding=\"utf-8\"?>
<container xmlns=\"http://symfony.com/schema/dic/services\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd\">
  <services>
    <service id=\"foo\" class=\"FooClass\">
      <argument type=\"service\">
        <service class=\"BarClass\">
          <argument type=\"service\">
            <service class=\"BazClass\"/>
          </argument>
        </service>
      </argument>
    </service>
  </services>
</container>
", $dumper->dump());
    }

    public function testDumpEntities()
    {
        $container = include self::$fixturesPath.'/containers/container12.php';
        $dumper = new ehough_iconic_dumper_XmlDumper($container);
        $this->assertEquals("<?xml version=\"1.0\" encoding=\"utf-8\"?>
<container xmlns=\"http://symfony.com/schema/dic/services\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd\">
  <services>
    <service id=\"foo\" class=\"FooClass\Foo\">
      <tag name=\"foo&quot;bar\bar\" foo=\"foo&quot;barřž€\"/>
      <argument>foo&lt;&gt;&amp;bar</argument>
    </service>
  </services>
</container>
", $dumper->dump());
    }

    /**
     * @dataProvider provideDecoratedServicesData
     */
    public function testDumpDecoratedServices($expectedXmlDump, $container)
    {
        $dumper = new ehough_iconic_dumper_XmlDumper($container);
        $this->assertEquals($expectedXmlDump, $dumper->dump());
    }

    public function provideDecoratedServicesData()
    {
        $fixturesPath = realpath(dirname(__FILE__).'/../Fixtures/');

        return array(
            array("<?xml version=\"1.0\" encoding=\"utf-8\"?>
<container xmlns=\"http://symfony.com/schema/dic/services\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd\">
  <services>
    <service id=\"foo\" class=\"FooClass\Foo\" decorates=\"bar\" decoration-inner-name=\"bar.woozy\"/>
  </services>
</container>
", include $fixturesPath.'/containers/container15.php'),
            array("<?xml version=\"1.0\" encoding=\"utf-8\"?>
<container xmlns=\"http://symfony.com/schema/dic/services\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd\">
  <services>
    <service id=\"foo\" class=\"FooClass\Foo\" decorates=\"bar\"/>
  </services>
</container>
", include $fixturesPath.'/containers/container16.php'),
        );
    }
}
