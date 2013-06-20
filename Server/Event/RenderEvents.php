<?php
namespace Theapi\Lcdproc\Server\Event;

final class RenderEvents
{
    /**
     * The render.backlight.pre event is thrown each time
     * the backlight is about to be rendered
     *
     * The event listener receives an
     * Theapi\Lcdproc\Server\Event\RenderEvent instance.
     *
     * @var string
     */
    const BACKLIGHT_PRE = 'render.backlight.pre';
}
