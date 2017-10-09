<?php
global $lang;

$id = (isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0));
if (!is_valid_id($id)) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}
//=== log  people who DL the file
sql_query('UPDATE `attachments` SET `times_downloaded` = times_downloaded + 1 WHERE `id` = ' . sqlesc($id));
$what_to_download_res = sql_query('SELECT file, extension FROM `attachments` WHERE `id` = ' . sqlesc($id));
$what_to_download_arr = mysqli_fetch_assoc($what_to_download_res);
header('Content-type: application/' . $what_to_download_arr['extension']);
header('Content-Disposition: attachment; filename="' . $what_to_download_arr['file'] . '"');
readfile($upload_folder . $what_to_download_arr['file']);
