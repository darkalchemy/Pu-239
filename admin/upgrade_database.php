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
    $qid = array_search($id, array_column($sql_updates, 'id'));

    if (isset($qid) && $submit === 'Run Query') {
        $sql = $sql_updates[$qid]['query'];
        $flush = $sql_updates[$qid]['flush'];
        if (sql_query($sql)) {
            $sql = 'INSERT INTO database_updates (id, query) VALUES (' . sqlesc($id) . ', ' . sqlesc($sql) . ')';
            sql_query($sql) or sqlerr(__FILE__, __LINE__);
            if ($flush) {
                $cache->flushDB();
                $session->set('is-success', 'You flushed the ' . ucfirst($_ENV['CACHE_DRIVER']) . ' cache');
            } elseif (!$flush) {
                // do nothing
            } else {
                $items = explode(', ', $flush);
                foreach ($items as $item) {
                    $cache->delete($item);
                    $session->set('is-success', "You flushed $item cache");
                }
            }
            $session->set('is-success', "Query #$id ran without error");
        } else {
            $session->set('is-danger', "[p]Query #$id failed to run, try to run manually[/p][p]" . htmlspecialchars($sql) . '[/p]');
        }
    } elseif (isset($qid) && $submit === 'Ignore Query') {
        $sql = $sql_updates[$qid]['query'];
        $sql = 'INSERT INTO database_updates (id, query) VALUES (' . sqlesc($id) . ', ' . sqlesc($sql) . ')';
        sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $session->set('is-success', "Query #$id has been ignored");
    }
}

$table_exists = $cache->get('table_exists_database_updates');
if ($table_exists === false || is_null($table_exists)) {
    $sql = "SHOW tables LIKE 'database_updates'";
    $result = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($result) != 1) {
        sql_query("CREATE TABLE `database_updates` (
              `id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
              `info` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `query` TEXT COLLATE utf8mb4_unicode_ci NOT NULL,
              `added` DATETIME DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;") or sqlerr(__FILE__, __LINE__);
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

    $body = '';
    foreach ($sql_updates as $update) {
        if (array_key_exists($update['id'], $results)) {
            continue;
        }

        $button = "
                <form action='{$site_config['baseurl']}/staffpanel.php?tool=upgrade_database' method='post'>
                    <div class='level-center'>
                        <span class='margin10'>
                            <input type='hidden' name='id' value={$update['id']}>
                            <input class='button is-small' type='submit' name='submit' value='Run Query' />
                        </span>
                        <span class='margin10'>
                            <input type='hidden' name='id' value={$update['id']}>
                            <input class='button is-small' type='submit' name='submit' value='Ignore Query' />
                        </span
                    </div>
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

$HTMLOUT = wrapper(main_table($body, $heading));

echo stdhead('Update Database') . wrapper($HTMLOUT) . stdfoot();
