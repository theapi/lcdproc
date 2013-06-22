<?php
namespace Theapi\Lcdproc\Server\Plugins;

/**
 * Takes control of the backlight away from the clients.
 */

use Theapi\Lcdproc\Server\Render;

use Symfony\Component\EventDispatcher\GenericEvent;

class Backlight
{
    public $backgrounds = array(
        '0',
        '1',
        'red',
        'green',
        'blue',
        'yellow',
        'teal',
        'violet',
        'white',
    );

    public function __construct($container)
    {
        $this->container = $container;

        $dispatcher = $this->container->dispatcher;

        $dispatcher->addListener('drivers.backlight', array($this, 'onDriversBacklight'));


    }

    public function onDriversBacklight()
    {

        // rendom number for now
        $num = rand(0, 8);
        $state = $this->backgrounds[$num];

        //$state = $this->container->drivers->backlight;
        //$screen = $this->container->screenList->current();

        $this->container->drivers->backlight = $state;
    }
}
