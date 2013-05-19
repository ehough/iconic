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
 * Tests for {@see \Symfony\Component\DependencyInjection\PhpDumper\NullDumper}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 *
 * @covers ehough_iconic_lazyproxy_phpdumper_NullDumper
 */
class ehough_iconic_lazyproxy_phpdumper_NullDumperTest extends PHPUnit_Framework_TestCase
{
    public function testNullDumper()
    {
        $dumper     = new ehough_iconic_lazyproxy_phpdumper_NullDumper();
        $definition = new ehough_iconic_Definition('stdClass');

        $this->assertFalse($dumper->isProxyCandidate($definition));
        $this->assertSame('', $dumper->getProxyFactoryCode($definition, 'foo'));
        $this->assertSame('', $dumper->getProxyCode($definition));
    }
}
