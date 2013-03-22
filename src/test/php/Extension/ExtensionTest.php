<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//namespace Symfony\Component\DependencyInjection\Tests\Extension;

class ExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getResolvedEnabledFixtures
     */
    public function testIsConfigEnabledReturnsTheResolvedValue($enabled)
    {
        $pb = $this->getMockBuilder('ehough_iconic_parameterbag_ParameterBag')
            ->setMethods(array('resolveValue'))
            ->getMock()
        ;

        $container = $this->getMockBuilder('ehough_iconic_ContainerBuilder')
            ->setMethods(array('getParameterBag'))
            ->getMock()
        ;

        $pb->expects($this->once())
            ->method('resolveValue')
            ->with($this->equalTo($enabled))
            ->will($this->returnValue($enabled))
        ;

        $container->expects($this->once())
            ->method('getParameterBag')
            ->will($this->returnValue($pb))
        ;

        $extension = $this->getMockBuilder('ehough_iconic_extension_Extension')
            ->setMethods(array())
            ->getMockForAbstractClass()
        ;

        $r = new \ReflectionMethod('ehough_iconic_extension_Extension', 'isConfigEnabled');
        $r->setAccessible(true);

        $r->invoke($extension, $container, array('enabled' => $enabled));
    }

    public function getResolvedEnabledFixtures()
    {
        return array(
            array(true),
            array(false)
        );
    }

    /**
     * @expectedException ehough_iconic_exception_InvalidArgumentException
     * @expectedExceptionMessage The config array has no 'enabled' key.
     */
    public function testIsConfigEnabledOnNonEnableableConfig()
    {
        $container = $this->getMockBuilder('ehough_iconic_ContainerBuilder')
            ->getMock()
        ;

        $extension = $this->getMockBuilder('ehough_iconic_extension_Extension')
            ->setMethods(array())
            ->getMockForAbstractClass()
        ;

        $r = new \ReflectionMethod('ehough_iconic_extension_Extension', 'isConfigEnabled');
        $r->setAccessible(true);

        $r->invoke($extension, $container, array());
    }
}
