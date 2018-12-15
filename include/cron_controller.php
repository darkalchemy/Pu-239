<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
dbconn();
global $cache;

if (!empty($argv[1]) && $argv[1] === 'force') {
    $cache->delete('cleanup_check_');
    $cache->delete('tfreak_cron_');
    $cache->delete('tfreak_news_links_');
}

$cleanup_check = $cache->get('cleanup_check_');
if (user_exists($site_config['chatBotID']) && ($cleanup_check === false || is_null($cleanup_check))) {
    autoclean();
} else {
    echo "Already running.\n";
}

function autoclean()
{
    global $site_config, $cache, $fluent;

    $cache->set('cleanup_check_', 'running', 600);
    $now = TIME_NOW;
    $query = $fluent->from('cleanup')
        ->where('clean_on = 1')
        ->where('clean_time < ?', $now)
        ->orderBy('clean_time ASC')
        ->orderBy('clean_increment ASC')
        ->fetchAll();

    echo "===================================================\n";
    echo get_date(TIME_NOW, 1, 0) . "\n";
    if (!$query) {
        echo "Nothing to process, all caught up.\n";
    } else {
        foreach ($query as $row) {
            if ($row['clean_id']) {
                $next_clean = ceil(TIME_NOW / $row['clean_increment']) * $row['clean_increment'];
                $set = [
                    'clean_time' => $next_clean,
                ];
                $fluent->update('cleanup')
                    ->set($set)
                    ->where('clean_id = ?', $row['clean_id'])
                    ->execute();

                if (file_exists(CLEAN_DIR . $row['clean_file'])) {
                    require_once CLEAN_DIR . $row['clean_file'];
                    if (function_exists($row['function_name'])) {
                        echo "Processing {$row['function_name']}\n";
                        $row['function_name']($row);
                    }
                }
            }
        }
    }
    $cache->delete('cleanup_check_');

    if ($site_config['newsrss_on']) {
        echo "Newsrss Starting\n";
        $tfreak_cron = $cache->get('tfreak_cron_');
        if ($tfreak_cron === false || is_null($tfreak_cron)) {
            $tfreak_news = $cache->get('tfreak_news_links_');
            if ($tfreak_news === false || is_null($tfreak_news)) {
                $query = $fluent->from('newsrss')
                    ->select(null)
                    ->select('link');

                foreach ($query as $tfreak_new) {
                    $tfreak_news[] = $tfreak_new['link'];
                }
                $cache->set('tfreak_news_links_', $tfreak_news, 3600);
            }

            $cache->set('tfreak_cron_', TIME_NOW, 30);
            require_once INCL_DIR . 'newsrss.php';
            if (empty($tfreak_news)) {
                github_shout();
                foxnews_shout();
                tfreak_shout();
            } else {
                github_shout($tfreak_news);
                foxnews_shout($tfreak_news);
                tfreak_shout($tfreak_news);
            }
        }
        echo "Newsrss Finished\n";
    } else {
        echo "Newsrss disabled\n";
    }
}
