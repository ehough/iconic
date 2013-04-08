<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ReplaceAliasByActualDefinitionPassTest extends PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new ehough_iconic_ContainerBuilder();

        $container->register('a', 'stdClass');

        $bDefinition = new ehough_iconic_Definition('stdClass');
        $bDefinition->setPublic(false);
        $container->setDefinition('b', $bDefinition);

        $container->setAlias('a_alias', 'a');
        $container->setAlias('b_alias', 'b');

        $this->process($container);

        $this->assertTrue($container->has('a'), '->process() does nothing to public definitions.');
        $this->assertTrue($container->hasAlias('a_alias'));
        $this->assertFalse($container->has('b'), '->process() removes non-public definitions.');
        $this->assertTrue(
            $container->has('b_alias') && !$container->hasAlias('b_alias'),
            '->process() replaces alias to actual.'
        );
    }

    /**
     * @expectedException ehough_iconic_exception_InvalidArgumentException
     */
    public function testProcessWithInvalidAlias()
    {
        $container = new ehough_iconic_ContainerBuilder();
        $container->setAlias('a_alias', 'a');
        $this->process($container);
    }

    protected function process(ehough_iconic_ContainerBuilder $container)
    {
        $pass = new ehough_iconic_compiler_ReplaceAliasByActualDefinitionPass();
        $pass->process($container);
    }
}
