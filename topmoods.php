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
// topmoods.php for PTF by pdq 2011
require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php');
require_once (INCL_DIR . 'user_functions.php');
dbconn(false);
loggedinorreturn();
$HTMLOUT = '';
$lang = array_merge(load_language('global'));
$stdhead = array(
    'js' => array(
        'popup'
    )
);
$HTMLOUT.= '<table>
      <tr>
      <td class="embedded">
You may select your mood by clicking on the smiley in the left side menu or clicking <a href="javascript:;" onclick="PopUp(\'usermood.php\',\'Mood\',530,500,1,1);">here</a>.
     </td>
     </tr>
     </table>';
$abba = '<h2>Top Moods</h2>
         <table border="1" cellspacing="0" cellpadding="5">
         <tr><td class="colhead" align="center">Count</td>
         <td class="colhead" align="center">Mood</td>
         <td class="colhead" align="center">Icon</td>
         </tr>';
$key = 'topmoods';
$topmoods = $mc1->get_value($key);
if ($topmoods === false) {
    $res = sql_query('SELECT moods.*, users.mood, COUNT(users.mood) as moodcount ' . 'FROM users LEFT JOIN moods ON (users.mood = moods.id) GROUP BY users.mood ' . 'ORDER BY moodcount DESC, moods.id ASC') or sqlerr(__FILE__, __LINE__);
    while ($arr = mysqli_fetch_assoc($res)) {
        $topmoods.= '<tr><td align="center">' . (int)$arr['moodcount'] . '</td>
                 <td align="center">' . htmlsafechars($arr['name']) . ' ' . ($arr['bonus'] == 1 ? '<a href="/mybonus.php" style="color:lime">(bonus)</a>' : '') . '</td>
                 <td align="center"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/' . htmlsafechars($arr['image']) . '" alt="" /></td>
                 </tr>';
    }
    $mc1->add_value($key, $topmoods, 0);
}
$HTMLOUT.= $abba . $topmoods . '</table>';
echo stdhead("Top Moods") . $HTMLOUT . stdfoot($stdhead);
?>
