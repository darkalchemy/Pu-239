<?php

/**
 * @param $data
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function pu_update($data)
{
    $time_start = microtime(true);
    global $site_config, $cache, $message_stuffs, $fluent;

    set_time_limit(1200);
    ignore_user_abort(true);

    $prev_class_name = $class_name = 'user';
    $prev_class = $count = 0;
    $dt = TIME_NOW;

    $promos = $fluent->from('class_promo')
        ->orderBy('id')
        ->fetchAll();
    foreach ($promos as $ac) {
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
        $classes = $fluent->from('class_config')
            ->where('value = ?', $class_value)
            ->fetch();
        $class_name = $classes['classname'];
        $prev_class = $class_value - 1;

        $classes = $fluent->from('class_config')
            ->where('value = ?', $prev_class)
            ->fetch();
        $prev_class_name = $classes['classname'];
        $users = $fluent->from('users')
            ->select(null)
            ->select('id')
            ->select('uploaded')
            ->select('downloaded')
            ->select('invites')
            ->select('modcomment')
            ->where('class = ?', $prev_class)
            ->where('enabled = "yes"')
            ->where('added < ?', $maxdt)
            ->where('uploaded>= ?', $limit)
            ->where('uploaded / IF(downloaded>0, downloaded, 1)>= ?', $minratio)
            ->fetchAll();

        $msgs_buffer = $users_buffer = [];
        $comment = '';
        if (count($users) > 0) {
            $subject = 'Class Promotion';
            $msg = 'Congratulations, you have been promoted to [b]' . $class_name . "[/b]. :)\n You get one extra invite.\n";
            foreach ($users as $arr) {
                $ratio = $arr['downloaded'] === 0 ? 'Infinite' : number_format($arr['uploaded'] / $arr['downloaded'], 3);
                $modcomment = $arr['modcomment'];
                $comment = get_date($dt, 'DATE', 1) . ' - Promoted to ' . $class_name . ' by System (UL=' . mksize($arr['uploaded']) . ', DL=' . mksize($arr['downloaded']) . ', R=' . $ratio . ").\n";
                $modcomment = $comment . $modcomment;
                $msgs_buffer[] = [
                    'sender' => 0,
                    'receiver' => $arr['id'],
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => $subject,
                ];
                $user = $cache->get('user_' . $arr['id']);
                if (!empty($user)) {
                    $cache->update_row('user_' . $arr['id'], [
                        'class' => $class_value,
                        'invites' => $arr['invites'] + 1,
                        'modcomment' => $modcomment,
                    ], $site_config['expires']['user_cache']);
                }
                status_change($arr['id']);
            }

            $count = count($msgs_buffer);
            if ($count > 0) {
                $message_stuffs->insert($msgs_buffer);
                $set = [
                    'invites' => new Envms\FluentPDO\Literal('invites + 1'),
                    'class' => $class_value,
                    'modcomment' => new Envms\FluentPDO\Literal("CONCAT(\"$comment\", modcomment)"),
                ];
                $fluent->update('users')
                    ->set($set)
                    ->where('class = ?', $prev_class)
                    ->where('enabled = "yes"')
                    ->where('added < ?', $maxdt)
                    ->where('uploaded>= ?', $limit)
                    ->where('uploaded / IF(downloaded>0, downloaded, 1)>= ?', $minratio)
                    ->execute();
            }
            $time_end = microtime(true);
            $run_time = $time_end - $time_start;
            $text = " Run time: $run_time seconds";
            echo $text . "\n";
            if ($data['clean_log']) {
                write_log('Cleanup: Promoted ' . $count . ' member(s) from ' . $prev_class_name . ' to ' . $class_name . $text);
            }
        }
    }
}
