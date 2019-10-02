<?php

declare(strict_types = 1);

$topic_id = isset($_GET['topic_id']) ? (int) $_GET['topic_id'] : (isset($_POST['topic_id']) ? (int) $_POST['topic_id'] : 0);
global $site_config, $CURUSER;

if ($topic_id > 0) {
    sql_query('DELETE FROM subscriptions WHERE topic_id = ' . sqlesc($topic_id) . ' AND user_id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    //=== ok, all done here, send them back! \o/
    header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_id . '&s=0');
    die();
}
if (isset($_POST['remove'])) {
    $_POST['remove'] = isset($_POST['remove']) ? $_POST['remove'] : [];
    $post_delete = [];
    foreach ($_POST['remove'] as $somevar) {
        $post_delete[] = intval($somevar);
    }
    $post_delete = array_unique($post_delete);
    $delete_count = count($post_delete);
    if ($delete_count > 0) {
        sql_query('DELETE FROM subscriptions WHERE id IN (' . implode(', ', $post_delete) . ') AND user_id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    } else {
        stderr(_('Error'), _('Nothing deleted!'));
    }
}
header('Location: ' . $_SERVER['PHP_SELF'] . '?action=subscriptions');
die();
