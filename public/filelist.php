<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'pager_functions.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('filelist'));
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!is_valid_id($id)) {
    stderr('USER ERROR', 'Bad id');
}
$res = sql_query(
    'SELECT COUNT(id)
    FROM files 
    WHERE torrent = ' . sqlesc($id)
) or sqlerr(__FILE__, __LINE__);

$row = mysqli_fetch_row($res);
$count = $row[0];
$perpage = 50;
$pager = pager($perpage, $count, "{$site_config['baseurl']}/filelist.php?id=$id&amp;");
$HTMLOUT = '';
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}

$subres = sql_query(
    "SELECT * FROM files 
    WHERE torrent = " . sqlesc($id) . " 
    ORDER BY id
    {$pager['limit']}"
);

$header = "
            <tr>
                <th class='has-text-centered w-1'>{$lang['filelist_type']}</th>
                <th>{$lang['filelist_path']}</th>
                <th class='has-text-right w-10'>{$lang['filelist_size']}</th>
            </tr>";
$body = '';
while ($subrow = mysqli_fetch_assoc($subres)) {
    $ext = 'Unknown';
    if (preg_match('/\\.([A-Za-z0-9]+)$/', $subrow['filename'], $ext)) {
        $ext = strtolower($ext[1]);
    }
    if (!file_exists(IMAGES_DIR . "icons/{$ext}.png")) {
        $ext = 'Unknown';
    }
    $body .= "
            <tr>
                <td class='has-text-centered'>
                    <img src='{$site_config['pic_base_url']}icons/" . htmlsafechars($ext) . ".png' class='tooltipper icon' alt='" . htmlsafechars($ext) . " file' title='" . htmlsafechars($ext) . " file' /></td>
                <td>" . htmlsafechars($subrow['filename']) . "</td>
                <td class='has-text-right'>" . mksize($subrow['size']) . "</td>
            </tr>";
}

$HTMLOUT .= main_table($body, $header);

if ($count > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
echo stdhead($lang['filelist_header']) . wrapper($HTMLOUT) . stdfoot();

