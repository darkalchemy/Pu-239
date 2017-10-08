<?php
if (!defined('IN_OFFERS')) {
    exit('No direct script access allowed');
}
$res2 = sql_query('SELECT COUNT(v.id) AS c
                    FROM voted_offers AS v
                    INNER JOIN users AS u ON v.userid = u.id
                    INNER JOIN offers AS o ON v.offerid = o.id
                    WHERE v.offerid =' . $id) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_assoc($res2);
$count = (int)$row['c'];
if ($count > 0) {
    $pager = pager(25, $count, 'viewoffers.php?');
    $res = sql_query('SELECT select u.id as userid, o.id as offerid, o.offer, o.added
                        FROM voted_offers AS v
                        INNER JOIN users AS u ON v.userid = u.id
                        INNER JOIN offers AS o ON v.offerid = o.id
                        WHERE v.offerid =' . $id . ' ' . $pager['limit']) or sqlerr(__FILE__, __LINE__);
    $res2 = sql_query("SELECT offer FROM offers WHERE id = " . sqlesc($id));
    $arr2 = mysqli_fetch_assoc($res2);
    $HTMLOUT .= "<h1>Voters for <a class='altlink' href='viewoffers.php?id=$id&amp;offer_details'><b>" . htmlspecialchars($arr2['offer']) . '</b></a></h1>';
    $HTMLOUT .= "<p>Vote for this <a class='altlink' href='viewoffers.php?id=$id&amp;offer_vote'><b>Offer</b></a></p>";
    $HTMLOUT .= $pager['pagertop'];
    if (mysqli_num_rows($res) == 0) {
        $HTMLOUT .= "<p><b>Nothing found</b></p>\n";
    } else {
        $HTMLOUT .= "<table border='1' cellspacing='0' cellpadding='5'>
<tr><td class='colhead'>Username</td><td class='colhead'>Uploaded</td><td class='colhead'>Downloaded</td>
<td class='colhead'>Share Ratio</td></tr>\n";
        while ($arr = mysqli_fetch_assoc($res)) {
            $ratio = member_ratio($arr['uploaded'], $arr['downloaded']);
            $uploaded = mksize($arr['uploaded']);
            $joindate = get_date($arr['added'], '');
            $downloaded = mksize($arr['downloaded']);
            $enabled = ($arr['enabled'] == 'no' ? '<span style="color:red;">No</span>' : '<span style="color:green;">Yes</span>');
            $arr['id'] = $arr['userid'];
            $username = format_username($arr['userid']);
            $HTMLOUT .= "<tr><td>$username</td>
             <td>$uploaded</td>
             <td>$downloaded</td>
             <td>$ratio</td></tr>\n";
        }
        $HTMLOUT .= "</table>\n";
    }
    $HTMLOUT .= $pager['pagerbottom'];
} else {
    $HTMLOUT .= 'Nothing here!';
}
/////////////////////// HTML OUTPUT //////////////////////////////
echo stdhead('Voters') . $HTMLOUT . stdfoot();
