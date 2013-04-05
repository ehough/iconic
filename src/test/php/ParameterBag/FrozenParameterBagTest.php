<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class FrozenParameterBagTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ehough_iconic_parameterbag_FrozenParameterBag::__construct
     */
    public function testConstructor()
    {
        $parameters = array(
            'foo' => 'foo',
            'bar' => 'bar',
        );
        $bag = new ehough_iconic_parameterbag_FrozenParameterBag($parameters);
        $this->assertEquals($parameters, $bag->all(), '__construct() takes an array of parameters as its first argument');
    }

    /**
     * @covers ehough_iconic_parameterbag_FrozenParameterBag::clear
     * @expectedException \LogicException
     */
    public function testClear()
    {
        $bag = new ehough_iconic_parameterbag_FrozenParameterBag(array());
        $bag->clear();
    }

    /**
     * @covers ehough_iconic_parameterbag_FrozenParameterBag::set
     * @expectedException \LogicException
     */
    public function testSet()
    {
        $bag = new ehough_iconic_parameterbag_FrozenParameterBag(array());
        $bag->set('foo', 'bar');
    }

    /**
     * @covers ehough_iconic_parameterbag_FrozenParameterBag::add
     * @expectedException \LogicException
     */
    public function testAdd()
    {
        $bag = new ehough_iconic_parameterbag_FrozenParameterBag(array());
        $bag->add(array());
    }
}
