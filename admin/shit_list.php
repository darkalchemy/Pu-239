<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $site_config, $lang, $cache;

$lang = array_merge($lang, load_language('ad_shitlist'));
$HTMLOUT = $message = $title = '';
//=== check if action2 is sent (either $_POST or $_GET) if so make sure it's what you want it to be
$action2 = (isset($_POST['action2']) ? htmlsafechars($_POST['action2']) : (isset($_GET['action2']) ? htmlsafechars($_GET['action2']) : ''));
$good_stuff = [
    'new',
    'add',
    'delete',
];
$action2 = (($action2 && in_array($action2, $good_stuff, true)) ? $action2 : '');
//=== action2 switch... do what must be done!
switch ($action2) {
    //=== action2: new

    case 'new':
        $shit_list_id = (isset($_GET['shit_list_id']) ? intval($_GET['shit_list_id']) : 0);
        $return_to = str_replace('&amp;', '&', htmlsafechars($_GET['return_to']));
        $cache->delete('shit_list_' . $CURUSER['id']);
        if ($shit_list_id == $CURUSER['id']) {
            stderr($lang['shitlist_stderr'], $lang['shitlist_stderr1']);
        }
        if (!is_valid_id($shit_list_id)) {
            stderr($lang['shitlist_stderr'], $lang['shitlist_stderr2']);
        }
        $res_name = sql_query('SELECT username FROM users WHERE id=' . sqlesc($shit_list_id));
        $arr_name = mysqli_fetch_assoc($res_name);
        $check_if_there = sql_query('SELECT suspect FROM shit_list WHERE userid=' . sqlesc($CURUSER['id']) . ' AND suspect=' . sqlesc($shit_list_id));
        if (mysqli_num_rows($check_if_there) == 1) {
            stderr($lang['shitlist_stderr'], $lang['shitlist_already1'] . htmlsafechars($arr_name['username']) . $lang['shitlist_already2']);
        }
        $level_of_shittyness = '';
        $level_of_shittyness .= '<select name="shittyness"><option value="0">' . $lang['shitlist_level'] . '</option>';
        $i = 1;
        while ($i <= 10) {
            $level_of_shittyness .= '<option value="' . $i . '">' . $i . '' . $lang['shitlist_outof'] . '</option>';
            ++$i;
        }
        $level_of_shittyness .= '</select>';
        $HTMLOUT .= '<h1><img src="' . $site_config['pic_baseurl'] . 'smilies/shit.gif" alt="*" />' . $lang['shitlist_add1'] . '' . htmlsafechars($arr_name['username']) . '' . $lang['shitlist_add2'] . '<img src="' . $site_config['pic_baseurl'] . 'smilies/shit.gif" alt="*" /></h1>
      <form method="post" action="staffpanel.php?tool=shit_list&amp;action=shit_list&amp;action2=add">
   <table border="0">
   <tr>
      <td class="colhead" colspan="2">new <img src="' . $site_config['pic_baseurl'] . 'smilies/shit.gif" alt="*" /><img src="' . $site_config['pic_baseurl'] . 'smilies/shit.gif" alt="*" /><img src="' . $site_config['pic_baseurl'] . 'smilies/shit.gif" alt="*" />
      <img src="' . $site_config['pic_baseurl'] . 'smilies/shit.gif" alt="*" /><img src="' . $site_config['pic_baseurl'] . 'smilies/shit.gif" alt="*" /><img src="' . $site_config['pic_baseurl'] . 'smilies/shit.gif" alt="*" />
      <img src="' . $site_config['pic_baseurl'] . 'smilies/shit.gif" alt="*" /><img src="' . $site_config['pic_baseurl'] . 'smilies/shit.gif" alt="*" /><img src="' . $site_config['pic_baseurl'] . 'smilies/shit.gif" alt="*" /><img src="' . $site_config['pic_baseurl'] . 'smilies/shit.gif" alt="*" />' . $lang['shitlist_outof2'] . '</td>
   </tr>
   <tr>
      <td><b>' . $lang['shitlist_shittyness'] . '</b></td>
      <td>' . $level_of_shittyness . '</td>
   </tr>
   <tr>
      <td><b>' . $lang['shitlist_reason'] . '</b></td>
      <td><textarea cols="60" rows="5" name="text"></textarea></td>
   </tr>
   <tr>
    <td colspan="2">
      <input type="hidden" name="shit_list_id" value="' . $shit_list_id . '" />
      <input type="hidden" name="return_to" value="' . $return_to . '" />
     
      <input type="submit" class="button is-small" value="' . $lang['shitlist_addthis'] . '" /></td>
   </tr>
   </table></form>';
        break;
    //=== action2: add

    case 'add':
        $shit_list_id = (isset($_POST['shit_list_id']) ? intval($_POST['shit_list_id']) : 0);
        $shittyness = (isset($_POST['shittyness']) ? intval($_POST['shittyness']) : 0);
        $return_to = str_replace('&amp;', '&', htmlsafechars($_POST['return_to']));
        if (!is_valid_id($shit_list_id) || !is_valid_id($shittyness)) {
            stderr($lang['shitlist_stderr'], $lang['shitlist_stderr2']);
        }
        $check_if_there = sql_query('SELECT suspect FROM shit_list WHERE userid=' . sqlesc($CURUSER['id']) . ' AND suspect=' . sqlesc($shit_list_id));
        if (mysqli_num_rows($check_if_there) == 1) {
            stderr($lang['shitlist_stderr'], $lang['shitlist_stderr3']);
        }
        sql_query('INSERT INTO shit_list VALUES (' . $CURUSER['id'] . ',' . sqlesc($shit_list_id) . ', ' . sqlesc($shittyness) . ', ' . TIME_NOW . ', ' . sqlesc($_POST['text']) . ')');
        $cache->delete('shit_list_' . $shit_list_id);
        $message = '<h1>' . $lang['shitlist_success'] . '</h1><a class="altlink" href="' . $return_to . '"><span class="button is-small" style="padding:1px;">' . $lang['shitlist_success1'] . '</span></a>';
        break;
    //=== action2: delete

    case 'delete':
        $shit_list_id = (isset($_GET['shit_list_id']) ? intval($_GET['shit_list_id']) : 0);
        $sure = (isset($_GET['sure']) ? intval($_GET['sure']) : '');
        if (!is_valid_id($shit_list_id)) {
            stderr($lang['shitlist_stderr'], $lang['shitlist_stderr2']);
        }
        $res_name = sql_query('SELECT username FROM users WHERE id=' . sqlesc($shit_list_id));
        $arr_name = mysqli_fetch_assoc($res_name);
        if (!$sure) {
            stderr($lang['shitlist_delete1'] . htmlsafechars($arr_name['username']) . $lang['shitlist_delete2'], '' . $lang['shitlist_delete3'] . '<b>' . htmlsafechars($arr_name['username']) . '</b>' . $lang['shitlist_delete4'] . '  
         <a class="altlink" href="' . $site_config['baseurl'] . '/staffpanel.php?tool=shit_list&amp;action=shit_list&amp;action2=delete&amp;shit_list_id=' . $shit_list_id . '&amp;sure=1"><span class="button is-small" style="padding:1px;">' . $lang['shitlist_delete5'] . '</span></a>' . $lang['shitlist_delete6'] . '');
        }
        sql_query('DELETE FROM shit_list WHERE userid=' . sqlesc($CURUSER['id']) . ' AND suspect=' . sqlesc($shit_list_id));
        if (mysqli_affected_rows($GLOBALS['___mysqli_ston']) == 0) {
            stderr($lang['shitlist_stderr'], $lang['shitlist_nomember']);
        }
        $cache->delete('shit_list_' . $shit_list_id);
        $message = '<legend>' . $lang['shitlist_delsuccess'] . ' <b>' . htmlsafechars($arr_name['username']) . '</b>' . $lang['shitlist_delsuccess1'] . ' </legend>';
        break;
} //=== end switch
//=== get stuff ready for page
$res = sql_query('SELECT s.suspect AS suspect_id, s.text, s.shittyness, s.added AS shit_list_added,
                  u.username, u.id, u.added, u.class, u.leechwarn, u.chatpost, u.pirate, u.king, u.avatar, u.donor, u.warned, u.enabled, u.suspended, u.last_access, u.offensive_avatar, u.avatar_rights
                  FROM shit_list AS s
                  LEFT JOIN users AS u ON s.suspect = u.id
                  WHERE s.userid=' . sqlesc($CURUSER['id']) . '
                  ORDER BY shittyness DESC');
//=== default page
$HTMLOUT .= $message . '
   <legend>' . $lang['shitlist_message1'] . '' . htmlsafechars($CURUSER['username']) . '</legend>
   <table width="950" class="table table-bordered">
   <tr>
     <td class="colhead" colspan="4">
     <img src="' . $site_config['pic_baseurl'] . 'smilies/shit.gif" alt="*" />' . $lang['shitlist_message2'] . '<img src="' . $site_config['pic_baseurl'] . 'smilies/shit.gif" alt="*" /></td>
   </tr>';
$i = 1;
if (mysqli_num_rows($res) == 0) {
    $HTMLOUT .= '
   <tr>
      <td colspan="4">
      <img src="' . $site_config['pic_baseurl'] . 'smilies/shit.gif" alt="*" />' . $lang['shitlist_empty'] . '<img src="' . $site_config['pic_baseurl'] . 'smilies/shit.gif" alt="*" /></td>
   </tr>';
} else {
    while ($shit_list = mysqli_fetch_array($res)) {
        $shit = '';
        for ($poop = 1; $poop <= $shit_list['shittyness']; ++$poop) {
            $shit .= ' <img src="' . $site_config['pic_baseurl'] . 'smilies/shit.gif" title="' . (int) $shit_list['shittyness'] . '' . $lang['shitlist_scale'] . '" alt="*" />';
        }
        $HTMLOUT .= (($i % 2 == 1) ? '<tr>' : '') . '
      <td class="has-text-centered w-15 mw-150 ' . (($i % 2 == 0) ? 'one' : 'two') . '">' . get_avatar($shit_list) . '<br>

      ' . format_username($shit_list['id']) . '<br>

      <b> [ ' . get_user_class_name($shit_list['class']) . ' ]</b><br>

      <a class="altlink" href="' . $site_config['baseurl'] . '/staffpanel.php?tool=shit_list&amp;action=shit_list&amp;action2=delete&amp;shit_list_id=' . (int) $shit_list['suspect_id'] . '" title="' . $lang['shitlist_remove1'] . '"><span class="button is-small" style="padding:1px;"><img style="vertical-align:middle;" src="' . $site_config['pic_baseurl'] . 'polls/p_delete.gif">' . $lang['shitlist_remove2'] . '</span></a>
      <a class="altlink" href="messages.php?action=send_message&receiver=' . (int) $shit_list['suspect_id'] . '" title="' . $lang['shitlist_send1'] . '"><span class="button is-small" style="padding:1px;"><img style="vertical-align:middle;" src="' . $site_config['pic_baseurl'] . 'message.gif">' . $lang['shitlist_send2'] . '</span></a></td>
      <td class="' . (($i % 2 == 0) ? 'one' : 'two') . '">' . $shit . '
      <b>' . $lang['shitlist_joined'] . '</b> ' . get_date($shit_list['added'], '') . '
      [ ' . get_date($shit_list['added'], '', 0, 1) . ' ]
      <b>' . $lang['shitlist_added'] . '</b> ' . get_date($shit_list['shit_list_added'], '') . '
      [ ' . get_date($shit_list['shit_list_added'], '', 0, 1) . ' ]
      <b>last seen:</b> ' . get_date($shit_list['last_access'], '') . ' 
      [ ' . get_date($shit_list['last_access'], '', 0, 1) . ' ]<hr>
      ' . format_comment($shit_list['text']) . '</td>' . (($i % 2 == 0) ? '</tr><tr><td class="colhead" colspan="4"></td></tr>' : '');
        ++$i;
    }
} //=== end while
$HTMLOUT .= (($i % 2 == 0) ? '<td class="one" colspan="2"></td></tr>' : '');
$HTMLOUT .= '</table><p><span class="button is-small" style="padding:3px;"><img style="vertical-align:middle;" src="' . $site_config['pic_baseurl'] . 'btn_search.gif" /><a class="altlink" href="' . $site_config['baseurl'] . '/users.php">' . $lang['shitlist_find'] . '</span></a></p>';
echo stdhead($lang['shitlist_stdhead'] . htmlsafechars($CURUSER['username'])) . wrapper($HTMLOUT) . stdfoot();
