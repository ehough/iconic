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
 * GraphvizDumper dumps a service container as a graphviz file.
 *
 * You can convert the generated dot file with the dot utility (http://www.graphviz.org/):
 *
 *   dot -Tpng container.dot > foo.png
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ehough_iconic_dumper_GraphvizDumper extends ehough_iconic_dumper_Dumper
{
    private $nodes;
    private $edges;
    private $options = array(
            'graph' => array('ratio' => 'compress'),
            'node'  => array('fontsize' => 11, 'fontname' => 'Arial', 'shape' => 'record'),
            'edge'  => array('fontsize' => 9, 'fontname' => 'Arial', 'color' => 'grey', 'arrowhead' => 'open', 'arrowsize' => 0.5),
            'node.instance' => array('fillcolor' => '#9999ff', 'style' => 'filled'),
            'node.definition' => array('fillcolor' => '#eeeeee'),
            'node.missing' => array('fillcolor' => '#ff9999', 'style' => 'filled'),
        );

    /**
     * Dumps the service container as a graphviz graph.
     *
     * Available options:
     *
     *  * graph: The default options for the whole graph
     *  * node: The default options for nodes
     *  * edge: The default options for edges
     *  * node.instance: The default options for services that are defined directly by object instances
     *  * node.definition: The default options for services that are defined via service definition instances
     *  * node.missing: The default options for missing services
     *
     * @param array $options An array of options
     *
     * @return string The dot representation of the service container
     */
    public function dump(array $options = array())
    {
        foreach (array('graph', 'node', 'edge', 'node.instance', 'node.definition', 'node.missing') as $key) {
            if (isset($options[$key])) {
                $this->options[$key] = array_merge($this->options[$key], $options[$key]);
            }
        }

        $this->nodes = $this->findNodes();

        $this->edges = array();
        foreach ($this->container->getDefinitions() as $id => $definition) {
            $this->edges[$id] = array_merge(
                $this->findEdges($id, $definition->getArguments(), true, ''),
                $this->findEdges($id, $definition->getProperties(), false, '')
            );

            foreach ($definition->getMethodCalls() as $call) {
                $this->edges[$id] = array_merge(
                    $this->edges[$id],
                    $this->findEdges($id, $call[1], false, $call[0].'()')
                );
            }
        }

        return $this->startDot().$this->addNodes().$this->addEdges().$this->endDot();
    }

    /**
     * Returns all nodes.
     *
     * @return string A string representation of all nodes
     */
    private function addNodes()
    {
        $code = '';
        foreach ($this->nodes as $id => $node) {
            $aliases = $this->getAliases($id);

            $code .= sprintf("  node_%s [label=\"%s\\n%s\\n\", shape=%s%s];\n", $this->dotize($id), $id.($aliases ? ' ('.implode(', ', $aliases).')' : ''), $node['class'], $this->options['node']['shape'], $this->addAttributes($node['attributes']));
        }

        return $code;
    }

    /**
     * Returns all edges.
     *
     * @return string A string representation of all edges
     */
    private function addEdges()
    {
        $code = '';
        foreach ($this->edges as $id => $edges) {
            foreach ($edges as $edge) {
                $code .= sprintf("  node_%s -> node_%s [label=\"%s\" style=\"%s\"];\n", $this->dotize($id), $this->dotize($edge['to']), $edge['name'], $edge['required'] ? 'filled' : 'dashed');
            }
        }

        return $code;
    }

    /**
     * Finds all edges belonging to a specific service id.
     *
     * @param string  $id        The service id used to find edges
     * @param array   $arguments An array of arguments
     * @param bool    $required
     * @param string  $name
     *
     * @return array An array of edges
     */
    private function findEdges($id, $arguments, $required, $name)
    {
        $edges = array();
        foreach ($arguments as $argument) {
            if ($argument instanceof ehough_iconic_Parameter) {
                $argument = $this->container->hasParameter($argument) ? $this->container->getParameter($argument) : null;
            } elseif (is_string($argument) && preg_match('/^%([^%]+)%$/', $argument, $match)) {
                $argument = $this->container->hasParameter($match[1]) ? $this->container->getParameter($match[1]) : null;
            }

            if ($argument instanceof ehough_iconic_Reference) {
                if (!$this->container->has((string) $argument)) {
                    $this->nodes[(string) $argument] = array('name' => $name, 'required' => $required, 'class' => '', 'attributes' => $this->options['node.missing']);
                }

                $edges[] = array('name' => $name, 'required' => $required, 'to' => $argument);
            } elseif (is_array($argument)) {
                $edges = array_merge($edges, $this->findEdges($id, $argument, $required, $name));
            }
        }

        return $edges;
    }

    /**
     * Finds all nodes.
     *
     * @return array An array of all nodes
     */
    private function findNodes()
    {
        $nodes = array();

        $container = $this->cloneContainer();

        foreach ($container->getDefinitions() as $id => $definition) {

            $className = $definition->getClass();

            try {
                $className = $this->container->getParameterBag()->resolveValue($className);
            } catch (ehough_iconic_exception_ParameterNotFoundException $e) {
            }

            $nodes[$id] = array('class' => str_replace('\\', '\\\\', $className), 'attributes' => array_merge($this->options['node.definition'], array('style' => ehough_iconic_ContainerInterface::SCOPE_PROTOTYPE !== $definition->getScope() ? 'filled' : 'dotted')));

            $container->setDefinition($id, new ehough_iconic_Definition('stdClass'));
        }

        foreach ($container->getServiceIds() as $id) {
            $service = $container->get($id);

            if (in_array($id, array_keys($container->getAliases()))) {
                continue;
            }

            if (!$container->hasDefinition($id)) {
                $class = ('service_container' === $id) ? get_class($this->container) : get_class($service);
                $nodes[$id] = array('class' => str_replace('\\', '\\\\', $class), 'attributes' => $this->options['node.instance']);
            }
        }

        return $nodes;
    }

    private function cloneContainer()
    {
        $parameterBag = new ehough_iconic_parameterbag_ParameterBag($this->container->getParameterBag()->all());

        $container = new ehough_iconic_ContainerBuilder($parameterBag);
        $container->setDefinitions($this->container->getDefinitions());
        $container->setAliases($this->container->getAliases());
        $container->setResources($this->container->getResources());
        foreach ($this->container->getScopes() as $scope => $parentScope) {
            $container->addScope(new ehough_iconic_Scope($scope, $parentScope));
        }
        foreach ($this->container->getExtensions() as $extension) {
            $container->registerExtension($extension);
        }

        return $container;
    }

    /**
     * Returns the start dot.
     *
     * @return string The string representation of a start dot
     */
    private function startDot()
    {
        return sprintf("digraph sc {\n  %s\n  node [%s];\n  edge [%s];\n\n",
            $this->addOptions($this->options['graph']),
            $this->addOptions($this->options['node']),
            $this->addOptions($this->options['edge'])
        );
    }

    /**
     * Returns the end dot.
     *
     * @return string
     */
    private function endDot()
    {
        return "}\n";
    }

    /**
     * Adds attributes
     *
     * @param array $attributes An array of attributes
     *
     * @return string A comma separated list of attributes
     */
    private function addAttributes($attributes)
    {
        $code = array();
        foreach ($attributes as $k => $v) {
            $code[] = sprintf('%s="%s"', $k, $v);
        }

        return $code ? ', '.implode(', ', $code) : '';
    }

    /**
     * Adds options
     *
     * @param array $options An array of options
     *
     * @return string A space separated list of options
     */
    private function addOptions($options)
    {
        $code = array();
        foreach ($options as $k => $v) {
            $code[] = sprintf('%s="%s"', $k, $v);
        }

        return implode(' ', $code);
    }

    /**
     * Dotizes an identifier.
     *
     * @param string $id The identifier to dotize
     *
     * @return string A dotized string
     */
    private function dotize($id)
    {
        return strtolower(preg_replace('/[^\w]/i', '_', $id));
    }

    /**
     * Compiles an array of aliases for a specified service id.
     *
     * @param string $id A service id
     *
     * @return array An array of aliases
     */
    private function getAliases($id)
    {
        $aliases = array();
        foreach ($this->container->getAliases() as $alias => $origin) {
            if ($id == $origin) {
                $aliases[] = $alias;
            }
        }

        return $aliases;
    }
}
