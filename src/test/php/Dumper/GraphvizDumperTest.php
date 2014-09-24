<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ehough_iconic_dumper_GraphvizDumperTest extends PHPUnit_Framework_TestCase
{
    protected static $fixturesPath;

    public static function setUpBeforeClass()
    {
        self::$fixturesPath = dirname(__FILE__).'/../Fixtures/';
    }

    public function testDump()
    {
        $dumper = new ehough_iconic_dumper_GraphvizDumper($container = new ehough_iconic_ContainerBuilder());

        $this->assertStringEqualsFile(self::$fixturesPath.'/graphviz/services1.dot', $dumper->dump(), '->dump() dumps an empty container as an empty dot file');

        $container = include self::$fixturesPath.'/containers/container9.php';
        $dumper = new ehough_iconic_dumper_GraphvizDumper($container);
        $this->assertEquals(str_replace('%path%', dirname(__FILE__), file_get_contents(self::$fixturesPath.'/graphviz/services9.dot')), $dumper->dump(), '->dump() dumps services');

        $container = include self::$fixturesPath.'/containers/container10.php';
        $dumper = new ehough_iconic_dumper_GraphvizDumper($container);
        $this->assertEquals(str_replace('%path%', dirname(__FILE__), file_get_contents(self::$fixturesPath.'/graphviz/services10.dot')), $dumper->dump(), '->dump() dumps services');

        $container = include self::$fixturesPath.'/containers/container10.php';
        $dumper = new ehough_iconic_dumper_GraphvizDumper($container);
        $this->assertEquals($dumper->dump(array(
            'graph' => array('ratio' => 'normal'),
            'node'  => array('fontsize' => 13, 'fontname' => 'Verdana', 'shape' => 'square'),
            'edge'  => array('fontsize' => 12, 'fontname' => 'Verdana', 'color' => 'white', 'arrowhead' => 'closed', 'arrowsize' => 1),
            'node.instance' => array('fillcolor' => 'green', 'style' => 'empty'),
            'node.definition' => array('fillcolor' => 'grey'),
            'node.missing' => array('fillcolor' => 'red', 'style' => 'empty'),
        )), str_replace('%path%', dirname(__FILE__), file_get_contents(self::$fixturesPath.'/graphviz/services10-1.dot')), '->dump() dumps services');
    }

    public function testDumpWithFrozenContainer()
    {
        if (version_compare(PHP_VERSION, '5.3') < 0) {
            $this->markTestSkipped('PHP < 5.3');
            return;
        }
        
        $container = include self::$fixturesPath.'/containers/container13.php';
        $dumper = new ehough_iconic_dumper_GraphvizDumper($container);
        $this->assertEquals(str_replace('%path%', dirname(__FILE__), file_get_contents(self::$fixturesPath.'/graphviz/services13.dot')), $dumper->dump(), '->dump() dumps services');
    }

    public function testDumpWithFrozenCustomClassContainer()
    {
        $container = include self::$fixturesPath.'/containers/container14.php';
        $dumper = new ehough_iconic_dumper_GraphvizDumper($container);
        $this->assertEquals(str_replace('%path%', dirname(__FILE__), file_get_contents(self::$fixturesPath.'/graphviz/services14.dot')), $dumper->dump(), '->dump() dumps services');
    }

    public function testDumpWithUnresolvedParameter()
    {
        $container = include self::$fixturesPath.'/containers/container17.php';
        $dumper = new ehough_iconic_dumper_GraphvizDumper($container);

        $this->assertEquals(str_replace('%path%', dirname(__FILE__), file_get_contents(self::$fixturesPath.'/graphviz/services17.dot')), $dumper->dump(), '->dump() dumps services');
    }

    public function testDumpWithScopes()
    {
        $container = include self::$fixturesPath.'/containers/container18.php';
        $dumper = new ehough_iconic_dumper_GraphvizDumper($container);
        $this->assertEquals(str_replace('%path%', __DIR__, file_get_contents(self::$fixturesPath.'/graphviz/services18.dot')), $dumper->dump(), '->dump() dumps services');
    }
}
