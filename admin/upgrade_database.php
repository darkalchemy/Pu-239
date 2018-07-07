<?php

require_once INCL_DIR . 'user_functions.php';
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'pager_functions.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $lang, $fluent, $site_config, $cache, $session;

$lang = array_merge($lang);
if (!defined('DATABASE_DIR')) {
    stderr('Error', "add \"define('DATABASE_DIR', ROOT_DIR . 'database' . DIRECTORY_SEPARATOR);\" to define.php");
    die();
} else {
    require_once DATABASE_DIR . 'sql_updates.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    extract($_POST);
    unset($_POST);
    if ($id >= 1 && $submit === 'Run Query') {
        $sql = $sql_updates[$id - 1]['query'];
        if (sql_query($sql)) {
            $sql = 'INSERT INTO database_updates (id, query) VALUES (' . sqlesc($id) . ', ' . sqlesc($sql) . ')';
            sql_query($sql) or sqlerr(__FILE__, __LINE__);
            $session->set('is-success', "Query #$id ran without error");
        } else {
            $session->set('is-danger', "[p]Query #$id failed to run, try to run manually[/p][p]" . htmlsafechars($sql) . '[/p]');
        }
    }
}

$table_exists = $cache->get('table_exists_database_updates');
if ($table_exists === false || is_null($table_exists)) {
    $sql = "SHOW tables LIKE 'database_updates'";
    $result = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($result) != 1) {
        sql_query(
            "CREATE TABLE `database_updates` (
              `id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
              `info` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `query` TEXT COLLATE utf8mb4_unicode_ci NOT NULL,
              `added` DATETIME DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;"
        ) or sqlerr(__FILE__, __LINE__);
    }

    $cache->set('table_exists_database_updates', 1, 0);
}

$heading = "
        <tr>
            <th class='has-text-centered w-10'>
                ID
            </th>
            <th class='has-text-centered'>
                Info
            </th>
            <th class='has-text-centered'>
                Date
            </th>
            <th class='has-text-centered'>
                Query
            </th>
            <th class='has-text-centered w-10'>
                Status
            </th>
        </tr>";

if (file_exists(DATABASE_DIR)) {
    $results = $fluent->from('database_updates')
        ->select(null)
        ->select('id')
        ->select('added')
        ->fetchPairs('id', 'added');

    $results = !empty($results) ? $results : [0 => '2017-12-06 14:43:22'];

    $count = count($sql_updates);
    $per_page = 15;
    $pager = pager($per_page, $count, "{$site_config['baseurl']}/staffpanel.php?tool=upgrade_database&amp;");
    preg_match('/LIMIT (\d*),(\d*)/i', $pager['limit'], $match);
    $first = isset($match[1]) ? $match[1] : 0;
    $last = isset($match[2]) ? $match[1] + $per_page : end($sql_updates)['id'];
    $page = !empty($_GET['page']) ? "&page={$_GET['page']}" : '';

    $body = '';
    foreach ($sql_updates as $update) {
        if (array_key_exists($update['id'], $results)) {
            continue;
        }

        if ($update['id'] > $first && $update['id'] <= $last) {
            $button = "
                <form action='{$site_config['baseurl']}/staffpanel.php?tool=upgrade_database{$page}' method='post'>
                    <input type='hidden' name='id' value={$update['id']}>
                    <input class='button is-small' type='submit' name='submit' value='Run Query' />
                </form>";
            $body .= "
        <tr>
            <td class='has-text-centered'>
                {$update['id']}
            </td>
            <td>
                {$update['info']}
            </td>
            <td class='has-text-centered'>
                " . (array_key_exists($update['id'], $results) ? $results[$update['id']] : $update['date']) . "
            </td>
            <td>
                {$update['query']}
            </td>
            <td class='has-text-centered'>
                " . (array_key_exists($update['id'], $results) ? 'Completed' : $button) . '
            </td>
        </tr>';
        }
    }
    if (empty($body)) {
        $body = "
        <tr>
            <td colspan='5'>
                There are no updates available!
            </td>
        </tr>";
    }
} else {
    $body = "
        <tr>
            <td colspan='5'>
                'Path Missing: => " . DATABASE_DIR . '
            </td>
        </tr>';
}

$HTMLOUT = wrapper(($count > $per_page ? $pager['pagertop'] : '') . main_table($body, $heading) . ($count > $per_page ? $pager['pagerbottom'] : ''));

echo stdhead('Update Database') . $HTMLOUT . stdfoot();
