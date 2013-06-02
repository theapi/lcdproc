<?php
namespace Theapi\Lcdproc\Server;

use Theapi\Lcdproc\Server\Render;

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

    const AUTOROTATE_OFF = 0;
    const AUTOROTATE_ON = 1;


    protected $serverScreenOn = true;
    protected $helloMsg =  array('Welcome to', 'PHP LCDproc');

    protected $backlight = Render::BACKLIGHT_OFF;
    protected $heartbeat = Render::HEARTBEAT_OFF;

    protected $duration = 32;
    protected $titleSpeed = Render::TITLESPEED_MAX;
    protected $autoRotate = self::AUTOROTATE_ON;

    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }

        return null;
    }
}
