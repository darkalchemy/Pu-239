<?php
//== Best film of the week
$categorie = genrelist();
foreach ($categorie as $key => $value) $change[$value['id']] = array(
    'id' => $value['id'],
    'name' => $value['name'],
    'image' => $value['image']
);
if (($motw_cached = $mc1->get_value('top_movie_2')) === false) {
    $motw = sql_query("SELECT torrents.id, torrents.leechers, torrents.seeders, torrents.category, torrents.name, torrents.times_completed FROM torrents INNER JOIN avps ON torrents.id=avps.value_u WHERE avps.arg='bestfilmofweek' LIMIT 1") or sqlerr(__FILE__, __LINE__);
    while ($motw_cache = mysqli_fetch_assoc($motw)) $motw_cached[] = $motw_cache;
    $mc1->cache_value('top_movie_2', $motw_cached, 0);
}
if (count($motw_cached) > 0) {
    $HTMLOUT.= "
	<fieldset class='header'>
		<legend>{$lang['index_mow_title']}</legend>
		<div class='container-fluid'>
			<div class='module'><div class='badge badge-hot'></div> 
				<table class='table table-bordered'>
					<thead>
						<tr>
							<th class='span1'>{$lang['index_mow_type']}</th>
							<th class='span5'>{$lang['index_mow_name']}</th>
							<th class='span1'>{$lang['index_mow_snatched']}</th>
							<th class='span1'>{$lang['index_mow_seeder']}</th>
							<th class='span1'>{$lang['index_mow_leecher']}</th>
						</tr>
					</thead>";
		if ($motw_cached) {
			foreach ($motw_cached as $m_w) {
				$mw['cat_name'] = htmlsafechars($change[$m_w['category']]['name']);
				$mw['cat_pic'] = htmlsafechars($change[$m_w['category']]['image']);
				$HTMLOUT.= "
					<tbody>
						<tr>
							<td class='span1'><img border='0' src='pic/caticons/{$CURUSER['categorie_icon']}/" . htmlsafechars($mw["cat_pic"]) . "' alt='" . htmlsafechars($mw["cat_name"]) . "' title='" . htmlsafechars($mw["cat_name"]) . "' /></td>
							<td class='span1'><a href='{$INSTALLER09['baseurl']}/details.php?id=" . (int)$m_w["id"] . "'><b>" . htmlsafechars($m_w["name"]) . "</b></a></td>
							<td class='span1'>" . (int)$m_w["times_completed"] . "</td>
							<td class='span1'>" . (int)$m_w["seeders"] . "</td>
							<td class='span1'>" . (int)$m_w["leechers"] . "</td>
						</tr>
					</tbody>";
			}
			$HTMLOUT.= "
				</table>
			</div>
		</div>
	</fieldset><hr />";
    } else {
        //== If there are no movie of the week
        if (empty($motw_cached)) $HTMLOUT.= "<tr><td colspan='5'>{$lang['index_mow_no']}!</td></tr></table></div></div></fieldset><hr />";
    }
}
//==End
// End Class
// End File
