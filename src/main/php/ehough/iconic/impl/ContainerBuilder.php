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

/**
 * ContainerBuilder is a DI container that provides an API to easily describe services.
 *
 * @author Eric Hough <eric@ehough.com>
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class ehough_iconic_impl_ContainerBuilder extends ehough_iconic_impl_Container
{
    private $_definitions = array();

    /**
     * Sets a service.
     *
     * @param string $id      The service identifier
     * @param object $service The service instance
     * @param string $scope   The scope
     *
     * @throws ehough_iconic_api_exception_BadMethodCallException When this ContainerBuilder is frozen
     *
     * @api
     */
    protected function _onBeforeSet($id, $service, $scope = self::SCOPE_CONTAINER)
    {
        if ($this->isFrozen()) {

            throw new ehough_iconic_api_exception_BadMethodCallException('Setting service on a frozen container is not allowed');
        }

        $id = strtolower($id);

        unset($this->_definitions[$id]);
    }

    /**
     * Removes a service definition.
     *
     * @param string $id The service identifier
     */
    public function removeDefinition($id)
    {
        unset($this->_definitions[strtolower($id)]);
    }

    /**
     * Returns true if the given service is defined.
     *
     * @param string $id The service identifier
     *
     * @return Boolean true if the service is defined, false otherwise
     */
    protected function _childHas($id)
    {
        return isset($this->_definitions[strtolower($id)]);
    }

    /**
     * @param ehough_iconic_api_exception_InvalidArgumentException $e               The caught exception.
     * @param string                                               $id              The service identifier.
     * @param int                                                  $invalidBehavior The behavior when the service does not exist.
     *
     * @return null|object|void
     *
     * @throws ehough_iconic_api_exception_LogicException           if the service has a circular reference to itself
     * @throws ehough_iconic_api_exception_InvalidArgumentException if the service is not defined
     */
    protected function _onGetCausedInvalidArgumentException(ehough_iconic_api_exception_InvalidArgumentException $e, $id, $invalidBehavior)
    {
        $id = strtolower($id);

        if ($this->_isServiceLoading($id)) {

            throw new ehough_iconic_api_exception_LogicException(sprintf('The service "%s" has a circular reference to itself.', $id), 0, $e);
        }

        try {

            $definition = $this->getDefinition($id);

        } catch (ehough_iconic_api_exception_InvalidArgumentException $e) {

            if (ehough_iconic_api_IContainer::EXCEPTION_ON_INVALID_REFERENCE !== $invalidBehavior) {

                return null;
            }

            throw $e;
        }

        $this->_markServiceAsLoading($id);

        $service = $this->createService($definition, $id);

        $this->_markServiceAsDoneLoading($id);

        return $service;
    }

    /**
     * Merges a ContainerBuilder with the current ContainerBuilder configuration.
     *
     * Service definitions overrides the current defined ones.
     *
     * But for parameters, they are overridden by the current ones. It allows
     * the parameters passed to the container constructor to have precedence
     * over the loaded ones.
     *
     * $container = new ContainerBuilder(array('foo' => 'bar'));
     * $loader = new LoaderXXX($container);
     * $loader->load('resource_name');
     * $container->register('foo', new stdClass());
     *
     * In the above example, even if the loaded resource defines a foo
     * parameter, the value will still be 'bar' as defined in the ContainerBuilder
     * constructor.
     *
     * @param ehough_iconic_impl_ContainerBuilder $container The ContainerBuilder instance to merge.
     *
     *
     * @throws BadMethodCallException When this ContainerBuilder is frozen
     *
     * @api
     */
    public function merge(ehough_iconic_impl_ContainerBuilder $container)
    {
        if ($this->isFrozen()) {

            throw new BadMethodCallException('Cannot merge on a frozen container.');
        }

        $this->addDefinitions($container->getDefinitions());
        $this->getParameterBag()->add($container->getParameterBag()->all());
    }

    /**
     * Gets all service ids.
     *
     * @return array An array of all defined service ids
     */
    protected function _childServiceIds()
    {
        return array_unique(array_keys($this->getDefinitions()));
    }

    /**
     * Registers a service definition.
     *
     * This methods allows for simple registration of service definition
     * with a fluid interface.
     *
     * @param string $id    The service identifier
     * @param string $class The service class
     *
     * @return ehough_iconic_impl_Definition A Definition instance
     */
    public function register($id, $class = null)
    {
        return $this->setDefinition(strtolower($id), new ehough_iconic_impl_Definition($class));
    }

    /**
     * Adds the service definitions.
     *
     * @param Definition[] $definitions An array of service definitions
     */
    public function addDefinitions(array $definitions)
    {
        foreach ($definitions as $id => $definition) {

            /** @noinspection PhpParamsInspection */
            $this->setDefinition($id, $definition);
        }
    }

    /**
     * Sets the service definitions.
     *
     * @param array $definitions An array of service definitions
     */
    public function setDefinitions(array $definitions)
    {
        $this->_definitions = array();

        $this->addDefinitions($definitions);
    }

    /**
     * Gets all service definitions.
     *
     * @return array An array of Definition instances
     */
    public function getDefinitions()
    {
        return $this->_definitions;
    }

    /**
     * Sets a service definition.
     *
     * @param string                        $id         The service identifier
     * @param ehough_iconic_impl_Definition $definition A Definition instance
     *
     * @return ehough_iconic_impl_Definition The definition.
     *
     * @throws ehough_iconic_api_exception_BadMethodCallException When this ContainerBuilder is frozen
     */
    public function setDefinition($id, ehough_iconic_impl_Definition $definition)
    {
        if ($this->isFrozen()) {

            throw new ehough_iconic_api_exception_BadMethodCallException('Adding definition to a frozen container is not allowed');
        }

        $id = strtolower($id);

        return $this->_definitions[$id] = $definition;
    }

    /**
     * Returns true if a service definition exists under the given identifier.
     *
     * @param string $id The service identifier
     *
     * @return Boolean true if the service definition exists, false otherwise
     */
    public function hasDefinition($id)
    {
        return array_key_exists(strtolower($id), $this->_definitions);
    }

    /**
     * Gets a service definition.
     *
     * @param string $id The service identifier
     *
     * @return ehough_iconic_impl_Definition A Definition instance
     *
     * @throws ehough_iconic_api_exception_InvalidArgumentException if the service definition does not exist
     */
    public function getDefinition($id)
    {
        $id = strtolower($id);

        if (!$this->hasDefinition($id)) {

            throw new ehough_iconic_api_exception_InvalidArgumentException(sprintf('The service definition "%s" does not exist.', $id));
        }

        return $this->_definitions[$id];
    }

    /**
     * Gets a service definition by id or alias.
     *
     * The method "unaliases" recursively to return a Definition instance.
     *
     * @param string $id The service identifier or alias
     *
     * @return ehough_iconic_impl_Definition A Definition instance
     *
     * @throws ehough_iconic_api_exception_InvalidArgumentException if the service definition does not exist
     */
    public function findDefinition($id)
    {
        return $this->getDefinition($id);
    }

    /**
     * Creates a service for a service definition.
     *
     * @param ehough_iconic_impl_Definition $definition A service definition instance
     * @param string                        $id         The service identifier
     *
     * @return object              The service described by the service definition
     *
     * @throws ehough_iconic_api_exception_RuntimeException         When factory specification is incomplete or scope is inactive
     * @throws ehough_iconic_api_exception_InvalidArgumentException When configure callable is not callable
     */
    private function createService(ehough_iconic_impl_Definition $definition, $id)
    {
        $parameterBag = $this->getParameterBag();

        if (null !== $definition->getFile()) {

            /** @noinspection PhpIncludeInspection */
            require_once $parameterBag->resolveValue($definition->getFile());
        }

        $arguments = $this->resolveServices(

            $parameterBag->unescapeValue($parameterBag->resolveValue($definition->getArguments()))
        );

        if (null !== $definition->getFactoryMethod()) {

            if (null !== $definition->getFactoryClass()) {

                $factory = $parameterBag->resolveValue($definition->getFactoryClass());

            } elseif (null !== $definition->getFactoryService()) {

                $factory = $this->get($parameterBag->resolveValue($definition->getFactoryService()));

            } else {

                throw new ehough_iconic_api_exception_RuntimeException('Cannot create service from factory method without a factory service or factory class.');
            }

            $service = call_user_func_array(array($factory, $definition->getFactoryMethod()), $arguments);

        } else {

            $r = new ReflectionClass($parameterBag->resolveValue($definition->getClass()));

            $service = null === $r->getConstructor() ? $r->newInstance() : $r->newInstanceArgs($arguments);
        }

        if (self::SCOPE_PROTOTYPE !== $scope = $definition->getScope()) {

            if (self::SCOPE_CONTAINER !== $scope) {

                throw new ehough_iconic_api_exception_RuntimeException('You tried to create a service of an inactive scope.');
            }

            $lowerId = strtolower($id);

            $this->_addService($lowerId, $service);

            if (self::SCOPE_CONTAINER !== $scope) {

                throw new ehough_iconic_api_exception_RuntimeException('Unsupported scope.');
            }
        }

        foreach ($definition->getMethodCalls() as $call) {

            $services = self::getServiceConditionals($call[1]);

            $ok = true;

            foreach ($services as $s) {

                if (!$this->has($s)) {

                    $ok = false;

                    break;
                }
            }

            if ($ok) {

                call_user_func_array(array($service, $call[0]), $this->resolveServices($parameterBag->resolveValue($call[1])));
            }
        }

        $properties = $this->resolveServices($parameterBag->resolveValue($definition->getProperties()));

        foreach ($properties as $name => $value) {

            $service->$name = $value;
        }

        if ($callable = $definition->getConfigurator()) {

            if (is_array($callable) && is_object($callable[0]) && $callable[0] instanceof ehough_iconic_impl_Reference) {

                $callable[0] = $this->get((string) $callable[0]);

            } elseif (is_array($callable)) {

                $callable[0] = $parameterBag->resolveValue($callable[0]);
            }

            if (!is_callable($callable)) {

                throw new ehough_iconic_api_exception_InvalidArgumentException(sprintf('The configure callable for class "%s" is not a callable.', get_class($service)));
            }

            call_user_func($callable, $service);
        }

        return $service;
    }

    /**
     * Replaces service references by the real service instance.
     *
     * @param mixed $value A value
     *
     * @return mixed The same value with all service references replaced by the real service instances
     */
    public function resolveServices($value)
    {
        if (is_array($value)) {

            foreach ($value as &$v) {

                $v = $this->resolveServices($v);
            }

        } elseif (is_object($value) && $value instanceof ehough_iconic_impl_Reference) {

            $value = $this->get((string) $value, $value->getInvalidBehavior());

        } elseif (is_object($value) && $value instanceof ehough_iconic_impl_Definition) {

            $value = $this->createService($value, null);
        }

        return $value;
    }

    /**
     * Returns the Service Conditionals.
     *
     * @param mixed $value An array of conditionals to return.
     *
     * @return array An array of Service conditionals
     */
    static public function getServiceConditionals($value)
    {
        $services = array();

        if (is_array($value)) {

            foreach ($value as $v) {

                $services = array_unique(array_merge($services, self::getServiceConditionals($v)));
            }

        } elseif (is_object($value) && $value instanceof ehough_iconic_impl_Reference && $value->getInvalidBehavior() === ehough_iconic_api_IContainer::IGNORE_ON_INVALID_REFERENCE) {

            $services[] = (string) $value;
        }

        return $services;
    }
}