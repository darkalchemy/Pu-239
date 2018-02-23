<?php
/**
 * @param $data
 */
function backup_update($data)
{
    global $site_config, $queries;
    set_time_limit(1200);
    ignore_user_abort(true);

    $days = 3;
    $res = sql_query('SELECT id, name FROM dbbackup WHERE added < '.sqlesc(TIME_NOW - ($days * 86400))) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) > 0) {
        $ids = [];
        while ($arr = mysqli_fetch_assoc($res)) {
            $ids[] = (int) $arr['id'];
            $filename = $site_config['backup_dir'].'/'.$arr['name'];
            if (is_file($filename)) {
                unlink($filename);
            }
        }
        sql_query('DELETE FROM dbbackup WHERE id IN ('.implode(', ', $ids).')') or sqlerr(__FILE__, __LINE__);
    }

    if ($data['clean_log'] && $queries > 0) {
        write_log("Backup Cleanup: Completed using $queries queries");
    }
}
