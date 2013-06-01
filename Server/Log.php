<?php
namespace Theapi\Lcdproc\Server;


/**
 * Logging
 */

/* This file is part of phpLCDd, the php lcdproc server.
 *
 * This file is released under the GNU General Public License.
 * Refer to the COPYING file distributed with this package.
 *
 */

class Log
{

    protected $level = LOG_ERR;

    public function __construct($level = LOG_ERR)
    {
        $this->level = $level;
    }

    public function msg($priority, $message)
    {
        if ($priority >= $this->level) {
            echo self::getPriorityString($priority) . ': ' . $message . "\n";

            //TODO: option to log to syslog
        }
    }

    /**
     * @see http://php.net/manual/en/function.syslog.php
     * @param unknown $priority
     * @return string
     */
    public static function getPriorityString($priority) {
        switch ($priority) {
            case LOG_EMERG:
                return 'EMERG';
            case LOG_ALERT:
                return 'ALERT';
            case LOG_CRIT:
                return 'CRIT';
            case LOG_ERR:
                return 'ERR';
            case LOG_WARNING:
                return 'WARNING';
            case LOG_NOTICE:
                return 'NOTICE';
            case LOG_INFO:
                return 'INFO';
            case LOG_DEBUG:
                return 'DEBUG';
        }

        return '';
    }
}
