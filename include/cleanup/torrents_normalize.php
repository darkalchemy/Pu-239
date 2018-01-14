<?php
/**
 * @param $data
 */
function torrents_normalize($data)
{
    global $site_config, $queries;
    set_time_limit(1200);
    ignore_user_abort(true);

    do {
        $res = sql_query('SELECT id FROM torrents');
        $ar = [];
        while ($row = mysqli_fetch_array($res, MYSQLI_NUM)) {
            $id = $row[0];
            $ar[$id] = 1;
        }
        if (!count($ar)) {
            break;
        }

        $dp = opendir($site_config['torrent_dir']);
        if (!$dp) {
            break;
        }

        $ar2 = [];
        while (($file = readdir($dp)) !== false) {
            if (!preg_match('/^(\d+)\.torrent$/', $file, $m)) {
                continue;
            }
            $id = $m[1];
            $ar2[$id] = 1;
            if (isset($ar[$id]) && $ar[$id]) {
                continue;
            }
            $ff = $site_config['torrent_dir'] . "/$file";
            unlink($ff);
        }
        closedir($dp);
        if (!count($ar2)) {
            break;
        }

        $delids = [];
        foreach (array_keys($ar) as $k) {
            if (isset($ar2[$k]) && $ar2[$k]) {
                continue;
            }
            $delids[] = $k;
            unset($ar[$k]);
        }
        if (count($delids)) {
            $ids = join(',', $delids);
            sql_query("DELETE torrents t, peers p, files f FROM torrents t
                  left join files f on f.torrent=t.id
                  left join peers p on p.torrent=t.id
                  WHERE f.torrent IN ($ids) 
                  OR p.torrent IN ($ids) 
                  OR t.id IN ($ids)");
        }
    } while (0);
    if ($data['clean_log'] && $queries > 0) {
        write_log("Normalize Cleanup: Completed using $queries queries");
    }
}
