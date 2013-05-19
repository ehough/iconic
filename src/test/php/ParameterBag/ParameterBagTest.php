<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ehough_iconic_parameterbag_ParameterBagTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ehough_iconic_parameterbag_ParameterBag::__construct
     */
    public function testConstructor()
    {
        $bag = new ehough_iconic_parameterbag_ParameterBag($parameters = array(
            'foo' => 'foo',
            'bar' => 'bar',
        ));
        $this->assertEquals($parameters, $bag->all(), '__construct() takes an array of parameters as its first argument');
    }

    /**
     * @covers ehough_iconic_parameterbag_ParameterBag::clear
     */
    public function testClear()
    {
        $bag = new ehough_iconic_parameterbag_ParameterBag($parameters = array(
            'foo' => 'foo',
            'bar' => 'bar',
        ));
        $bag->clear();
        $this->assertEquals(array(), $bag->all(), '->clear() removes all parameters');
    }

    /**
     * @covers ehough_iconic_parameterbag_ParameterBag::remove
     */
    public function testRemove()
    {
        $bag = new ehough_iconic_parameterbag_ParameterBag(array(
            'foo' => 'foo',
            'bar' => 'bar',
        ));
        $bag->remove('foo');
        $this->assertEquals(array('bar' => 'bar'), $bag->all(), '->remove() removes a parameter');
        $bag->remove('BAR');
        $this->assertEquals(array(), $bag->all(), '->remove() converts key to lowercase before removing');
    }

    /**
     * @covers ehough_iconic_parameterbag_ParameterBag::get
     * @covers ehough_iconic_parameterbag_ParameterBag::set
     */
    public function testGetSet()
    {
        $bag = new ehough_iconic_parameterbag_ParameterBag(array('foo' => 'bar'));
        $bag->set('bar', 'foo');
        $this->assertEquals('foo', $bag->get('bar'), '->set() sets the value of a new parameter');

        $bag->set('foo', 'baz');
        $this->assertEquals('baz', $bag->get('foo'), '->set() overrides previously set parameter');

        $bag->set('Foo', 'baz1');
        $this->assertEquals('baz1', $bag->get('foo'), '->set() converts the key to lowercase');
        $this->assertEquals('baz1', $bag->get('FOO'), '->get() converts the key to lowercase');

        try {
            $bag->get('baba');
            $this->fail('->get() throws an Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException if the key does not exist');
        } catch (Exception $e) {
            $this->assertInstanceOf('ehough_iconic_exception_ParameterNotFoundException', $e, '->get() throws an Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException if the key does not exist');
            $this->assertEquals('You have requested a non-existent parameter "baba".', $e->getMessage(), '->get() throws an Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException if the key does not exist');
        }
    }

    public function testGetThrowParameterNotFoundException()
    {
        $bag = new ehough_iconic_parameterbag_ParameterBag(array(
            'foo' => 'foo',
            'bar' => 'bar',
            'baz' => 'baz',
        ));

        try {
            $bag->get('foo1');
            $this->fail('->get() throws an Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException if the key does not exist');
        } catch (Exception $e) {
            $this->assertInstanceOf('ehough_iconic_exception_ParameterNotFoundException', $e, '->get() throws an Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException if the key does not exist');
            $this->assertEquals('You have requested a non-existent parameter "foo1". Did you mean this: "foo"?', $e->getMessage(), '->get() throws an Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException with some advices');
        }

        try {
            $bag->get('bag');
            $this->fail('->get() throws an Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException if the key does not exist');
        } catch (Exception $e) {
            $this->assertInstanceOf('ehough_iconic_exception_ParameterNotFoundException', $e, '->get() throws an Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException if the key does not exist');
            $this->assertEquals('You have requested a non-existent parameter "bag". Did you mean one of these: "bar", "baz"?', $e->getMessage(), '->get() throws an Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException with some advices');
        }

        try {
            $bag->get('');
            $this->fail('->get() throws an Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException if the key does not exist');
        } catch (Exception $e) {
            $this->assertInstanceOf('ehough_iconic_exception_ParameterNotFoundException', $e, '->get() throws an Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException if the key does not exist');
            $this->assertEquals('You have requested a non-existent parameter "".', $e->getMessage(), '->get() throws an Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException with some advices');
        }
    }

    /**
     * @covers ehough_iconic_parameterbag_ParameterBag::has
     */
    public function testHas()
    {
        $bag = new ehough_iconic_parameterbag_ParameterBag(array('foo' => 'bar'));
        $this->assertTrue($bag->has('foo'), '->has() returns true if a parameter is defined');
        $this->assertTrue($bag->has('Foo'), '->has() converts the key to lowercase');
        $this->assertFalse($bag->has('bar'), '->has() returns false if a parameter is not defined');
    }

    /**
     * @covers ehough_iconic_parameterbag_ParameterBag::resolveValue
     */
    public function testResolveValue()
    {
        $bag = new ehough_iconic_parameterbag_ParameterBag(array());
        $this->assertEquals('foo', $bag->resolveValue('foo'), '->resolveValue() returns its argument unmodified if no placeholders are found');

        $bag = new ehough_iconic_parameterbag_ParameterBag(array('foo' => 'bar'));
        $this->assertEquals('I\'m a bar', $bag->resolveValue('I\'m a %foo%'), '->resolveValue() replaces placeholders by their values');
        $this->assertEquals(array('bar' => 'bar'), $bag->resolveValue(array('%foo%' => '%foo%')), '->resolveValue() replaces placeholders in keys and values of arrays');
        $this->assertEquals(array('bar' => array('bar' => array('bar' => 'bar'))), $bag->resolveValue(array('%foo%' => array('%foo%' => array('%foo%' => '%foo%')))), '->resolveValue() replaces placeholders in nested arrays');
        $this->assertEquals('I\'m a %%foo%%', $bag->resolveValue('I\'m a %%foo%%'), '->resolveValue() supports % escaping by doubling it');
        $this->assertEquals('I\'m a bar %%foo bar', $bag->resolveValue('I\'m a %foo% %%foo %foo%'), '->resolveValue() supports % escaping by doubling it');
        $this->assertEquals(array('foo' => array('bar' => array('ding' => 'I\'m a bar %%foo %%bar'))), $bag->resolveValue(array('foo' => array('bar' => array('ding' => 'I\'m a bar %%foo %%bar')))), '->resolveValue() supports % escaping by doubling it');

        $bag = new ehough_iconic_parameterbag_ParameterBag(array('foo' => true));
        $this->assertTrue($bag->resolveValue('%foo%'), '->resolveValue() replaces arguments that are just a placeholder by their value without casting them to strings');
        $bag = new ehough_iconic_parameterbag_ParameterBag(array('foo' => null));
        $this->assertNull($bag->resolveValue('%foo%'), '->resolveValue() replaces arguments that are just a placeholder by their value without casting them to strings');

        $bag = new ehough_iconic_parameterbag_ParameterBag(array('foo' => 'bar', 'baz' => '%%%foo% %foo%%% %%foo%% %%%foo%%%'));
        $this->assertEquals('%%bar bar%% %%foo%% %%bar%%', $bag->resolveValue('%baz%'), '->resolveValue() replaces params placed besides escaped %');

        $bag = new ehough_iconic_parameterbag_ParameterBag(array('baz' => '%%s?%%s'));
        $this->assertEquals('%%s?%%s', $bag->resolveValue('%baz%'), '->resolveValue() is not replacing greedily');

        $bag = new ehough_iconic_parameterbag_ParameterBag(array());
        try {
            $bag->resolveValue('%foobar%');
            $this->fail('->resolveValue() throws an InvalidArgumentException if a placeholder references a non-existent parameter');
        } catch (ehough_iconic_exception_ParameterNotFoundException $e) {
            $this->assertEquals('You have requested a non-existent parameter "foobar".', $e->getMessage(), '->resolveValue() throws a ParameterNotFoundException if a placeholder references a non-existent parameter');
        }

        try {
            $bag->resolveValue('foo %foobar% bar');
            $this->fail('->resolveValue() throws a ParameterNotFoundException if a placeholder references a non-existent parameter');
        } catch (ehough_iconic_exception_ParameterNotFoundException $e) {
            $this->assertEquals('You have requested a non-existent parameter "foobar".', $e->getMessage(), '->resolveValue() throws a ParameterNotFoundException if a placeholder references a non-existent parameter');
        }

        $bag = new ehough_iconic_parameterbag_ParameterBag(array('foo' => 'a %bar%', 'bar' => array()));
        try {
            $bag->resolveValue('%foo%');
            $this->fail('->resolveValue() throws a RuntimeException when a parameter embeds another non-string parameter');
        } catch (RuntimeException $e) {
            $this->assertEquals('A string value must be composed of strings and/or numbers, but found parameter "bar" of type array inside string value "a %bar%".', $e->getMessage(), '->resolveValue() throws a RuntimeException when a parameter embeds another non-string parameter');
        }

        $bag = new ehough_iconic_parameterbag_ParameterBag(array('foo' => '%bar%', 'bar' => '%foobar%', 'foobar' => '%foo%'));
        try {
            $bag->resolveValue('%foo%');
            $this->fail('->resolveValue() throws a ParameterCircularReferenceException when a parameter has a circular reference');
        } catch (ehough_iconic_exception_ParameterCircularReferenceException $e) {
            $this->assertEquals('Circular reference detected for parameter "foo" ("foo" > "bar" > "foobar" > "foo").', $e->getMessage(), '->resolveValue() throws a ParameterCircularReferenceException when a parameter has a circular reference');
        }

        $bag = new ehough_iconic_parameterbag_ParameterBag(array('foo' => 'a %bar%', 'bar' => 'a %foobar%', 'foobar' => 'a %foo%'));
        try {
            $bag->resolveValue('%foo%');
            $this->fail('->resolveValue() throws a ParameterCircularReferenceException when a parameter has a circular reference');
        } catch (ehough_iconic_exception_ParameterCircularReferenceException $e) {
            $this->assertEquals('Circular reference detected for parameter "foo" ("foo" > "bar" > "foobar" > "foo").', $e->getMessage(), '->resolveValue() throws a ParameterCircularReferenceException when a parameter has a circular reference');
        }

        $bag = new ehough_iconic_parameterbag_ParameterBag(array('host' => 'foo.bar', 'port' => 1337));
        $this->assertEquals('foo.bar:1337', $bag->resolveValue('%host%:%port%'));
    }

    /**
     * @covers ehough_iconic_parameterbag_ParameterBag::resolve
     */
    public function testResolveIndicatesWhyAParameterIsNeeded()
    {
        $bag = new ehough_iconic_parameterbag_ParameterBag(array('foo' => '%bar%'));

        try {
            $bag->resolve();
        } catch (ehough_iconic_exception_ParameterNotFoundException $e) {
            $this->assertEquals('The parameter "foo" has a dependency on a non-existent parameter "bar".', $e->getMessage());
        }

        $bag = new ehough_iconic_parameterbag_ParameterBag(array('foo' => '%bar%'));

        try {
            $bag->resolve();
        } catch (ehough_iconic_exception_ParameterNotFoundException $e) {
            $this->assertEquals('The parameter "foo" has a dependency on a non-existent parameter "bar".', $e->getMessage());
        }
    }

    /**
     * @covers ehough_iconic_parameterbag_ParameterBag::resolve
     */
    public function testResolveUnescapesValue()
    {
        $bag = new ehough_iconic_parameterbag_ParameterBag(array(
            'foo' => array('bar' => array('ding' => 'I\'m a bar %%foo %%bar')),
            'bar' => 'I\'m a %%foo%%',
        ));

        $bag->resolve();

        $this->assertEquals('I\'m a %foo%', $bag->get('bar'), '->resolveValue() supports % escaping by doubling it');
        $this->assertEquals(array('bar' => array('ding' => 'I\'m a bar %foo %bar')), $bag->get('foo'), '->resolveValue() supports % escaping by doubling it');
    }

    /**
     * @covers ehough_iconic_parameterbag_ParameterBag::escapeValue
     */
    public function testEscapeValue()
    {
        $bag = new ehough_iconic_parameterbag_ParameterBag();

        $bag->add(array(
            'foo' => $bag->escapeValue(array('bar' => array('ding' => 'I\'m a bar %foo %bar', 'zero' => null))),
            'bar' => $bag->escapeValue('I\'m a %foo%'),
        ));

        $this->assertEquals('I\'m a %%foo%%', $bag->get('bar'), '->escapeValue() escapes % by doubling it');
        $this->assertEquals(array('bar' => array('ding' => 'I\'m a bar %%foo %%bar', 'zero' => null)), $bag->get('foo'), '->escapeValue() escapes % by doubling it');
    }

    /**
     * @covers ehough_iconic_parameterbag_ParameterBag::resolve
     * @dataProvider stringsWithSpacesProvider
     */
    public function testResolveStringWithSpacesReturnsString($expected, $test, $description)
    {
        $bag = new ehough_iconic_parameterbag_ParameterBag(array('foo' => 'bar'));

        try {
            $this->assertEquals($expected, $bag->resolveString($test), $description);
        } catch (ehough_iconic_exception_ParameterNotFoundException $e) {
            $this->fail(sprintf('%s - "%s"', $description, $expected));
        }
    }

    public function stringsWithSpacesProvider()
    {
        return array(
            array('bar', '%foo%', 'Parameters must be wrapped by %.'),
            array('% foo %', '% foo %', 'Parameters should not have spaces.'),
            array('{% set my_template = "foo" %}', '{% set my_template = "foo" %}', 'Twig-like strings are not parameters.'),
            array('50% is less than 100%', '50% is less than 100%', 'Text between % signs is allowed, if there are spaces.'),
        );
    }
}
