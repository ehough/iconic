<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class PhpDumperTest extends PHPUnit_Framework_TestCase
{
    protected static $fixturesPath;

    public static function setUpBeforeClass()
    {
        self::$fixturesPath = realpath(dirname(__FILE__).'/../Fixtures/');
    }

    public function testDump()
    {
        $dumper = new ehough_iconic_dumper_PhpDumper($container = new ehough_iconic_ContainerBuilder());

        $this->assertStringEqualsFile(self::$fixturesPath.'/php/services1.php', $dumper->dump(), '->dump() dumps an empty container as an empty PHP class');
        $this->assertStringEqualsFile(self::$fixturesPath.'/php/services1-1.php', $dumper->dump(array('class' => 'ehough_iconic_Container', 'base_class' => 'AbstractContainer')), '->dump() takes a class and a base_class options');

        $container = new ehough_iconic_ContainerBuilder();
        new ehough_iconic_dumper_PhpDumper($container);
    }

    public function testDumpFrozenContainerWithNoParameter()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->setResourceTracking(false);
        $container->register('foo', 'stdClass');

        $container->compile();

        $dumper = new ehough_iconic_dumper_PhpDumper($container);

        $dumpedString = $dumper->dump();
        $this->assertStringEqualsFile(self::$fixturesPath.'/php/services11.php', $dumpedString, '->dump() does not add getDefaultParameters() method call if container have no parameters.');
        $this->assertNotRegexp("/function getDefaultParameters\(/", $dumpedString, '->dump() does not add getDefaultParameters() method definition.');
    }

    public function testDumpOptimizationString()
    {
        $definition = new ehough_iconic_Definition();
        $definition->setClass('stdClass');
        $definition->addArgument(array(
            'only dot' => '.',
            'concatenation as value' => '.\'\'.',
            'concatenation from the start value' => '\'\'.',
            '.' => 'dot as a key',
            '.\'\'.' => 'concatenation as a key',
            '\'\'.' =>'concatenation from the start key',
            'optimize concatenation' => "string1%some_string%string2",
            'optimize concatenation with empty string' => "string1%empty_value%string2",
            'optimize concatenation from the start' => '%empty_value%start',
            'optimize concatenation at the end' => 'end%empty_value%',
        ));

        $container = new ehough_iconic_ContainerBuilder();
        $container->setResourceTracking(false);
        $container->setDefinition('test', $definition);
        $container->setParameter('empty_value', '');
        $container->setParameter('some_string', '-');
        $container->compile();

        $dumper = new ehough_iconic_dumper_PhpDumper($container);
        $this->assertStringEqualsFile(self::$fixturesPath.'/php/services10.php', $dumper->dump(), '->dump() dumps an empty container as an empty PHP class');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExportParameters()
    {
        $dumper = new ehough_iconic_dumper_PhpDumper(new ehough_iconic_ContainerBuilder(new ehough_iconic_parameterbag_ParameterBag(array('foo' => new ehough_iconic_Reference('foo')))));
        $dumper->dump();
    }

    public function testAddParameters()
    {
        $container = include self::$fixturesPath.'/containers/container8.php';
        $dumper = new ehough_iconic_dumper_PhpDumper($container);
        $this->assertStringEqualsFile(self::$fixturesPath.'/php/services8.php', $dumper->dump(), '->dump() dumps parameters');
    }

    public function testAddService()
    {
        if (version_compare(PHP_VERSION, '5.3', '>=')) {

            $file = '.php';

        } else {

            $file = '-php52.php';
        }

        // without compilation
        $container = include self::$fixturesPath.'/containers/container9.php';
        $dumper = new ehough_iconic_dumper_PhpDumper($container);
        $realPath = str_replace('\\', '\\\\', self::$fixturesPath . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR);
        $services9Contents = file_get_contents(self::$fixturesPath . '/php/services9' . $file);
        $expected = str_replace('%path%', $realPath, $services9Contents);
        $dumped = $dumper->dump();
        $this->assertEquals($expected, $dumped, '->dump() dumps services (not compiled) ');

        // with compilation
        $container = include self::$fixturesPath.'/containers/container9.php';
        $container->compile();
        $dumper = new ehough_iconic_dumper_PhpDumper($container);
        $this->assertEquals(str_replace('%path%', str_replace('\\','\\\\',self::$fixturesPath.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR), file_get_contents(self::$fixturesPath.'/php/services9_compiled' . $file)), $dumper->dump(), '->dump() dumps services (compiled)');

        $dumper = new ehough_iconic_dumper_PhpDumper($container = new ehough_iconic_ContainerBuilder());
        $container->register('foo', 'FooClass')->addArgument(new stdClass());
        try {
            $dumper->dump();
            $this->fail('->dump() throws a RuntimeException if the container to be dumped has reference to objects or resources');
        } catch (Exception $e) {
            $this->assertInstanceOf('ehough_iconic_exception_RuntimeException', $e, '->dump() throws a RuntimeException if the container to be dumped has reference to objects or resources');
            $this->assertEquals('Unable to dump a service container if a parameter is an object or a resource.', $e->getMessage(), '->dump() throws a RuntimeException if the container to be dumped has reference to objects or resources');
        }
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Service id "bar$" cannot be converted to a valid PHP method name.
     */
    public function testAddServiceInvalidServiceId()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->register('bar$', 'FooClass');
        $dumper = new ehough_iconic_dumper_PhpDumper($container);
        $dumper->dump();
    }

    public function testAliases()
    {
        $container = include self::$fixturesPath.'/containers/container9.php';
        $container->compile();
        $dumper = new ehough_iconic_dumper_PhpDumper($container);
        eval('?>'.$dumper->dump(array('class' => 'Symfony_DI_PhpDumper_Test_Aliases')));

        $container = new Symfony_DI_PhpDumper_Test_Aliases();
        $container->set('foo', $foo = new stdClass());
        $this->assertSame($foo, $container->get('foo'));
        $this->assertSame($foo, $container->get('alias_for_foo'));
        $this->assertSame($foo, $container->get('alias_for_alias'));
    }

    public function testFrozenContainerWithoutAliases()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->compile();

        $dumper = new ehough_iconic_dumper_PhpDumper($container);
        eval('?>'.$dumper->dump(array('class' => 'Symfony_DI_PhpDumper_Test_Frozen_No_Aliases')));

        $container = new Symfony_DI_PhpDumper_Test_Frozen_No_Aliases();
        $this->assertFalse($container->has('foo'));
    }

    public function testOverrideServiceWhenUsingADumpedContainer()
    {
        require_once self::$fixturesPath.'/php/services9.php';
        require_once self::$fixturesPath.'/includes/foo.php';

        $container = new ProjectServiceContainer();
        $container->set('bar', $bar = new stdClass());
        $container->setParameter('foo_bar', 'foo_bar');

        $this->assertEquals($bar, $container->get('bar'), '->set() overrides an already defined service');
    }

    public function testOverrideServiceWhenUsingADumpedContainerAndServiceIsUsedFromAnotherOne()
    {
        require_once self::$fixturesPath.'/php/services9.php';
        require_once self::$fixturesPath.'/includes/foo.php';
        require_once self::$fixturesPath.'/includes/classes.php';

        $container = new ProjectServiceContainer();
        $container->set('bar', $bar = new stdClass());

        $this->assertSame($bar, $container->get('foo')->bar, '->set() overrides an already defined service');
    }

    /**
     * @expectedException ehough_iconic_exception_ServiceCircularReferenceException
     */
    public function testCircularReference()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->register('foo', 'stdClass')->addArgument(new ehough_iconic_Reference('bar'));
        $container->register('bar', 'stdClass')->setPublic(false)->addMethodCall('setA', array(new ehough_iconic_Reference('baz')));
        $container->register('baz', 'stdClass')->addMethodCall('setA', array(new ehough_iconic_Reference('foo')));
        $container->compile();

        $dumper = new ehough_iconic_dumper_PhpDumper($container);
        $dumper->dump();
    }
}
