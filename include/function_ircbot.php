<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;

/**
 * @param string $messages
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function ircbot(string $messages)
{
    if (empty($messages)) {
        return;
    }
    $messages = explode("\n", str_replace([
        "\r\n",
        "\n\r",
        "\r",
        '\n',
    ], "\n", $messages));
    $bot = [
        // IP or FQDN that points to eggdrop
        'ip' => 'localhost',
        'port' => 35791,
        //change this here and announce.tcl
        'pass' => 'XZ0jMsqZi2va1ENI',
        // change this here and eggdrop.conf
        'pidfile' => '/home/ircbot/ion/pid.IoN',
        'sleep' => 2,
    ];
    $bot = [
        'ip' => 'pu-239.pw',
        // IP or FQDN
        'port' => 4588,
        'pass' => 'zDOm7kEWoWF4ynqNMSf3NMdxca1JQryF',
        'sleep' => 1,
    ];
    if (!empty($bot['pidfile']) && !file_exists($bot['pidfile'])) {
        write_log("IRCBOT does not appear to be online\n");

        return;
    }
    $fp = fsockopen($bot['ip'], $bot['port'], $errno, $errstr);
    if (!$fp) {
        write_log("IRCBOT Failed to connect: $errstr ($errno)\n");

        return;
    }

    $i = 0;
    sleep($bot['sleep']);
    foreach ($messages as $message) {
        fputs($fp, $bot['pass'] . ' ' . $message . "\n");
        if ($i++ > 0) {
            sleep($bot['sleep']);
        }
    }
    fclose($fp);
}
