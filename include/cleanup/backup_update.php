<?php
function backup_update($data)
{
    global $INSTALLER09, $queries, $mc1;
    set_time_limit(0);
    ignore_user_abort(1);
    //== Delete old backup's
    $days = 3;
    $res = sql_query('SELECT id, name FROM dbbackup WHERE added < ' . sqlesc(TIME_NOW - ($days * 86400))) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) > 0) {
        $ids = [];
        while ($arr = mysqli_fetch_assoc($res)) {
            $ids[] = (int)$arr['id'];
            $filename = $INSTALLER09['backup_dir'] . '/' . $arr['name'];
            if (is_file($filename)) {
                unlink($filename);
            }
        }
        sql_query('DELETE FROM dbbackup WHERE id IN (' . implode(', ', $ids) . ')') or sqlerr(__FILE__, __LINE__);
    }
    //== end
    if ($queries > 0) {
        write_log("Backup Cleanup: Completed using $queries queries");
    }
    if (false !== mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS['___mysqli_ston']) . ' items deleted/updated';
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
