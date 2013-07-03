#! /bin/sh
#
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin

WS=/mnt/www/repos/lcdproc/Server/Drivers/WebSocket/cli_server.php

DAEMON=/mnt/www/repos/lcdproc/cli_server.php
NAME="phpLCDd"
DESC="phpLCDd"

php -f $WS > /dev/null 2>&1 &
sleep 1
php -f $DAEMON > /dev/null 2>&1 &

exit 0;
