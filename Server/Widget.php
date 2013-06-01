<?php
namespace Theapi\Lcdproc\Server;

use Theapi\Lcdproc\Server\Screen;
use Theapi\Lcdproc\Server\Exception\ClientException;

/**
 * This houses code that handles the creation and destruction of widget
 * objects for the server. These functions are called from the command parser
 * storing the specified widget in a generic container that is parsed later
 * by the screen renderer.
 */

/* This file is part of phpLCDd, the php lcdproc server.
 *
* This file is released under the GNU General Public License.
* Refer to the COPYING file distributed with this package.
*
*/

class Widget
{

    const WID_NONE     = 'none';
    const WID_STRING   = 'string';
    const WID_HBAR     = 'hbar';
    const WID_VBAR     = 'vbar';
    const WID_ICON     = 'icon';
    const WID_TITLE    = 'title';
    const WID_SCROLLER = 'scroller';
    const WID_FRAME    = 'frame';
    const WID_NUM      = 'num';


    /**
     * Create a widget.
     *
     * @param string $id
     * @param string $type;
     * @param Screen $client
     */
    public function __construct($id, $type, Screen $screen)
    {
        if (!$id) {
            throw new ClientException($screen->client->stream, 'Need id string');
        }

        $this->id = $id;
        $this->type = $type;
        $this->screen = $screen;
        $this->x = 1;
        $this->y = 1;
        $this->width = 0;
        $this->height = 0;
        $this->left = 1;
        $this->top = 1;
        $this->right = 0;
        $this->bottom = 0;
        $this->length = 1;
        $this->speed = 1;
        $this->text = null;
    }

    /**
     * Destroy a widget.
     */
    public function destroy()
    {
        // Just unset in the screen. No need to worry about memory like c does.
    }

    /**
     * Convert a widget type name to a widget type.
     *
     * @param $typeName  Name of the widget type.
     */
    public static function typeNameToType($typeName)
    {
        switch ($typeName) {
            case 'none':
                return self::WID_NONE;
            case 'string':
                return self::WID_STRING;
            case 'hbar':
                return self::WID_HBAR;
            case 'vbar':
                return self::WID_VBAR;
            case 'icon':
                return self::WID_ICON;
            case 'title':
                return self::WID_TITLE;
            case 'scroller':
                return self::WID_SCROLLER;
            case 'frame':
                return self::WID_FRAME;
            case 'num':
                return self::WID_NUM;
            default:
                return self::WID_NONE;
        }
    }

    /**
     * Convert a widget type to the associated type name.
     *
     */
    public static function typeToTypeName($type)
    {

    }

    /**
     * Find subordinate widgets of a widget by name.
     */
    public function searchSubs($type)
    {

    }

    /**
     * Find a widget icon by type.
     */
    public static function iconToIconName($type)
    {

    }

    /**
     * Find a widget icon by name.
     */
    public static function iconNameToIcon($type)
    {

    }
}
