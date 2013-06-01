<?php
namespace Theapi\Lcdproc\Server\Commands;

/**
 * Implements handlers for client commands concerning menus.
 *
 * NB: I don't expect to implement this any further than stubs
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

use Theapi\Lcdproc\Server\Server;
use Theapi\Lcdproc\Server\Exception\ClientException;

class MenuCommands
{

    protected $client;

    public function __construct($client)
    {
        $this->client = $client;
    }


    /**
     * Adds an item to a menu.
     *
     * Usage: menu_add_item <menuid> <newitemid> <type> [<text>] {<option>}+
     *
     * You should use "" as id for the client's main menu. This menu will be
     * created automatically when you add an item to it the first time.
     *
     * You cannot create a menu in the main level yourself, unless you replace the
     * main menu with the client's menu.
     * The names you use for items should be unique for your client.
     * The text is the visible text for the item.
     *
     * The following types are available:
     * - menu
     * - action
     * - checkbox
     * - ring (a kind of listbox of one line)
     * - slider
     * - numeric
     * - alpha
     * - ip
     *
     * For the list of supported options see menu_set_item_func.
     */
    public function addItem($args)
    {
        if (!$this->client->isActive()) {
            return 1;
        }

        //TODO: functionality

        Server::sendString($this->client->stream, "success\n");

        return 0;
    }


    /**
     * Deletes an item from a menu
     *
     * Usage: menu_del_item <menuid> <itemid>
     *
     * The given item in the given menu will be deleted. If you have deleted all
     * the items from your client menu, that menu will automatically be removed.
     */
    public function delItem($args)
    {
        if (!$this->client->isActive()) {
            return 1;
        }

        //TODO: functionality

        Server::sendString($this->client->stream, "success\n");

        return 0;
    }

    /**
     * Sets the info about a menu item.
     *
     * For example, text displayed, value, etc...
     *
     * Usage: menu_set_item <menuid> <itemid> {<option>}+
     *
     * The following parameters can be set per item:
     * (you should include the - in the option)
     *
     * For all types:
     * -text "text"			("")
     *	Sets the visible text.
     * -is_hidden false|true	(false)
     *	If the item currently should not appear in a menu.
     * -prev id			()
     *	Sets the predecessor of this item (what happens after "Escape")
     *
     * For all except menus:
     * -next id			()
     *	Sets the successor of this item (what happens after "Enter")
     *
     * action:
     * -menu_result none|close|quit	(none)
     *	Sets what to do with the menu when this action is selected:
     *	- none: the menu stays as it is.
     *	- close: the menu closes and returns to a higher level.
     *	- quit: quits the menu completely so you can foreground your app.
     *
     * checkbox:
     * -value off|on|gray		(off)
     *	Sets its current value.
     * -allow_gray false|true	(false)
     *	Sets if a grayed checkbox is allowed.
     *
     * ring:
     * -value <int>			(0)
     *	Sets the index in the stringlist that is currently selected.
     * -strings <string>		(empty)
     *	The subsequent strings that can be selected. They should be
     *	tab-separated in ONE string.
     *
     * slider:
     * -value <int>			(0)
     *	Sets its current value.
     * -mintext <string>		("")
     * -maxtex <string>		("")
     *	Text at the minimal and maximal side. On small displays these might
     *	not be displayed.
     * -minvalue <int>		(0)
     * -maxvalue <int>		(100)
     *	The minimum and maximum value of the slider.
     * -stepsize <int>		(1)
     *	The stepsize of the slider. If you use 0, you can control it yourself
     *	completely.
     *
     * numeric:
     * -value <int>			(0)
     *	Sets its current value.
     * -minvalue <int>		(0)
     * -maxvalue <int>		(100)
     *	The minimum and maximum value that are allowed. If you make one of
     *	them negative, the user will be able to enter negative numbers too.
     * Maybe floats will work too in the future.
     *
     * alpha:
     * -value <string>
     *	Sets its current value.	("")
     * -password_char <char>	(none)
     * -minlength <int>		(0)
     * -maxlength <int>		(10)
     *	Set the minimum and maximum allowed length.
     * -allow_caps false|true	(true)
     * -allow_noncaps false|true	(false)
     * -allow_numbers false|true	(true)
     *	Allows these groups of characters.
     * -allowed_extra <string>	("")
     *	The chars in this string are also allowed.
     *
     * ip:
     * -value <string>
     *	Sets its current value.	("")
     * -v6 false|true
     *
     * Hmm, this is getting very big. We might need a some real parser after all.
     */
    public function setItem($args)
    {
        if (!$this->client->isActive()) {
            return 1;
        }

        //TODO: functionality

        Server::sendString($this->client->stream, "success\n");

        return 0;
    }

    /**
     * Requests the menu system to display the given menu screen.
     *
     * Depending on
     * the setting of the LCDPROC_PERMISSIVE_MENU_GOTO it is impossible
     * to go to a menu of another client (or the server menus). Same
     * restriction applies to the optional predecessor_id
     *
     * Usage: menu_goto <id> [<predecessor_id>]
     */
    public function menuGoto($args)
    {
        if (!$this->client->isActive()) {
            return 1;
        }

        //TODO: functionality

        Server::sendString($this->client->stream, "success\n");

        return 0;
    }

    /** Sets the predecessor of a Menuitem item to itemid (for wizzards).
     * For example the menuitem to go to after hitting "Enter" on item.
     *
     * @return 0 on success and -1 otherwise
     */
    public function setPredecessor($args)
    {
        if (!$this->client->isActive()) {
            return 1;
        }

        //TODO: functionality

        return 0;
    }

    /** Sets the successor of a Menuitem item to itemid (for wizzards). For example the
     * menuitem to go to after hitting "Enter" on item. Checks that a matching
     * menu item can be found. Checks if item is not a menu. (If you would
     * redefine the meaning of "Enter" for a menu it would not be useful
     * anymore.)
     *
     * @return 0 on success and -1 otherwise
     */
    public function setSuccessor($args)
    {
        if (!$this->client->isActive()) {
            return 1;
        }

        //TODO: functionality

        return 0;
    }

    /**
     * Requests the menu system to set the entry point into the menu system.
     *
     * Usage: menu_set_main <id>
     */
    public function setMain($args)
    {
        if (!$this->client->isActive()) {
            return 1;
        }

        //TODO: functionality

        Server::sendString($this->client->stream, "success\n");

        return 0;
    }

    /**
     * This function catches the event for the menus that have been
     * created on behalf of the clients. It informs the client with
     * an event message.
     */
    public function menuEventFunc()
    {

        return 0;
    }
}
