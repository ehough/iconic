<?php

class ProjectWithXsdExtension extends ProjectExtension
{
    public function getXsdValidationBasePath()
    {
        return dirname(__FILE__).'/schema';
    }

    public function getNamespace()
    {
        return 'http://www.example.com/schema/projectwithxsd';
    }

    public function getAlias()
    {
        return 'projectwithxsd';
    }
}
