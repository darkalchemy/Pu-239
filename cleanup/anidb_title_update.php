<?php

function anidb_titles_update($data)
{
    $time_start = microtime(true);
    global $BLOCKS, $fluent, $site_config;

    set_time_limit(1200);
    if (!$BLOCKS['anidb_api_on']) {
        return;
    }

    $file = CACHE_DIR . 'anime-titles.dat.gz', $dat;
    $dat = fetch('http://anidb.net/api/anime-titles.dat.gz');
    file_put_contents($file, $dat);

    $uncompress = 'compress.zlib://';
    $contents = file($uncompress . $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $anidb = [];
    foreach ($contents as $line) {
        if (!preg_match('/#\s/', $line)) {
            $content = explode('|', $line);
            switch ($content[1]) {
                case 1:
                    $type = 'official';
                    break;
                case 2:
                    $type = 'syn';
                    break;
                case 3:
                    $type = 'short';
                    break;
                case 4:
                    $type = 'main';
                    break;
            }
            $anidb[] = [
                'aid' => $content[0],
                'type' => $type,
                'language' => $content[2],
                'title' => $content[3],
            ];
        }
    }

    if (!empty($anidb)) {
        $count = floor($site_config['query_limit'] / 2 / max(array_map('count', $anidb)));
        $update = [
           'title' => new Envms\FluentPDO\Literal('VALUES(title)'),
        ];

        foreach (array_chunk($anidb, $count) as $t) {
            $fluent->insertInto('anidb_titles', $t)
                ->onDuplicateKeyUpdate($update)
                ->execute();
        }
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('ANIDB Title Cleanup: completed.' . $text);
    }
}
