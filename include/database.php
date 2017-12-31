<?php
/**
 * Created by PhpStorm.
 * User: jonnyboy
 * Date: 12/16/17
 * Time: 4:00 AM
 */

If (!SOCKET) {
    $pdo = new PDO("{$_ENV['DB_CONNECTION']}:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_DATABASE']};charset={$_ENV['DB_CHARSET']}", "{$_ENV['DB_USERNAME']}", "{$_ENV['DB_PASSWORD']}");
} else {
    $pdo = new PDO("{$_ENV['DB_CONNECTION']}:unix_socket={$_ENV['DB_SOCKET']};dbname={$_ENV['DB_DATABASE']};charset={$_ENV['DB_CHARSET']}", "{$_ENV['DB_USERNAME']}", "{$_ENV['DB_PASSWORD']}");
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

$fluent = new Envms\FluentPDO\Query($pdo);

$page = $_SERVER['PHP_SELF'];
if (SQL_DEBUG && $page != '/announce.php') {
    $fluent->debug = function ($BaseQuery) {
        global $pdo, $query_stat;
        $params = [];
        $query = str_replace('?', '%s', $BaseQuery->getQuery(true));
        $paramaters = $BaseQuery->getParameters();
        if (!empty($paramaters) && count($paramaters) >= 1) {
            foreach ($paramaters as $param) {
                $params[] = $pdo->quote($param);
            }
            $params = implode(', ', $params);
            $query = sprintf($query, $params);
        }
        if (!empty($query)) {
            $query_stat[] = [
                'seconds' => 'PDO',
                'query'   => $query,
            ];
        }
    };
}
