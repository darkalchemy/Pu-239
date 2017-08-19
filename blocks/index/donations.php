<?php
$progress = '';
if (($totalfunds_cache = $mc1->get_value('totalfunds_')) === false) {
    $totalfunds_cache = mysqli_fetch_assoc(sql_query('SELECT sum(cash) as total_funds FROM funds'));
    $totalfunds_cache['total_funds'] = (int)$totalfunds_cache['total_funds'];
    $mc1->cache_value('totalfunds_', $totalfunds_cache, $INSTALLER09['expires']['total_funds']);
}
$funds_so_far = (int)$totalfunds_cache['total_funds'];
$funds_difference = $INSTALLER09['totalneeded'] - $funds_so_far;
$Progress_so_far = number_format($funds_so_far / $INSTALLER09['totalneeded'] * 100, 1);
if ($Progress_so_far >= 100) {
    $Progress_so_far = 100;
}

$HTMLOUT .= "<fieldset class='header'><legend>{$lang['index_donations']}</legend>
			<div class='container-fluid text-center'>
			<a href='./donate.php'><img border='0' src='{$INSTALLER09['pic_base_url']}makedonation.gif' alt='{$lang['index_donations']}' title='{$lang['index_donations']}'  /></a><br>
				<table class='text-center' width='140' style='margin-bottom: 20px; height: 20%;' border='2'>
					<tr>
						<td bgcolor='transparent' align='center' valign='middle' width='$Progress_so_far%'>$Progress_so_far%</td>
						<td bgcolor='grey' align='center' valign='middle'></td>
					</tr>
				</table>
			</div>
			</fieldset>";
