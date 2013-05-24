<?php
namespace Theapi\Lcdproc\Server;

use Theapi\Lcdproc\Server\Client;

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

  /**
   * Create a screen.
   *
   * @param string $id
   * @param Client $client
   */
  public function create($id, Client $client) {

  }

  /**
   * Destroy a screen.
   */
  public function destroy() {

  }

  /**
   * Add a widget to a screen.
   */
  public function addWidget($widget) {

  }

  /**
   * Remove a widget from a screen.
   */
  public function removeWidget($widget) {

  }

  /**
   * Find a widget on a screen by its id.
   */
  public function findWidget($widget) {

  }

  /**
   * Convert a priority name to the priority id.
   * @param priname  Name of the screen priority.
   */
  public function priNameToPri($priName) {

  }

  /**
   * Convert a priority id to the associated name.
   * @param pri  Priority id.
   */
  public function priToPriName($pri) {

  }
}
