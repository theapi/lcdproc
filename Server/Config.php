<?php
namespace Theapi\Lcdproc\Server;

/**
 * Confiuration
 */

/* This file is part of phpLCDd, the php lcdproc server.
 *
 * This file is released under the GNU General Public License.
 * Refer to the COPYING file distributed with this package.
 *
 */

class Config
{

  const HEARTBEAT_OFF = 0;
  const HEARTBEAT_ON = 1;
  const HEARTBEAT_OPEN = 2;

  const BACKLIGHT_OFF = 0;
  const BACKLIGHT_ON = 1;
  const BACKLIGHT_OPEN = 2;

  const BACKLIGHT_BLINK = 100;
  const BACKLIGHT_FLASH	=200;

  const CURSOR_OFF = 0;
  const CURSOR_DEFAULT_ON	= 1;
  const CURSOR_BLOCK = 4;
  const CURSOR_UNDER = 5;

  const TITLESPEED_NO = 0;	/* needs to be (TITLESPEED_MIN - 1) */
  const TITLESPEED_MIN = 1;
  const TITLESPEED_MAX = 10;

  const AUTOROTATE_OFF = 0;
  const AUTOROTATE_ON = 1;


  protected $serverScreenOn = TRUE;
  protected $helloMsg =  array('Welcome to', 'PHP LCDproc');

  protected $backlight = self::BACKLIGHT_OFF;
  protected $heartbeat = self::HEARTBEAT_OFF;

  protected $screenDuration = 32;
  protected $titleSpeed = self::TITLESPEED_MAX;
  protected $autoRotate = self::AUTOROTATE_ON;

  public function __get($name) {
    if (isset($this->$name)) {
      return $this->$name;
    }
    return NULL;
  }

}
