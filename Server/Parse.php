<?php
namespace Theapi\Lcdproc\Server;

use Theapi\Lcdproc\Server\Exception\ClientException;
use Theapi\Lcdproc\Server\Client;
use Theapi\Lcdproc\Server\Server;

/**
 * Handles input commands from clients, by splitting strings into tokens
 *
 * It works much like a command line.  Only the first token is used to
 * determine what function to call.
 */

/* This file is part of phpLCDd, the php lcdproc server.
 *
 * This file is released under the GNU General Public License.
 * Refer to the COPYING file distributed with this package.
 *
 */

class Parse
{

    const ST_INITIAL    = 0;
    const ST_WHITESPACE = 1;
    const ST_ARGUMENT   = 2;
    const ST_FINAL      = 3;


    public static function message($str, Client $c)
    {
        // explode(' ', trim($str)); is not enough :(

        // widget_set -name {LCDproc ubuntu}
        // widget_set -name "LCDproc ubuntu"

        $quote = null; // The quote used to open a quote string
        $state = self::ST_INITIAL;
        $args = array();
        $arg = '';
        $str = trim($str);
        $length = strlen($str);
        for ($i = 0; $i < $length; $i++) {
            $ch = $str[$i]; //echo $ch . ':';

            // white space is either an argument boundary
            // or a regular white space if in a quoted string

            if ($quote == null) {
                // not in a quote
                if (self::isWhitespace($ch)) {
                    // an argument boundary
                    if (!empty($arg)) {
                        $args[] = $arg;
                    }
                    $arg = '';
                } elseif (self::isOpeningQuote($ch, $quote)) {
                    // start of a quote, bypass $ch
                    $quote = $ch;
                } else {
                   // a regular character to add to the argument
                   $arg .= $ch;
                }
            } else {
               // in a quote
               if (self::isClosingQuote($ch, $quote)) {
                    // end of a quote
                    // an argument boundary
                    if (!empty($arg)) {
                        $args[] = $arg;
                    }
                    $arg = '';
                    $quote = null;
                } else {
                    // a character in quotes
                    $arg .= $ch;
                }
            }
        }
        // the ultimate argument boundary
        if (!empty($arg)) {
            $args[] = $arg;
        }

        // Now find and call the appropriate function...
        $function = array_shift($args);
        try {
            $c->command($function, $args);
        } catch (ClientException $e) {
            $c->container->sendError($e->getStream(), $e->getMessage());
        }

        return 0;
    }

    public static function isWhitespace($x)
    {
        return (($x == ' ') || ($x == "\t") || ($x == "\r"));
    }

    public static function isOpeningQuote($x, $q)
    {
        return (($q == null) && (($x == '"') || ($x == '{')));
    }

    public static function isClosingQuote($x, $q)
    {
        return ((($q == '{') && ($x == '}')) || (($q == '"') && ($x == '"')));
    }

}
