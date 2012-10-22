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

class ehough_iconic_impl_compiler_ResolveInvalidReferencesPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new ehough_iconic_impl_ContainerBuilder();
        $def = $container
            ->register('foo')
            ->setArguments(array(new ehough_iconic_impl_Reference('bar', ehough_iconic_api_IContainer::NULL_ON_INVALID_REFERENCE)))
            ->addMethodCall('foo', array(new ehough_iconic_impl_Reference('moo', ehough_iconic_api_IContainer::IGNORE_ON_INVALID_REFERENCE)))
        ;

        $this->process($container);

        $arguments = $def->getArguments();
        $this->assertNull($arguments[0]);
        $this->assertCount(0, $def->getMethodCalls());
    }

    public function testProcessIgnoreNonExistentServices()
    {
        $container = new ehough_iconic_impl_ContainerBuilder();
        $def = $container
            ->register('foo')
            ->setArguments(array(new ehough_iconic_impl_Reference('bar')))
        ;

        $this->process($container);

        $arguments = $def->getArguments();
        $this->assertEquals('bar', (string) $arguments[0]);
    }

    public function testProcessRemovesPropertiesOnInvalid()
    {
        $container = new ehough_iconic_impl_ContainerBuilder();
        $def = $container
            ->register('foo')
            ->setProperty('foo', new ehough_iconic_impl_Reference('bar', ehough_iconic_api_IContainer::IGNORE_ON_INVALID_REFERENCE))
        ;

        $this->process($container);

        $this->assertEquals(array(), $def->getProperties());
    }

    protected function process(ehough_iconic_impl_ContainerBuilder $container)
    {
        $pass = new ehough_iconic_impl_compiler_ResolveInvalidReferencesPass();
        $pass->process($container);
    }
}