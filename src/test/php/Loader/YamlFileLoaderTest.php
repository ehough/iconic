<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class YamlFileLoaderTest extends PHPUnit_Framework_TestCase
{
    protected static $fixturesPath;

    public static function setUpBeforeClass()
    {
        self::$fixturesPath = realpath(dirname(__FILE__).'/../Fixtures/');
        require_once self::$fixturesPath.'/includes/foo.php';
        require_once self::$fixturesPath.'/includes/ProjectExtension.php';
    }

    protected function setUp()
    {
        if (!class_exists('Symfony\Component\Config\Loader\Loader')) {
            $this->markTestSkipped('The "Config" component is not available');
        }

        if (!class_exists('Symfony\Component\Yaml\Yaml')) {
            $this->markTestSkipped('The "Yaml" component is not available');
        }
    }

    public function testLoadFile()
    {
        $loader = new ehough_iconic_loader_YamlFileLoader(new ehough_iconic_ContainerBuilder(), $this->_buildFileLocator(self::$fixturesPath.'/ini'));
        $r = new ReflectionObject($loader);
        $m = $r->getMethod('loadFile');
        $m->setAccessible(true);

        try {
            $m->invoke($loader, 'foo.yml');
            $this->fail('->load() throws an InvalidArgumentException if the loaded file does not exist');
        } catch (Exception $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e, '->load() throws an InvalidArgumentException if the loaded file does not exist');
            $this->assertEquals('The service file "foo.yml" is not valid.', $e->getMessage(), '->load() throws an InvalidArgumentException if the loaded file does not exist');
        }

        try {
            $m->invoke($loader, 'parameters.ini');
            $this->fail('->load() throws an InvalidArgumentException if the loaded file is not a valid YAML file');
        } catch (Exception $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e, '->load() throws an InvalidArgumentException if the loaded file is not a valid YAML file');
            $this->assertEquals('The service file "parameters.ini" is not valid.', $e->getMessage(), '->load() throws an InvalidArgumentException if the loaded file is not a valid YAML file');
        }

        $loader = new ehough_iconic_loader_YamlFileLoader(new ehough_iconic_ContainerBuilder(), $this->_buildFileLocator(self::$fixturesPath.'/yaml'));

        foreach (array('nonvalid1', 'nonvalid2') as $fixture) {
            try {
                $m->invoke($loader, $fixture.'.yml');
                $this->fail('->load() throws an InvalidArgumentException if the loaded file does not validate');
            } catch (Exception $e) {
                $this->assertInstanceOf('InvalidArgumentException', $e, '->load() throws an InvalidArgumentException if the loaded file does not validate');
                $this->assertStringMatchesFormat('The service file "nonvalid%d.yml" is not valid.', $e->getMessage(), '->load() throws an InvalidArgumentException if the loaded file does not validate');
            }
        }
    }

    public function testLoadParameters()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $loader = new ehough_iconic_loader_YamlFileLoader($container, $this->_buildFileLocator(self::$fixturesPath.'/yaml'));
        $loader->load('services2.yml');
        $this->assertEquals(array('foo' => 'bar', 'mixedcase' => array('MixedCaseKey' => 'value'), 'values' => array(true, false, 0, 1000.3), 'bar' => 'foo', 'escape' => '@escapeme', 'foo_bar' => new ehough_iconic_Reference('foo_bar')), $container->getParameterBag()->all(), '->load() converts YAML keys to lowercase');
    }

    public function testLoadImports()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $ref = new ReflectionClass('\Symfony\Component\Config\Loader\LoaderResolver');
        $resolver = $ref->newInstanceArgs(array(array(
            new ehough_iconic_loader_IniFileLoader($container, $this->_buildFileLocator(self::$fixturesPath.'/yaml')),
            new ehough_iconic_loader_XmlFileLoader($container, $this->_buildFileLocator(self::$fixturesPath.'/yaml')),
            new ehough_iconic_loader_PhpFileLoader($container, $this->_buildFileLocator(self::$fixturesPath.'/php')),
            $loader = new ehough_iconic_loader_YamlFileLoader($container, $this->_buildFileLocator(self::$fixturesPath.'/yaml')),
        )));
        $loader->setResolver($resolver);
        $loader->load('services4.yml');

        $actual = $container->getParameterBag()->all();
        $expected = array('foo' => 'bar', 'values' => array(true, false), 'bar' => '%foo%', 'escape' => '@escapeme', 'foo_bar' => new ehough_iconic_Reference('foo_bar'), 'mixedcase' => array('MixedCaseKey' => 'value'), 'imported_from_ini' => true, 'imported_from_xml' => true);
        $this->assertEquals(array_keys($expected), array_keys($actual), '->load() imports and merges imported files');

        // Bad import throws no exception due to ignore_errors value.
        $loader->load('services4_bad_import.yml');
    }

    public function testLoadServices()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $loader = new ehough_iconic_loader_YamlFileLoader($container, $this->_buildFileLocator(self::$fixturesPath.'/yaml'));
        $loader->load('services6.yml');
        $services = $container->getDefinitions();
        $this->assertTrue(isset($services['foo']), '->load() parses service elements');
        $this->assertInstanceOf('ehough_iconic_Definition', $services['foo'], '->load() converts service element to ehough_iconic_Definition instances');
        $this->assertEquals('FooClass', $services['foo']->getClass(), '->load() parses the class attribute');
        $this->assertEquals('container', $services['scope.container']->getScope());
        $this->assertEquals('custom', $services['scope.custom']->getScope());
        $this->assertEquals('prototype', $services['scope.prototype']->getScope());
        $this->assertEquals('getInstance', $services['constructor']->getFactoryMethod(), '->load() parses the factory_method attribute');
        $this->assertEquals('%path%/foo.php', $services['file']->getFile(), '->load() parses the file tag');
        $this->assertEquals(array('foo', new ehough_iconic_Reference('foo'), array(true, false)), $services['arguments']->getArguments(), '->load() parses the argument tags');
        $this->assertEquals('sc_configure', $services['configurator1']->getConfigurator(), '->load() parses the configurator tag');
        $this->assertEquals(array(new ehough_iconic_Reference('baz'), 'configure'), $services['configurator2']->getConfigurator(), '->load() parses the configurator tag');
        $this->assertEquals(array('BazClass', 'configureStatic'), $services['configurator3']->getConfigurator(), '->load() parses the configurator tag');

        if (class_exists('Symfony\Component\ExpressionLanguage\Expression')) {

            $ref = new ReflectionClass('Symfony\Component\ExpressionLanguage\Expression');
            $expression = $ref->newInstance('service("foo").foo() ~ parameter("foo")');
            $this->assertEquals(array(array('setBar', array()), array('setBar', array()), array('setBar', array($expression))), $services['method_call1']->getMethodCalls(), '->load() parses the method_call tag');
        }

        $this->assertEquals(array(array('setBar', array('foo', new ehough_iconic_Reference('foo'), array(true, false)))), $services['method_call2']->getMethodCalls(), '->load() parses the method_call tag');
        $this->assertEquals('baz_factory', $services['factory_service']->getFactoryService());

        $this->assertTrue($services['request']->isSynthetic(), '->load() parses the synthetic flag');
        $this->assertTrue($services['request']->isSynchronized(), '->load() parses the synchronized flag');
        $this->assertTrue($services['request']->isLazy(), '->load() parses the lazy flag');

        $aliases = $container->getAliases();
        $this->assertTrue(isset($aliases['alias_for_foo']), '->load() parses aliases');
        $this->assertEquals('foo', (string) $aliases['alias_for_foo'], '->load() parses aliases');
        $this->assertTrue($aliases['alias_for_foo']->isPublic());
        $this->assertTrue(isset($aliases['another_alias_for_foo']));
        $this->assertEquals('foo', (string) $aliases['another_alias_for_foo']);
        $this->assertFalse($aliases['another_alias_for_foo']->isPublic());

        $this->assertNull($services['request']->getDecoratedService());
        $this->assertEquals(array('decorated', null), $services['decorator_service']->getDecoratedService());
        $this->assertEquals(array('decorated', 'decorated.pif-pouf'), $services['decorator_service_with_name']->getDecoratedService());
    }

    public function testExtensions()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->registerExtension(new ProjectExtension());
        $loader = new ehough_iconic_loader_YamlFileLoader($container, $this->_buildFileLocator(self::$fixturesPath.'/yaml'));
        $loader->load('services10.yml');
        $container->compile();
        $services = $container->getDefinitions();
        $parameters = $container->getParameterBag()->all();

        $this->assertTrue(isset($services['project.service.bar']), '->load() parses extension elements');
        $this->assertTrue(isset($parameters['project.parameter.bar']), '->load() parses extension elements');

        $this->assertEquals('BAR', $services['project.service.foo']->getClass(), '->load() parses extension elements');
        $this->assertEquals('BAR', $parameters['project.parameter.foo'], '->load() parses extension elements');

        try {
            $loader->load('services11.yml');
            $this->fail('->load() throws an InvalidArgumentException if the tag is not valid');
        } catch (Exception $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e, '->load() throws an InvalidArgumentException if the tag is not valid');
            $this->assertStringStartsWith('There is no extension able to load the configuration for "foobarfoobar" (in', $e->getMessage(), '->load() throws an InvalidArgumentException if the tag is not valid');
        }
    }

    /**
     * @covers ehough_iconic_loader_YamlFileLoader::supports
     */
    public function testSupports()
    {
        $loader = new ehough_iconic_loader_YamlFileLoader(new ehough_iconic_ContainerBuilder(), $this->_buildFileLocator());

        $this->assertTrue($loader->supports('foo.yml'), '->supports() returns true if the resource is loadable');
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns true if the resource is loadable');
    }

    public function testNonArrayTagThrowsException()
    {
        $loader = new ehough_iconic_loader_YamlFileLoader(new ehough_iconic_ContainerBuilder(), $this->_buildFileLocator(self::$fixturesPath.'/yaml'));
        try {
            $loader->load('badtag1.yml');
            $this->fail('->load() should throw an exception when the tags key of a service is not an array');
        } catch (Exception $e) {
            $this->assertInstanceOf('ehough_iconic_exception_InvalidArgumentException', $e, '->load() throws an InvalidArgumentException if the tags key is not an array');
            $this->assertStringStartsWith('Parameter "tags" must be an array for service', $e->getMessage(), '->load() throws an InvalidArgumentException if the tags key is not an array');
        }
    }

    public function testTagWithoutNameThrowsException()
    {
        $loader = new ehough_iconic_loader_YamlFileLoader(new ehough_iconic_ContainerBuilder(), $this->_buildFileLocator(self::$fixturesPath.'/yaml'));
        try {
            $loader->load('badtag2.yml');
            $this->fail('->load() should throw an exception when a tag is missing the name key');
        } catch (Exception $e) {
            $this->assertInstanceOf('ehough_iconic_exception_InvalidArgumentException', $e, '->load() throws an InvalidArgumentException if a tag is missing the name key');
            $this->assertStringStartsWith('A "tags" entry is missing a "name" key for service ', $e->getMessage(), '->load() throws an InvalidArgumentException if a tag is missing the name key');
        }
    }

    public function testTagWithAttributeArrayThrowsException()
    {
        $loader = new ehough_iconic_loader_YamlFileLoader(new ehough_iconic_ContainerBuilder(), $this->_buildFileLocator(self::$fixturesPath.'/yaml'));
        try {
            $loader->load('badtag3.yml');
            $this->fail('->load() should throw an exception when a tag-attribute is not a scalar');
        } catch (Exception $e) {
            $this->assertInstanceOf('ehough_iconic_exception_InvalidArgumentException', $e, '->load() throws an InvalidArgumentException if a tag-attribute is not a scalar');
            $this->assertStringStartsWith('A "tags" attribute must be of a scalar-type for service "foo_service", tag "foo", attribute "bar"', $e->getMessage(), '->load() throws an InvalidArgumentException if a tag-attribute is not a scalar');
        }
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
