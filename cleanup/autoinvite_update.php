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
 * @throws UnbegunTransaction
 * @throws \Delight\Auth\AuthError
 * @throws \Delight\Auth\NotLoggedInException
 * @throws \Envms\FluentPDO\Exception
 * @throws \PHPMailer\PHPMailer\Exception
 * @throws \Spatie\Image\Exceptions\InvalidManipulation
 */
function autoinvite_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    $ratiocheck = 1.0;
    $dt = TIME_NOW;
    $joined = ($dt - 86400 * 90);
    $res = sql_query('SELECT id, uploaded, invites, downloaded, modcomment FROM users WHERE invites = 1 AND class = ' . UC_MIN . " AND uploaded / downloaded <= $ratiocheck AND status = 0 AND registered < $joined") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $subject = 'Auto Invites';
        $msg = "Congratulations, your user group met a set out criteria therefore you have been awarded 2 invites  :)\n Please use them carefully. Cheers " . $site_config['site']['name'] . " staff.\n";
        $cache = $container->get(Cache::class);
        while ($arr = mysqli_fetch_assoc($res)) {
            $ratio = number_format($arr['uploaded'] / $arr['downloaded'], 3);
            $modcomment = $arr['modcomment'];
            $modcomment = get_date((int) $dt, 'DATE', 1) . ' - Awarded 2 bonus invites by System (UL=' . mksize($arr['uploaded']) . ', DL=' . mksize($arr['downloaded']) . ', R=' . $ratio . ") .\n" . $modcomment;
            $modcom = sqlesc($modcomment);
            $msgs_buffer[] = [
                'receiver' => $arr['id'],
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
            $users_buffer[] = '(' . $arr['id'] . ', 2, ' . $modcom . ')'; //== 2 in the user_buffer is award amount :)
            $update['invites'] = ($arr['invites'] + 2); //== 2 in the user_buffer is award amount :)
            $cache->update_row('user_' . $arr['id'], [
                'invites' => $update['invites'],
                'modcomment' => $modcomment,
            ], $site_config['expires']['user_cache']);
        }
        $count = count($users_buffer);
        if ($count > 0) {
            $messages_class = $container->get(Message::class);
            $messages_class->insert($msgs_buffer);
            sql_query('INSERT INTO users (id, invites, modcomment) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE KEY UPDATE invites = invites + VALUES(invites), modcomment = VALUES(modcomment)') or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log']) {
            write_log('Cleanup: Awarded 2 bonus invites to ' . $count . ' member(s) ');
        }
        unset($users_buffer, $msgs_buffer, $update, $count);
    }
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Auto Invites Cleanup: Completed' . $text);
    }
}
