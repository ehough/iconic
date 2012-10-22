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

class ehough_iconic_impl_compiler_CheckCircularReferencesPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testProcess()
    {
        $container = new ehough_iconic_impl_ContainerBuilder();
        $container->register('a')->addArgument(new ehough_iconic_impl_Reference('b'));
        $container->register('b')->addArgument(new ehough_iconic_impl_Reference('a'));

        $this->process($container);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testProcessDetectsIndirectCircularReference()
    {
        $container = new ehough_iconic_impl_ContainerBuilder();
        $container->register('a')->addArgument(new ehough_iconic_impl_Reference('b'));
        $container->register('b')->addArgument(new ehough_iconic_impl_Reference('c'));
        $container->register('c')->addArgument(new ehough_iconic_impl_Reference('a'));

        $this->process($container);
    }

    public function testProcessIgnoresMethodCalls()
    {
        $container = new ehough_iconic_impl_ContainerBuilder();
        $container->register('a')->addArgument(new ehough_iconic_impl_Reference('b'));
        $container->register('b')->addMethodCall('setA', array(new ehough_iconic_impl_Reference('a')));

        $this->process($container);

        $this->assertTrue(true);
    }

    protected function process(ehough_iconic_impl_ContainerBuilder $container)
    {
        $compiler = new ehough_iconic_impl_compiler_Compiler();
        $passConfig = $compiler->getPassConfig();
        $passConfig->setOptimizationPasses(array(
            new ehough_iconic_impl_compiler_AnalyzeServiceReferencesPass(true),
            new ehough_iconic_impl_compiler_CheckCircularReferencesPass(),
        ));
        $passConfig->setRemovingPasses(array());

        $compiler->compile($container);
    }
}