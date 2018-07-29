<?php

/**
 * @param $messages
 */
function ircbot($messages)
{
    $bot = [
        'ip' => '127.0.0.1',
        'port' => 3458,
        'pass' => 'bWFtYWFyZW1lcmU',
        'pidfile' => '/home/ircbot/ion/pid.IoN',
        //path to the pid. file
        'sleep' => 5,
    ];
    if (empty($messages)) {
        return;
    } //die ('Empty message');
    if (!file_exists($bot['pidfile'])) {
        return;
    } //die ('Bot not online');
    if ($bot['hand'] = fsockopen($bot['ip'], $bot['port'], $errno, $errstr, 45)) {
        sleep($bot['sleep']);
        if (is_array($messages)) {
            foreach ($messages as $message) {
                fputs($bot['hand'], $bot['pass'] . ' ' . $message . "\n");
                sleep($bot['sleep']);
            }
        } else {
            fputs($bot['hand'], $bot['pass'] . ' ' . $messages . "\n");
            sleep($bot['sleep']);
        }
        fclose($bot['hand']);
    }
}
