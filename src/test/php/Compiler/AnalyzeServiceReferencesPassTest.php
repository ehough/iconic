<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//namespace Symfony\Component\DependencyInjection\Tests\Compiler;

//use Symfony\Component\DependencyInjection\Definition;
//use Symfony\Component\DependencyInjection\Compiler\Compiler;
//use Symfony\Component\DependencyInjection\Compiler\AnalyzeServiceReferencesPass;
//use Symfony\Component\DependencyInjection\Compiler\RepeatedPass;
//use Symfony\Component\DependencyInjection\Reference;
//use Symfony\Component\DependencyInjection\ContainerBuilder;

class AnalyzeServiceReferencesPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new ehough_iconic_ContainerBuilder();

        $a = $container
            ->register('a')
            ->addArgument($ref1 = new ehough_iconic_Reference('b'))
        ;

        $b = $container
            ->register('b')
            ->addMethodCall('setA', array($ref2 = new ehough_iconic_Reference('a')))
        ;

        $c = $container
            ->register('c')
            ->addArgument($ref3 = new ehough_iconic_Reference('a'))
            ->addArgument($ref4 = new ehough_iconic_Reference('b'))
        ;

        $d = $container
            ->register('d')
            ->setProperty('foo', $ref5 = new ehough_iconic_Reference('b'))
        ;

        $e = $container
            ->register('e')
            ->setConfigurator(array($ref6 = new ehough_iconic_Reference('b'), 'methodName'))
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
        $container = new ehough_iconic_ContainerBuilder();

        $container
            ->register('a')
        ;

        $container
            ->register('b')
            ->addArgument(new ehough_iconic_Definition(null, array($ref = new ehough_iconic_Reference('a'))))
        ;

        $graph = $this->process($container);

        $this->assertCount(1, $refs = $graph->getNode('a')->getInEdges());
        $this->assertSame($ref, $refs[0]->getValue());
    }

    public function testProcessDoesNotSaveDuplicateReferences()
    {
        $container = new ehough_iconic_ContainerBuilder();

        $container
            ->register('a')
        ;
        $container
            ->register('b')
            ->addArgument(new ehough_iconic_Definition(null, array($ref1 = new ehough_iconic_Reference('a'))))
            ->addArgument(new ehough_iconic_Definition(null, array($ref2 = new ehough_iconic_Reference('a'))))
        ;

        $graph = $this->process($container);

        $this->assertCount(2, $graph->getNode('a')->getInEdges());
    }

    protected function process(ehough_iconic_ContainerBuilder $container)
    {
        $pass = new ehough_iconic_compiler_RepeatedPass(array(new ehough_iconic_compiler_AnalyzeServiceReferencesPass()));
        $pass->process($container);

        return $container->getCompiler()->getServiceReferenceGraph();
    }
}
