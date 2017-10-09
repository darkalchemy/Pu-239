<?php
if ($CURUSER['class'] >= UC_MODERATOR) {
    if (empty($_POST['deloff'])) {
        stderr('ERROR', "Don't leave any fields blank.");
    }
    sql_query('DELETE FROM offers WHERE id IN (' . implode(', ', array_map('sqlesc', $_POST['deloff'])) . ')');
    sql_query('DELETE FROM voted_offers WHERE offerid IN (' . implode(', ', array_map('sqlesc', $_POST['deloff'])) . ')');
    sql_query('DELETE FROM comments WHERE offer IN (' . implode(', ', array_map('sqlesc', $_POST['deloff'])) . ')');
    header('Refresh: 0; url=viewoffers.php');
    exit();
} else {
    stderr('ERROR', 'tweedle-dee tweedle-dum');
}
