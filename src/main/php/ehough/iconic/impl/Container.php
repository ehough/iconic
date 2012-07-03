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
 * Container is a dependency injection container.
 *
 * It gives access to object instances (services).
 *
 * Services and parameters are simple key/pair stores.
 *
 * Parameter and service keys are case insensitive.
 *
 * A service id can contain lowercased letters, digits, underscores, and dots.
 * Underscores are used to separate words, and dots to group services
 * under namespaces:
 *
 * <ul>
 *   <li>request</li>
 *   <li>mysql_session_storage</li>
 *   <li>symfony.mysql_session_storage</li>
 * </ul>
 *
 * A service can also be defined by creating a method named
 * getXXXService(), where XXX is the camelized version of the id:
 *
 * <ul>
 *   <li>request -> getRequestService()</li>
 *   <li>mysql_session_storage -> getMysqlSessionStorageService()</li>
 *   <li>symfony.mysql_session_storage -> getSymfony_MysqlSessionStorageService()</li>
 * </ul>
 *
 * The container can have three possible behaviors when a service does not exist:
 *
 *  * EXCEPTION_ON_INVALID_REFERENCE: Throws an exception (the default)
 *  * NULL_ON_INVALID_REFERENCE:      Returns null
 *  * IGNORE_ON_INVALID_REFERENCE:    Ignores the wrapping command asking for the reference
 *                                    (for instance, ignore a setter if the service does not exist)
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class ehough_iconic_impl_Container implements ehough_iconic_api_IIntrospectableContainer
{
    private $parameterBag;
    private $services;
    private $loading = array();

    /**
     * Constructor.
     *
     * @param ehough_iconic_api_parameterbag_IParameterBag $parameterBag A ParameterBagInterface instance
     */
    public function __construct(ehough_iconic_api_parameterbag_IParameterBag $parameterBag = null)
    {
        $this->parameterBag = null === $parameterBag ? new ehough_iconic_impl_parameterbag_StandardParameterBag() : $parameterBag;

        $this->services       = array();

        $this->set('service_container', $this);
    }

    /**
     * Compiles the container.
     *
     * This method does two things:
     *
     *  * Parameter values are resolved;
     *  * The parameter bag is frozen.
     */
    public function compile()
    {
        $this->parameterBag->resolve();

        $this->parameterBag = new ehough_iconic_impl_parameterbag_FrozenParameterBag($this->parameterBag->all());
    }

    /**
     * Returns true if the container parameter bag are frozen.
     *
     * @return boolean True if the container parameter bag are frozen, false otherwise
     */
    public function isFrozen()
    {
        return $this->parameterBag instanceof ehough_iconic_impl_parameterbag_FrozenParameterBag;
    }

    /**
     * Gets the service container parameter bag.
     *
     * @return ehough_iconic_api_parameterbag_IParameterBag A ParameterBagInterface instance
     */
    public function getParameterBag()
    {
        return $this->parameterBag;
    }

    /**
     * Gets a parameter.
     *
     * @param string $name The parameter name
     *
     * @return mixed  The parameter value
     *
     * @throws ehough_iconic_api_exception_InvalidArgumentException if the parameter is not defined
     */
    public function getParameter($name)
    {
        return $this->parameterBag->get($name);
    }

    /**
     * Checks if a parameter exists.
     *
     * @param string $name The parameter name
     *
     * @return Boolean The presence of parameter in container
     */
    public function hasParameter($name)
    {
        return $this->parameterBag->has($name);
    }

    /**
     * Sets a parameter.
     *
     * @param string $name  The parameter name
     * @param mixed  $value The parameter value
     */
    public function setParameter($name, $value)
    {
        $this->parameterBag->set($name, $value);
    }

    /**
     * Sets a service.
     *
     * @param string $id      The service identifier
     * @param object $service The service instance
     * @param string $scope   The scope of the service
     *
     * @throws ehough_iconic_api_exception_InvalidArgumentException
     */
    public function set($id, $service, $scope = self::SCOPE_CONTAINER)
    {
        if (self::SCOPE_PROTOTYPE === $scope) {

            throw new ehough_iconic_api_exception_InvalidArgumentException('You cannot set services of scope "prototype".');
        }

        $this->services[strtolower($id)] = $service;
    }

    /**
     * Returns true if the given service is defined.
     *
     * @param string $id The service identifier
     *
     * @return Boolean true if the service is defined, false otherwise
     */
    public function has($id)
    {
        $id = strtolower($id);

        return isset($this->services[$id]) || method_exists($this, 'get'.strtr($id, array('_' => '', '.' => '_')).'Service');
    }

    /**
     * Gets a service.
     *
     * If a service is both defined through a set() method and
     * with a set*Service() method, the former has always precedence.
     *
     * @param string  $id              The service identifier
     * @param integer $invalidBehavior The behavior when the service does not exist
     *
     * @return object The associated service
     *
     * @throws ehough_iconic_api_exception_ServiceCircularReferenceException if the service is not defined
     * @throws ehough_iconic_api_exception_ServiceNotFoundException
     * @throws Exception
     */
    public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE)
    {
        $id = strtolower($id);

        if (isset($this->services[$id])) {

            return $this->services[$id];
        }

        if (isset($this->loading[$id])) {

            throw new ehough_iconic_api_exception_ServiceCircularReferenceException($id, array_keys($this->loading));
        }

        if (method_exists($this, $method = 'get'.strtr($id, array('_' => '', '.' => '_')).'Service')) {

            $this->loading[$id] = true;

            try {

                $service = $this->$method();

            } catch (Exception $e) {

                unset($this->loading[$id]);

                throw $e;
            }

            unset($this->loading[$id]);

            return $service;
        }

        if (self::EXCEPTION_ON_INVALID_REFERENCE === $invalidBehavior) {

            throw new ehough_iconic_api_exception_ServiceNotFoundException($id);
        }

        return null;
    }

    /**
     * Returns true if the given service has actually been initialized
     *
     * @param string $id The service identifier
     *
     * @return Boolean true if service has already been initialized, false otherwise
     */
    public function initialized($id)
    {
        return isset($this->services[strtolower($id)]);
    }

    /**
     * Gets all service ids.
     *
     * @return array An array of all defined service ids
     */
    public function getServiceIds()
    {
        $ids     = array();
        $r       = new \ReflectionClass($this);
        $methods = $r->getMethods();

        foreach ($methods as $method) {

            if (preg_match('/^get(.+)Service$/', $method->name, $match)) {

                $ids[] = self::underscore($match[1]);
            }
        }

        return array_unique(array_merge($ids, array_keys($this->services)));
    }

    /**
     * Camelizes a string.
     *
     * @param string $id A string to camelize
     *
     * @return string The camelized string
     */
    static public function camelize($id)
    {
        return preg_replace_callback('/(^|_|\.)+(.)/', function ($match) { return ('.' === $match[1] ? '_' : '').strtoupper($match[2]); }, $id);
    }

    /**
     * A string to underscore.
     *
     * @param string $id The string to underscore
     *
     * @return string The underscored string
     */
    static public function underscore($id)
    {
        return strtolower(preg_replace(array('/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'), array('\\1_\\2', '\\1_\\2'), strtr($id, '_', '.')));
    }
}