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

    // Icons below are one character wide
    const ICON_BLOCK_FILLED	 = 0x100;
    const ICON_HEART_OPEN    = 0x108;
    const ICON_HEART_FILLED  = 0x109;
    const ICON_ARROW_UP      = 0x110;
    const ICON_ARROW_DOWN    = 0x111;
    const ICON_ARROW_LEFT    = 0x112;
    const ICON_ARROW_RIGHT   = 0x113;
    const ICON_CHECKBOX_OFF  = 0x120;
    const ICON_CHECKBOX_ON   = 0x121;
    const ICON_CHECKBOX_GRAY = 0x122;
    const ICON_SELECTOR_AT_LEFT	  = 0x128;
    const ICON_SELECTOR_AT_RIGHT	= 0x129;
    const ICON_ELLIPSIS      = 0x130;

    // Icons below are two characters wide
    const ICON_STOP	 = 0x200;	// should look like  []
    const ICON_PAUSE = 0x201;	// should look like  ||
    const ICON_PLAY  = 0x202;	// should look like  >
    const ICON_PLAYR = 0x203;	// should llok like  <
    const ICON_FF    = 0x204;	// should look like  >>
    const ICON_FR	   = 0x205;	// should look like  <<
    const ICON_NEXT	 = 0x206;	// should look like  >|
    const ICON_PREV	 = 0x207;	// should look like  |<
    const ICON_REC   = 0x208;	// should look like  ()


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

        if ($this->type == self::WID_FRAME) {
            // create a screen for the frame widget
            $frameName = 'frame_' . $id;
            $this->frameScreen = new Screen($screen->client->container, $frameName, $screen->client);
        }

        return $this;
    }

    /**
     * Destroy a widget.
     */
    public function destroy()
    {
        // No need to worry about memory like c does.

        // Free subscreen of frame widget too
        if ($this->type == self::WID_FRAME) {
            $this->frameScreen->destroy();
        }

        return 0;
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
    public function searchSubs($id)
    {
        if ($this->type == self::WID_FRAME) {
            return $this->frameScreen->findWidget($id);
        }

        return null;
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
        return 'ICON_' . $type;
    }
}
