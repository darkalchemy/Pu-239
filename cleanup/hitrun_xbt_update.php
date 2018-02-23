<?php
/**
 * @param $data
 *
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function hitrun_xbt_update($data)
{
    global $site_config, $queries, $cache;

    set_time_limit(1200);
    ignore_user_abort(true);

    include CACHE_DIR.'hit_and_run_settings.php';
    if ($site_config['hnr_config']['hnr_online'] == 1) {
        $dt = TIME_NOW;
        $secs = $site_config['hnr_config']['caindays'] * 86400;
        $hnr = $dt - $secs;
        $res = sql_query('SELECT fid, uid FROM xbt_files_users WHERE seedtime = \'0\' OR seedtime < '.sqlesc($hnr).' AND completed>=\'1\' AND downloaded >\'0\' AND active=\'0\'') or sqlerr(__FILE__, __LINE__);
        while ($arr = mysqli_fetch_assoc($res)) {
            sql_query('UPDATE xbt_files_users SET mark_of_cain = \'yes\', hit_and_run = '.$dt.' WHERE fid='.sqlesc($arr['fid']).' AND uid='.sqlesc($arr['uid'])) or sqlerr(__FILE__, __LINE__);
        }

        $res_fuckers = sql_query('SELECT COUNT(*) AS poop, xbt_files_users.uid, users.username, users.modcomment, users.hit_and_run_total, users.downloadpos, users.can_leech FROM xbt_files_users LEFT JOIN users ON xbt_files_users.uid = users.id WHERE xbt_files_users.mark_of_cain = \'yes\' AND users.hnrwarn = \'no\' AND users.immunity = \'0\' GROUP BY xbt_files_users.uid') or sqlerr(__FILE__, __LINE__);
        while ($arr_fuckers = mysqli_fetch_assoc($res_fuckers)) {
            if ($arr_fuckers['poop'] > $site_config['hnr_config']['cainallowed'] && 1 == $arr_fuckers['downloadpos']) {
                $subject = sqlesc('Download disabled by System');
                $msg = sqlesc('Sorry '.htmlsafechars($arr_fuckers['username']).",\n Because you have ".$site_config['hnr_config']['cainallowed']." or more torrents that have not been seeded to either a 1:1 ratio, or for the expected seeding time, your downloading rights have been disabled by the Auto system !\nTo get your Downloading rights back is simple,\n just start seeding the torrents in your profile [ click your username, then click your [url=".$site_config['baseurl'].'/userdetails.php?id='.(int) $arr_fuckers['uid']."&completed=1]Completed Torrents[/url] link to see what needs seeding ] and your downloading rights will be turned back on by the Auto system after the next clean-time [ updates 4 times per hour ].\n\nDownloads are disabled after a member has three or more torrents that have not been seeded to either a 1 to 1 ratio, OR for the required seed time [ please see the [url=".$site_config['baseurl'].'/faq.php]FAQ[/url] or [url='.$site_config['baseurl']."/rules.php]Site Rules[/url] for more info ]\n\nIf this message has been in error, or you feel there is a good reason for it, please feel free to PM a staff member with your concerns.\n\n we will do our best to fix this situation.\n\nBest of luck!\n ".$site_config['site_name']." staff.\n");
                $modcomment = $arr_fuckers['modcomment'];
                $modcomment = get_date($dt, 'DATE', 1)." - Download rights removed for H and R - AutoSystem.\n".$modcomment;
                $modcom = sqlesc($modcomment);
                $_pms[] = '(0,'.sqlesc($arr_fuckers['uid']).','.sqlesc($dt).','.$msg.','.$subject.',0)';
                $_users[] = '('.sqlesc($arr_fuckers['uid']).','.sqlesc($arr_fuckers['poop']).',0, \'yes\',0,'.$modcom.')';
                if (count($_pms) > 0) {
                    sql_query('INSERT INTO messages (sender, receiver, added, msg, subject, poster) VALUES '.implode(',', $_pms)) or sqlerr(__FILE__, __LINE__);
                }
                if (count($_users) > 0) {
                    sql_query('INSERT INTO users(id,hit_and_run_total,downloadpos,hnrwarn,can_leech,modcomment) VALUES '.implode(',', $_users).' ON DUPLICATE KEY UPDATE hit_and_run_total=hit_and_run_total + VALUES(hit_and_run_total),downloadpos = VALUES(downloadpos),hnrwarn = VALUES(hnrwarn),can_leech = VALUES(can_leech),modcomment = VALUES(modcomment)') or sqlerr(__FILE__, __LINE__);
                }
                unset($_pms, $_users);
                $update['hit_and_run_total'] = ($arr_fuckers['hit_and_run_total'] + $arr_fuckers['poop']);
                $cache->update_row('user'.$arr_fuckers['uid'], [
                    'hit_and_run_total' => $update['hit_and_run_total'],
                    'downloadpos' => 0,
                    'can_leech' => 0,
                    'hnrwarn' => 'yes',
                    'modcomment' => $modcomment,
                ], $site_config['expires']['user_cache']);
                $cache->increment('inbox_'.$arr_fuckers['uid']);
            }
        }

        $res_good_boy = sql_query('SELECT id, username, modcomment FROM users WHERE hnrwarn = \'yes\' AND downloadpos = \'0\' AND can_leech=\'0\'') or sqlerr(__FILE__, __LINE__);
        while ($arr_good_boy = mysqli_fetch_assoc($res_good_boy)) {
            $res_count = sql_query('SELECT COUNT(*) FROM xbt_files_users WHERE uid = '.sqlesc($arr_good_boy['id']).' AND mark_of_cain = \'yes\' AND active=\'1\'') or sqlerr(__FILE__, __LINE__);
            $arr_count = mysqli_fetch_row($res_count);
            if ($arr_count[0] < $site_config['hnr_config']['cainallowed']) {
                $subject = sqlesc('Download restored by System');
                $msg = sqlesc('Hi '.htmlsafechars($arr_good_boy['username']).",\n Congratulations ! Because you have seeded the torrents that needed seeding, your downloading rights have been restored by the Auto System !\n\nhave fun !\n ".$site_config['site_name']." staff.\n");
                $modcomment = $arr_good_boy['modcomment'];
                $modcomment = get_date($dt, 'DATE', 1)." - Download rights restored from H and R - AutoSystem.\n".$modcomment;
                $modcom = sqlesc($modcomment);
                $_pms[] = '(0,'.sqlesc($arr_good_boy['id']).','.sqlesc($dt).','.$msg.','.$subject.',0)';
                $_users[] = '('.sqlesc($arr_good_boy['id']).',1,\'no\',1,'.$modcom.')';
                if (count($_pms) > 0) {
                    sql_query('INSERT INTO messages (sender, receiver, added, msg, subject, poster) VALUES '.implode(',', $_pms)) or sqlerr(__FILE__, __LINE__);
                }
                if (count($_users) > 0) {
                    sql_query('INSERT INTO users(id,downloadpos,hnrwarn,can_leech,modcomment) VALUES '.implode(',', $_users).' ON DUPLICATE KEY UPDATE downloadpos = VALUES(downloadpos),hnrwarn = VALUES(hnrwarn),can_leech = VALUES(can_leech),modcomment = VALUES(modcomment)') or sqlerr(__FILE__, __LINE__);
                }
                sql_query('UPDATE xbt_files_users SET mark_of_cain = \'no\', hit_and_run = \'0\' WHERE uid='.sqlesc($arr_good_boy['id'])) or sqlerr(__FILE__, __LINE__);
                unset($_pms, $_users);
                $cache->update_row('user'.$arr_good_boy['id'], [
                    'downloadpos' => 1,
                    'can_leech' => 1,
                    'hnrwarn' => 'no',
                    'modcomment' => $modcomment,
                ], $site_config['expires']['user_cache']);
                $cache->increment('inbox_'.$arr_good_boy['id']);
            }
        }

        if ($data['clean_log'] && $queries > 0) {
            write_log("XBT HnR Cleanup: Completed using $queries queries");
        }
    }
}
