<?php

if (!SOCKET) {
    $pdo = new PDO("{$_ENV['DB_CONNECTION']}:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_DATABASE']};charset={$_ENV['DB_CHARSET']}", "{$_ENV['DB_USERNAME']}", "{$_ENV['DB_PASSWORD']}");
} else {
    $pdo = new PDO("{$_ENV['DB_CONNECTION']}:unix_socket={$_ENV['DB_SOCKET']};dbname={$_ENV['DB_DATABASE']};charset={$_ENV['DB_CHARSET']}", "{$_ENV['DB_USERNAME']}", "{$_ENV['DB_PASSWORD']}");
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

if (SQL_DEBUG && !in_array($_SERVER['PHP_SELF'], $ignore)) {
    file_put_contents('/var/log/nginx/fluent.log', $_SERVER['PHP_SELF'] . PHP_EOL, FILE_APPEND);
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
                'query' => trim($query) . '<br>[color=red]PDO: time may not be accurate[/color]',
            ];
        }
    };
}
