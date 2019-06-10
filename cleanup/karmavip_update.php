<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Cache;
use Pu239\Message;

/**
 * @param $data
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 */
function karmavip_update($data)
{
    $time_start = microtime(true);
    $dt = TIME_NOW;
    $res = sql_query("SELECT id, modcomment FROM users WHERE vip_added = 'yes' AND donoruntil < " . $dt . ' AND vip_until < ' . $dt) or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        global $container, $site_config;

        $cache = $container->get(Cache::class);
        $subject = 'VIP status expired.';
        $msg = "Your VIP status has timed out and has been auto-removed by the system. Become a VIP again by donating to {$site_config['site']['name']} , or exchanging some Karma Bonus Points. Cheers !\n";
        while ($arr = mysqli_fetch_assoc($res)) {
            $modcomment = $arr['modcomment'];
            $modcomment = get_date((int) $dt, 'DATE', 1) . " - Vip status Automatically Removed By System.\n" . $modcomment;
            $modcom = sqlesc($modcomment);
            $msgs_buffer[] = [
                'sender' => 0,
                'receiver' => $arr['userid'],
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];

            $users_buffer[] = '(' . $arr['id'] . ',1, \'no\', \'0\' , ' . $modcom . ')';
            $cache->update_row('user_' . $arr['id'], [
                'class' => 1,
                'vip_added' => 'no',
                'vip_until' => 0,
                'modcomment' => $modcomment,
            ], $site_config['expires']['user_cache']);
        }
        $count = count($users_buffer);
        if ($count > 0) {
            $message_stuffs = $container->get(Message::class);
            $message_stuffs->insert($msgs_buffer);
            sql_query('INSERT INTO users (id, class, vip_added, vip_until, modcomment) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE KEY UPDATE class = VALUES(class),vip_added = VALUES(vip_added),vip_until = VALUES(vip_until),modcomment = VALUES(modcomment)') or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log']) {
            write_log('Cleanup - Karma Vip status expired on - ' . $count . ' Member(s)');
        }
        unset($users_buffer, $msgs_buffer, $count);
    }
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Karma Vip Cleanup: Completed' . $text);
    }
}
