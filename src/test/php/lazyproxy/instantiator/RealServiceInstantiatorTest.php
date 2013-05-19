<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Tests for {@see \Symfony\Component\DependencyInjection\Instantiator\RealServiceInstantiator}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 *
 * @covers ehough_iconic_lazyproxy_instantiator_RealServiceInstantiator
 */
class RealServiceInstantiatorTest extends PHPUnit_Framework_TestCase
{
    private $_closureVarInstance;

    public function testInstantiateProxy()
    {
        $instantiator = new ehough_iconic_lazyproxy_instantiator_RealServiceInstantiator();
        $instance     = new stdClass();
        $container    = $this->getMock('ehough_iconic_ContainerInterface');
        $this->_closureVarInstance = $instance;
        $callback     = array($this, '_callbackTestInstantiateProxy');

        $this->assertSame($instance, $instantiator->instantiateProxy($container, new ehough_iconic_Definition(), 'foo', $callback));
    }

    public function _callbackTestInstantiateProxy()
    {
        return $this->_closureVarInstance;
    }
}
