<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//namespace Symfony\Component\DependencyInjection\Compiler;

//use Symfony\Component\DependencyInjection\Definition;
//use Symfony\Component\DependencyInjection\ContainerInterface;
//use Symfony\Component\DependencyInjection\Reference;
//use Symfony\Component\DependencyInjection\ContainerBuilder;
//use Symfony\Component\DependencyInjection\Exception\RuntimeException;
//use Symfony\Component\DependencyInjection\Exception\ScopeCrossingInjectionException;
//use Symfony\Component\DependencyInjection\Exception\ScopeWideningInjectionException;

/**
 * Checks the validity of references
 *
 * The following checks are performed by this pass:
 * - target definitions are not abstract
 * - target definitions are of equal or wider scope
 * - target definitions are in the same scope hierarchy
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class ehough_iconic_compiler_CheckReferenceValidityPass implements ehough_iconic_compiler_CompilerPassInterface
{
    private $container;
    private $currentId;
    private $currentDefinition;
    private $currentScope;
    private $currentScopeAncestors;
    private $currentScopeChildren;

    /**
     * Processes the ContainerBuilder to validate ehough_iconic_References.
     *
     * @param ehough_iconic_ContainerBuilder $container
     */
    public function process(ehough_iconic_ContainerBuilder $container)
    {
        $this->container = $container;

        $children = $this->container->getScopeChildren();
        $ancestors = array();

        $scopes = $this->container->getScopes();
        foreach ($scopes as $name => $parent) {
            $ancestors[$name] = array($parent);

            while (isset($scopes[$parent])) {
                $ancestors[$name][] = $parent = $scopes[$parent];
            }
        }

        foreach ($container->getDefinitions() as $id => $definition) {
            if ($definition->isSynthetic() || $definition->isAbstract()) {
                continue;
            }

            $this->currentId = $id;
            $this->currentDefinition = $definition;
            $this->currentScope = $scope = $definition->getScope();

            if (ehough_iconic_ContainerInterface::SCOPE_CONTAINER === $scope) {
                $this->currentScopeChildren = array_keys($scopes);
                $this->currentScopeAncestors = array();
            } elseif (ehough_iconic_ContainerInterface::SCOPE_PROTOTYPE !== $scope) {
                $this->currentScopeChildren = $children[$scope];
                $this->currentScopeAncestors = $ancestors[$scope];
            }

            $this->validateReferences($definition->getArguments());
            $this->validateReferences($definition->getMethodCalls());
            $this->validateReferences($definition->getProperties());
        }
    }

    /**
     * Validates an array of ehough_iconic_References.
     *
     * @param array $arguments An array of ehough_iconic_Reference objects
     *
     * @throws RuntimeException when there is a reference to an abstract definition.
     */
    private function validateReferences(array $arguments)
    {
        foreach ($arguments as $argument) {
            if (is_array($argument)) {
                $this->validateReferences($argument);
            } elseif ($argument instanceof ehough_iconic_Reference) {
                $targetDefinition = $this->getDefinition((string) $argument);

                if (null !== $targetDefinition && $targetDefinition->isAbstract()) {
                    throw new RuntimeException(sprintf(
                        'The definition "%s" has a reference to an abstract definition "%s". '
                       .'Abstract definitions cannot be the target of references.',
                       $this->currentId,
                       $argument
                    ));
                }

                $this->validateScope($argument, $targetDefinition);
            }
        }
    }

    /**
     * Validates the scope of a single ehough_iconic_Reference.
     *
     * @param ehough_iconic_Reference  $reference
     * @param ehough_iconic_Definition $definition
     *
     * @throws ehough_iconic_exception_ScopeWideningInjectionException when the definition references a service of a narrower scope
     * @throws ehough_iconic_exception_ScopeCrossingInjectionException when the definition references a service of another scope hierarchy
     */
    private function validateScope(ehough_iconic_Reference $reference, ehough_iconic_Definition $definition = null)
    {
        if (ehough_iconic_ContainerInterface::SCOPE_PROTOTYPE === $this->currentScope) {
            return;
        }

        if (!$reference->isStrict()) {
            return;
        }

        if (null === $definition) {
            return;
        }

        if ($this->currentScope === $scope = $definition->getScope()) {
            return;
        }

        $id = (string) $reference;

        if (in_array($scope, $this->currentScopeChildren, true)) {
            throw new ehough_iconic_exception_ScopeWideningInjectionException($this->currentId, $this->currentScope, $id, $scope);
        }

        if (!in_array($scope, $this->currentScopeAncestors, true)) {
            throw new ehough_iconic_exception_ScopeCrossingInjectionException($this->currentId, $this->currentScope, $id, $scope);
        }
    }

    /**
     * Returns the ehough_iconic_Definition given an id.
     *
     * @param string $id ehough_iconic_Definition identifier
     *
     * @return ehough_iconic_Definition
     */
    private function getDefinition($id)
    {
        if (!$this->container->hasDefinition($id)) {
            return null;
        }

        return $this->container->getDefinition($id);
    }
}
