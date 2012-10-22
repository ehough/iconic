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

class ehough_iconic_impl_compiler_AnalyzeServiceReferencesPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new ehough_iconic_impl_ContainerBuilder();

        $a = $container
            ->register('a')
            ->addArgument($ref1 = new ehough_iconic_impl_Reference('b'))
        ;

        $b = $container
            ->register('b')
            ->addMethodCall('setA', array($ref2 = new ehough_iconic_impl_Reference('a')))
        ;

        $c = $container
            ->register('c')
            ->addArgument($ref3 = new ehough_iconic_impl_Reference('a'))
            ->addArgument($ref4 = new ehough_iconic_impl_Reference('b'))
        ;

        $d = $container
            ->register('d')
            ->setProperty('foo', $ref5 = new ehough_iconic_impl_Reference('b'))
        ;

        $e = $container
            ->register('e')
            ->setConfigurator(array($ref6 = new ehough_iconic_impl_Reference('b'), 'methodName'))
        ;

        $graph = $this->process($container);

        $this->assertCount(4, $edges = $graph->getNode('b')->getInEdges());

        $this->assertSame($ref1, $edges[0]->getValue());
        $this->assertSame($ref4, $edges[1]->getValue());
        $this->assertSame($ref5, $edges[2]->getValue());
        $this->assertSame($ref6, $edges[3]->getValue());
    }

    public function testProcessDetectsReferencesFromInlinedDefinitions()
    {
        $container = new ehough_iconic_impl_ContainerBuilder();

        $container
            ->register('a')
        ;

        $container
            ->register('b')
            ->addArgument(new ehough_iconic_impl_Definition(null, array($ref = new ehough_iconic_impl_Reference('a'))))
        ;

        $graph = $this->process($container);

        $this->assertCount(1, $refs = $graph->getNode('a')->getInEdges());
        $this->assertSame($ref, $refs[0]->getValue());
    }

    public function testProcessDoesNotSaveDuplicateReferences()
    {
        $container = new ehough_iconic_impl_ContainerBuilder();

        $container
            ->register('a')
        ;
        $container
            ->register('b')
            ->addArgument(new ehough_iconic_impl_Definition(null, array($ref1 = new ehough_iconic_impl_Reference('a'))))
            ->addArgument(new ehough_iconic_impl_Definition(null, array($ref2 = new ehough_iconic_impl_Reference('a'))))
        ;

        $graph = $this->process($container);

        $this->assertCount(2, $graph->getNode('a')->getInEdges());
    }

    protected function process(ehough_iconic_impl_ContainerBuilder $container)
    {
        $pass = new ehough_iconic_impl_compiler_RepeatedPass(array(new ehough_iconic_impl_compiler_AnalyzeServiceReferencesPass()));
        $pass->process($container);

        return $container->getCompiler()->getServiceReferenceGraph();
    }
}