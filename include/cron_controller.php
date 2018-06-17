<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'bittorrent.php';
global $cache;

$cleanup_check = $cache->get('cleanup_check_');
if (user_exists($site_config['chatBotID']) && $cleanup_check === false || is_null($cleanup_check)) {
    autoclean();
}

function autoclean()
{
    global $site_config, $cache, $fluent;

    $cache->set('cleanup_check_', 'running', 90);
    $now   = TIME_NOW;
    $query = $fluent->from('cleanup')
        ->where('clean_on = 1')
        ->where('clean_time < ?', $now)
        ->orderBy('clean_time ASC')
        ->orderBy('clean_increment DESC');

    foreach ($query as $row) {
        if ($row['clean_id']) {
            $next_clean = ceil(TIME_NOW / $row['clean_increment']) * $row['clean_increment'];
            $set        = [
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

    if ($site_config['newsrss_on']) {
        $tfreak_cron = $cache->get('tfreak_cron_');
        if ($tfreak_cron === false || is_null($tfreak_cron)) {
            $tfreak_news = $cache->get('tfreak_news_links_');
            if ($tfreak_news === false || is_null($tfreak_news)) {
                $query = $fluent->from('newsrss')
                    ->select(null)
                    ->select(link);

                foreach ($query as $tfreak_new) {
                    $tfreak_news[] = $tfreak_new['link'];
                }
                $cache->set('tfreak_news_links_', $tfreak_news, 86400);
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
    }
    $cache->delete('cleanup_check_');
}
