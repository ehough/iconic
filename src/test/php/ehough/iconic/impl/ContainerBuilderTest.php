<?php
/**
 * Copyright 2012 Eric D. Hough (http://ehough.com)
 *
 * This file is part of iconic (https://github.com/ehough/iconic)
 *
 * iconic is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * iconic is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with iconic.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/*
 * Original author:
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

require_once __DIR__.'/../../../../resources/fixtures/includes/classes.php';
require_once __DIR__.'/../../../../resources/fixtures/includes/ProjectExtension.php';

class ContainerBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testDefinitions()
    {
        $builder = new ehough_iconic_impl_ContainerBuilder();
        $definitions = array(
            'foo' => new ehough_iconic_impl_Definition('FooClass'),
            'bar' => new ehough_iconic_impl_Definition('BarClass'),
        );
        $builder->setDefinitions($definitions);
        $this->assertEquals($definitions, $builder->getDefinitions(), '->setDefinitions() sets the service definitions');
        $this->assertTrue($builder->hasDefinition('foo'), '->hasDefinition() returns true if a service definition exists');
        $this->assertFalse($builder->hasDefinition('foobar'), '->hasDefinition() returns false if a service definition does not exist');

        $builder->setDefinition('foobar', $foo = new ehough_iconic_impl_Definition('FooBarClass'));
        $this->assertEquals($foo, $builder->getDefinition('foobar'), '->getDefinition() returns a service definition if defined');
        $this->assertTrue($builder->setDefinition('foobar', $foo = new ehough_iconic_impl_Definition('FooBarClass')) === $foo, '->setDefinition() implements a fluid interface by returning the service reference');

        $builder->addDefinitions($defs = array('foobar' => new ehough_iconic_impl_Definition('FooBarClass')));
        $this->assertEquals(array_merge($definitions, $defs), $builder->getDefinitions(), '->addDefinitions() adds the service definitions');

        try {
            $builder->getDefinition('baz');
            $this->fail('->getDefinition() throws an InvalidArgumentException if the service definition does not exist');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('The service definition "baz" does not exist.', $e->getMessage(), '->getDefinition() throws an InvalidArgumentException if the service definition does not exist');
        }
    }

    public function testRegister()
    {
        $builder = new ehough_iconic_impl_ContainerBuilder();
        $builder->register('foo', 'FooClass');
        $this->assertTrue($builder->hasDefinition('foo'), '->register() registers a new service definition');
        $this->assertInstanceOf('ehough_iconic_impl_Definition', $builder->getDefinition('foo'), '->register() returns the newly created Definition instance');
    }

    public function testHas()
    {
        $builder = new ehough_iconic_impl_ContainerBuilder();
        $this->assertFalse($builder->has('foo'), '->has() returns false if the service does not exist');
        $builder->register('foo', 'FooClass');
        $this->assertTrue($builder->has('foo'), '->has() returns true if a service definition exists');
        $builder->set('bar', new \stdClass());
        $this->assertTrue($builder->has('bar'), '->has() returns true if a service exists');
    }

    public function testGet()
    {
        $builder = new ehough_iconic_impl_ContainerBuilder();
        try {
            $builder->get('foo');
            $this->fail('->get() throws an InvalidArgumentException if the service does not exist');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('The service definition "foo" does not exist.', $e->getMessage(), '->get() throws an InvalidArgumentException if the service does not exist');
        }

        $this->assertNull($builder->get('foo', ehough_iconic_api_IContainer::NULL_ON_INVALID_REFERENCE), '->get() returns null if the service does not exist and NULL_ON_INVALID_REFERENCE is passed as a second argument');

        $builder->register('foo', 'stdClass');
        $this->assertInternalType('object', $builder->get('foo'), '->get() returns the service definition associated with the id');
        $builder->set('bar', $bar = new stdClass());
        $this->assertEquals($bar, $builder->get('bar'), '->get() returns the service associated with the id');
        $builder->register('bar', 'stdClass');
        $this->assertEquals($bar, $builder->get('bar'), '->get() returns the service associated with the id even if a definition has been defined');

        $builder->register('baz', 'stdClass')->setArguments(array(new ehough_iconic_impl_Reference('baz')));
        try {
            @$builder->get('baz');
            $this->fail('->get() throws a ServiceCircularReferenceException if the service has a circular reference to itself');
        } catch (ehough_iconic_api_exception_ServiceCircularReferenceException $e) {
            $this->assertEquals('Circular reference detected for service "baz", path: "baz".', $e->getMessage(), '->get() throws a LogicException if the service has a circular reference to itself');
        }

        $builder->register('foobar', 'stdClass')->setScope('container');
        $this->assertTrue($builder->get('bar') === $builder->get('bar'), '->get() always returns the same instance if the service is shared');
    }

    public function testGetServiceIds()
    {
        $builder = new ehough_iconic_impl_ContainerBuilder();
        $builder->register('foo', 'stdClass');
        $builder->bar = $bar = new stdClass();
        $builder->register('bar', 'stdClass');
        $this->assertEquals(array('foo', 'bar', 'service_container'), $builder->getServiceIds(), '->getServiceIds() returns all defined service ids');
    }

    public function testAddGetCompilerPass()
    {
        $builder = new ehough_iconic_impl_ContainerBuilder();
        $builderCompilerPasses = $builder->getCompiler()->getPassConfig()->getPasses();
        $builder->addCompilerPass($this->getMock('ehough_iconic_api_compiler_ICompilerPass'));
        $this->assertEquals(sizeof($builderCompilerPasses) + 1, sizeof($builder->getCompiler()->getPassConfig()->getPasses()));
    }

    public function testCreateService()
    {
        $builder = new ehough_iconic_impl_ContainerBuilder();
        $builder->register('foo1', 'FooClass')->setFile(__DIR__.'/../../../../resources/fixtures/includes/foo.php');
        $this->assertInstanceOf('FooClass', $builder->get('foo1'), '->createService() requires the file defined by the service definition');
        $builder->register('foo2', 'FooClass')->setFile(__DIR__.'/../../../../resources/fixtures/includes/%file%.php');
        $builder->setParameter('file', 'foo');
        $this->assertInstanceOf('FooClass', $builder->get('foo2'), '->createService() replaces parameters in the file provided by the service definition');
    }

    public function testCreateServiceClass()
    {
        $builder = new ehough_iconic_impl_ContainerBuilder();
        $builder->register('foo1', '%class%');
        $builder->setParameter('class', 'stdClass');
        $this->assertInstanceOf('stdClass', $builder->get('foo1'), '->createService() replaces parameters in the class provided by the service definition');
    }

    public function testCreateServiceArguments()
    {
        $builder = new ehough_iconic_impl_ContainerBuilder();
        $builder->register('bar', 'stdClass');
        $builder->register('foo1', 'FooClass')->addArgument(array('foo' => '%value%', '%value%' => 'foo', new ehough_iconic_impl_Reference('bar'), '%%unescape_it%%'));
        $builder->setParameter('value', 'bar');
        $this->assertEquals(array('foo' => 'bar', 'bar' => 'foo', $builder->get('bar'), '%unescape_it%'), $builder->get('foo1')->arguments, '->createService() replaces parameters and service references in the arguments provided by the service definition');
    }

    public function testCreateServiceFactoryMethod()
    {
        $builder = new ehough_iconic_impl_ContainerBuilder();
        $builder->register('bar', 'stdClass');
        $builder->register('foo1', 'FooClass')->setFactoryClass('FooClass')->setFactoryMethod('getInstance')->addArgument(array('foo' => '%value%', '%value%' => 'foo', new ehough_iconic_impl_Reference('bar')));
        $builder->setParameter('value', 'bar');
        $this->assertTrue($builder->get('foo1')->called, '->createService() calls the factory method to create the service instance');
        $this->assertEquals(array('foo' => 'bar', 'bar' => 'foo', $builder->get('bar')), $builder->get('foo1')->arguments, '->createService() passes the arguments to the factory method');
    }

    public function testCreateServiceFactoryService()
    {
        $builder = new ehough_iconic_impl_ContainerBuilder();
        $builder->register('baz_service')->setFactoryService('baz_factory')->setFactoryMethod('getInstance');
        $builder->register('baz_factory', 'BazClass');

        $this->assertInstanceOf('BazClass', $builder->get('baz_service'));
    }

    public function testCreateServiceMethodCalls()
    {
        $builder = new ehough_iconic_impl_ContainerBuilder();
        $builder->register('bar', 'stdClass');
        $builder->register('foo1', 'FooClass')->addMethodCall('setBar', array(array('%value%', new ehough_iconic_impl_Reference('bar'))));
        $builder->setParameter('value', 'bar');
        $this->assertEquals(array('bar', $builder->get('bar')), $builder->get('foo1')->bar, '->createService() replaces the values in the method calls arguments');
    }

    public function testCreateServiceConfigurator()
    {
        $builder = new ehough_iconic_impl_ContainerBuilder();
        $builder->register('foo1', 'FooClass')->setConfigurator('sc_configure');
        $this->assertTrue($builder->get('foo1')->configured, '->createService() calls the configurator');

        $builder->register('foo2', 'FooClass')->setConfigurator(array('%class%', 'configureStatic'));
        $builder->setParameter('class', 'BazClass');
        $this->assertTrue($builder->get('foo2')->configured, '->createService() calls the configurator');

        $builder->register('baz', 'BazClass');
        $builder->register('foo3', 'FooClass')->setConfigurator(array(new ehough_iconic_impl_Reference('baz'), 'configure'));
        $this->assertTrue($builder->get('foo3')->configured, '->createService() calls the configurator');

        $builder->register('foo4', 'FooClass')->setConfigurator('foo');
        try {
            $builder->get('foo4');
            $this->fail('->createService() throws an InvalidArgumentException if the configure callable is not a valid callable');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('The configure callable for class "FooClass" is not a callable.', $e->getMessage(), '->createService() throws an InvalidArgumentException if the configure callable is not a valid callable');
        }
    }

    public function testResolveServices()
    {
        $builder = new ehough_iconic_impl_ContainerBuilder();
        $builder->register('foo', 'FooClass');
        $this->assertEquals($builder->get('foo'), $builder->resolveServices(new ehough_iconic_impl_Reference('foo')), '->resolveServices() resolves service references to service instances');
        $this->assertEquals(array('foo' => array('foo', $builder->get('foo'))), $builder->resolveServices(array('foo' => array('foo', new ehough_iconic_impl_Reference('foo')))), '->resolveServices() resolves service references to service instances in nested arrays');
    }

    public function testMerge()
    {
        $container = new ehough_iconic_impl_ContainerBuilder(new ehough_iconic_impl_parameterbag_ParameterBag(array('bar' => 'foo')));
        $config = new ehough_iconic_impl_ContainerBuilder(new ehough_iconic_impl_parameterbag_ParameterBag(array('foo' => 'bar')));
        $container->merge($config);
        $this->assertEquals(array('bar' => 'foo', 'foo' => 'bar'), $container->getParameterBag()->all(), '->merge() merges current parameters with the loaded ones');

        $container = new ehough_iconic_impl_ContainerBuilder(new ehough_iconic_impl_parameterbag_ParameterBag(array('bar' => 'foo')));
        $config = new ehough_iconic_impl_ContainerBuilder(new ehough_iconic_impl_parameterbag_ParameterBag(array('foo' => '%bar%')));
        $container->merge($config);
////// FIXME
        $container->compile();
        $this->assertEquals(array('bar' => 'foo', 'foo' => 'foo'), $container->getParameterBag()->all(), '->merge() evaluates the values of the parameters towards already defined ones');

        $container = new ehough_iconic_impl_ContainerBuilder(new ehough_iconic_impl_parameterbag_ParameterBag(array('bar' => 'foo')));
        $config = new ehough_iconic_impl_ContainerBuilder(new ehough_iconic_impl_parameterbag_ParameterBag(array('foo' => '%bar%', 'baz' => '%foo%')));
        $container->merge($config);
////// FIXME
        $container->compile();
        $this->assertEquals(array('bar' => 'foo', 'foo' => 'foo', 'baz' => 'foo'), $container->getParameterBag()->all(), '->merge() evaluates the values of the parameters towards already defined ones');

        $container = new ehough_iconic_impl_ContainerBuilder();
        $container->register('foo', 'FooClass');
        $container->register('bar', 'BarClass');
        $config = new ehough_iconic_impl_ContainerBuilder();
        $config->setDefinition('baz', new ehough_iconic_impl_Definition('BazClass'));
        $config->setAlias('alias_for_foo', 'foo');
        $container->merge($config);
        $this->assertEquals(array('foo', 'bar', 'baz'), array_keys($container->getDefinitions()), '->merge() merges definitions already defined ones');

        $aliases = $container->getAliases();
        $this->assertTrue(isset($aliases['alias_for_foo']));
        $this->assertEquals('foo', (string) $aliases['alias_for_foo']);

        $container = new ehough_iconic_impl_ContainerBuilder();
        $container->register('foo', 'FooClass');
        $config->setDefinition('foo', new ehough_iconic_impl_Definition('BazClass'));
        $container->merge($config);
        $this->assertEquals('BazClass', $container->getDefinition('foo')->getClass(), '->merge() overrides already defined services');
    }

    /**
     * @expectedException LogicException
     */
    public function testMergeLogicException()
    {
        $container = new ehough_iconic_impl_ContainerBuilder();
        $container->compile();
        $container->merge(new ehough_iconic_impl_ContainerBuilder());
    }

    public function testfindTaggedServiceIds()
    {
        $builder = new ehough_iconic_impl_ContainerBuilder();
        $builder
            ->register('foo', 'FooClass')
            ->addTag('foo', array('foo' => 'foo'))
            ->addTag('bar', array('bar' => 'bar'))
            ->addTag('foo', array('foofoo' => 'foofoo'))
        ;
        $this->assertEquals($builder->findTaggedServiceIds('foo'), array(
            'foo' => array(
                array('foo' => 'foo'),
                array('foofoo' => 'foofoo'),
            )
        ), '->findTaggedServiceIds() returns an array of service ids and its tag attributes');
        $this->assertEquals(array(), $builder->findTaggedServiceIds('foobar'), '->findTaggedServiceIds() returns an empty array if there is annotated services');
    }

    public function testFindDefinition()
    {
        $container = new ehough_iconic_impl_ContainerBuilder();
        $container->setDefinition('foo', $definition = new ehough_iconic_impl_Definition('FooClass'));

        $container->setAlias('bar', 'foo');
        $container->setAlias('foobar', 'bar');

        $this->assertEquals($definition, $container->findDefinition('foo'), '->findDefinition() returns a Definition');
    }

    public function testExtension()
    {
        $container = new ehough_iconic_impl_ContainerBuilder();

        $container->registerExtension($extension = new ehough_iconic_impl_extension_ProjectExtension());
        $this->assertTrue($container->getExtension('project') === $extension, '->registerExtension() registers an extension');

        $this->setExpectedException('ehough_iconic_api_exception_LogicException');
        $container->getExtension('no_registered');
    }

    public function testRegisteredAndLoadedExtension()
    {
        $extension = $this->getMock('ehough_iconic_api_extension_IExtension');
        $extension->expects($this->exactly(1))->method('getAlias')->will($this->returnValue('project'));
        $extension->expects($this->once())->method('load');

        $container = new ehough_iconic_impl_ContainerBuilder();
        $container->registerExtension($extension);
        $container->compile();
    }


    public function testPrivateServiceUser()
    {
        $fooDefinition     = new ehough_iconic_impl_Definition('BarClass');
        $fooUserDefinition = new ehough_iconic_impl_Definition('BarUserClass', array(new ehough_iconic_impl_Reference('bar')));
        $container         = new ehough_iconic_impl_ContainerBuilder();

        $fooDefinition->setPublic(false);

        $container->addDefinitions(array(
            'bar'       => $fooDefinition,
            'bar_user'  => $fooUserDefinition
        ));

        $container->compile();
        $this->assertInstanceOf('BarClass', $container->get('bar_user')->bar);
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testThrowsExceptionWhenSetServiceOnAFrozenContainer()
    {
        $container = new ehough_iconic_impl_ContainerBuilder();
        $container->compile();
        $container->set('a', new \stdClass());
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testThrowsExceptionWhenSetDefinitionOnAFrozenContainer()
    {
        $container = new ehough_iconic_impl_ContainerBuilder();
        $container->compile();
        $container->setDefinition('a', new ehough_iconic_impl_Definition());
    }

    public function testAliases()
    {
        $builder = new ehough_iconic_impl_ContainerBuilder();
        $builder->register('foo', 'stdClass');
        $builder->setAlias('bar', 'foo');
        $this->assertTrue($builder->hasAlias('bar'), '->hasAlias() returns true if the alias exists');
        $this->assertFalse($builder->hasAlias('foobar'), '->hasAlias() returns false if the alias does not exist');
        $this->assertEquals('foo', (string) $builder->getAlias('bar'), '->getAlias() returns the aliased service');
        $this->assertTrue($builder->has('bar'), '->setAlias() defines a new service');
        $this->assertTrue($builder->get('bar') === $builder->get('foo'), '->setAlias() creates a service that is an alias to another one');

        try {
            $builder->getAlias('foobar');
            $this->fail('->getAlias() throws an InvalidArgumentException if the alias does not exist');
        } catch (\ehough_iconic_api_exception_InvalidArgumentException $e) {
            $this->assertEquals('The service alias "foobar" does not exist.', $e->getMessage(), '->getAlias() throws an InvalidArgumentException if the alias does not exist');
        }
    }

    public function testGetAliases()
    {
        $builder = new ehough_iconic_impl_ContainerBuilder();
        $builder->setAlias('bar', 'foo');
        $builder->setAlias('foobar', 'foo');
        $builder->setAlias('moo', new ehough_iconic_impl_Alias('foo', false));

        $aliases = $builder->getAliases();
        $this->assertEquals('foo', (string) $aliases['bar']);
        $this->assertTrue($aliases['bar']->isPublic());
        $this->assertEquals('foo', (string) $aliases['foobar']);
        $this->assertEquals('foo', (string) $aliases['moo']);
        $this->assertFalse($aliases['moo']->isPublic());

        $builder->register('bar', 'stdClass');
        $this->assertFalse($builder->hasAlias('bar'));

        $builder->set('foobar', 'stdClass');
        $builder->set('moo', 'stdClass');
        $this->assertCount(0, $builder->getAliases(), '->getAliases() does not return aliased services that have been overridden');
    }


    public function testSetAliases()
    {
        $builder = new ehough_iconic_impl_ContainerBuilder();
        $builder->setAliases(array('bar' => 'foo', 'foobar' => 'foo'));

        $aliases = $builder->getAliases();
        $this->assertTrue(isset($aliases['bar']));
        $this->assertTrue(isset($aliases['foobar']));
    }

    public function testAddAliases()
    {
        $builder = new ehough_iconic_impl_ContainerBuilder();
        $builder->setAliases(array('bar' => 'foo'));
        $builder->addAliases(array('foobar' => 'foo'));

        $aliases = $builder->getAliases();
        $this->assertTrue(isset($aliases['bar']));
        $this->assertTrue(isset($aliases['foobar']));
    }
}