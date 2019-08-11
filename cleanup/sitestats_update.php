<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Roles;

/**
 * @param $data
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function sitestats_update($data)
{
    global $container;

    $time_start = microtime(true);
    $dt = TIME_NOW - 300;
    $fluent = $container->get(Database::class);
    $users = $fluent->from('users')
                    ->select(null)
                    ->select('status')
                    ->select('verified')
                    ->select('perms')
                    ->select('anonymous_until')
                    ->select('donor')
                    ->select('last_access')
                    ->select('gender')
                    ->select('class')
                    ->select('roles_mask');

    $numanonymous = $unverified = $donors = $numactive = $gender_na = $gender_male = $gender_female = $disabled = $powerusers = $superusers = $uploaders = $moderators = $administrators = $sysops = $registered = $vips = 0;
    foreach ($users as $user) {
        $numanonymous += $user['anonymous_until'] > TIME_NOW || $user['perms'] >= 8;
        $unverified += $user['verified'] === 'no' ? 1 : 0;
        $donors += $user['donor'] === 'yes' ? 1 : 0;
        $numactive += $user['last_access'] >= $dt ? 1 : 0;
        $gender_na += $user['gender'] === 'NA' ? 1 : 0;
        $gender_male += $user['gender'] === 'Male' ? 1 : 0;
        $gender_female += $user['gender'] === 'Female' ? 1 : 0;
        $disabled += $user['status'] === 2 ? 1 : 0;
        $powerusers += $user['class'] === UC_POWER_USER ? 1 : 0;
        $superusers += $user['class'] === UC_SUPER_USER ? 1 : 0;
        $uploaders += $user['roles_mask'] & Roles::UPLOADER ? 1 : 0;
        $vips += $user['class'] === UC_VIP ? 1 : 0;
        $moderators += $user['class'] === UC_MODERATOR ? 1 : 0;
        $administrators += $user['class'] === UC_ADMINISTRATOR ? 1 : 0;
        $sysops += $user['class'] === UC_SYSOP ? 1 : 0;
        ++$registered;
    }

    $unconnectables = $seeders = $leechers = $connectable = 0;
    $peers = $fluent->from('peers')
                    ->select(null)
                    ->select('connectable')
                    ->select('seeder');

    foreach ($peers as $peer) {
        $seeders += $peer['seeder'] === 'yes' ? 1 : 0;
        $leechers += $peer['seeder'] === 'no' ? 1 : 0;
        $unconnectables += $peer['connectable'] === 'no' ? 1 : 0;
    }

    $posts_count = $poststoday = $postsmonth = 0;
    $forumposts = $fluent->from('posts')
                         ->select(null)
                         ->select('added');

    foreach ($forumposts as $post) {
        ++$posts_count;
        $poststoday += date('Ymd') == date('Ymd', $post['added']) ? 1 : 0;
        $postsmonth += date('Ym') == date('Ym', $post['added']) ? 1 : 0;
    }

    $topics_count = $topicstoday = $topicsmonth = 0;
    $forumtopics = $fluent->from('topics')
                          ->select(null)
                          ->select('added');

    foreach ($forumtopics as $topic) {
        ++$topics_count;
        $topicstoday += date('Ymd') == date('Ymd', $topic['added']) ? 1 : 0;
        $topicsmonth += date('Ym') == date('Ym', $topic['added']) ? 1 : 0;
    }

    $torrent_count = $torrentstoday = $torrentsmonth = 0;
    $torrents = $fluent->from('torrents')
                       ->select(null)
                       ->select('added');

    foreach ($torrents as $torrent) {
        ++$torrent_count;
        $torrentstoday += date('Ymd') == date('Ymd', $torrent['added']) ? 1 : 0;
        $torrentsmonth += date('Ym') == date('Ym', $torrent['added']) ? 1 : 0;
    }

    $set = [
        'numanonymous' => $numanonymous,
        'regusers' => $registered,
        'unconusers' => $unverified,
        'torrents' => $torrent_count,
        'seeders' => $seeders,
        'leechers' => $leechers,
        'unconnectables' => $unconnectables,
        'torrentstoday' => $torrentstoday,
        'donors' => $donors,
        'forumposts' => $posts_count,
        'poststoday' => $poststoday,
        'postsmonth' => $postsmonth,
        'forumtopics' => $topics_count,
        'topicstoday' => $topicstoday,
        'topicsmonth' => $topicsmonth,
        'numactive' => $numactive,
        'torrentsmonth' => $torrentsmonth,
        'gender_na' => $gender_na,
        'gender_male' => $gender_male,
        'gender_female' => $gender_female,
        'powerusers' => $powerusers,
        'superusers' => $superusers,
        'disabled' => $disabled,
        'uploaders' => $uploaders,
        'vips' => $vips,
        'moderators' => $moderators,
        'administrators' => $administrators,
        'sysops' => $sysops,
        'peers' => $seeders + $leechers,
        'ratio' => $seeders != 0 && $leechers != 0 ? $seeders / $leechers : 0,
        'ratiounconn' => $unconnectables != 0 && $seeders + $leechers != 0 ? $unconnectables / ($seeders + $leechers) : 0,
        'updated' => TIME_NOW,
    ];
    $cache = $container->get(Cache::class);
    $cache->set('site_stats_', $set, 0);
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Stats Cleanup completed' . $text);
    }
}
