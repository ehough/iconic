<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ehough_iconic_DefinitionDecoratorTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $def = new ehough_iconic_DefinitionDecorator('foo');

        $this->assertEquals('foo', $def->getParent());
        $this->assertEquals(array(), $def->getChanges());
    }

    /**
     * @dataProvider getPropertyTests
     */
    public function testSetProperty($property, $changeKey)
    {
        $def = new ehough_iconic_DefinitionDecorator('foo');

        $getter = 'get'.ucfirst($property);
        $setter = 'set'.ucfirst($property);

        $this->assertNull($def->$getter());
        $this->assertSame($def, $def->$setter('foo'));
        $this->assertEquals('foo', $def->$getter());
        $this->assertEquals(array($changeKey => true), $def->getChanges());
    }

    public function getPropertyTests()
    {
        return array(
            array('class', 'class'),
            array('factoryClass', 'factory_class'),
            array('factoryMethod', 'factory_method'),
            array('factoryService', 'factory_service'),
            array('configurator', 'configurator'),
            array('file', 'file'),
        );
    }

    public function testSetPublic()
    {
        $def = new ehough_iconic_DefinitionDecorator('foo');

        $this->assertTrue($def->isPublic());
        $this->assertSame($def, $def->setPublic(false));
        $this->assertFalse($def->isPublic());
        $this->assertEquals(array('public' => true), $def->getChanges());
    }

    public function testSetLazy()
    {
        $def = new ehough_iconic_DefinitionDecorator('foo');

        $this->assertFalse($def->isLazy());
        $this->assertSame($def, $def->setLazy(false));
        $this->assertFalse($def->isLazy());
        $this->assertEquals(array('lazy' => true), $def->getChanges());
    }

    public function testSetArgument()
    {
        $def = new ehough_iconic_DefinitionDecorator('foo');

        $this->assertEquals(array(), $def->getArguments());
        $this->assertSame($def, $def->replaceArgument(0, 'foo'));
        $this->assertEquals(array('index_0' => 'foo'), $def->getArguments());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testReplaceArgumentShouldRequireIntegerIndex()
    {
        $def = new ehough_iconic_DefinitionDecorator('foo');

        $def->replaceArgument('0', 'foo');
    }

    public function testReplaceArgument()
    {
        $def = new ehough_iconic_DefinitionDecorator('foo');

        $def->setArguments(array(0 => 'foo', 1 => 'bar'));
        $this->assertEquals('foo', $def->getArgument(0));
        $this->assertEquals('bar', $def->getArgument(1));

        $this->assertSame($def, $def->replaceArgument(1, 'baz'));
        $this->assertEquals('foo', $def->getArgument(0));
        $this->assertEquals('baz', $def->getArgument(1));

        $this->assertEquals(array(0 => 'foo', 1 => 'bar', 'index_1' => 'baz'), $def->getArguments());
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testGetArgumentShouldCheckBounds()
    {
        $def = new ehough_iconic_DefinitionDecorator('foo');

        $def->setArguments(array(0 => 'foo'));
        $def->replaceArgument(0, 'foo');

        $def->getArgument(1);
    }
}
