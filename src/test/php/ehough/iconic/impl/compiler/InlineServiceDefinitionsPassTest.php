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

class ehough_iconic_impl_compiler_InlineServiceDefinitionsPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new ehough_iconic_impl_ContainerBuilder();
        $container
            ->register('inlinable.service')
            ->setPublic(false)
        ;

        $container
            ->register('service')
            ->setArguments(array(new ehough_iconic_impl_Reference('inlinable.service')))
        ;

        $this->process($container);

        $arguments = $container->getDefinition('service')->getArguments();
        $this->assertInstanceOf('ehough_iconic_impl_Definition', $arguments[0]);
        $this->assertSame($container->getDefinition('inlinable.service'), $arguments[0]);
    }


    public function testProcessDoesInlineServiceOfPrototypeScope()
    {
        $container = new ehough_iconic_impl_ContainerBuilder();
        $container
            ->register('foo')
            ->setScope('prototype')
        ;
        $container
            ->register('bar')
            ->setPublic(false)
            ->setScope('prototype')
        ;

        $container
            ->register('service')
            ->setArguments(array(new ehough_iconic_impl_Reference('foo'), $ref = new ehough_iconic_impl_Reference('moo'), new ehough_iconic_impl_Reference('bar')))
        ;

        $this->process($container);

        $arguments = $container->getDefinition('service')->getArguments();
        $this->assertEquals($container->getDefinition('foo'), $arguments[0]);
        $this->assertNotSame($container->getDefinition('foo'), $arguments[0]);
        $this->assertSame($ref, $arguments[1]);
        $this->assertEquals($container->getDefinition('bar'), $arguments[2]);
        $this->assertNotSame($container->getDefinition('bar'), $arguments[2]);
    }

    public function testProcessInlinesIfMultipleReferencesButAllFromTheSameDefinition()
    {
        $container = new ehough_iconic_impl_ContainerBuilder();

        $a = $container->register('a')->setPublic(false);
        $b = $container
            ->register('b')
            ->addArgument(new ehough_iconic_impl_Reference('a'))
            ->addArgument(new ehough_iconic_impl_Definition(null, array(new ehough_iconic_impl_Reference('a'))))
        ;

        $this->process($container);

        $arguments = $b->getArguments();
        $this->assertSame($a, $arguments[0]);

        $inlinedArguments = $arguments[1]->getArguments();
        $this->assertSame($a, $inlinedArguments[0]);
    }

    protected function process(ehough_iconic_impl_ContainerBuilder $container)
    {
        $repeatedPass = new ehough_iconic_impl_compiler_RepeatedPass(array(new ehough_iconic_impl_compiler_AnalyzeServiceReferencesPass(), new ehough_iconic_impl_compiler_InlineServiceDefinitionsPass()));
        $repeatedPass->process($container);
    }
}