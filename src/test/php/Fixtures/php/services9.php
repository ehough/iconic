<?php

/**
 * ProjectServiceContainer
 *
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 */
class ProjectServiceContainer extends ehough_iconic_Container
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(new ehough_iconic_parameterbag_ParameterBag($this->getDefaultParameters()));
        $this->methodMap = array(
            'bar' => 'getBarService',
            'baz' => 'getBazService',
            'configurator_service' => 'getConfiguratorServiceService',
            'configured_service' => 'getConfiguredServiceService',
            'decorated' => 'getDecoratedService',
            'decorator_service' => 'getDecoratorServiceService',
            'decorator_service_with_name' => 'getDecoratorServiceWithNameService',
            'depends_on_request' => 'getDependsOnRequestService',
            'factory_service' => 'getFactoryServiceService',
            'foo' => 'getFooService',
            'foo.baz' => 'getFoo_BazService',
            'foo_bar' => 'getFooBarService',
            'foo_with_inline' => 'getFooWithInlineService',
            'inlined' => 'getInlinedService',
            'method_call1' => 'getMethodCall1Service',
            'request' => 'getRequestService',
        );
        $this->aliases = array(
            'alias_for_alias' => 'foo',
            'alias_for_foo' => 'foo',
        );
    }

    /**
     * Gets the 'bar' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return FooClass A FooClass instance.
     */
    protected function getBarService()
    {
        $a = $this->get('foo.baz');

        $this->services['bar'] = $instance = new FooClass('foo', $a, $this->getParameter('foo_bar'));

        $a->configure($instance);

        return $instance;
    }

    /**
     * Gets the 'baz' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Baz A Baz instance.
     */
    protected function getBazService()
    {
        $this->services['baz'] = $instance = new Baz();

        $instance->setFoo($this->get('foo_with_inline'));

        return $instance;
    }

    /**
     * Gets the 'configured_service' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return stdClass A stdClass instance.
     */
    protected function getConfiguredServiceService()
    {
        $this->services['configured_service'] = $instance = new stdClass();

        $this->get('configurator_service')->configureStdClass($instance);

        return $instance;
    }

    /**
     * Gets the 'decorated' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return stdClass A stdClass instance.
     */
    protected function getDecoratedService()
    {
        return $this->services['decorated'] = new stdClass();
    }

    /**
     * Gets the 'decorator_service' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return stdClass A stdClass instance.
     */
    protected function getDecoratorServiceService()
    {
        return $this->services['decorator_service'] = new stdClass();
    }

    /**
     * Gets the 'decorator_service_with_name' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return stdClass A stdClass instance.
     */
    protected function getDecoratorServiceWithNameService()
    {
        return $this->services['decorator_service_with_name'] = new stdClass();
    }

    /**
     * Gets the 'depends_on_request' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return stdClass A stdClass instance.
     */
    protected function getDependsOnRequestService()
    {
        $this->services['depends_on_request'] = $instance = new stdClass();

        $instance->setRequest($this->get('request', ehough_iconic_ContainerInterface::NULL_ON_INVALID_REFERENCE));

        return $instance;
    }

    /**
     * Gets the 'factory_service' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Bar A Bar instance.
     */
    protected function getFactoryServiceService()
    {
        return $this->services['factory_service'] = $this->get('foo.baz')->getInstance();
    }

    /**
     * Gets the 'foo' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return FooClass A FooClass instance.
     */
    protected function getFooService()
    {
        $a = $this->get('foo.baz');

        $this->services['foo'] = $instance = FooClass::getInstance('foo', $a, array($this->getParameter('foo') => 'foo is '.$this->getParameter('foo').'', 'foobar' => $this->getParameter('foo')), true, $this);

        $instance->setBar($this->get('bar'));
        $instance->initialize();
        $instance->foo = 'bar';
        $instance->moo = $a;
        $instance->qux = array($this->getParameter('foo') => 'foo is '.$this->getParameter('foo').'', 'foobar' => $this->getParameter('foo'));
        sc_configure($instance);

        return $instance;
    }

    /**
     * Gets the 'foo.baz' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return object A %baz_class% instance.
     */
    protected function getFoo_BazService()
    {
        $this->services['foo.baz'] = $instance = call_user_func(array($this->getParameter('baz_class'), 'getInstance'));

        call_user_func(array($this->getParameter('baz_class'), 'configureStatic1'), $instance);

        return $instance;
    }

    /**
     * Gets the 'foo_bar' service.
     *
     * @return object A %foo_class% instance.
     */
    protected function getFooBarService()
    {
        $class = $this->getParameter('foo_class');

        return new $class();
    }

    /**
     * Gets the 'foo_with_inline' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Foo A Foo instance.
     */
    protected function getFooWithInlineService()
    {
        $this->services['foo_with_inline'] = $instance = new Foo();

        $instance->setBar($this->get('inlined'));

        return $instance;
    }

    /**
     * Gets the 'method_call1' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return FooClass A FooClass instance.
     */
    protected function getMethodCall1Service()
    {
        require_once '%path%foo.php';

        $this->services['method_call1'] = $instance = new FooClass();

        $instance->setBar($this->get('foo'));
        $instance->setBar($this->get('foo2', ehough_iconic_ContainerInterface::NULL_ON_INVALID_REFERENCE));
        if ($this->has('foo3')) {
            $instance->setBar($this->get('foo3', ehough_iconic_ContainerInterface::NULL_ON_INVALID_REFERENCE));
        }
        if ($this->has('foobaz')) {
            $instance->setBar($this->get('foobaz', ehough_iconic_ContainerInterface::NULL_ON_INVALID_REFERENCE));
        }
        $instance->setBar(($this->get("foo")->foo() . $this->getParameter("foo")));

        return $instance;
    }

    /**
     * Gets the 'request' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @throws ehough_iconic_exception_RuntimeException always since this service is expected to be injected dynamically
     */
    protected function getRequestService()
    {
        throw new ehough_iconic_exception_RuntimeException('You have requested a synthetic service ("request"). The DIC does not know how to construct this service.');
    }

    /**
     * Updates the 'request' service.
     */
    protected function synchronizeRequestService()
    {
        if ($this->initialized('depends_on_request')) {
            $this->get('depends_on_request')->setRequest($this->get('request', ehough_iconic_ContainerInterface::NULL_ON_INVALID_REFERENCE));
        }
    }

    /**
     * Gets the 'configurator_service' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return ConfClass A ConfClass instance.
     */
    protected function getConfiguratorServiceService()
    {
        $this->services['configurator_service'] = $instance = new ConfClass();

        $instance->setFoo($this->get('baz'));

        return $instance;
    }

    /**
     * Gets the 'inlined' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return Bar A Bar instance.
     */
    protected function getInlinedService()
    {
        $this->services['inlined'] = $instance = new Bar();

        $instance->setBaz($this->get('baz'));
        $instance->pub = 'pub';

        return $instance;
    }

    /**
     * Gets the default parameters.
     *
     * @return array An array of the default parameters
     */
    protected function getDefaultParameters()
    {
        return array(
            'baz_class' => 'BazClass',
            'foo_class' => 'FooClass',
            'foo' => 'bar',
        );
    }
}
