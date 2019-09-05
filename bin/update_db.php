<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Settings;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'define.php';
require_once INCL_DIR . 'app.php';

$env = $container->get('env');
$settings = $container->get(Settings::class);
$site_config = $settings->get_settings();

require_once DATABASE_DIR . 'sql_updates.php';
if (!empty($argv[1]) && $argv[1] === 'complete') {
    update_all($argv, $sql_updates);
} elseif (!empty($argv[1]) && !empty($argv[2])) {
    update_database($argv, $sql_updates, false);
} else {
    get_updates($argv, $sql_updates, false);
}

/**
 * @param array $argv
 * @param array $sql_updates
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function update_all(array $argv, array $sql_updates)
{
    $updates = get_updates($argv, $sql_updates, true);
    if (!empty($updates)) {
        foreach ($updates as $update) {
            $args = [
                0 => $argv[0],
                1 => 'run',
                2 => $update['id'],
            ];
            echo "\nRunning update query #{$update['id']}\n";
            update_database($args, $sql_updates, true);
        }
    }
}

/**
 * @param array $argv
 * @param array $sql_updates
 * @param bool  $all
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function update_database(array $argv, array $sql_updates, bool $all)
{
    global $container, $site_config;

    $cache = $container->get(Cache::class);
    $fluent = $container->get(Database::class);
    $qid = array_search($argv[2], array_column($sql_updates, 'id'));
    if (empty($qid)) {
        die("{$argv[2]} is an invalid ID\n");
    }
    $id = (int) $sql_updates[$qid]['id'];
    $sql = $sql_updates[$qid]['query'];
    $flush = $sql_updates[$qid]['flush'];
    if ($argv[1] === 'run') {
        $comment = [];
        try {
            $query = $fluent->getPdo()
                ->prepare($sql);
            $query->execute();
            $values = [
                'id' => $id,
                'query' => $sql,
            ];
            $fluent->insertInto('database_updates')
                ->values($values)
                ->execute();

            if ($flush) {
                $cache->flushDB();
                $comment[] = 'You flushed the ' . ucfirst($site_config['cache']['driver']) . ' cache';
            } elseif (!$flush) {
                // do nothing
            } else {
                $items = explode(', ', $flush);
                foreach ($items as $item) {
                    $cache->delete($item);
                    $comment[] = "You flushed $item cache";
                }
            }
            $comment[] = "Query #$id ran without error";
        } catch (Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
            if ($code === '42S01') {
                if ($all) {
                    $comment[] = "\n{$msg}\n\nYou should check the database to ensure the table schema is the same\n\nTo ignore query:\nphp {$argv[0]} ignore {$id}\n\n";
                } else {
                    $comment[] = "{$msg}\nYou should be safe if you ignore this query\n{$sql}\n";
                }
            } elseif ($code === '42S21') {
                if ($all) {
                    $comment[] = "\n{$msg}\n\nYou should be safe if you ignore this query\nTo ignore query:\nphp {$argv[0]} ignore {$id}\n\n";
                } else {
                    $comment[] = "{$msg}\nYou should be safe if you ignore this query\n{$sql}\n";
                }
            } else {
                $comment[] = "{$msg}\nTry to run manually\n{$sql}\n";
            }
        }
        if ($all) {
            if (!empty($msg)) {
                die(implode("\n", $comment) . "\n");
            }
            echo implode("\n", $comment) . "\n";

            return;
        } else {
            echo implode("\n", $comment) . "\n";
        }
    } elseif ($argv[1] === 'ignore') {
        $values = [
            'id' => (int) $id,
            'query' => $sql,
        ];
        $fluent->insertInto('database_updates')
            ->values($values)
            ->execute();
    }
    echo "\n\n======================================================================\n\n";
    get_updates($argv, $sql_updates, false);
}

/**
 * @param array $argv
 * @param array $sql_updates
 * @param bool  $all
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return array
 */
function get_updates(array $argv, array $sql_updates, bool $all)
{
    global $container;

    $fluent = $container->get(Database::class);

    $results = $fluent->from('database_updates')
        ->select(null)
        ->select('id')
        ->select('added')
        ->fetchPairs('id', 'added');

    $updates = [];
    foreach ($sql_updates as $update) {
        if (array_key_exists($update['id'], $results)) {
            continue;
        }

        if ($all) {
            $updates[] = [
                'id' => $update['id'],
            ];
        } else {
            echo "ID: {$update['id']}\nInfo: {$update['info']}\nQuery: {$update['query']}\n\n";
            echo "To run all queries:\nphp {$argv[0]} complete\n\n";
            echo "To run query:\nphp {$argv[0]} run {$update['id']}\n\n";
            echo "To ignore query:\nphp {$argv[0]} ignore {$update['id']}\n\n";
            die();
        }
    }

    if ($all && !empty($updates)) {
        return $updates;
    }
    echo "There are no database updates.\n";
}
