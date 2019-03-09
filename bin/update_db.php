<?php

require_once __DIR__ . '/../include/bittorrent.php';
global $fluent, $site_config, $cache, $fluent;

require_once DATABASE_DIR . 'sql_updates.php';

if (!empty($argv[1]) && !empty($argv[2])) {
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
                $comment[] = 'You flushed the ' . ucfirst($_ENV['CACHE_DRIVER']) . ' cache';
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
        die();
    } elseif ($argv[1] === 'ignore') {
        $values = [
            'id' => (int) $id,
            'query' => $sql,
        ];
        $fluent->insertInto('database_updates')
            ->values($values)
            ->execute();
    }
}
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

echo "There are not database updates.\n";
