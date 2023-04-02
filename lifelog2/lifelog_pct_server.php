<?php
//This is modified 2014 for PCT from http://www.php.net/manual/en/sockets.examples.php


// Modified 2018 in Vancouver

error_reporting(E_ALL);

/* Allow the script to hang around waiting for connections. */
set_time_limit(0);

/* Turn on implicit output flushing so we see what we're getting
 * as it comes in. */
ob_implicit_flush();

$address = '192.168.1.200';
$port = 3280;

$pct_server_password="PCT2010";

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
    $msg = "\nWelcome to PCT \n" .
        "Syntax: Password port-id bit-pattern\n";
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
        echo "READ: $buf";
        $args=explode(" ",$buf);
        if ($args[0]==$pct_server_password){
            //Password ok
            $icport="0x14"; //default to port 1
            if ($args[1]==2){
                $icport="0x15"; //port 2
            }
            $icval="0x".dechex(bindec($args[2]));
            $shellcmd="i2cset -y 1 0x20 $icport $icval";
            echo ""
            passthru($shellcmd);
            $talkback="+OK\n";
        } else {
            $talkback="-ERR bad password\n";
        }
        socket_write($msgsock, $talkback, strlen($talkback));
        echo "$buf\n";
        socket_close($msgsock);
    } while (true);
    socket_close($msgsock);
} while (true);

socket_close($sock);
?>