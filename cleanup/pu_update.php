<?php

/**
 * @param $data
 *
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function pu_update($data)
{
    dbconn();
    global $site_config, $queries, $cache, $message_stuffs;

    set_time_limit(1200);
    ignore_user_abort(true);

    $prev_class_name = $class_name = 'user';
    $prev_class = 0;
    $dt = TIME_NOW;

    $pconf = sql_query('SELECT * FROM class_promo ORDER BY id ASC ') or sqlerr(__FILE__, __LINE__);
    while ($ac = mysqli_fetch_assoc($pconf)) {
        $class_config[$ac['name']]['id'] = $ac['id'];
        $class_config[$ac['name']]['name'] = $ac['name'];
        $class_config[$ac['name']]['min_ratio'] = $ac['min_ratio'];
        $class_config[$ac['name']]['uploaded'] = $ac['uploaded'];
        $class_config[$ac['name']]['time'] = $ac['time'];
        $class_config[$ac['name']]['low_ratio'] = $ac['low_ratio'];

        $limit = $class_config[$ac['name']]['uploaded'] * 1024 * 1024 * 1024;
        $minratio = $class_config[$ac['name']]['min_ratio'];
        $maxdt = ($dt - 86400 * $class_config[$ac['name']]['time']);

        $class_value = $class_config[$ac['name']]['name'];
        $res1 = sql_query('SELECT * FROM class_config WHERE value = ' . sqlesc($class_value)) or sqlerr(__FILE__, __LINE__);
        while ($arr1 = mysqli_fetch_assoc($res1)) {
            $class_name = $arr1['classname'];
            $prev_class = $class_value - 1;
        }

        $res2 = sql_query('SELECT * FROM class_config WHERE value = ' . sqlesc($prev_class)) or sqlerr(__FILE__, __LINE__);
        while ($arr2 = mysqli_fetch_assoc($res2)) {
            $prev_class_name = $arr2['classname'];
        }

        $res = sql_query('SELECT id, uploaded, downloaded, invites, modcomment FROM users WHERE class = ' . sqlesc($prev_class) . "  AND uploaded >= $limit AND uploaded / downloaded >= $minratio AND enabled='yes' AND added < $maxdt") or sqlerr(__FILE__, __LINE__);
        $msgs_buffer = $users_buffer = [];
        if (mysqli_num_rows($res) > 0) {
            $subject = 'Class Promotion';
            $msg = 'Congratulations, you have been promoted to [b]' . $class_name . "[/b]. :)\n You get one extra invite.\n";
            while ($arr = mysqli_fetch_assoc($res)) {
                $ratio = number_format($arr['uploaded'] / $arr['downloaded'], 3);
                $modcomment = $arr['modcomment'];
                $modcomment = get_date($dt, 'DATE', 1) . ' - Promoted to ' . $class_name . ' by System (UL=' . mksize($arr['uploaded']) . ', DL=' . mksize($arr['downloaded']) . ', R=' . $ratio . ").\n" . $modcomment;
                $modcom = sqlesc($modcomment);
                $msgs_buffer[] = [
                    'sender' => 0,
                    'receiver' => $arr['id'],
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => $subject,
                ];
                $users_buffer[] = '(' . $arr['id'] . ', ' . $class_value . ', 1, ' . $modcom . ')';
                $update['invites'] = ($arr['invites'] + 1);
                $cache->update_row('user' . $arr['id'], [
                    'class' => $class_value,
                    'invites' => $update['invites'],
                    'modcomment' => $modcomment,
                ], $site_config['expires']['user_cache']);
            }
            $count = count($users_buffer);
            if ($count > 0) {
                $message_stuffs->insert($msgs_buffer);
                sql_query('INSERT INTO users (id, class, invites, modcomment) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE KEY UPDATE class = VALUES(class), invites = invites + VALUES(invites), modcomment = VALUES(modcomment)') or sqlerr(__FILE__, __LINE__);
            }
            if ($data['clean_log']) {
                write_log('Cleanup: Promoted ' . $count . ' member(s) from ' . $prev_class_name . ' to ' . $class_name . '');
            }
            unset($users_buffer, $msgs_buffer, $update, $count);
            status_change($arr['id']);
        }
        if ($data['clean_log'] && $queries > 0) {
            write_log("$class_name Updates Cleanup: Completed using $queries queries");
        }
    }
}
