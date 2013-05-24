<?php
namespace Theapi\Lcdproc\Server;

use Theapi\Lcdproc\Server\Client;

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

  /**
   * Create a widget.
   *
   * @param string $id
   * @param Client $client
   */
  public function create($id, Client $client) {

  }

  /**
   * Destroy a widget.
   */
  public function destroy() {

  }

  /**
   * Convert a widget type name to a widget type.
   *
   * @param $typeName  Name of the widget type.
   */
  public function typeNameToType($typeName) {

  }

  /**
   * Convert a widget type to the associated type name.
   *
   */
  public function typeToTypeName($type) {

  }

  /**
   * Find subordinate widgets of a widget by name.
   */
  public function searchSubs($type) {

  }

  /**
   * Find a widget icon by type.
   */
  public function iconToIconName($type) {

  }

  /**
   * Find a widget icon by name.
   */
  public function iconNameToIcon($type) {

  }

}
