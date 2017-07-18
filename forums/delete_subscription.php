<?php
/**
 |--------------------------------------------------------------------------|
 |   https://github.com/Bigjoos/                			    |
 |--------------------------------------------------------------------------|
 |   Licence Info: GPL			                                    |
 |--------------------------------------------------------------------------|
 |   Copyright (C) 2010 U-232 V4					    |
 |--------------------------------------------------------------------------|
 |   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.   |
 |--------------------------------------------------------------------------|
 |   Project Leaders: Mindless,putyn.					    |
 |--------------------------------------------------------------------------|
  _   _   _   _   _     _   _   _   _   _   _     _   _   _   _
 / \ / \ / \ / \ / \   / \ / \ / \ / \ / \ / \   / \ / \ / \ / \
( U | - | 2 | 3 | 2 )-( S | o | u | r | c | e )-( C | o | d | e )
 \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/
 */
/**********************************************************
New 2010 forums that don't suck for TB based sites....
pretty much coded page by page, but coming from a 
history ot TBsourse and TBDev and the many many 
coders who helped develop them over time.
proper credits to follow :)

beta fri june 11th 2010 v0.1

thanks to pdq & elephant for suggestions :D

Powered by Bunnies!!!
**********************************************************/
if (!defined('BUNNY_FORUMS')) {
    $HTMLOUT = '';
    $HTMLOUT.= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
        <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
        <title>ERROR</title>
        </head><body>
        <h1 style="text-align:center;">ERROR</h1>
        <p style="text-align:center;">How did you get here? silly rabbit Trix are for kids!.</p>
        </body></html>';
    echo $HTMLOUT;
    exit();
}
global $lang;

$topic_id = (isset($_GET['topic_id']) ? intval($_GET['topic_id']) : (isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0));
//=== delete stuff from topic page
if ($topic_id > 0) {
    sql_query('DELETE FROM subscriptions WHERE topic_id = ' . sqlesc($topic_id) . ' AND user_id = ' . sqlesc($CURUSER['id']));
    //=== ok, all done here, send them back! \o/
    header('Location: ' . $INSTALLER09['baseurl'] . '/forums.php?action=view_topic&topic_id=' . $topic_id . '&s=0');
    die();
}
//=== delete stuff from subscriptions page stolen from pdq... thanks hun \o
if (isset($_POST['remove'])) {
    $_POST['remove'] = (isset($_POST['remove']) ? $_POST['remove'] : '');
    $post_delete = array();
    foreach ($_POST['remove'] as $somevar) $post_delete[] = intval($somevar);
    $post_delete = array_unique($post_delete);
    $delete_count = count($post_delete);
    if ($delete_count > 0) {
        sql_query('DELETE FROM subscriptions WHERE id IN (' . implode(', ', $post_delete) . ') AND user_id = ' . sqlesc($CURUSER['id']));
    } else {
        stderr($lang['gl_error'], $lang['fe_nothing_deleted']);
    }
}
//=== ok, all done here, send them back! \o/
header('Location: ' . $INSTALLER09['baseurl'] . '/forums.php?action=subscriptions');
die();
?>