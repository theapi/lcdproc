<?php
namespace Theapi\Lcdproc\Server;

/**
 * Document the events
 *
 */
final class Events
{

    /**
     * The screenlist.pre_process event is thrown
     * just before the screenList's process() method is called.
     *
     * @var string
     */
    const SCREENLIST_PRE_PROCESS = 'screenlist.pre_process';

    /**
     * The drivers.backlight event is thrown
     * just before each driver's backlight() method is called.
     *
     * @var string
     */
    const DRIVERS_BACKLIGHT = 'drivers.backlight';
}
