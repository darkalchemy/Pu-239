<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;

$ignore = [
    '/announce.php',
    '/scrape.php',
    '/rss.php',
    '/ajaxchat.php',
    INCL_DIR . 'cron_controller.php',
    INCL_DIR . 'images_update.php',
    PUBLIC_DIR . 'ajax/trivia_lookup.php',
    PUBLIC_DIR . 'ajax/trivia_answers.php',
];

global $site_config;

if ($site_config['db']['debug'] && !in_array($_SERVER['PHP_SELF'], $ignore)) {
    debug_pdo();
}

/**
 * @throws DependencyException
 * @throws NotFoundException
 */
function debug_pdo()
{
    global $container;

    $fluent = $container->get(Database::class);
    $fluent->debug = function ($BaseQuery) {
        global $container;

        $params = [];
        $query = str_replace([
            ' ?',
            '(?',
        ], [
            ' %s',
            '(%s',
        ], $BaseQuery->getQuery(true));
        $paramaters = $BaseQuery->getParameters();
        $time = $BaseQuery->getExecutionTime();
        if (!empty($paramaters) && count($paramaters) >= 1) {
            foreach ($paramaters as $param) {
                if (is_int($param)) {
                    $params[] = $param;
                } else {
                    $pdo = $container->get(PDO::class);
                    $params[] = $pdo->quote($param);
                }
            }
            $query = vsprintf($query, $params) . "\n";
        }

        if (!empty($query)) {
            store_query(trim($query), $time, $params);
        }
    };
}

/**
 * @param $query
 *
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return bool|mysqli_result
 */
function sql_query($query)
{
    global $container, $site_config, $ignore;

    $mysqli = $container->get(mysqli::class);
    if ($site_config['db']['debug'] && !in_array($_SERVER['PHP_SELF'], $ignore)) {
        $query_start_time = microtime(true);
        mysqli_set_charset($mysqli, 'utf8mb4');
        $result = mysqli_query($mysqli, $query);
        $query_end_time = microtime(true);
        store_query(formatQuery($query), $query_end_time - $query_start_time, []);
    } else {
        mysqli_set_charset($mysqli, 'utf8mb4');
        $result = mysqli_query($mysqli, $query);
    }

    return $result;
}

/**
 * @param $x
 *
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return int|string
 */
function sqlesc($x)
{
    if (is_integer($x)) {
        return (int) $x;
    } elseif (is_float($x)) {
        return (float) $x;
    }
    global $container;

    $mysqli = $container->get(mysqli::class);

    return sprintf('\'%s\'', mysqli_real_escape_string($mysqli, $x));
}

/**
 * @param $x
 *
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return int|string
 */
function sqlesc_noquote($x)
{
    if (is_integer($x)) {
        return (int) $x;
    }

    global $container;

    $mysqli = $container->get(mysqli::class);

    return mysqli_real_escape_string($mysqli, $x);
}

/**
 * @param string $file
 * @param string $line
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function sqlerr($file = '', $line = '')
{
    global $container, $site_config, $CURUSER;

    $mysqli = $container->get(mysqli::class);
    $the_error = ((is_object($mysqli)) ? mysqli_error($mysqli) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false));
    $the_error_no = ((is_object($mysqli)) ? mysqli_errno($mysqli) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false));
    if (!$site_config['db']['debug']) {
        die();
    } elseif ($site_config['paths']['sql_error_log'] && $site_config['db']['debug']) {
        $_error_string = "\n===================================================";
        $_error_string .= "\n Date: " . date('r');
        $_error_string .= "\n Error Number: " . $the_error_no;
        $_error_string .= "\n Error: " . $the_error;
        $_error_string .= "\n in file " . $file . ' on line ' . $line;
        $_error_string = !empty($_SERVER['REQUEST_URI']) ? $_error_string . "\n URL: {$_SERVER['REQUEST_URI']}" : '';
        $_error_string .= "\n Username: {$CURUSER['username']}[{$CURUSER['id']}]";
        if ($FH = @fopen($site_config['paths']['sql_error_log'], 'a')) {
            @fwrite($FH, $_error_string);
            @fclose($FH);
        }
        echo '<html><head><title>MySQLI Error</title>
                    <style>P,BODY{ font-family:arial,sans-serif; font-size:11px; }</style></head><body>
                       <blockquote><h1>MySQLI Error</h1><b>There appears to be an error with the database.</b><br>
                       You can try to refresh the page by clicking <a href="javascript:window.location=window.location;">here</a>
                  </body></html>';
    } else {
        $the_error = "\nSQL error: " . $the_error . "\n";
        $the_error .= 'SQL error code: ' . $the_error_no . "\n";
        $the_error .= 'Date: ' . date("l dS \of F Y h:i:s A");
        $out = "<html>\n<head>\n<title>MySQLI Error</title>\n
                   <style>P,BODY{ font-family:arial,sans-serif; font-size:11px; }</style>\n</head>\n<body>\n
                   <blockquote>\n<h1>MySQLI Error</h1><b>There appears to be an error with the database.</b><br>
                   You can try to refresh the page by clicking <a href=\"javascript:window.location=window.location;\">here</a>.
                   <br><br><b>Error Returned</b><br>
                   <form name='mysql'><textarea rows=\"15\" cols=\"60\">" . htmlsafechars($the_error) . '</textarea></form><br>We apologise for any inconvenience</blockquote></body></html>';
        echo $out;
    }
    die();
}

/**
 * @param string $query
 * @param float  $time
 * @param array  $params
 *
 * @throws DependencyException
 * @throws NotFoundException
 */
function store_query(string $query, float $time, array $params)
{
    if (PHP_SAPI !== 'cli') {
        global $container;

        $cache = $container->get(Cache::class);
        $id = session_id();
        $query_stat = $cache->get('query_stats_' . $id);
        $query_stat[] = [
            'seconds' => number_format($time, 6),
            'query' => $query,
            'params' => $params,
        ];
        $cache->set('query_stats_' . $id, $query_stat, 60);
    }
}
