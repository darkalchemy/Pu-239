<?php

/**
 * @param $data
 *
 * @throws Exception
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function freeuser_update($data)
{
    dbconn();
    global $site_config, $queries, $fluent, $cache, $message_stuffs, $user_stuffs;

    set_time_limit(1200);
    ignore_user_abort(true);

    $query = $fluent->from('users')
        ->select(null)
        ->select('id')
        ->select('modcomment')
        ->where('free_switch > 1')
        ->where('free_switch < ?', TIME_NOW);

    $dt = TIME_NOW;
    $subject = 'Freeleech expired.';
    $msg = "Your freeleech has expired and has been auto-removed by the system.\n";
    $values = $set = $update = [];
    foreach ($query as $arr) {
        $modcomment = $arr['modcomment'];
        $modcomment = get_date($dt, 'DATE', 1) . " - Freeleech Removed By System.\n" . $modcomment;
        $modcom = sqlesc($modcomment);
        $values[] = [
            'sender' => 0,
            'receiver' => $arr['id'],
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
        /*
                $set[] = [
                    'free_switch' => 0,
                    'modcomment' => $modcom,
                    'id' => $arr['id'],
                ];

                $update[] = [
                    'free_switch' => 0,
                    'modcomment' => new Envms\FluentPDO\Literal('VALUES(modcomment)'),
                ];
        */
        $users_buffer[] = "({$arr['id']}, 0, {$modcom})";
        $cache->update_row('user' . $arr['id'], [
            'free_switch' => 0,
            'modcomment' => $modcomment,
        ], $site_config['expires']['user_cache']);
    }

    $count = count($values);
    if ($count) {
        $message_stuffs->insert($values);
        sql_query('INSERT INTO users (id, free_switch, modcomment) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE key UPDATE free_switch=values(free_switch), modcomment=values(modcomment)') or sqlerr(__FILE__, __LINE__);
        //$user_stuffs->insert($set, $update);
    }

    if ($data['clean_log']) {
        write_log('Cleanup - Removed Freeleech from ' . $count . ' members');
    }

    if ($data['clean_log'] && $queries > 0) {
        write_log("Freeleech Cleanup: Completed using $queries queries");
    }
}
