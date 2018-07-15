<?php

global $fluent;

$text = "
    <a id='startcomments'></a>
    <div>
        <h1 class='has-text-centered'>{$lang['userdetails_comm_left']}" . format_username($id) . "</a></h1>
        <div class='has-text-centered bottom20'>
            <a href='{$site_config['baseurl']}/usercomment.php?action=add&amp;userid={$id}' class='button is-small'>Add a comment</a>
        </div>";
$subres = sql_query('SELECT COUNT(id) FROM usercomments WHERE userid = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$subrow = mysqli_fetch_array($subres, MYSQLI_NUM);
$count = $subrow[0];
if (!$count) {
    $text .= "
        <h2>{$lang['userdetails_comm_yet']}</h2>\n";
} else {
    require_once INCL_DIR . 'pager_functions.php';
    $pager = pager(5, $count, "userdetails.php?id=$id&amp;", [
        'lastpagedefault' => 1,
    ]);

    $res = $fluent->from('usercomments')
        ->select('id as comment_id')
        ->where('userid = ?', $id)
        ->orderBy('id DESC')
        ->limit('?, ?', $pager['pdo'][0], $pager['pdo'][1]);

    foreach ($res as $row) {
        $row['anonymous'] = false;
        $allrows[] = $row;
    }
    $text .= ($pager['pagertop']);
    $text .= commenttable($allrows, 'usercomment');
}
$text .= '</div>';

$HTMLOUT .= main_div($text);
