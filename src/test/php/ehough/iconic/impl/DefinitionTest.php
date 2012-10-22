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

class ehough_iconic_impl_DefinitionTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $def = new ehough_iconic_impl_Definition('stdClass');
        $this->assertEquals('stdClass', $def->getClass(), '__construct() takes the class name as its first argument');

        $def = new ehough_iconic_impl_Definition('stdClass', array('foo'));
        $this->assertEquals(array('foo'), $def->getArguments(), '__construct() takes an optional array of arguments as its second argument');
    }

    public function testSetGetFactoryClass()
    {
        $def = new ehough_iconic_impl_Definition('stdClass');
        $this->assertNull($def->getFactoryClass());
        $this->assertSame($def, $def->setFactoryClass('stdClass2'), "->setFactoryClass() implements a fluent interface.");
        $this->assertEquals('stdClass2', $def->getFactoryClass(), "->getFactoryClass() returns current class to construct this service.");
    }

    public function testSetGetFactoryMethod()
    {
        $def = new ehough_iconic_impl_Definition('stdClass');
        $this->assertNull($def->getFactoryMethod());
        $this->assertSame($def, $def->setFactoryMethod('foo'), '->setFactoryMethod() implements a fluent interface');
        $this->assertEquals('foo', $def->getFactoryMethod(), '->getFactoryMethod() returns the factory method name');
    }

    public function testSetGetFactoryService()
    {
        $def = new ehough_iconic_impl_Definition('stdClass');
        $this->assertNull($def->getFactoryService());
        $this->assertSame($def, $def->setFactoryService('foo.bar'), "->setFactoryService() implements a fluent interface.");
        $this->assertEquals('foo.bar', $def->getFactoryService(), "->getFactoryService() returns current service to construct this service.");
    }

    public function testSetGetClass()
    {
        $def = new ehough_iconic_impl_Definition('stdClass');
        $this->assertSame($def, $def->setClass('foo'), '->setClass() implements a fluent interface');
        $this->assertEquals('foo', $def->getClass(), '->getClass() returns the class name');
    }

    public function testArguments()
    {
        $def = new ehough_iconic_impl_Definition('stdClass');
        $this->assertSame($def, $def->setArguments(array('foo')), '->setArguments() implements a fluent interface');
        $this->assertEquals(array('foo'), $def->getArguments(), '->getArguments() returns the arguments');
        $this->assertSame($def, $def->addArgument('bar'), '->addArgument() implements a fluent interface');
        $this->assertEquals(array('foo', 'bar'), $def->getArguments(), '->addArgument() adds an argument');
    }

    public function testMethodCalls()
    {
        $def = new ehough_iconic_impl_Definition('stdClass');
        $this->assertSame($def, $def->setMethodCalls(array(array('foo', array('foo')))), '->setMethodCalls() implements a fluent interface');
        $this->assertEquals(array(array('foo', array('foo'))), $def->getMethodCalls(), '->getMethodCalls() returns the methods to call');
        $this->assertSame($def, $def->addMethodCall('bar', array('bar')), '->addMethodCall() implements a fluent interface');
        $this->assertEquals(array(array('foo', array('foo')), array('bar', array('bar'))), $def->getMethodCalls(), '->addMethodCall() adds a method to call');
        $this->assertTrue($def->hasMethodCall('bar'), '->hasMethodCall() returns true if first argument is a method to call registered');
        $this->assertFalse($def->hasMethodCall('no_registered'), '->hasMethodCall() returns false if first argument is not a method to call registered');
        $this->assertSame($def, $def->removeMethodCall('bar'), '->removeMethodCall() implements a fluent interface');
        $this->assertEquals(array(array('foo', array('foo'))), $def->getMethodCalls(), '->removeMethodCall() removes a method to call');
    }

    /**
     * @expectedException ehough_iconic_api_exception_InvalidArgumentException
     * @expectedExceptionMessage Method name cannot be empty.
     */
    public function testExceptionOnEmptyMethodCall()
    {
        $def = new ehough_iconic_impl_Definition('stdClass');
        $def->addMethodCall('');
    }

    public function testSetGetFile()
    {
        $def = new ehough_iconic_impl_Definition('stdClass');
        $this->assertSame($def, $def->setFile('foo'), '->setFile() implements a fluent interface');
        $this->assertEquals('foo', $def->getFile(), '->getFile() returns the file to include');
    }

    public function testSetGetScope()
    {
        $def = new ehough_iconic_impl_Definition('stdClass');
        $this->assertEquals('container', $def->getScope());
        $this->assertSame($def, $def->setScope('foo'));
        $this->assertEquals('foo', $def->getScope());
    }

    public function testSetIsPublic()
    {
        $def = new ehough_iconic_impl_Definition('stdClass');
        $this->assertTrue($def->isPublic(), '->isPublic() returns true by default');
        $this->assertSame($def, $def->setPublic(false), '->setPublic() implements a fluent interface');
        $this->assertFalse($def->isPublic(), '->isPublic() returns false if the instance must not be public.');
    }

    public function testSetIsSynthetic()
    {
        $def = new ehough_iconic_impl_Definition('stdClass');
        $this->assertFalse($def->isSynthetic(), '->isSynthetic() returns false by default');
        $this->assertSame($def, $def->setSynthetic(true), '->setSynthetic() implements a fluent interface');
        $this->assertTrue($def->isSynthetic(), '->isSynthetic() returns true if the instance must not be public.');
    }

    public function testSetIsAbstract()
    {
        $def = new ehough_iconic_impl_Definition('stdClass');
        $this->assertFalse($def->isAbstract(), '->isAbstract() returns false by default');
        $this->assertSame($def, $def->setAbstract(true), '->setAbstract() implements a fluent interface');
        $this->assertTrue($def->isAbstract(), '->isAbstract() returns true if the instance must not be public.');
    }

    public function testSetGetConfigurator()
    {
        $def = new ehough_iconic_impl_Definition('stdClass');
        $this->assertSame($def, $def->setConfigurator('foo'), '->setConfigurator() implements a fluent interface');
        $this->assertEquals('foo', $def->getConfigurator(), '->getConfigurator() returns the configurator');
    }

    public function testSetArgument()
    {
        $def = new ehough_iconic_impl_Definition('stdClass');

        $def->addArgument('foo');
        $this->assertSame(array('foo'), $def->getArguments());

        $this->assertSame($def, $def->replaceArgument(0, 'moo'));
        $this->assertSame(array('moo'), $def->getArguments());

        $def->addArgument('moo');
        $def
            ->replaceArgument(0, 'foo')
            ->replaceArgument(1, 'bar')
        ;
        $this->assertSame(array('foo', 'bar'), $def->getArguments());
    }

    /**
     * @expectedException ehough_iconic_api_exception_OutOfBoundsException
     */
    public function testGetArgumentShouldCheckBounds()
    {
        $def = new ehough_iconic_impl_Definition('stdClass');

        $def->addArgument('foo');
        $def->getArgument(1);
    }

    /**
     * @expectedException ehough_iconic_api_exception_OutOfBoundsException
     */
    public function testReplaceArgumentShouldCheckBounds()
    {
        $def = new ehough_iconic_impl_Definition('stdClass');

        $def->addArgument('foo');
        $def->replaceArgument(1, 'bar');
    }

    public function testSetGetProperties()
    {
        $def = new ehough_iconic_impl_Definition('stdClass');

        $this->assertEquals(array(), $def->getProperties());
        $this->assertSame($def, $def->setProperties(array('foo' => 'bar')));
        $this->assertEquals(array('foo' => 'bar'), $def->getProperties());
    }

    public function testSetProperty()
    {
        $def = new ehough_iconic_impl_Definition('stdClass');

        $this->assertEquals(array(), $def->getProperties());
        $this->assertSame($def, $def->setProperty('foo', 'bar'));
        $this->assertEquals(array('foo' => 'bar'), $def->getProperties());
    }


    public function testClearTags()
    {
        $def = new ehough_iconic_impl_Definition('stdClass');
        $this->assertSame($def, $def->clearTags(), '->clearTags() implements a fluent interface');
        $def->addTag('foo', array('foo' => 'bar'));
        $def->clearTags();
        $this->assertEquals(array(), $def->getTags(), '->clearTags() removes all current tags');
    }


    public function testClearTag()
    {
        $def = new ehough_iconic_impl_Definition('stdClass');
        $this->assertSame($def, $def->clearTags(), '->clearTags() implements a fluent interface');
        $def->addTag('1foo1', array('foo1' => 'bar1'));
        $def->addTag('2foo2', array('foo2' => 'bar2'));
        $def->addTag('3foo3', array('foo3' => 'bar3'));
        $def->clearTag('2foo2');
        $this->assertTrue($def->hasTag('1foo1'));
        $this->assertFalse($def->hasTag('2foo2'));
        $this->assertTrue($def->hasTag('3foo3'));
        $def->clearTag('1foo1');
        $this->assertFalse($def->hasTag('1foo1'));
        $this->assertTrue($def->hasTag('3foo3'));
    }

    public function testTags()
    {
        $def = new ehough_iconic_impl_Definition('stdClass');
        $this->assertEquals(array(), $def->getTag('foo'), '->getTag() returns an empty array if the tag is not defined');
        $this->assertFalse($def->hasTag('foo'));
        $this->assertSame($def, $def->addTag('foo'), '->addTag() implements a fluent interface');
        $this->assertTrue($def->hasTag('foo'));
        $this->assertEquals(array(array()), $def->getTag('foo'), '->getTag() returns attributes for a tag name');
        $def->addTag('foo', array('foo' => 'bar'));
        $this->assertEquals(array(array(), array('foo' => 'bar')), $def->getTag('foo'), '->addTag() can adds the same tag several times');
        $def->addTag('bar', array('bar' => 'bar'));
        $this->assertEquals($def->getTags(), array(
            'foo' => array(array(), array('foo' => 'bar')),
            'bar' => array(array('bar' => 'bar')),
        ), '->getTags() returns all tags');
    }
}