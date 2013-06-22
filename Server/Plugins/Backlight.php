<?php
namespace Theapi\Lcdproc\Server\Plugins;

/**
 * Takes control of the backlight away from the clients.
 */


use Symfony\Component\EventDispatcher\GenericEvent;

class Backlight
{

    public function __construct($container)
    {
        $this->container = $container;

        $dispatcher = $this->container->dispatcher;

        $dispatcher->addListener('drivers.backlight', array($this, 'onDriversBacklight'));
    }

    public function onDriversBacklight(GenericEvent $event)
    {
        $state = $event->getSubject();
        $screen = $this->container->screenList->current();

        //var_dump($state, $screen->name); exit;
    }
}
