<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//namespace Symfony\Component\DependencyInjection;

/**
 * ContainerAwareInterface should be implemented by classes that depends on a Container.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
interface ContainerAwareInterface
{
    /**
     * Sets the Container.
     *
     * @param ehough_iconic_ContainerInterface $container A ContainerInterface instance
     *
     * @api
     */
    public function setContainer(ehough_iconic_ContainerInterface $container = null);
}
