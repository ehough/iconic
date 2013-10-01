<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once dirname(__FILE__).'/Fixtures/includes/classes.php';
require_once dirname(__FILE__).'/Fixtures/includes/ProjectExtension.php';

class ehough_iconic_ContainerBuilderTest extends PHPUnit_Framework_TestCase
{
    private $_closureVarClassesPath;

    /**
     * @covers ehough_iconic_ContainerBuilder::setDefinitions
     * @covers ehough_iconic_ContainerBuilder::getDefinitions
     * @covers ehough_iconic_ContainerBuilder::setDefinition
     * @covers ehough_iconic_ContainerBuilder::getDefinition
     */
    public function testDefinitions()
    {
        $builder = new ehough_iconic_ContainerBuilder();
        $definitions = array(
            'foo' => new ehough_iconic_Definition('FooClass'),
            'bar' => new ehough_iconic_Definition('BarClass'),
        );
        $builder->setDefinitions($definitions);
        $this->assertEquals($definitions, $builder->getDefinitions(), '->setDefinitions() sets the service definitions');
        $this->assertTrue($builder->hasDefinition('foo'), '->hasDefinition() returns true if a service definition exists');
        $this->assertFalse($builder->hasDefinition('foobar'), '->hasDefinition() returns false if a service definition does not exist');

        $builder->setDefinition('foobar', $foo = new ehough_iconic_Definition('FooBarClass'));
        $this->assertEquals($foo, $builder->getDefinition('foobar'), '->getDefinition() returns a service definition if defined');
        $this->assertTrue($builder->setDefinition('foobar', $foo = new ehough_iconic_Definition('FooBarClass')) === $foo, '->setDefinition() implements a fluid interface by returning the service reference');

        $builder->addDefinitions($defs = array('foobar' => new ehough_iconic_Definition('FooBarClass')));
        $this->assertEquals(array_merge($definitions, $defs), $builder->getDefinitions(), '->addDefinitions() adds the service definitions');

        try {
            $builder->getDefinition('baz');
            $this->fail('->getDefinition() throws an InvalidArgumentException if the service definition does not exist');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('The service definition "baz" does not exist.', $e->getMessage(), '->getDefinition() throws an InvalidArgumentException if the service definition does not exist');
        }
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::register
     */
    public function testRegister()
    {
        $builder = new ehough_iconic_ContainerBuilder();
        $builder->register('foo', 'FooClass');
        $this->assertTrue($builder->hasDefinition('foo'), '->register() registers a new service definition');
        $this->assertInstanceOf('ehough_iconic_Definition', $builder->getDefinition('foo'), '->register() returns the newly created ehough_iconic_Definition instance');
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::has
     */
    public function testHas()
    {
        $builder = new ehough_iconic_ContainerBuilder();
        $this->assertFalse($builder->has('foo'), '->has() returns false if the service does not exist');
        $builder->register('foo', 'FooClass');
        $this->assertTrue($builder->has('foo'), '->has() returns true if a service definition exists');
        $builder->set('bar', new stdClass());
        $this->assertTrue($builder->has('bar'), '->has() returns true if a service exists');
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::get
     */
    public function testGet()
    {
        $builder = new ehough_iconic_ContainerBuilder();
        try {
            $builder->get('foo');
            $this->fail('->get() throws an InvalidArgumentException if the service does not exist');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('The service definition "foo" does not exist.', $e->getMessage(), '->get() throws an InvalidArgumentException if the service does not exist');
        }

        $this->assertNull($builder->get('foo', ehough_iconic_ContainerInterface::NULL_ON_INVALID_REFERENCE), '->get() returns null if the service does not exist and NULL_ON_INVALID_REFERENCE is passed as a second argument');

        $builder->register('foo', 'stdClass');
        $this->assertInternalType('object', $builder->get('foo'), '->get() returns the service definition associated with the id');
        $builder->set('bar', $bar = new stdClass());
        $this->assertEquals($bar, $builder->get('bar'), '->get() returns the service associated with the id');
        $builder->register('bar', 'stdClass');
        $this->assertEquals($bar, $builder->get('bar'), '->get() returns the service associated with the id even if a definition has been defined');

        $builder->register('baz', 'stdClass')->setArguments(array(new ehough_iconic_Reference('baz')));
        try {
            @$builder->get('baz');
            $this->fail('->get() throws a ServiceCircularReferenceException if the service has a circular reference to itself');
        } catch (ehough_iconic_exception_ServiceCircularReferenceException $e) {
            $this->assertEquals('Circular reference detected for service "baz", path: "baz".', $e->getMessage(), '->get() throws a LogicException if the service has a circular reference to itself');
        }

        $builder->register('foobar', 'stdClass')->setScope('container');
        $this->assertTrue($builder->get('bar') === $builder->get('bar'), '->get() always returns the same instance if the service is shared');
    }

    /**
     * @covers                   ehough_iconic_ContainerBuilder::get
     * @expectedException        ehough_iconic_exception_RuntimeException
     * @expectedExceptionMessage You have requested a synthetic service ("foo"). The DIC does not know how to construct this service.
     */
    public function testGetUnsetLoadingServiceWhenCreateServiceThrowsAnException()
    {
        $builder = new ehough_iconic_ContainerBuilder();
        $builder->register('foo', 'stdClass')->setSynthetic(true);

        // we expect a RuntimeException here as foo is synthetic
        try {
            $builder->get('foo');
        } catch (RuntimeException $e) {
        }

        // we must also have the same RuntimeException here
        $builder->get('foo');
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::get
     */
    public function testGetReturnsNullOnInactiveScope()
    {
        $builder = new ehough_iconic_ContainerBuilder();
        $builder->register('foo', 'stdClass')->setScope('request');

        $this->assertNull($builder->get('foo', ehough_iconic_ContainerInterface::NULL_ON_INVALID_REFERENCE));
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::getServiceIds
     */
    public function testGetServiceIds()
    {
        $builder = new ehough_iconic_ContainerBuilder();
        $builder->register('foo', 'stdClass');
        $builder->bar = $bar = new stdClass();
        $builder->register('bar', 'stdClass');
        $this->assertEquals(array('foo', 'bar', 'service_container'), $builder->getServiceIds(), '->getServiceIds() returns all defined service ids');
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::setAlias
     * @covers ehough_iconic_ContainerBuilder::hasAlias
     * @covers ehough_iconic_ContainerBuilder::getAlias
     */
    public function testAliases()
    {
        $builder = new ehough_iconic_ContainerBuilder();
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
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('The service alias "foobar" does not exist.', $e->getMessage(), '->getAlias() throws an InvalidArgumentException if the alias does not exist');
        }
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::getAliases
     */
    public function testGetAliases()
    {
        $builder = new ehough_iconic_ContainerBuilder();
        $builder->setAlias('bar', 'foo');
        $builder->setAlias('foobar', 'foo');
        $builder->setAlias('moo', new ehough_iconic_Alias('foo', false));

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

    /**
     * @covers ehough_iconic_ContainerBuilder::setAliases
     */
    public function testSetAliases()
    {
        $builder = new ehough_iconic_ContainerBuilder();
        $builder->setAliases(array('bar' => 'foo', 'foobar' => 'foo'));

        $aliases = $builder->getAliases();
        $this->assertTrue(isset($aliases['bar']));
        $this->assertTrue(isset($aliases['foobar']));
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::addAliases
     */
    public function testAddAliases()
    {
        $builder = new ehough_iconic_ContainerBuilder();
        $builder->setAliases(array('bar' => 'foo'));
        $builder->addAliases(array('foobar' => 'foo'));

        $aliases = $builder->getAliases();
        $this->assertTrue(isset($aliases['bar']));
        $this->assertTrue(isset($aliases['foobar']));
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::addCompilerPass
     * @covers ehough_iconic_ContainerBuilder::getCompilerPassConfig
     */
    public function testAddGetCompilerPass()
    {
        $builder = new ehough_iconic_ContainerBuilder();
        $builder->setResourceTracking(false);
        $builderCompilerPasses = $builder->getCompiler()->getPassConfig()->getPasses();
        $builder->addCompilerPass($this->getMock('ehough_iconic_compiler_CompilerPassInterface'));
        $this->assertEquals(sizeof($builderCompilerPasses) + 1, sizeof($builder->getCompiler()->getPassConfig()->getPasses()));
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::createService
     */
    public function testCreateService()
    {
        $builder = new ehough_iconic_ContainerBuilder();
        $builder->register('foo1', 'FooClass')->setFile(dirname(__FILE__).'/Fixtures/includes/foo.php');
        $this->assertInstanceOf('FooClass', $builder->get('foo1'), '->createService() requires the file defined by the service definition');
        $builder->register('foo2', 'FooClass')->setFile(dirname(__FILE__).'/Fixtures/includes/%file%.php');
        $builder->setParameter('file', 'foo');
        $this->assertInstanceOf('FooClass', $builder->get('foo2'), '->createService() replaces parameters in the file provided by the service definition');
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::createService
     */
    public function testCreateProxyWithRealServiceInstantiator()
    {
        $builder = new ehough_iconic_ContainerBuilder();

        $builder->register('foo1', 'FooClass')->setFile(dirname(__FILE__).'/Fixtures/includes/foo.php');
        $builder->getDefinition('foo1')->setLazy(true);

        $foo1 = $builder->get('foo1');

        $this->assertSame($foo1, $builder->get('foo1'), 'The same proxy is retrieved on multiple subsequent calls');
        $this->assertSame('FooClass', get_class($foo1));
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::createService
     */
    public function testCreateServiceClass()
    {
        $builder = new ehough_iconic_ContainerBuilder();
        $builder->register('foo1', '%class%');
        $builder->setParameter('class', 'stdClass');
        $this->assertInstanceOf('stdClass', $builder->get('foo1'), '->createService() replaces parameters in the class provided by the service definition');
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::createService
     */
    public function testCreateServiceArguments()
    {
        $builder = new ehough_iconic_ContainerBuilder();
        $builder->register('bar', 'stdClass');
        $builder->register('foo1', 'FooClass')->addArgument(array('foo' => '%value%', '%value%' => 'foo', new ehough_iconic_Reference('bar'), '%%unescape_it%%'));
        $builder->setParameter('value', 'bar');
        $this->assertEquals(array('foo' => 'bar', 'bar' => 'foo', $builder->get('bar'), '%unescape_it%'), $builder->get('foo1')->arguments, '->createService() replaces parameters and service references in the arguments provided by the service definition');
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::createService
     */
    public function testCreateServiceFactoryMethod()
    {
        $builder = new ehough_iconic_ContainerBuilder();
        $builder->register('bar', 'stdClass');
        $builder->register('foo1', 'FooClass')->setFactoryClass('FooClass')->setFactoryMethod('getInstance')->addArgument(array('foo' => '%value%', '%value%' => 'foo', new ehough_iconic_Reference('bar')));
        $builder->setParameter('value', 'bar');
        $this->assertTrue($builder->get('foo1')->called, '->createService() calls the factory method to create the service instance');
        $this->assertEquals(array('foo' => 'bar', 'bar' => 'foo', $builder->get('bar')), $builder->get('foo1')->arguments, '->createService() passes the arguments to the factory method');
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::createService
     */
    public function testCreateServiceFactoryService()
    {
        $builder = new ehough_iconic_ContainerBuilder();
        $builder->register('baz_service')->setFactoryService('baz_factory')->setFactoryMethod('getInstance');
        $builder->register('baz_factory', 'BazClass');

        $this->assertInstanceOf('BazClass', $builder->get('baz_service'));
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::createService
     */
    public function testCreateServiceMethodCalls()
    {
        $builder = new ehough_iconic_ContainerBuilder();
        $builder->register('bar', 'stdClass');
        $builder->register('foo1', 'FooClass')->addMethodCall('setBar', array(array('%value%', new ehough_iconic_Reference('bar'))));
        $builder->setParameter('value', 'bar');
        $this->assertEquals(array('bar', $builder->get('bar')), $builder->get('foo1')->bar, '->createService() replaces the values in the method calls arguments');
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::createService
     */
    public function testCreateServiceConfigurator()
    {
        $builder = new ehough_iconic_ContainerBuilder();
        $builder->register('foo1', 'FooClass')->setConfigurator('sc_configure');
        $this->assertTrue($builder->get('foo1')->configured, '->createService() calls the configurator');

        $builder->register('foo2', 'FooClass')->setConfigurator(array('%class%', 'configureStatic'));
        $builder->setParameter('class', 'BazClass');
        $this->assertTrue($builder->get('foo2')->configured, '->createService() calls the configurator');

        $builder->register('baz', 'BazClass');
        $builder->register('foo3', 'FooClass')->setConfigurator(array(new ehough_iconic_Reference('baz'), 'configure'));
        $this->assertTrue($builder->get('foo3')->configured, '->createService() calls the configurator');

        $builder->register('foo4', 'FooClass')->setConfigurator('foo');
        try {
            $builder->get('foo4');
            $this->fail('->createService() throws an InvalidArgumentException if the configure callable is not a valid callable');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('The configure callable for class "FooClass" is not a callable.', $e->getMessage(), '->createService() throws an InvalidArgumentException if the configure callable is not a valid callable');
        }
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::createService
     * @expectedException RuntimeException
     */
    public function testCreateSyntheticService()
    {
        $builder = new ehough_iconic_ContainerBuilder();
        $builder->register('foo', 'FooClass')->setSynthetic(true);
        $builder->get('foo');
    }

    public function testCreateServiceWithExpression()
    {
        if (!class_exists('Symfony\Component\ExpressionLanguage\Expression')) {

            $this->markTestSkipped('Symfony expression language component is missing');
            return;
        }

        $builder = new ehough_iconic_ContainerBuilder();
        $builder->setParameter('bar', 'bar');
        $builder->register('bar', 'BarClass');
        $ref = new ReflectionClass('Symfony\Component\ExpressionLanguage\Expression');
        $expression = $ref->newInstance('service("bar").foo ~ parameter("bar")');
        $builder->register('foo', 'FooClass')->addArgument(array('foo' => $expression));
        $this->assertEquals('foobar', $builder->get('foo')->arguments['foo']);
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::resolveServices
     */
    public function testResolveServices()
    {
        $builder = new ehough_iconic_ContainerBuilder();
        $builder->register('foo', 'FooClass');
        $this->assertEquals($builder->get('foo'), $builder->resolveServices(new ehough_iconic_Reference('foo')), '->resolveServices() resolves service references to service instances');
        $this->assertEquals(array('foo' => array('foo', $builder->get('foo'))), $builder->resolveServices(array('foo' => array('foo', new ehough_iconic_Reference('foo')))), '->resolveServices() resolves service references to service instances in nested arrays');

        if (class_exists('Symfony\Component\ExpressionLanguage\Expression')) {

            $ref = new ReflectionClass('Symfony\Component\ExpressionLanguage\Expression');
            $expression = $ref->newInstance('service("foo")');
            $this->assertEquals($builder->get('foo'), $builder->resolveServices($expression), '->resolveServices() resolves expressions');
        }
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::merge
     */
    public function testMerge()
    {
        $container = new ehough_iconic_ContainerBuilder(new ehough_iconic_parameterbag_ParameterBag(array('bar' => 'foo')));
        $container->setResourceTracking(false);
        $config = new ehough_iconic_ContainerBuilder(new ehough_iconic_parameterbag_ParameterBag(array('foo' => 'bar')));
        $container->merge($config);
        $this->assertEquals(array('bar' => 'foo', 'foo' => 'bar'), $container->getParameterBag()->all(), '->merge() merges current parameters with the loaded ones');

        $container = new ehough_iconic_ContainerBuilder(new ehough_iconic_parameterbag_ParameterBag(array('bar' => 'foo')));
        $container->setResourceTracking(false);
        $config = new ehough_iconic_ContainerBuilder(new ehough_iconic_parameterbag_ParameterBag(array('foo' => '%bar%')));
        $container->merge($config);
        $container->compile();
        $this->assertEquals(array('bar' => 'foo', 'foo' => 'foo'), $container->getParameterBag()->all(), '->merge() evaluates the values of the parameters towards already defined ones');

        $container = new ehough_iconic_ContainerBuilder(new ehough_iconic_parameterbag_ParameterBag(array('bar' => 'foo')));
        $container->setResourceTracking(false);
        $config = new ehough_iconic_ContainerBuilder(new ehough_iconic_parameterbag_ParameterBag(array('foo' => '%bar%', 'baz' => '%foo%')));
        $container->merge($config);
        $container->compile();
        $this->assertEquals(array('bar' => 'foo', 'foo' => 'foo', 'baz' => 'foo'), $container->getParameterBag()->all(), '->merge() evaluates the values of the parameters towards already defined ones');

        $container = new ehough_iconic_ContainerBuilder();
        $container->setResourceTracking(false);
        $container->register('foo', 'FooClass');
        $container->register('bar', 'BarClass');
        $config = new ehough_iconic_ContainerBuilder();
        $config->setDefinition('baz', new ehough_iconic_Definition('BazClass'));
        $config->setAlias('alias_for_foo', 'foo');
        $container->merge($config);
        $this->assertEquals(array('foo', 'bar', 'baz'), array_keys($container->getDefinitions()), '->merge() merges definitions already defined ones');

        $aliases = $container->getAliases();
        $this->assertTrue(isset($aliases['alias_for_foo']));
        $this->assertEquals('foo', (string) $aliases['alias_for_foo']);

        $container = new ehough_iconic_ContainerBuilder();
        $container->setResourceTracking(false);
        $container->register('foo', 'FooClass');
        $config->setDefinition('foo', new ehough_iconic_Definition('BazClass'));
        $container->merge($config);
        $this->assertEquals('BazClass', $container->getDefinition('foo')->getClass(), '->merge() overrides already defined services');
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::merge
     * @expectedException LogicException
     */
    public function testMergeLogicException()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->setResourceTracking(false);
        $container->compile();
        $container->merge(new ehough_iconic_ContainerBuilder());
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::findTaggedServiceIds
     */
    public function testfindTaggedServiceIds()
    {
        $builder = new ehough_iconic_ContainerBuilder();
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

    /**
     * @covers ehough_iconic_ContainerBuilder::findDefinition
     */
    public function testFindDefinition()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->setDefinition('foo', $definition = new ehough_iconic_Definition('FooClass'));
        $container->setAlias('bar', 'foo');
        $container->setAlias('foobar', 'bar');
        $this->assertEquals($definition, $container->findDefinition('foobar'), '->findDefinition() returns a ehough_iconic_Definition');
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::addObjectResource
     */
    public function testAddObjectResource()
    {
        if (version_compare(PHP_VERSION, '5.3') < 0 || !class_exists('Symfony\Component\Config\Resource\FileResource')) {
            $this->markTestSkipped('The "Config" component is not available');
        }

        $container = new ehough_iconic_ContainerBuilder();

        $container->setResourceTracking(false);
        $container->addObjectResource(new BarClass());

        $this->assertEmpty($container->getResources(), 'No resources get registered without resource tracking');

        $container->setResourceTracking(true);
        $container->addObjectResource(new BarClass());

        $resources = $container->getResources();

        $this->assertCount(1, $resources, '1 resource was registered');

        /* @var $resource \Symfony\Component\Config\Resource\FileResource */
        $resource = end($resources);

        $this->assertInstanceOf('Symfony\Component\Config\Resource\FileResource', $resource);
        $this->assertSame(realpath(dirname(__FILE__).'/Fixtures/includes/classes.php'), realpath($resource->getResource()));
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::addClassResource
     */
    public function testAddClassResource()
    {
        if (version_compare(PHP_VERSION, '5.3') < 0 || !class_exists('Symfony\Component\Config\Resource\FileResource')) {
            $this->markTestSkipped('The "Config" component is not available');
        }

        $container = new ehough_iconic_ContainerBuilder();

        $container->setResourceTracking(false);
        $container->addClassResource(new ReflectionClass('BarClass'));

        $this->assertEmpty($container->getResources(), 'No resources get registered without resource tracking');

        $container->setResourceTracking(true);
        $container->addClassResource(new ReflectionClass('BarClass'));

        $resources = $container->getResources();

        $this->assertCount(1, $resources, '1 resource was registered');

        /* @var $resource \Symfony\Component\Config\Resource\FileResource */
        $resource = end($resources);

        $this->assertInstanceOf('Symfony\Component\Config\Resource\FileResource', $resource);
        $this->assertSame(realpath(dirname(__FILE__).'/Fixtures/includes/classes.php'), realpath($resource->getResource()));
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::compile
     */
    public function testCompilesClassDefinitionsOfLazyServices()
    {
        if (version_compare(PHP_VERSION, '5.3') < 0 || !class_exists('Symfony\Component\Config\Resource\FileResource')) {
            $this->markTestSkipped('The "Config" component is not available');
        }

        $container = new ehough_iconic_ContainerBuilder();

        $this->assertEmpty($container->getResources(), 'No resources get registered without resource tracking');

        $container->register('foo', 'BarClass');
        $container->getDefinition('foo')->setLazy(true);

        $container->compile();

        $classesPath       = realpath(dirname(__FILE__).'/Fixtures/includes/classes.php');
        $this->_closureVarClassesPath = $classesPath;
        $matchingResources = array_filter(
            $container->getResources(),
            array($this, '_callbackTestCompilesClassDefinitionsOfLazyServices')
        );

        $this->assertNotEmpty($matchingResources);
    }

    public function _callbackTestCompilesClassDefinitionsOfLazyServices($resource)
    {
        return is_a($resource, 'Symfony\Component\Config\Resource\FileResource') && $this->_closureVarClassesPath === realpath($resource->getResource());
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::getResources
     * @covers ehough_iconic_ContainerBuilder::addResource
     */
    public function testResources()
    {
        if (version_compare(PHP_VERSION, '5.3') < 0 || !class_exists('Symfony\Component\Config\Resource\FileResource')) {
            $this->markTestSkipped('The "Config" component is not available');
        }

        $container = new ehough_iconic_ContainerBuilder();
        $ref = new ReflectionClass('\Symfony\Component\Config\Resource\FileResource');
        $a = $ref->newInstanceArgs(array(dirname(__FILE__).'/Fixtures/xml/services1.xml'));
        $b = $ref->newInstanceArgs(array(dirname(__FILE__).'/Fixtures/xml/services2.xml'));
        $container->addResource($a);
        $container->addResource($b);
        $resources = array();
        foreach ($container->getResources() as $resource) {
            if (false === strpos($resource, '.php')) {
                $resources[] = $resource;
            }
        }
        $this->assertEquals(array($a, $b), $resources, '->getResources() returns an array of resources read for the current configuration');
        $this->assertSame($container, $container->setResources(array()));
        $this->assertEquals(array(), $container->getResources());
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::registerExtension
     * @covers ehough_iconic_ContainerBuilder::getExtension
     */
    public function testExtension()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->setResourceTracking(false);

        $container->registerExtension($extension = new ProjectExtension());
        $this->assertTrue($container->getExtension('project') === $extension, '->registerExtension() registers an extension');

        $this->setExpectedException('LogicException');
        $container->getExtension('no_registered');
    }

    public function testRegisteredButNotLoadedExtension()
    {
        $extension = $this->getMock('ehough_iconic_extension_ExtensionInterface');
        $extension->expects($this->once())->method('getAlias')->will($this->returnValue('project'));
        $extension->expects($this->never())->method('load');

        $container = new ehough_iconic_ContainerBuilder();
        $container->setResourceTracking(false);
        $container->registerExtension($extension);
        $container->compile();
    }

    public function testRegisteredAndLoadedExtension()
    {
        $extension = $this->getMock('ehough_iconic_extension_ExtensionInterface');
        $extension->expects($this->exactly(2))->method('getAlias')->will($this->returnValue('project'));
        $extension->expects($this->once())->method('load')->with(array(array('foo' => 'bar')));

        $container = new ehough_iconic_ContainerBuilder();
        $container->setResourceTracking(false);
        $container->registerExtension($extension);
        $container->loadFromExtension('project', array('foo' => 'bar'));
        $container->compile();
    }

    public function testPrivateServiceUser()
    {
        $fooDefinition     = new ehough_iconic_Definition('BarClass');
        $fooUserDefinition = new ehough_iconic_Definition('BarUserClass', array(new ehough_iconic_Reference('bar')));
        $container         = new ehough_iconic_ContainerBuilder();
        $container->setResourceTracking(false);

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
        $container = new ehough_iconic_ContainerBuilder();
        $container->setResourceTracking(false);
        $container->setDefinition('a', new ehough_iconic_Definition('stdClass'));
        $container->compile();
        $container->set('a', new stdClass());
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testThrowsExceptionWhenAddServiceOnAFrozenContainer()
    {
        if (version_compare(PHP_VERSION, '5.3') < 0 || !class_exists('Symfony\Component\Config\Resource\FileResource')) {
            $this->markTestSkipped('The "Config" component is not available');
        }

        $container = new ehough_iconic_ContainerBuilder();
        $container->compile();
        $container->set('a', new stdClass());
    }

    public function testNoExceptionWhenSetSyntheticServiceOnAFrozenContainer()
    {
        if (version_compare(PHP_VERSION, '5.3') < 0 || !class_exists('Symfony\Component\Config\Resource\FileResource')) {
            $this->markTestSkipped('The "Config" component is not available');
        }

        $container = new ehough_iconic_ContainerBuilder();
        $def = new ehough_iconic_Definition('stdClass');
        $def->setSynthetic(true);
        $container->setDefinition('a', $def);
        $container->compile();
        $container->set('a', $a = new stdClass());
        $this->assertEquals($a, $container->get('a'));
    }

    public function testSetOnSynchronizedService()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->register('baz', 'BazClass')
            ->setSynchronized(true)
        ;
        $container->register('bar', 'BarClass')
            ->addMethodCall('setBaz', array(new ehough_iconic_Reference('baz')))
        ;

        $container->set('baz', $baz = new BazClass());
        $this->assertSame($baz, $container->get('bar')->getBaz());

        $container->set('baz', $baz = new BazClass());
        $this->assertSame($baz, $container->get('bar')->getBaz());
    }

    public function testSynchronizedServiceWithScopes()
    {
        if (version_compare(PHP_VERSION, '5.3') < 0) {
            $this->markTestSkipped('PHP < 5.3');
            return;
        }

        $container = new ehough_iconic_ContainerBuilder();
        $container->addScope(new ehough_iconic_Scope('foo'));
        $container->register('baz', 'BazClass')
            ->setSynthetic(true)
            ->setSynchronized(true)
            ->setScope('foo')
        ;
        $container->register('bar', 'BarClass')
            ->addMethodCall('setBaz', array(new ehough_iconic_Reference('baz', ehough_iconic_ContainerInterface::NULL_ON_INVALID_REFERENCE, false)))
        ;
        $container->compile();

        $container->enterScope('foo');
        $container->set('baz', $outerBaz = new BazClass(), 'foo');
        $this->assertSame($outerBaz, $container->get('bar')->getBaz());

        $container->enterScope('foo');
        $container->set('baz', $innerBaz = new BazClass(), 'foo');
        $this->assertSame($innerBaz, $container->get('bar')->getBaz());
        $container->leaveScope('foo');

        $this->assertNotSame($innerBaz, $container->get('bar')->getBaz());
        $this->assertSame($outerBaz, $container->get('bar')->getBaz());

        $container->leaveScope('foo');
    }

    /**
     * @expectedException ehough_iconic_exception_BadMethodCallException
     */
    public function testThrowsExceptionWhenSetDefinitionOnAFrozenContainer()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->setResourceTracking(false);
        $container->compile();
        $container->setDefinition('a', new ehough_iconic_Definition());
    }

    /**
     * @covers ehough_iconic_ContainerBuilder::getExtensionConfig
     * @covers ehough_iconic_ContainerBuilder::prependExtensionConfig
     */
    public function testExtensionConfig()
    {
        $container = new ehough_iconic_ContainerBuilder();

        $configs = $container->getExtensionConfig('foo');
        $this->assertEmpty($configs);

        $first = array('foo' => 'bar');
        $container->prependExtensionConfig('foo', $first);
        $configs = $container->getExtensionConfig('foo');
        $this->assertEquals(array($first), $configs);

        $second = array('ding' => 'dong');
        $container->prependExtensionConfig('foo', $second);
        $configs = $container->getExtensionConfig('foo');
        $this->assertEquals(array($second, $first), $configs);
    }
}

class ehough_iconic_FooClass {}
