<?php

global $lang;

$res2 = sql_query('SELECT count(voted_requests.id) AS c FROM voted_requests INNER JOIN users ON voted_requests.userid = users.id INNER JOIN requests ON voted_requests.requestid = requests.id WHERE voted_requests.requestid ='.$id) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_assoc($res2);
$count = (int) $row['c'];
if ($count > 0) {
    $pager = pager(25, $count, 'viewrequests.php?');
    $res = sql_query('SELECT u.id AS userid, r.id AS requestid, r.request, r.added
                        FROM voted_requests AS v
                        INNER JOIN users AS u ON v.userid = u.id
                        INNER JOIN requests AS r ON v.requestid = r.id WHERE v.requestid = '.$id.' '.$pager['limit']) or sqlerr(__FILE__, __LINE__);
    $res2 = sql_query("select request from requests where id=$id");
    $arr2 = mysqli_fetch_assoc($res2);
    $HTMLOUT .= "<h1>{$lang['view_voters']}<a class='altlink' href='viewrequests.php?id=$id&amp;req_details'><b>".htmlspecialchars($arr2['request']).'</b></a></h1>';
    $HTMLOUT .= "<p>{$lang['view_vote_this']}<a class='altlink' href='viewrequests.php?id=$id&amp;req_vote'><b>{$lang['view_req']}</b></a></p>";
    $HTMLOUT .= $pager['pagertop'];
    if (0 == mysqli_num_rows($res)) {
        $HTMLOUT .= "<p><b>{$lang['view_nothing']}</b></p>\n";
    } else {
        $HTMLOUT .= "<table >
<tr><td class='colhead'>{$lang['view_name']}</td><td class='colhead'>{$lang['view_upl']}</td><td class='colhead'>{$lang['view_dl']}</td>
<td class='colhead'>{$lang['view_ratio']}</td></tr>\n";
        while ($arr = mysqli_fetch_assoc($res)) {
            $ratio = member_ratio($arr['uploaded'], $arr['downloaded']);
            $uploaded = mksize($arr['uploaded']);
            $joindate = get_date($arr['added'], '');
            $downloaded = mksize($arr['downloaded']);
            $enabled = ('no' == $arr['enabled'] ? '<span style="color:red;">No</span>' : '<span style="color:green;">Yes</span>');
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
    $HTMLOUT .= "{$lang['req_nothing']}";
}
/////////////////////// HTML OUTPUT //////////////////////////////
echo stdhead('Voters').$HTMLOUT.stdfoot();
