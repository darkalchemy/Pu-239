<?php

/**
 * @param $data
 *
 * @throws Exception
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function birthday_update($data)
{
    require_once INCL_DIR . 'user_functions.php';
    global $site_config, $cache, $message_stuffs, $user_stuffs;

    set_time_limit(1200);
    ignore_user_abort(true);
    $dt = TIME_NOW;
    $users = $user_stuffs->get_birthday_users(getdate());
    $count = 0;
    if (!empty($users)) {
        $msgs_buffer = $users_buffer = $insert = $values = [];
        foreach ($users as $arr) {
            $msg = 'Hey there <span class="' . get_user_class_name($arr['class'], true) . '">' . htmlsafechars($arr['username']) . "</span> happy birthday, hope you have a good day. We awarded you 10 gig...Njoi.\n";
            $subject = 'Its your birthday!!';
            $update['uploaded'] = ($arr['uploaded'] + 10737418240);
            $cache->update_row('user' . $arr['id'], [
                'uploaded' => $update['uploaded'],
            ], $site_config['expires']['user_cache']);

            $insert[] = [
                'id' => $arr['id'],
                'uploaded' => $update['uploaded'],
                'username' => $arr['username'],
                'email' => $arr['email'],
                'ip' => inet_pton($arr['ip']),
            ];

            $values[] = [
                'sender' => 0,
                'receiver' => $arr['id'],
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
        }
        $count = count($values);
        if ($data['clean_log'] && $count > 0) {
            if ($count > 0) {
                $message_stuffs->insert($values);
                $update = [
                    'uploaded' => new Envms\FluentPDO\Literal('VALUES(uploaded)'),
                ];

                $user_stuffs->insert($insert, $update);
            }
        }
    }
    if ($data['clean_log']) {
        write_log("Birthday Cleanup: Pm'd' " . $count . ' member(s) and awarded a birthday prize');
    }
    unset($users_buffer, $insert, $values, $count, $update, $users, $msgs_buffer);
}
