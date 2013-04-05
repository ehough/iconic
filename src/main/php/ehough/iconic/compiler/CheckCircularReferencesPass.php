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
 * Checks your services for circular references
 *
 * References from method calls are ignored since we might be able to resolve
 * these references depending on the order in which services are called.
 *
 * Circular reference from method calls will only be detected at run-time.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class ehough_iconic_compiler_CheckCircularReferencesPass implements ehough_iconic_compiler_CompilerPassInterface
{
    private $currentId;
    private $currentPath;

    /**
     * Checks the ContainerBuilder object for circular references.
     *
     * @param ehough_iconic_ContainerBuilder $container The ContainerBuilder instances
     */
    public function process(ehough_iconic_ContainerBuilder $container)
    {
        $graph = $container->getCompiler()->getServiceReferenceGraph();

        foreach ($graph->getNodes() as $id => $node) {
            $this->currentId = $id;
            $this->currentPath = array($id);

            $this->checkOutEdges($node->getOutEdges());
        }
    }

    /**
     * Checks for circular references.
     *
     * @param ehough_iconic_compiler_ServiceReferenceGraphEdge[] $edges An array of Edges
     *
     * @throws ehough_iconic_exception_ServiceCircularReferenceException When a circular reference is found.
     */
    private function checkOutEdges(array $edges)
    {
        foreach ($edges as $edge) {
            $node = $edge->getDestNode();
            $this->currentPath[] = $id = $node->getId();

            if ($this->currentId === $id) {
                throw new ehough_iconic_exception_ServiceCircularReferenceException($this->currentId, $this->currentPath);
            }

            $this->checkOutEdges($node->getOutEdges());
            array_pop($this->currentPath);
        }
    }
}
