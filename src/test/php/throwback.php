<?php

__throwback::$config = array(

    'name'         => 'ehough_iconic',
    'autoload'     => dirname(__FILE__) . '/../../main/php',
    'dependencies' => array(

        array('symfony/yaml', 'https://github.com/symfony/Yaml', '', 'Symfony/Component/Yaml'),
        array('symfony/yaml', 'https://github.com/symfony/Config', '', 'Symfony/Component/Config'),
    )
);