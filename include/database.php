<?php

global $site_config;

if (!$site_config['database']['use_socket']) {
    $pdo = new PDO("{$site_config['database']['type']}:host={$site_config['database']['host']};port={$site_config['database']['port']};dbname={$site_config['database']['database']};charset=utf8mb4", "{$site_config['database']['username']}", "{$site_config['database']['password']}");
} else {
    $pdo = new PDO("{$site_config['database']['type']}:unix_socket={$site_config['database']['socket']};dbname={$site_config['database']['database']};charset=utf8mb4", "{$site_config['database']['username']}", "{$site_config['database']['password']}");
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->setAttribute(PDO::ATTR_PERSISTENT, false);

$fluent = new Envms\FluentPDO\Query($pdo);

$ignore = [
    '/announce.php',
    '/scrape.php',
    '/rss.php',
    '/ajaxchat.php',
    INCL_DIR . 'cron_controller.php',
    PUBLIC_DIR . 'ajax/trivia_lookup.php',
    PUBLIC_DIR . 'ajax/trivia_answers.php',
];

if ($site_config['database']['debug'] && !in_array($_SERVER['PHP_SELF'], $ignore)) {
    $fluent->debug = function ($BaseQuery) {
        global $pdo, $query_stat;

        $params = [];
        $query = str_replace(' ?', ' %s', $BaseQuery->getQuery(true));
        $paramaters = $BaseQuery->getParameters();
        $time = $BaseQuery->getExecutionTime();
        if (!empty($paramaters) && count($paramaters) >= 1) {
            foreach ($paramaters as $param) {
                if (is_int($param)) {
                    $params[] = $param;
                } else {
                    $params[] = $pdo->quote($param);
                }
            }
            $query = vsprintf($query, $params) . "\n";
        }
        if (!empty($query)) {
            $query_stat[] = [
                'seconds' => number_format($time, 6),
                'query' => trim($query) . '<br>[color=yellow]FluentPDO: query time [i]may[/i] not be accurate[/color]',
            ];
        }
    };
}
