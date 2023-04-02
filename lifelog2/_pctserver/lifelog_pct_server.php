<?php
//This is modified 2014 for PCT from http://www.php.net/manual/en/sockets.examples.php


error_reporting(E_ALL);

/* Allow the script to hang around waiting for connections. */
set_time_limit(0);

/* Turn on implicit output flushing so we see what we're getting
 * as it comes in. */
ob_implicit_flush();

$port = 3280;
$address="192.168.1.221";
$pct_server_password="PCT2010";

echo "Starting...\n";

if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
    echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
}

if (socket_bind($sock, $address, $port) === false) {
    echo "socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
}

if (socket_listen($sock, 5) === false) {
    echo "socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
}

do {
    if (($msgsock = socket_accept($sock)) === false) {
        echo "socket_accept() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
        break;
    }
    /* Send instructions. */
    echo "Accepted client\n";
    $msg = "Welcome\n";
    socket_write($msgsock, $msg, strlen($msg));

    do {
        if (false === ($buf = socket_read($msgsock, 2048, PHP_NORMAL_READ))) {
            echo "socket_read() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n";
            break 2;
        }
        if (!$buf = trim($buf)) {
            continue;
        }
        if ($buf == 'quit') {
            break;
        }
        if ($buf == 'shutdown') {
            socket_close($msgsock);
            break 2;
        }
        #$talkback = "PHP: You said '$buf'.\n";
	echo "READ: $buf\n";
        $args=explode(" ",$buf);
        if ($args[0]==$pct_server_password){
            //Password ok
            $icport="0x12"; //default to port 1
            if ($args[1]==2){
                $icport="0x13"; //port 2
            }
            $icval="0x".dechex($args[2]);
            $shellcmd="i2cset -y 1 0x20 $icport $icval";
	    echo "PASSTHRU: $shellcmd"."\n";
            passthru($shellcmd);
            $talkback="+OK\r\n";
        } else {
            $talkback="-ERR bad password\n";
        }
        socket_write($msgsock, $talkback, strlen($talkback));
        //echo "$buf\n";
        break;
    } while (true);
    socket_close($msgsock);
} while (true);

socket_close($sock);
?>
