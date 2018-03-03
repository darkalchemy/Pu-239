<?php

if (!SOCKET) {
    $pdo = new PDO("{$_ENV['DB_CONNECTION']}:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_DATABASE']};charset={$_ENV['DB_CHARSET']}", "{$_ENV['DB_USERNAME']}", "{$_ENV['DB_PASSWORD']}");
} else {
    $pdo = new PDO("{$_ENV['DB_CONNECTION']}:unix_socket={$_ENV['DB_SOCKET']};dbname={$_ENV['DB_DATABASE']};charset={$_ENV['DB_CHARSET']}", "{$_ENV['DB_USERNAME']}", "{$_ENV['DB_PASSWORD']}");
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

$fluent = new Envms\FluentPDO\Query($pdo);

if (SQL_DEBUG && $_SERVER['PHP_SELF'] != '/announce.php') {
    $fluent->debug = function ($BaseQuery) {
        global $pdo, $query_stat;
        $params     = [];
        $query      = str_replace(' ?', ' %s', $BaseQuery->getQuery(true));
        $paramaters = $BaseQuery->getParameters();
        $time       = $BaseQuery->getTime();
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
                'query'   => trim($query) . '<br>[color=red]PDO: time may not be accurate[/color]',
            ];
        }
    };
}
