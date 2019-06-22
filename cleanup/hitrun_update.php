<?php

declare(strict_types = 1);

use Pu239\Snatched;
use Pu239\User;

function hitrun_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    if ($site_config['hnr_config']['hnr_online'] === 1) {
        $user_class = $container->get(User::class);
        $snatched = $container->get(Snatched::class);
        $dt = TIME_NOW;
        $classes = [
            'first',
            'second',
            'third',
        ];
        $i = 1;
        $work = [];
        foreach ($classes as $class) {
            $work[] = [
                'min_class' => $site_config['hnr_config'][$class . 'class'],
                'days_3' => $site_config['hnr_config']['_3day_' . $class] * 3600,
                'days_14' => $site_config['hnr_config']['_14day_' . $class] * 3600,
                'days_over_14' => $site_config['hnr_config']['_14day_over_' . $class] * 3600,
                'age' => $site_config['hnr_config']['torrentage' . $i++],
                'caindays' => $site_config['hnr_config']['caindays'],
                'cainallowed' => $site_config['hnr_config']['cainallowed'],
                'all_torrents' => $site_config['hnr_config']['all_torrents'],
            ];
        }
        $hnrs = [];
        foreach ($work as $hnr) {
            $snatched->set_hnr($hnr);
            $snatched->remove_hnr($hnr);
            $users = $snatched->get_hit_and_runs($hnr);
            $hnrs = array_merge($hnrs, $users);
        }
        $set = [
            'hnr_warn' => 'yes',
        ];
        foreach ($hnrs as $hnr) {
            if (count($hnr) > 3) {
                $user_class->update($set, $hnr['id']);
            }
        }
        $users = $snatched->get_user_to_add_hnr();
        foreach ($users as $user) {
            $subject = 'Download disabled by System';
            $msg = 'Sorry ' . htmlsafechars($user['username']) . ",\n Because you have " . $site_config['hnr_config']['cainallowed'] . " or more torrents that have not been seeded to either a 1:1 ratio, or for the expected seeding time, your downloading rights have been disabled by the Auto system !\nTo get your Downloading rights back is simple,\n just start seeding the torrents in your profile [ click your username, then click your [url=" . $site_config['paths']['baseurl'] . '/userdetails.php?id=' . (int) $user['userid'] . "&completed=1]Completed Torrents[/url] link to see what needs seeding ] and your downloading rights will be turned back on by the Auto system after the next clean-time [ updates 4 times per hour ].\n\nDownloads are disabled after a member has three or more torrents that have not been seeded to either a 1 to 1 ratio, OR for the required seed time [ please see the [url=" . $site_config['paths']['baseurl'] . '/faq.php]FAQ[/url] or [url=' . $site_config['paths']['baseurl'] . "/rules.php]Site Rules[/url] for more info ]\n\nIf this message has been in error, or you feel there is a good reason for it, please feel free to PM a staff member with your concerns.\n\n we will do our best to fix this situation.\n\nBest of luck!\n " . $site_config['site']['name'] . " staff.\n";
            $_pms[] = [
                'receiver' => $user['userid'],
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
            $modcomment = get_date((int) $dt, 'DATE', 1) . " - Download rights removed for H and R - AutoSystem.\n" . $user['modcomment'];
            $_users[] = [
                'id' => $user['userid'],
                'username' => $user['username'],
                'hit_and_run_total' => $user['hit_and_run_total'] + $user['count'],
                'downloadpos' => 0,
                'hnrwarn' => 'yes',
                'modcomment' => $modcomment,
            ];
        }
        $users = $snatched->get_user_to_remove_hnr();
        foreach ($users as $user) {
            $subject = 'Download restored by System';
            $msg = 'Hi ' . htmlsafechars($user['username']) . ",\n Congratulations ! Because you have seeded the torrents that needed seeding, your downloading rights have been restored by the Auto System !\n\nhave fun !\n " . $site_config['site']['name'] . " staff.\n";
            $_pms[] = [
                'receiver' => $user['userid'],
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
            $modcomment = get_date((int) $dt, 'DATE', 1) . " - Download rights restored from H and R - AutoSystem.\n" . $user['modcomment'];
            $_users[] = [
                'id' => $user['userid'],
                'username' => $user['username'],
                'downloadpos' => 1,
                'hnrwarn' => 'no',
                'modcomment' => $modcomment,
            ];
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
