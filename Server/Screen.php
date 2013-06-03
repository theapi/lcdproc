<?php
namespace Theapi\Lcdproc\Server;

use Theapi\Lcdproc\Server\Client;
use Theapi\Lcdproc\Server\Render;
use Theapi\Lcdproc\Server\Widget;

/**
 * This stores all the screen definition-handling code. Functions here
 * provide means to create new screens and destroy existing ones. Screens are
 * identified by client and by the client's own identifiers for screens.
 */

/* This file is part of phpLCDd, the php lcdproc server.
 *
 * This file is released under the GNU General Public License.
 * Refer to the COPYING file distributed with this package.
 *
 */

class Screen
{

    const PRI_HIDDEN = 0;
    const PRI_BACKGROUND = 1;
    const PRI_INFO = 2;
    const PRI_FOREGROUND = 3;
    const PRI_ALERT = 4;
    const PRI_INPUT = 5;

    public $name = null;
    public $priority = self::PRI_INFO;
    public $duration = 32;
    public $backlight = Render::BACKLIGHT_OFF;
    public $heartbeat = Render::HEARTBEAT_OFF;
    public $width;
    public $height;
    public $keys = null;
    public $client = null;
    public $timeout = -1; 	/*ignored unless greater than 0.*/
    public $cursor = Render::CURSOR_OFF;
    public $cursor_x = 1;
    public $cursor_y = 1;
    public $widgetlist = array();

    protected $container;
    protected $config;

    /**
     * Create a screen.
     *
     * @param Config $config
     * @param string $id
     * @param Client $client
     */
    public function __construct($container, $id, Client $client = null)
    {
        $this->container = $container;
        $this->config = $this->container->config;

        $this->client = $client;
        if (!$id) {
            throw new ClientException($this->client, 'Need id string');
        }

        $this->id = $id;
        //$this->duration = $this->config->duration;
        $this->width = $this->container->drivers->displayProps->width;
        $this->height = $this->container->drivers->displayProps->height;

        // Client can be null for serverscreens and other client-less screens

        // menuscreen_add_screen(s)
    }

    /**
     * Destroy a screen.
     */
    public function destroy()
    {
        //menuscreen_remove_screen(s);

        $this->container->screenList->remove($this);

        // Destroy subscreens recursively
        foreach ($this->widgetlist as $w) {
            if ($w->type == Widget::WID_FRAME) {
                $w->destroy();
            }
        }

        // detroy widgets
        $this->widgetlist = array();

    }

    /**
     * Add a widget to a screen.
     */
    public function addWidget($widget)
    {
        $this->widgetlist[$widget->id] = $widget;

        return 0;
    }

    /**
     * Remove a widget from a screen.
     */
    public function removeWidget($widget)
    {
        if (isset($this->widgetlist[$widget->id])) {
            unset($this->widgetlist[$widget->id]);
        }

        return 0;
    }

    /**
     * Find a widget on a screen by its id.
     */
    public function findWidget($id)
    {
        if (isset($this->widgetlist[$id])) {
            return $this->widgetlist[$id];
        }

        // Search subscreens recursively
        foreach ($this->widgetlist as $w) {
            if ($w->type == Widget::WID_FRAME) {
                $w = $w->searchSubs($id);
                if ($w) {
                    return $w;
                }
            }
        }

        return null;
    }

    /**
     * Convert a priority name to the priority id.
     * @param priname  Name of the screen priority.
     */
    public static function priNameToPri($priName)
    {
        switch ($priName) {
            case 'hidden':
                return self::PRI_HIDDEN;
            case 'background':
                return self::PRI_BACKGROUND;
            case 'info':
                return self::PRI_INFO;
            case 'foreground':
                return self::PRI_FOREGROUND;
            case 'alert':
                return self::PRI_ALERT;
            case 'input':
                return self::PRI_INPUT;
            default:
                return null;
        }
    }

    /**
     * Convert a priority id to the associated name.
     * @param pri  Priority id.
     */
    public static function priToPriName($pri)
    {

    }
}
