<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
global $CURUSER, $site_config;

$lang = array_merge(load_language('global'), load_language('achievementlist'));
//$doUpdate = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $CURUSER['class'] >= UC_MAX) {
    $clienticon = htmlsafechars(trim($_POST['clienticon']));
    $achievname = htmlsafechars(trim($_POST['achievname']));
    $notes = htmlsafechars($_POST['notes']);
    $clienticon = htmlsafechars($clienticon);
    $achievname = htmlsafechars($achievname);
    sql_query('INSERT INTO achievementist (achievname, notes, clienticon) VALUES(' . sqlesc($achievname) . ', ' . sqlesc($notes) . ', ' . sqlesc($clienticon) . ')') or sqlerr(__FILE__, __LINE__);
    $message = "{$lang['achlst_new_ach_been_added']}. {$lang['achlst_achievement']}: [{$achievname}]";
    //autoshout($message);
    //$doUpdate = true;
}
$res = sql_query('SELECT a1.*, (SELECT COUNT(a2.id) FROM achievements AS a2 WHERE a2.achievement = a1.achievname) AS count FROM achievementist AS a1 ORDER BY a1.id') or sqlerr(__FILE__, __LINE__);
$HTMLOUT = '';
$HTMLOUT .= "<h1>{$lang['achlst_std_head']}</h1>\n";
if (mysqli_num_rows($res) == 0) {
    $HTMLOUT .= "<p><b>{$lang['achlst_there_no_ach_msg']}!<br>{$lang['achlst_staff_been_lazy']}!</b></p>\n";
} else {
    $heading = "
            <tr>
                <th>{$lang['achlst_achievname']}</th>
                <th>{$lang['achlst_description']}</th>
                <th>{$lang['achlst_earned']}</th>
            </tr>";
    $body = '';
    while ($arr = mysqli_fetch_assoc($res)) {
        $notes = htmlsafechars($arr['notes']);
        $clienticon = '';
        if ($arr['clienticon'] != '') {
            $clienticon = "<img src='" . $site_config['pic_base_url'] . 'achievements/' . htmlsafechars($arr['clienticon']) . "' title='" . htmlsafechars($arr['achievname']) . "' alt='" . htmlsafechars($arr['achievname']) . "' />";
        }
        $body .= "
            <tr>
                <td>$clienticon</td>
                <td>$notes</td>
                <td>" . htmlsafechars($arr['count']) . " time" . plural($arr['count']) . "</td>
            </tr>";
    }
}
$HTMLOUT .= main_table($body, $heading);

if ($CURUSER['class'] === UC_MAX) {
    $HTMLOUT .= "
    <h2>{$lang['achlst_add_an_ach_lst']}</h2>
    <form method='post' action='achievementlist.php'>" . main_table("
            <tr>
                <td class='w-15'>{$lang['achlst_achievname']}</td>
                <td><input class='w-100' type='text' name='achievname' /></td>
            </tr>
            <tr>
                <td>{$lang['achlst_achievicon']}</td>
                <td><textarea class='w-100' rows='3' name='clienticon'></textarea></td>
            </tr>
            <tr>
                <td>{$lang['achlst_description']}</td>
                <td><textarea class='w-100' rows='6' name='notes'></textarea></td>
            </tr>
            <tr>
                <td colspan='2' class='has-text-centered'>
                    <input type='submit' name='okay' value='{$lang['achlst_add_me']}!' class='button is-small' />
                </td>
            </tr>") . "
    </form>";
}
echo stdhead($lang['achlst_std_head']) . wrapper($HTMLOUT, 'has-text-centered') . stdfoot();
