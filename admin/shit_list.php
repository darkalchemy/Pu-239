<?php

declare(strict_types = 1);

use Pu239\Cache;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $CURUSER;

$HTMLOUT = $message = $title = '';
//=== check if action2 is sent (either $_POST or $_GET) if so make sure it's what you want it to be
$action2 = isset($_POST['action2']) ? htmlsafechars($_POST['action2']) : (isset($_GET['action2']) ? htmlsafechars($_GET['action2']) : '');
$good_stuff = [
    'new',
    'add',
    'delete',
];
$action2 = (($action2 && in_array($action2, $good_stuff, true)) ? $action2 : '');
//=== action2 switch... do what must be done!
$cache = $container->get(Cache::class);
switch ($action2) {
    //=== action2: new

    case 'new':
        $shit_list_id = isset($_GET['shit_list_id']) ? (int) $_GET['shit_list_id'] : 0;
        $return_to = str_replace('&amp;', '&', htmlsafechars($_GET['return_to']));
        $cache->delete('shit_list_' . $CURUSER['id']);
        if ($shit_list_id == $CURUSER['id']) {
            stderr(_('Error'), _("Can't add yourself"));
        }
        if (!is_valid_id($shit_list_id)) {
            stderr(_('Error'), _('Invalid ID'));
        }
        $res_name = sql_query('SELECT username FROM users WHERE id=' . sqlesc($shit_list_id));
        $arr_name = mysqli_fetch_assoc($res_name);
        $check_if_there = sql_query('SELECT suspect FROM shit_list WHERE userid=' . sqlesc($CURUSER['id']) . ' AND suspect=' . sqlesc($shit_list_id));
        if (mysqli_num_rows($check_if_there) == 1) {
            stderr(_('Error'), _fe('The member {0} is already on your shit list!', htmlsafechars($arr_name['username'])));
        }
        $level_of_shittyness = '';
        $level_of_shittyness .= '<select name="shittyness"><option value="0">' . _('level of shittyness') . '</option>';
        $i = 1;
        while ($i <= 10) {
            $level_of_shittyness .= '<option value="' . $i . '">' . _fe('{0} out of 10', $i) . '</option>';
            ++$i;
        }
        $level_of_shittyness .= '</select>';
        $HTMLOUT .= '<h1><img src="' . $site_config['paths']['images_baseurl'] . 'smilies/shit.gif" alt="*">' . _fe('Add {0} to your Shit List {1}', htmlsafechars($arr_name['username']), '<img src="' . $site_config['paths']['images_baseurl'] . 'smilies/shit.gif" alt="*">') . '</h1>
      <form method="post" action="staffpanel.php?tool=shit_list&amp;action=shit_list&amp;action2=add" accept-charset="utf-8">
   <table>
   <tr>
      <td class="colhead" colspan="2">new <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/shit.gif" alt=" * "><img src="' . $site_config['paths']['images_baseurl'] . 'smilies/shit.gif" alt=" * "><img src="' . $site_config['paths']['images_baseurl'] . 'smilies/shit.gif" alt=" * ">
      <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/shit.gif" alt=" * "><img src="' . $site_config['paths']['images_baseurl'] . 'smilies/shit.gif" alt=" * "><img src="' . $site_config['paths']['images_baseurl'] . 'smilies/shit.gif" alt=" * ">
      <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/shit.gif" alt=" * "><img src="' . $site_config['paths']['images_baseurl'] . 'smilies/shit.gif" alt=" * "><img src="' . $site_config['paths']['images_baseurl'] . 'smilies/shit.gif" alt=" * "><img src="' . $site_config['paths']['images_baseurl'] . 'smilies/shit.gif" alt=" * ">' . _('out of 10, 1 being not so shitty, 10 being really shitty... Please select one.') . '</td>
   </tr>
   <tr>
      <td><b>' . _('Shittyness') . ':</b></td>
      <td>' . $level_of_shittyness . '</td>
   </tr>
   <tr>
      <td><b>' . _('Reason') . ':</b></td>
      <td><textarea cols="60" rows="5" name="text"></textarea></td>
   </tr>
   <tr>
    <td colspan="2">
      <input type="hidden" name="shit_list_id" value="' . $shit_list_id . '">
      <input type="hidden" name="return_to" value="' . $return_to . '">
     
      <input type="submit" class="button is-small" value="' . _('add this shit bag!') . '"></td>
   </tr>
   </table></form>';
        break;
    //=== action2: add

    case 'add':
        $shit_list_id = isset($_POST['shit_list_id']) ? (int) $_POST['shit_list_id'] : 0;
        $shittyness = isset($_POST['shittyness']) ? (int) $_POST['shittyness'] : 0;
        $return_to = str_replace('&amp;', '&', htmlsafechars($_POST['return_to']));
        if (!is_valid_id($shit_list_id) || !is_valid_id($shittyness)) {
            stderr(_('Error'), _('Invalid ID'));
        }
        $check_if_there = sql_query('SELECT suspect FROM shit_list WHERE userid=' . sqlesc($CURUSER['id']) . ' AND suspect=' . sqlesc($shit_list_id));
        if (mysqli_num_rows($check_if_there) == 1) {
            stderr(_('Error'), _('That user is already on your shit list.'));
        }
        sql_query('INSERT INTO shit_list VALUES (' . $CURUSER['id'] . ',' . sqlesc($shit_list_id) . ', ' . sqlesc($shittyness) . ', ' . TIME_NOW . ', ' . sqlesc($_POST['text']) . ')');
        $cache->delete('shit_list_' . $shit_list_id);
        $message = '<h1>' . _('Success! Member added to your personal shitlist!') . '</h1><a class="is-link" href="' . $return_to . '"><span class="button is-small" style="padding:1px;">' . _('go back to where you were?') . '</span></a>';
        break;
    //=== action2: delete

    case 'delete':
        $shit_list_id = isset($_GET['shit_list_id']) ? (int) $_GET['shit_list_id'] : 0;
        $sure = isset($_GET['sure']) ? (int) $_GET['sure'] : 0;
        if (!is_valid_id($shit_list_id)) {
            stderr(_('Error'), _('Invalid ID'));
        }
        $res_name = sql_query('SELECT username FROM users WHERE id=' . sqlesc($shit_list_id));
        $arr_name = mysqli_fetch_assoc($res_name);
        if (!$sure) {
            stderr(_('Warning'), _fe('Do you really want to delete {0} from your Shit List? Click {1}here{2} if you are sure.', format_comment($arr_name['username']), '<a class="is-link" href="' . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=shit_list&amp;action=shit_list&amp;action2=delete&amp;shit_list_id=' . $shit_list_id . '&amp;sure=1">', '</a>'));
        }
        sql_query('DELETE FROM shit_list WHERE userid=' . sqlesc($CURUSER['id']) . ' AND suspect=' . sqlesc($shit_list_id));
        if (mysqli_affected_rows($mysqli) == 0) {
            stderr(_('Error'), _('No member found to delete!'));
        }
        $cache->delete('shit_list_' . $shit_list_id);
        $message = '<legend>' . _fe('Success, {0} deleted from your shit list!', format_comment($arr_name['username'])) . ' </legend>';
        break;
} //=== end switch
//=== get stuff ready for page
$res = sql_query('SELECT s.suspect AS suspect_id, s.text, s.shittyness, s.added AS shit_list_added,
                  u.username, u.id, u.registered, u.class, u.leechwarn, u.chatpost, u.pirate, u.king, u.avatar, u.donor, u.warned, u.status, u.last_access, u.offensive_avatar, u.avatar_rights
                  FROM shit_list AS s
                  LEFT JOIN users AS u ON s.suspect = u.id
                  WHERE s.userid=' . sqlesc($CURUSER['id']) . '
                  ORDER BY shittyness DESC');
//=== default page
$HTMLOUT .= $message . '
   <legend>' . _fe('Shit List for {0}', format_comment($CURUSER['username'])) . '</legend>
   <table class="table table-bordered">
   <tr>
     <td class="colhead" colspan="4">
     <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/shit.gif" alt=" * ">' . _('shittiest at the top ') . '<img src="' . $site_config['paths']['images_baseurl'] . 'smilies/shit.gif" alt=" * "></td>
   </tr>';
$i = 1;
if (mysqli_num_rows($res) == 0) {
    $HTMLOUT .= '
   <tr>
      <td colspan="4">
      <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/shit.gif" alt=" * ">' . _('Your shit list is empty. ') . '<img src="' . $site_config['paths']['images_baseurl'] . 'smilies/shit.gif" alt="*"></td>
   </tr>';
} else {
    while ($shit_list = mysqli_fetch_array($res)) {
        $shit = '';
        for ($poop = 1; $poop <= $shit_list['shittyness']; ++$poop) {
            $shit .= ' <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/shit.gif" title="' . _fe('{0} out of 10 on the shittyness scale', $shit_list['shittyness']) . '" alt=" * ">';
        }
        $HTMLOUT .= (($i % 2 == 1) ? '<tr>' : '') . '
      <td class="has-text-centered w-15 mw-150 ' . (($i % 2 == 0) ? 'one' : 'two') . '">' . get_avatar($shit_list) . '<br>

      ' . format_username((int) $shit_list['id']) . '<br>

      <b> [ ' . get_user_class_name((int) $shit_list['class']) . ' ]</b><br>

      <a class="is-link" href="' . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=shit_list&amp;action=shit_list&amp;action2=delete&amp;shit_list_id=' . (int) $shit_list['suspect_id'] . '" title="' . _('remove this toad from your shit list') . '"><span class="button is-small" style="padding:1px;"><img style="vertical-align:middle;" src="' . $site_config['paths']['images_baseurl'] . 'polls/p_delete.gif" alt="Delete">' . _('Remove') . '</span></a>
      <a class="is-link" href="messages.php?action=send_message&amp;receiver=' . (int) $shit_list['suspect_id'] . '" title="' . _('send a PM to this evil toad') . '"><span class="button is-small" style="padding:1px;"><img style="vertical-align:middle;" src="' . $site_config['paths']['images_baseurl'] . 'message.gif" alt="Message">' . _('Send PM') . '</span></a></td>
      <td class="' . (($i % 2 == 0) ? 'one' : 'two') . '">' . $shit . '
      <b>' . _('joined: ') . '</b> ' . get_date((int) $shit_list['added'], '') . '
      [ ' . get_date((int) $shit_list['added'], '', 0, 1) . ' ]
      <b>' . _('added to shit list: ') . '</b> ' . get_date((int) $shit_list['shit_list_added'], '') . '
      [ ' . get_date((int) $shit_list['shit_list_added'], '', 0, 1) . ' ]
      <b>last seen:</b> ' . get_date((int) $shit_list['last_access'], '') . ' 
      [ ' . get_date((int) $shit_list['last_access'], '', 0, 1) . ' ]<hr>
      ' . format_comment($shit_list['text']) . '</td>' . (($i % 2 == 0) ? '</tr><tr><td class="colhead" colspan="4"></td></tr>' : '');
        ++$i;
    }
}
$HTMLOUT .= (($i % 2 == 0) ? '<td class="one" colspan="2"></td></tr>' : '');
$HTMLOUT .= '</table><p><span class="button is-small" style="padding:3px;"><img style="vertical-align:middle;" src="' . $site_config['paths']['images_baseurl'] . 'btn_search.gif" alt="Search"><a class="is-link" href="' . $site_config['paths']['baseurl'] . '/users.php">' . _('Find Member / Browse Member List') . '</span></a></p>';
$title = _('Shitlist');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
