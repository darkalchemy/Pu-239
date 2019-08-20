<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Database;
use Pu239\User;

/**
 * @param $data
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 */
function karma_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    $fluent = $container->get(Database::class);
    if ($site_config['bonus']['on']) {
        $bmt = $site_config['bonus']['max_torrents'];
        $sql = $fluent->from('peers AS p')
                      ->select(null)
                      ->select('p.userid')
                      ->select('COUNT(p.torrent) AS tcount')
                      ->select('u.seedbonus')
                      ->innerJoin('users AS u ON p.userid = u.id')
                      ->where('p.seeder = "yes"');
        if ($site_config['tracker']['connectable_check']) {
            $sql = $sql->where('connectable = "yes"');
        }
        $sql = $sql->groupBy('userid')
                   ->groupBy('seedbonus')
                   ->fetchAll();
        if (!empty($sql)) {
            $total = 0;
            $count = count($sql);
            $user_class = $container->get(User::class);
            foreach ($sql as $arr) {
                if (!empty($arr['userid']) && is_valid_id($arr['userid'])) {
                    $bonus = $site_config['bonus']['per_duration'] * ($arr['tcount'] > $bmt ? $bmt : $arr['tcount']);
                    $total += $bonus;
                    $update['seedbonus'] = $arr['seedbonus'] + $bonus;
                    $user_class->update($update, $arr['userid']);
                }
            }
            if ($data['clean_log']) {
                write_log('Seedbonus Cleanup - ' . $count . ' user' . plural($count) . ' received seedbonus totaling ' . $total . ' karma');
            }
        }
    }
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Karma Cleanup: Completed' . $text);
    }
}
