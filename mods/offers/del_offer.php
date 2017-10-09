<?php
$res = sql_query('SELECT userid, offer FROM offers WHERE id = ' . $id) or sqlerr(__FILE__, __LINE__);
$num = mysqli_fetch_assoc($res);
if ($CURUSER['id'] != $num['userid'] && $CURUSER['class'] < UC_MODERATOR) {
    stderr('Error', 'This is not your Offer to delete!');
}
if (!isset($_GET['sure'])) {
    stderr('Delete Offer', "You`re about to delete this offer. Click\n <a class='altlink' href='viewoffers.php?id=$id&amp;del_offer&amp;sure=1'>here</a>, if you`re sure.", false);
} else {
    sql_query('DELETE FROM offers WHERE id = ' . $id) or sqlerr(__FILE__, __LINE__);
    sql_query('DELETE FROM voted_offers WHERE offerid = ' . $id) or sqlerr(__FILE__, __LINE__);
    sql_query('DELETE FROM comments WHERE offer = ' . $id) or sqlerr(__FILE__, __LINE__);
    write_log('Offer: ' . $id . ' (' . $num['offer'] . ') was deleted from the Offer section by ' . $CURUSER['username']);
    header('Refresh: 0; url=viewoffers.php');
}
