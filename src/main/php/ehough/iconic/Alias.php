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
 * @api
 */
class ehough_iconic_Alias
{
    private $id;
    private $public;

    /**
     * Constructor.
     *
     * @param string $id     ehough_iconic_Alias identifier
     * @param bool   $public If this alias is public
     *
     * @api
     */
    public function __construct($id, $public = true)
    {
        $this->id = strtolower($id);
        $this->public = $public;
    }

    /**
     * Checks if this DI ehough_iconic_Alias should be public or not.
     *
     * @return bool
     *
     * @api
     */
    public function isPublic()
    {
        return $this->public;
    }

    /**
     * Sets if this ehough_iconic_Alias is public.
     *
     * @param bool $boolean If this ehough_iconic_Alias should be public
     *
     * @api
     */
    public function setPublic($boolean)
    {
        $this->public = (bool) $boolean;
    }

    /**
     * Returns the Id of this alias.
     *
     * @return string The alias id
     *
     * @api
     */
    public function __toString()
    {
        return $this->id;
    }
}
