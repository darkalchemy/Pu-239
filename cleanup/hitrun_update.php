<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Database;
use Pu239\Message;
use Pu239\User;

/**
 * @param $data
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function hitrun_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    if ($site_config['hnr_config']['hnr_online'] === 1) {
        $dt = TIME_NOW;
        $secs = $site_config['hnr_config']['caindays'] * 86400;
        $hnr = $dt - $secs;
        $_pms = $_users = [];

        $set = [
            'mark_of_cain' => 'yes',
        ];
        $fluent = $container->get(Database::class);
        $fluent->update('snatched AS s')
               ->set($set)
               ->where('s.hit_and_run != 0')
               ->where('s.hit_and_run < ?', $hnr)
               ->where('s.userid != t.owner')
               ->innerJoin('torrents  AS t ON s.torrentid = t.id')
               ->execute();

        $query = $fluent->from('snatched AS s')
                        ->select(null)
                        ->select('COUNT(s.id) AS count')
                        ->select('s.userid')
                        ->select('u.username')
                        ->select('u.modcomment')
                        ->select('u.hit_and_run_total')
                        ->select('u.downloadpos')
                        ->select('s.mark_of_cain')
                        ->innerJoin('users AS u ON s.userid = u.id')
                        ->innerJoin('torrents  AS t ON s.torrentid = t.id')
                        ->where('s.mark_of_cain = "yes"')
                        ->where('u.hnrwarn = "no"')
                        ->where('u.immunity = 0')
                        ->where('s.userid != t.owner')
                        ->groupBy('s.userid')
                        ->groupBy('u.username')
                        ->groupBy('u.modcomment')
                        ->groupBy('u.hit_and_run_total')
                        ->groupBy('u.downloadpos');

        foreach ($query as $bad_users) {
            if ($bad_users['count'] > $site_config['hnr_config']['cainallowed'] && $bad_users['downloadpos'] === 1) {
                $subject = 'Download disabled by System';
                $msg = 'Sorry ' . htmlsafechars($bad_users['username']) . ",\n Because you have " . $site_config['hnr_config']['cainallowed'] . " or more torrents that have not been seeded to either a 1:1 ratio, or for the expected seeding time, your downloading rights have been disabled by the Auto system !\nTo get your Downloading rights back is simple,\n just start seeding the torrents in your profile [ click your username, then click your [url=" . $site_config['paths']['baseurl'] . '/userdetails.php?id=' . (int) $bad_users['userid'] . "&completed=1]Completed Torrents[/url] link to see what needs seeding ] and your downloading rights will be turned back on by the Auto system after the next clean-time [ updates 4 times per hour ].\n\nDownloads are disabled after a member has three or more torrents that have not been seeded to either a 1 to 1 ratio, OR for the required seed time [ please see the [url=" . $site_config['paths']['baseurl'] . '/faq.php]FAQ[/url] or [url=' . $site_config['paths']['baseurl'] . "/rules.php]Site Rules[/url] for more info ]\n\nIf this message has been in error, or you feel there is a good reason for it, please feel free to PM a staff member with your concerns.\n\n we will do our best to fix this situation.\n\nBest of luck!\n " . $site_config['site']['name'] . " staff.\n";
                $_pms[] = [
                    'receiver' => $bad_users['userid'],
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => $subject,
                ];
                $modcomment = get_date((int) $dt, 'DATE', 1) . " - Download rights removed for H and R - AutoSystem.\n" . $bad_users['modcomment'];
                $_users[] = [
                    'id' => $bad_users['userid'],
                    'username' => $bad_users['username'],
                    'hit_and_run_total' => $bad_users['hit_and_run_total'] + $bad_users['count'],
                    'downloadpos' => 0,
                    'hnrwarn' => 'yes',
                    'modcomment' => $modcomment,
                ];
            }
        }
        $query = $fluent->from('users')
                        ->select(null)
                        ->select('id')
                        ->select('username')
                        ->select('modcomment')
                        ->where('hnrwarn = "yes"')
                        ->where('downloadpos = 0');

        foreach ($query as $arr_good_boy) {
            $count = $fluent->from('snatched')
                            ->select(null)
                            ->select('COUNT(id) AS count')
                            ->where('userid = ?', $arr_good_boy['id'])
                            ->where('mark_of_cain = "yes"')
                            ->fetch('count');
            if ($count < $site_config['hnr_config']['cainallowed']) {
                $subject = 'Download restored by System';
                $msg = 'Hi ' . htmlsafechars($arr_good_boy['username']) . ",\n Congratulations ! Because you have seeded the torrents that needed seeding, your downloading rights have been restored by the Auto System !\n\nhave fun !\n " . $site_config['site']['name'] . " staff.\n";
                $_pms[] = [
                    'sender' => 0,
                    'receiver' => $arr_good_boy['userid'],
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => $subject,
                ];
                $modcomment = get_date((int) $dt, 'DATE', 1) . " - Download rights restored from H and R - AutoSystem.\n" . $arr_good_boy['modcomment'];
                $_users[] = [
                    'id' => $arr_good_boy['userid'],
                    'username' => $arr_good_boy['username'],
                    'downloadpos' => 1,
                    'hnrwarn' => 'no',
                    'modcomment' => $modcomment,
                ];
            }
        }

        if (count($_pms) > 0) {
            $messages_class = $container->get(Message::class);
            $messages_class->insert($_pms);
        }
        if (count($_users) > 0) {
            $users_class = $container->get(User::class);
            $update = $_users;
            unset($update['id'], $update['username']);
            $users_class->insert($_users, $update);
        }
        $users = $fluent->from('snatched AS s')
                        ->select(null)
                        ->select('s.id')
                        ->select('s.torrentid')
                        ->select('s.userid')
                        ->select('s.seedtime')
                        ->select('s.uploaded')
                        ->select('s.downloaded')
                        ->select('s.start_date')
                        ->select('u .class')
                        ->select('t.added')
                        ->innerJoin('users AS u ON s.userid = u.id')
                        ->innerJoin('torrents AS t ON s.torrentid = t.id')
                        ->where('s.start_date > 0')
                        ->where('s.mark_of_cain = "no"')
                        ->where('s.userid != t.owner');

        foreach ($users as $user) {
            switch (true) {
                case $user['class'] <= $site_config['hnr_config']['firstclass']:
                    $days_3 = $site_config['hnr_config']['_3day_first'] * 3600;
                    $days_14 = $site_config['hnr_config']['_14day_first'] * 3600;
                    $days_over_14 = $site_config['hnr_config']['_14day_over_first'] * 3600;
                    break;

                case $user['class'] < $site_config['hnr_config']['secondclass']:
                    $days_3 = $site_config['hnr_config']['_3day_second'] * 3600;
                    $days_14 = $site_config['hnr_config']['_14day_second'] * 3600;
                    $days_over_14 = $site_config['hnr_config']['_14day_over_second'] * 3600;
                    break;

                case $user['class'] >= $site_config['hnr_config']['thirdclass']:
                    $days_3 = $site_config['hnr_config']['_3day_third'] * 3600;
                    $days_14 = $site_config['hnr_config']['_14day_third'] * 3600;
                    $days_over_14 = $site_config['hnr_config']['_14day_over_third'] * 3600;
                    break;

                default:
                    $days_3 = 0;
                    $days_14 = 0;
                    $days_over_14 = 0;
            }
            switch (true) {
                case $site_config['hnr_config']['torrentage1'] * 86400 > ($user['start_date'] - $user['added']):
                    $minus_ratio = $days_3 - $user['seedtime'];
                    break;

                case $site_config['hnr_config']['torrentage2'] * 86400 > ($user['start_date'] - $user['added']):
                    $minus_ratio = $days_14 - $user['seedtime'];
                    break;

                case $site_config['hnr_config']['torrentage3'] * 86400 <= ($user['start_date'] - $user['added']):
                    $minus_ratio = $days_over_14 - $user['seedtime'];
                    break;

                default:
                    $minus_ratio = 0;
            }
            if ($minus_ratio <= 0 || $user['uploaded'] >= $user['downloaded']) {
                $set = [
                    'hit_and_run' => 0,
                ];
                $fluent->update('snatched')
                       ->set($set)
                       ->where('id = ?', $user['id'])
                       ->execute();
            }
        }

        $time_end = microtime(true);
        $run_time = $time_end - $time_start;
        $text = " Run time: $run_time seconds";
        echo $text . "\n";
        if ($data['clean_log']) {
            write_log('HnR Cleanup: Completed' . $text);
        }
    }
}
