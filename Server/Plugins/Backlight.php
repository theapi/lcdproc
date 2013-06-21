<?php
namespace Theapi\Lcdproc\Server\Plugins;

/**
 * Takes control of the backlight away from the clients.
 */

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;

class Backlight
{

    public function __construct($container)
    {
        $this->container = $container;

        $dispatcher = new EventDispatcher();

        $dispatcher->addListener('drivers.backlight', array($this, 'onDriversBacklight'));
    }

    public function onDriversBacklight(GenericEvent $event)
    {
        var_dump($event);
    }
}
