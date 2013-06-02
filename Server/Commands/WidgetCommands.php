<?php
namespace Theapi\Lcdproc\Server\Commands;

use Theapi\Lcdproc\Server\Widget;
use Theapi\Lcdproc\Server\Server;
use Theapi\Lcdproc\Server\Exception\ClientException;

/**
 * Implements handlers for client commands concerning widgets.
 *
 * This contains definitions for all the functions which clients can run.
 *
 * The client's available function set is defined here, as is the syntax
 * for each command.
 */

/* This file is part of phpLCDd, the php lcdproc server.
 *
 * This file is released under the GNU General Public License.
 * Refer to the COPYING file distributed with this package.
 *
 */

class WidgetCommands
{

    protected $client;

    public function __construct($client)
    {
        $this->client = $client;
    }


    /**
     * Adds a widget to a screen, but doesn't give it a value
     *
     * Usage: widget_add <screenid> <widgetid> <widgettype> [-in <id>]
     */
    public function add($args)
    {
        if (!$this->client->isActive()) {
            return 1;
        }

        $countArgs = count($args);
        if ($countArgs < 3 || $countArgs > 5) {
            throw new ClientException(
                $this->client->stream,
                'Usage: widget_add <screenid> <widgetid> <widgettype> [-in <id>]'
            );
        }

        $sid = $args[0];
        $wid = $args[1];
        $s = $this->client->findScreen($sid);
        if ($s == null) {
            throw new ClientException($this->client, 'Invalid screen id');
        }

        // Find widget type
        $wtype = Widget::typeNameToType($args[2]);
        if ($wtype == Widget::WID_NONE) {
            throw new ClientException($this->client, 'Invalid widget type');
        }

        // Check for additional flags...
        if ($countArgs > 3) {
            // Not implementing frames (in options)...
            throw new ClientException($this->client, 'Frames not implemented');
        }

        // Create the widget
        $w = new Widget($wid, $wtype, $s);
        if ($w == null) {
            throw new ClientException($this->client, 'Error adding widget');
        }

        // Add the widget to the screen
        $err = $s->addWidget($w);
        if ($err == 0) {
            Server::sendString($this->client->stream, "success\n");
        } else {
            throw new ClientException($this->client, 'Error adding widget');
        }

        return 0;
    }


    /**
     * Removes a widget from a screen, and forgets about it
     *
     * Usage: widget_del <screenid> <widgetid>
     */
    public function del($args)
    {
        if (!$this->client->isActive()) {
            return 1;
        }

        if (count($args) != 2) {
            throw new ClientException($this->client, 'Usage: widget_del <screenid> <widgetid>');
        }

        $sid = $args[0];
        $wid = $args[1];
        $s = $this->client->findScreen($sid);
        if ($s == null) {
            throw new ClientException($this->client, 'Invalid screen id');
        }

        $w = $s->findWidget($wid);
        if ($w == null) {
            throw new ClientException($this->client, 'Invalid widget id');
        }

        $err = $s->removeWidget($w);
        if ($err == 0) {
            Server::sendString($this->client->stream, "success\n");
        } else {
            throw new ClientException($this->client, 'Error removing widget');
        }

        return 0;
    }

    /**
     * Configures information about a widget, such as its size, shape,
     * contents, position, speed, etc.
     *
     * widget_set <screenid> <widgetid> <widget-SPECIFIC-data>
     *
     */
    public function set($args)
    {
        if (!$this->client->isActive()) {
            return 1;
        }

        // If there weren't enough parameters...
        // We can't test for too many, since each widget may have a
        // different number - plus, if the argument count is wrong, what ELSE
        // could be wrong...?
        if (count($args) < 3) {
            throw new ClientException(
                $this->client->stream,
                'Usage: widget_set <screenid> <widgetid> <widget-SPECIFIC-data>'
            );
        }

        // Find screen
        $sid = $args[0];
        $s = $this->client->findScreen($sid);
        if ($s == null) {
            throw new ClientException($this->client, 'Unknown screen id');
        }

        // Find widget
        $wid = $args[1];
        $w = $s->findWidget($wid);
        if ($w == null) {
            throw new ClientException($this->client, 'Unknown widget id');
        }

        switch ($w->type) {
            case Widget::WID_STRING:
                // String takes "x y text"
                if (!isset($args[4])) {
                    throw new ClientException($this->client, 'Wrong number of arguments');
                }
                if (!is_numeric($args[2]) || !is_numeric($args[3])) {
                    throw new ClientException($this->client, 'Invalid coordinates');
                }

                $w->x = (int) $args[2];
                $w->y = (int) $args[3];
                $w->text = $args[4];
                Server::sendString($this->client->stream, "success\n");
                break;
            case Widget::WID_HBAR:
                // Hbar takes "x y length"
                if (!isset($args[4])) {
                    throw new ClientException($this->client, 'Wrong number of arguments');
                }
                if (!is_numeric($args[2]) || !is_numeric($args[3])) {
                    throw new ClientException($this->client, 'Invalid coordinates');
                }

                $w->x = (int) $args[2];
                $w->y = (int) $args[3];
                // This is the length in pixels
                $w->length = (int) $args[4];

                Server::sendString($this->client->stream, "success\n");
                break;
            case Widget::WID_VBAR:
                // Vbar takes "x y length"
                if (!isset($args[4])) {
                    throw new ClientException($this->client, 'Wrong number of arguments');
                }
                if (!is_numeric($args[2]) || !is_numeric($args[3])) {
                    throw new ClientException($this->client, 'Invalid coordinates');
                }

                $w->x = (int) $args[2];
                $w->y = (int) $args[3];
                // This is the length in pixels
                $w->length = (int) $args[4];

                Server::sendString($this->client->stream, "success\n");
                break;
            case Widget::WID_ICON:
                // Icon takes "x y icon"
                if (!isset($args[4])) {
                    throw new ClientException($this->client, 'Wrong number of arguments');
                }
                if (!is_numeric($args[2]) || !is_numeric($args[3])) {
                    throw new ClientException($this->client, 'Invalid coordinates');
                }

                $w->x = (int) $args[2];
                $w->y = (int) $args[3];
                $icon = Widget::iconNameToIcon($args[4]);
                if (!$icon) {
                    throw new ClientException($this->client, 'Invalid icon name');
                }
                $w->length = $icon;

                Server::sendString($this->client->stream, "success\n");
                break;
            case Widget::WID_TITLE:
                // title takes "text"
                if (!isset($args[2])) {
                    throw new ClientException($this->client, 'Wrong number of arguments');
                }

                $w->text = $args[2];
                // Set width too
                $w->width = $this->client->container->drivers->displayProps->width;

                Server::sendString($this->client->stream, "success\n");
                break;
            case Widget::WID_SCROLLER:
                // Scroller takes "left top right bottom direction speed text"
                if (!isset($args[8])) {
                    throw new ClientException($this->client, 'Wrong number of arguments');
                }

                if (!is_numeric($args[2])
                    || !is_numeric($args[3])
                    || !is_numeric($args[4])
                    || !is_numeric($args[5])) {
                    throw new ClientException($this->client, 'Invalid coordinates');
                }

                // Direction must be m, v or h
                if ($args[6] != 'm' || $args[6] != 'v' || $args[6] != 'h') {
                    throw new ClientException($this->client, 'Invalid direction');
                }

                $w->left = (int) $args[2];
                $w->top = (int) $args[3];
                $w->right = (int) $args[4];
                $w->bottom = (int) $args[5];
                $w->length = (int) $args[6];
                $w->speed = (int) $args[7];
                $w->text = $args[8];

                Server::sendString($this->client->stream, "success\n");
                break;
            case Widget::WID_FRAME:
                // not doing frames
                // remove the screen
                $this->client->removeScreen($s);
                throw new ClientException($this->client, 'Not implemented: widget type frame');
                break;
            case Widget::WID_NUM:
                // Num takes "x num"
                if (!isset($args[3])) {
                    throw new ClientException($this->client, 'Wrong number of arguments');
                }
                if (!is_numeric($args[2]) || !is_numeric($args[2])) {
                    throw new ClientException($this->client, 'Invalid coordinates');
                }

                $w->x = (int) $args[2];
                $w->y = (int) $args[3];

                Server::sendString($this->client->stream, "success\n");
                break;
            case Widget::WID_NONE:
                throw new ClientException($this->client, 'Widget has no type');
                break;
        }

        return 0;
    }
}
