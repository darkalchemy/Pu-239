<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'phpzip.php';
check_user_status();
global $site_config;

$lang = load_language('global');

$action = (isset($_POST['action']) ? htmlsafechars($_POST['action']) : '');
if ($action === 'download') {
    $id = isset($_POST['sid']) ? (int) $_POST['sid'] : 0;
    if ($id == 0) {
        stderr($lang['gl_error'], $lang['gl_not_a_valid_id']);
    } else {
        $res = sql_query('SELECT id, name, filename FROM subtitles WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_assoc($res);
        $ext = (substr($arr['filename'], -3));
        $fileName = str_replace([
                ' ',
                '.',
                '-',
            ], '_', $arr['name']) . '.' . $ext;
        $file = UPLOADSUB_DIR . $arr['filename'];
        $fileContent = file_get_contents($file);
        $newFile = fopen(UPLOADSUB_DIR . $fileName, 'w');
        @fwrite($newFile, $fileContent);
        @fclose($newFile);
        $file = [];
        $zip = new PHPZip();
        $file[] = UPLOADSUB_DIR . $fileName;
        $fName = UPLOADSUB_DIR . str_replace([
                ' ',
                '.',
                '-',
            ], '_', $arr['name']) . '.zip';
        $zip->Zip($file, $fName);
        $zip->forceDownload($fName);
        @unlink($fName);
        @unlink(UPLOADSUB_DIR . $fileName);
        sql_query('UPDATE subtitles SET hits = hits + 1 WHERE id = ' . sqlesc($id));
    }
} else {
    stderr($lang['gl_error'], $lang['gl_no_way']);
}
