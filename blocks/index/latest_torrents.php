<?php
//== O9 Top 5 and last5 torrents with tooltip
$HTMLOUT.= "<script type='text/javascript' src='{$INSTALLER09['baseurl']}/scripts/wz_tooltip.js'></script>";
$HTMLOUT.= "
   <fieldset class='header'><legend>{$lang['index_latest']}</legend></fieldset>
   <div class='container-fluid'>
   <!--<a href=\"javascript: klappe_news('a4')\"><img border=\"0\" src=\"pic/plus.gif\" id=\"pica4\" alt=\"{$lang['index_hide_show']}\" /></a><div id=\"ka4\" style=\"display: none;\">-->";
if (($top5torrents = $mc1->get_value('top5_tor_')) === false) {
    $res = sql_query("SELECT id, seeders, poster, leechers, name from torrents ORDER BY seeders + leechers DESC LIMIT {$INSTALLER09['latest_torrents_limit']}") or sqlerr(__FILE__, __LINE__);
    while ($top5torrent = mysqli_fetch_assoc($res)) $top5torrents[] = $top5torrent;
    $mc1->cache_value('top5_tor_', $top5torrents, $INSTALLER09['expires']['top5_torrents']);
}
if (count($top5torrents) > 0) {
    $HTMLOUT.= "<div class='module'><div class='badge badge-top'></div>
				<table class='table table-bordered'>";
    $HTMLOUT.= " <thead><tr>
                <th class='span5'><b>{$lang['top5torrents_title']}</b></th>
                <th class='span1'>{$lang['top5torrents_seeders']}</th>
                <th class='span1'>{$lang['top5torrents_leechers']}</th></tr></thead>\n";
    if ($top5torrents) {
        foreach ($top5torrents as $top5torrentarr) {
            $torrname = htmlsafechars($top5torrentarr['name']);
            if (strlen($torrname) > 50) $torrname = substr($torrname, 0, 50) . "...";
            $poster = empty($top5torrentarr["poster"]) ? "<img src=\'{$INSTALLER09['pic_base_url']}noposter.jpg\' width=\'150\' height=\'220\' />" : "<img src=\'" . htmlsafechars($top5torrentarr['poster']) . "\' width=\'150\' height=\'220\' />";
            $HTMLOUT.= " <tbody><tr>
                <td class='span5'><a href=\"{$INSTALLER09['baseurl']}/details.php?id=" . (int)$top5torrentarr['id'] . "&amp;hit=1\" onmouseover=\"Tip('<b>{$lang['index_ltst_name']}" . htmlsafechars($top5torrentarr['name']) . "</b><br /><b>{$lang['index_ltst_seeder']}" . (int)$top5torrentarr['seeders'] . "</b><br /><b>{$lang['index_ltst_leecher']}" . (int)$top5torrentarr['leechers'] . "</b><br />$poster');\" onmouseout=\"UnTip();\">{$torrname}</a></td>
<td class='span1'>" . (int)$top5torrentarr['seeders'] . "</td>
<td class='span1'>" . (int)$top5torrentarr['leechers'] . "</td>
</tr></tbody>\n";
        }
        $HTMLOUT.= "</table></div><br />";
    } else {
        //== If there are no torrents
        if (empty($top5torrents)) $HTMLOUT.= "<tbody><tr><td  colspan='5'>{$lang['top5torrents_no_torrents']}</td></tr></tbody></table></div>";
    }
}
//==Last 5 begin
if (($last5torrents = $mc1->get_value('last5_tor_')) === false) {
    $sql = "SELECT id, seeders, poster, leechers, name FROM torrents WHERE visible='yes' ORDER BY added DESC LIMIT {$INSTALLER09['latest_torrents_limit']}";
    $result = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    while ($last5torrent = mysqli_fetch_assoc($result)) $last5torrents[] = $last5torrent;
    $mc1->cache_value('last5_tor_', $last5torrents, $INSTALLER09['expires']['last5_torrents']);
}
if (count($last5torrents) > 0) {
    $HTMLOUT.= "<div class='module'><div class='badge badge-new'></div><table class='table table-bordered'>";
    $HTMLOUT.= "<thead><tr>";
    $HTMLOUT.= "<th class='span5'><b>{$lang['last5torrents_title']}</b></th>";
    $HTMLOUT.= "<th class='span1'>{$lang['last5torrents_seeders']}</th>";
    $HTMLOUT.= "<th class='span1'>{$lang['last5torrents_leechers']}</th>";
    $HTMLOUT.= "</tr></thead>";
    if ($last5torrents) {
        foreach ($last5torrents as $last5torrentarr) {
            $torrname = htmlsafechars($last5torrentarr['name']);
            if (strlen($torrname) > 50) $torrname = substr($torrname, 0, 50) . "...";
            $poster = empty($last5torrentarr["poster"]) ? "<img src=\'{$INSTALLER09['pic_base_url']}noposter.jpg\' width=\'150\' height=\'220\' />" : "<img src=\'" . htmlsafechars($last5torrentarr['poster']) . "\' width=\'150\' height=\'220\' />";
            $HTMLOUT.= "<tbody><tr><td class='span5'><a href=\"{$INSTALLER09['baseurl']}/details.php?id=" . (int)$last5torrentarr['id'] . "&amp;hit=1\"></a><a href=\"{$INSTALLER09['baseurl']}/details.php?id=" . (int)$last5torrentarr['id'] . "&amp;hit=1\" onmouseover=\"Tip('<b>{$lang['index_ltst_name']}" . htmlsafechars($last5torrentarr['name']) . "</b><br /><b>{$lang['index_ltst_seeder']}" . (int)$last5torrentarr['seeders'] . "</b><br /><b>{$lang['index_ltst_leecher']}" . (int)$last5torrentarr['leechers'] . "</b><br />$poster');\" onmouseout=\"UnTip();\">{$torrname}</a></td>";
            $HTMLOUT.= "<td class='span1'>".(int)$last5torrentarr['seeders']."</td>";
            $HTMLOUT.= "<td class='span1'>".(int)$last5torrentarr['leechers']."</td>";
            $HTMLOUT.= "</tr></tbody>";
        }
        $HTMLOUT.= "</table></div><hr />";
    } else {
        //== If there are no torrents
        if (empty($last5torrents)) $HTMLOUT.= "<tbody><tr><td colspan='5'>{$lang['last5torrents_no_torrents']}</td></tr></tbody></table></div><hr />";
    }
}
$HTMLOUT.= "</div>";
//== End 09 last5 and top5 torrents
//==
// End Class
// End File
