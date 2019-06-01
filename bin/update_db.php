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
if (!empty($argv[1]) && !empty($argv[2])) {
    update_database($argv, $sql_updates, $site_config);
} else {
    get_updates($argv, $sql_updates, $site_config);
}

/**
 * @param $argv
 * @param $sql_updates
 * @param $site_config
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function update_database($argv, $sql_updates, $site_config)
{
    global $container;

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
            if ($code === '42S21') {
                $comment[] = "{$msg}\nYou should be safe if you ignore this query\n{$sql}\n";
            } else {
                $comment[] = "{$msg}\nTry to run manually\n{$sql}\n";
            }
        }
        echo implode("\n", $comment) . "\n";
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
    get_updates($argv, $sql_updates);
}

/**
 * @param $argv
 * @param $sql_updates
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function get_updates($argv, $sql_updates)
{
    global $container;

    $fluent = $container->get(Database::class);

    $results = $fluent->from('database_updates')
                      ->select(null)
                      ->select('id')
                      ->select('added')
                      ->fetchPairs('id', 'added');

    foreach ($sql_updates as $update) {
        if (array_key_exists($update['id'], $results)) {
            continue;
        }

        echo "ID: {$update['id']}\nInfo: {$update['info']}\nQuery: {$update['query']}\n\n";
        echo "To run query:\nphp {$argv[0]} run {$update['id']}\n\n";
        echo "To ignore query:\nphp {$argv[0]} ignore {$update['id']}\n\n";
        die();
    }

    echo "There are no database updates.\n";
}
