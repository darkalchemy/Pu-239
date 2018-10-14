<?php

/**
 * @param $data
 *
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function pu_demote_update($data)
{
    dbconn();
    global $site_config, $queries, $cache, $message_stuffs;

    set_time_limit(1200);
    ignore_user_abort(true);

    $prev_class = 0;
    $class_name = $prev_class_name = 'user';

    $pconf = sql_query('SELECT * FROM class_promo ORDER BY id ASC ') or sqlerr(__FILE__, __LINE__);
    while ($ac = mysqli_fetch_assoc($pconf)) {
        $class_config[$ac['name']]['id'] = $ac['id'];
        $class_config[$ac['name']]['name'] = $ac['name'];
        $class_config[$ac['name']]['min_ratio'] = $ac['min_ratio'];
        $class_config[$ac['name']]['uploaded'] = $ac['uploaded'];
        $class_config[$ac['name']]['time'] = $ac['time'];
        $class_config[$ac['name']]['low_ratio'] = $ac['low_ratio'];

        $minratio = $class_config[$ac['name']]['low_ratio'];

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

        $res = sql_query('SELECT id, uploaded, downloaded, modcomment FROM users WHERE class = ' . sqlesc($class_value) . " AND uploaded / downloaded < $minratio") or sqlerr(__FILE__, __LINE__);
        $subject = 'Auto Demotion';
        $msgs_buffer = $users_buffer = [];
        $dt = TIME_NOW;
        if (mysqli_num_rows($res) > 0) {
            $msg = "You have been auto-demoted from [b]{$class_name}[/b] to [b]{$prev_class_name}[/b] because your share ratio has dropped below  $minratio.\n";

            while ($arr = mysqli_fetch_assoc($res)) {
                $ratio = number_format($arr['uploaded'] / $arr['downloaded'], 3);
                $modcomment = $arr['modcomment'];
                $modcomment = get_date(TIME_NOW, 'DATE', 1) . ' - Demoted To ' . $prev_class_name . ' by System (UL=' . mksize($arr['uploaded']) . ', DL=' . mksize($arr['downloaded']) . ', R=' . $ratio . ").\n" . $modcomment;
                $modcom = sqlesc($modcomment);
                $users_buffer[] = '(' . $arr['id'] . ', ' . $prev_class . ', ' . $modcom . ')';
                $msgs_buffer[] = [
                    'sender' => 0,
                    'receiver' => $arr['id'],
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => $subject,
                ];
                $cache->update_row('user' . $arr['id'], [
                    'class' => $prev_class,
                    'modcomment' => $modcomment,
                ], $site_config['expires']['user_cache']);
            }
            $count = count($users_buffer);
            if ($count > 0) {
                $message_stuffs->insert($msgs_buffer);
                sql_query('INSERT INTO users (id, class, modcomment) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE KEY UPDATE class = VALUES(class),modcomment = VALUES(modcomment)') or sqlerr(__FILE__, __LINE__);
            }
            if ($data['clean_log']) {
                write_log('Cleanup: Demoted ' . $count . " member(s) from {$class_name} to {$prev_class_name}");
            }
            unset($users_buffer, $msgs_buffer, $count);
            status_change($arr['id']);
        }
        if ($data['clean_log'] && $queries > 0) {
            write_log("{$prev_class_name} Updates Cleanup: Completed using $queries queries");
        }
    }
}
