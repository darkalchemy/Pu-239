<?php

/**
 * @param $data
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function hitrun_update($data)
{
    $time_start = microtime(true);
    dbconn();
    global $site_config, $queries, $cache, $message_stuffs, $fluent;

    set_time_limit(1200);
    ignore_user_abort(true);

    if ($site_config['hnr_config']['online'] == 1) {
        $dt = TIME_NOW;
        $secs = $site_config['hnr_config']['caindays'] * 86400;
        $hnr = $dt - $secs;
        $_pms = $_users = [];

        $set = [
            'mark_of_cain' => 'yes',
        ];
        $fluent->update('snatched')
            ->set($set)
            ->where('hit_and_run != 0')
            ->where('hit_and_run < ?', $hnr)
            ->execute();

        $query = $fluent->from('snatched AS s')
            ->select(null)
            ->select('COUNT(*) AS count')
            ->select('s.userid')
            ->select('u.username')
            ->select('u.modcomment')
            ->select('u.hit_and_run_total')
            ->select('u.downloadpos')
            ->innerJoin('users AS u ON s.userid=u.id')
            ->where('s.mark_of_cain = "yes"')
            ->where('u.hnrwarn = "no"')
            ->where('u.immunity = 0')
            ->groupBy('s.userid')
            ->groupBy('u.username')
            ->groupBy('u.modcomment')
            ->groupBy('u.hit_and_run_total')
            ->groupBy('u.downloadpos');

        foreach ($query as $bad_users) {
            if ($bad_users['count'] > $site_config['hnr_config']['cainallowed'] && $bad_users['downloadpos'] == 1) {
                $subject = 'Download disabled by System';
                $msg = 'Sorry ' . htmlsafechars($bad_users['username']) . ",\n Because you have " . $site_config['hnr_config']['cainallowed'] . " or more torrents that have not been seeded to either a 1:1 ratio, or for the expected seeding time, your downloading rights have been disabled by the Auto system !\nTo get your Downloading rights back is simple,\n just start seeding the torrents in your profile [ click your username, then click your [url=" . $site_config['paths']['baseurl'] . '/userdetails.php?id=' . (int) $bad_users['userid'] . "&completed=1]Completed Torrents[/url] link to see what needs seeding ] and your downloading rights will be turned back on by the Auto system after the next clean-time [ updates 4 times per hour ].\n\nDownloads are disabled after a member has three or more torrents that have not been seeded to either a 1 to 1 ratio, OR for the required seed time [ please see the [url=" . $site_config['paths']['baseurl'] . '/faq.php]FAQ[/url] or [url=' . $site_config['paths']['baseurl'] . "/rules.php]Site Rules[/url] for more info ]\n\nIf this message has been in error, or you feel there is a good reason for it, please feel free to PM a staff member with your concerns.\n\n we will do our best to fix this situation.\n\nBest of luck!\n " . $site_config['site']['name'] . " staff.\n";
                $modcomment = $bad_users['modcomment'];
                $modcomment = get_date($dt, 'DATE', 1) . " - Download rights removed for H and R - AutoSystem.\n" . $modcomment;
                $modcom = sqlesc($modcomment);
                $_pms[] = [
                    'sender' => 0,
                    'receiver' => $bad_users['userid'],
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => $subject,
                ];
                $_users[] = '(' . sqlesc($bad_users['userid']) . ',' . sqlesc($bad_users['username']) . ',' . sqlesc($bad_users['count']) . ',0, \'yes\',' . $modcom . ')';

                $update['hit_and_run_total'] = ($bad_users['hit_and_run_total'] + $bad_users['count']);
                $cache->update_row('user_' . $bad_users['userid'], [
                    'hit_and_run_total' => $update['hit_and_run_total'],
                    'downloadpos' => 0,
                    'hnrwarn' => 'yes',
                    'modcomment' => $modcomment,
                ], $site_config['expires']['user_cache']);
            }
        }

        if (count($_pms) > 0) {
            $message_stuffs->insert($_pms);
        }
        if (count($_users) > 0) {
            sql_query('INSERT INTO users(id, username, hit_and_run_total, downloadpos, hnrwarn, modcomment) VALUES ' . implode(',', $_users) . ' ON DUPLICATE KEY UPDATE hit_and_run_total=hit_and_run_total + VALUES(hit_and_run_total),downloadpos = VALUES(downloadpos),hnrwarn = VALUES(hnrwarn),modcomment = VALUES(modcomment)') or sqlerr(__FILE__, __LINE__);
        }

        $_pms = $_users = [];
        $res_good_boy = sql_query('SELECT id, username, modcomment FROM users WHERE hnrwarn = \'yes\' AND downloadpos = \'0\'') or sqlerr(__FILE__, __LINE__);
        while ($arr_good_boy = mysqli_fetch_assoc($res_good_boy)) {
            $res_count = sql_query('SELECT COUNT(*) FROM snatched WHERE userid=' . sqlesc($arr_good_boy['id']) . ' AND mark_of_cain = \'yes\'') or sqlerr(__FILE__, __LINE__);
            $arr_count = mysqli_fetch_row($res_count);
            if ($arr_count[0] < $site_config['hnr_config']['cainallowed']) {
                $subject = 'Download restored by System';
                $msg = 'Hi ' . htmlsafechars($arr_good_boy['username']) . ",\n Congratulations ! Because you have seeded the torrents that needed seeding, your downloading rights have been restored by the Auto System !\n\nhave fun !\n " . $site_config['site']['name'] . " staff.\n";
                $modcomment = $arr_good_boy['modcomment'];
                $modcomment = get_date($dt, 'DATE', 1) . " - Download rights restored from H and R - AutoSystem.\n" . $modcomment;
                $modcom = sqlesc($modcomment);
                $_pms[] = [
                    'sender' => 0,
                    'receiver' => $arr_good_boy['userid'],
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => $subject,
                ];
                $_users[] = '(' . sqlesc($arr_good_boy['id']) . ',1,\'no\',' . $modcom . ')';
                $cache->update_row('user_' . $arr_good_boy['id'], [
                    'downloadpos' => 1,
                    'hnrwarn' => 'no',
                    'modcomment' => $modcomment,
                ], $site_config['expires']['user_cache']);
            }
        }

        if (count($_pms) > 0) {
            $message_stuffs->insert($_pms);
        }

        if (count($_users) > 0) {
            sql_query('INSERT INTO users(id,downloadpos,hnrwarn,modcomment) VALUES ' . implode(',', $_users) . ' ON DUPLICATE KEY UPDATE downloadpos = VALUES(downloadpos),hnrwarn = VALUES(hnrwarn),modcomment = VALUES(modcomment)') or sqlerr(__FILE__, __LINE__);
        }
        unset($_pms, $_users);

        $time_end = microtime(true);
        $run_time = $time_end - $time_start;
        $text = " Run time: $run_time seconds";
        echo $text . "\n";
        if ($data['clean_log'] && $queries > 0) {
            write_log("HnR Cleanup: Completed using $queries queries" . $text);
        }
    }
}
