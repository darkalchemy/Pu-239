<?php

declare(strict_types = 1);

$id = (isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0));
if (!is_valid_id($id)) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}

sql_query('UPDATE `attachments` SET `times_downloaded` = times_downloaded + 1 WHERE `id` = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$what_to_download_res = sql_query('SELECT file, file_name, extension FROM `attachments` WHERE `id` = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$what_to_download_arr = mysqli_fetch_assoc($what_to_download_res);
$download_as = "{$what_to_download_arr['file_name']}.{$what_to_download_arr['extension']}";
header('Content-type: application/' . $what_to_download_arr['extension']);
header('Content-Disposition: attachment; filename="' . $download_as . '"');
$upload_folder = ROOT_DIR . $site_config['forum_config']['upload_folder'];
readfile($upload_folder . $what_to_download_arr['file']);
