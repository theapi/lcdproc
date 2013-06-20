<?php
namespace Theapi\Lcdproc\Server;

/**
 * Take control of what the clients ask for adn render them how the director wants.
 */


class Director
{

    protected $container;


    public function __construct($container)
    {
        $this->container = $container;
    }
}
