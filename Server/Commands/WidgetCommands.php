<?php
namespace Theapi\Lcdproc\Server\Commands;

use Theapi\Lcdproc\Server\Widget;

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

use Theapi\Lcdproc\Server;
use Theapi\Lcdproc\Server\Exception\ClientException;

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
        if ($s != null) {
            throw new ClientException($this->client->stream, 'Invalid screen id');
        }

        // Find widget type
        $wtype = Widget::typeNameToType($args[2]);
        if ($wtype == Widget::WID_NONE) {
            throw new ClientException($this->client->stream, 'Invalid widget type');
        }

        // Check for additional flags...
        if ($countArgs > 3) {
            // Not implementing frames (in options)...
            throw new ClientException($this->client->stream, 'Frames not implemented');
        }

        // Create the widget
        $w = new Widget($wid, $wtype, $s);
        if ($w == null) {
            throw new ClientException($this->client->stream, 'Error adding widget');
        }

        // Add the widget to the screen
        $err = $s->addWidget($w);
        if ($err == 0) {
            Server::sendString($this->client->stream, "success\n");
        } else {
            throw new ClientException($this->client->stream, 'Error adding widget');
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

        //TODO: functionality

        Server::sendString($this->client->stream, "success\n");

        return 0;
    }

    /**
     * Configures information about a widget, such as its size, shape,
     * contents, position, speed, etc.
     *
     *
     * widget_set <screenid> <widgetid> <widget-SPECIFIC-data>
     *
     */
    public function set($args)
    {
        if (!$this->client->isActive()) {
            return 1;
        }

        //TODO: functionality

        Server::sendString($this->client->stream, "success\n");

        return 0;
    }
}
