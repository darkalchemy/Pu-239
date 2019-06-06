<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'function_pager.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang);
global $container, $site_config;

require_once DATABASE_DIR . 'sql_updates.php';
$fluent = $container->get(Database::class);
$cache = $container->get(Cache::class);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['id']) && !empty($_POST['submit'])) {
        $id = $_POST['id'];
        $submit = $_POST['submit'];
        $qid = array_search($id, array_column($sql_updates, 'id'));
        $sql = $sql_updates[$qid]['query'];

        if (isset($qid) && $submit === 'Run Query') {
            $flush = $sql_updates[$qid]['flush'];

            try {
                $query = $fluent->getPdo()
                                ->prepare($sql);
                $query->execute();
                $values = [
                    'id' => (int) $id,
                    'query' => $sql,
                ];
                $fluent->insertInto('database_updates')
                       ->values($values)
                       ->execute();

                if ($flush) {
                    $cache->flushDB();
                    $session->set('is-success', 'You flushed the ' . ucfirst($site_config['cache']['driver']) . ' cache');
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
            } catch (Exception $e) {
                $code = $e->getCode();
                $msg = $e->getMessage();
                if ($code === '42S21') {
                    $session->set('is-danger', "{$msg}[p]\n you should be safe if you ignore this query[/p][p]" . htmlsafechars($sql) . '[/p]');
                } else {
                    $session->set('is-danger', "{$msg}[p]\n try to run manually:[/p][p]" . htmlsafechars($sql) . '[/p]');
                }
            }
        } elseif (isset($qid) && $submit === 'Ignore Query') {
            $values = [
                'id' => (int) $id,
                'query' => $sql,
            ];
            $fluent->insertInto('database_updates')
                   ->values($values)
                   ->execute();
            $session->set('is-success', "Query #$id has been ignored");
        }
    }
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
                <form action='{$_SERVER['PHP_SELF']}?tool=upgrade_database' method='post' accept-charset='utf-8'>
                    <div class='level-center'>
                        <span class='margin10'>
                            <input type='hidden' name='id' value={$update['id']}>
                            <input class='button is-small' type='submit' name='submit' value='Run Query'>
                        </span>
                        <span class='margin10'>
                            <input type='hidden' name='id' value={$update['id']}>
                            <input class='button is-small' type='submit' name='submit' value='Ignore Query'>
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
