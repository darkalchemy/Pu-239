<?php
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'pager_new.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $site_config, $cache, $lang;

$lang = array_merge($lang, load_language('ad_watchedusers'));
$stdfoot = [
    'js' => [
        get_file('warn_js'),
    ],
];

$HTMLOUT = $H1_thingie = $count2 = '';
$div_link_number = $count = 0;
//=== to delete members from the watched user list... admin and up only!
if (isset($_GET['remove'])) {
    if ($CURUSER['class'] < UC_STAFF) {
        stderr($lang['watched_stderr'], $lang['watched_stderr1']);
    }
    $remove_me_Ive_been_good = (isset($_POST['wu']) ? $_POST['wu'] : $_GET['wu']);
    $removed_log = '';
    //=== if single delete use
    if (isset($_GET['wu'])) {
        if (is_valid_id($remove_me_Ive_been_good)) {
            //=== get mod comments for member
            $res = sql_query('SELECT username, modcomment FROM users WHERE id=' . sqlesc($remove_me_Ive_been_good)) or sqlerr(__FILE__, __LINE__);
            $user = mysqli_fetch_assoc($res);
            $modcomment = get_date(TIME_NOW, 'DATE', 1) . " - {$lang['watched_removed']} $CURUSER[username].\n" . $user['modcomment'];
            sql_query('UPDATE users SET watched_user = \'0\', modcomment=' . sqlesc($modcomment) . ' WHERE id=' . sqlesc($remove_me_Ive_been_good)) or sqlerr(__FILE__, __LINE__);
            $cache->update_row('MyUser_' . $remove_me_Ive_been_good, [
                'watched_user' => 0,
            ], $site_config['expires']['curuser']);
            $cache->update_row('user' . $remove_me_Ive_been_good, [
                'watched_user' => 0,
            ], $site_config['expires']['user_cache']);
            $cache->update_row('user_stats_' . $remove_me_Ive_been_good, [
                'modcomment' => $modcomment,
            ], $site_config['expires']['user_stats']);
            $count = 1;
            $removed_log = '<a href="userdetails.php?id=' . $remove_me_Ive_been_good . '" class="altlink">' . htmlsafechars($user['username']) . '</a>';
        }
    } else {
        foreach ($remove_me_Ive_been_good as $id) {
            if (is_valid_id($id)) {
                //=== get mod comments for member
                $res = sql_query('SELECT username, modcomment FROM users WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
                $user = mysqli_fetch_assoc($res);
                $modcomment = get_date(TIME_NOW, 'DATE', 1) . " - {$lang['watched_removed']} $CURUSER[username].\n" . $user['modcomment'];
                sql_query('UPDATE users SET watched_user = \'0\', modcomment=' . sqlesc($modcomment) . ' WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
                $cache->update_row('MyUser_' . $id, [
                    'watched_user' => 0,
                ], $site_config['expires']['curuser']);
                $cache->update_row('user' . $id, [
                    'watched_user' => 0,
                ], $site_config['expires']['user_cache']);
                $cache->update_row('user_stats_' . $id, [
                    'modcomment' => $modcomment,
                ], $site_config['expires']['user_stats']);
                $count = (++$count);
                $removed_log .= '<a href="userdetails.php?id=' . $id . '" class="altlink">' . htmlsafechars($user['username']) . '</a> ';
            }
        }
    }
    //=== Check if members were removed
    if (mysqli_affected_rows($GLOBALS['___mysqli_ston']) == 0) {
        stderr($lang['watched_stderr'], '' . $lang['watched_stderr2'] . '!');
    } else {
        write_log('[b]' . $CURUSER['username'] . '[/b] ' . $lang['watched_removed1'] . '<br>' . $removed_log . ' <br>' . $lang['watched_removedfrom'] . '');
    }
    $H1_thingie = '<h1>' . $count . ' ' . $lang['watched_member'] . '' . ($count == 1 ? '' : 's') . ' ' . $lang['watched_removelist'] . '</h1>';
}
//=== to add members to the watched user list... all staff!
if (isset($_GET['add'])) {
    $member_whos_been_bad = (int)$_GET['id'];
    if (is_valid_id($member_whos_been_bad)) {
        //=== make sure they are not being watched...
        $res = sql_query('SELECT modcomment, watched_user, watched_user_reason, username FROM users WHERE id = ' . sqlesc($member_whos_been_bad)) or sqlerr(__FILE__, __LINE__);
        $user = mysqli_fetch_assoc($res);
        if ($user['watched_user'] > 0) {
            stderr($lang['watched_stderr'], htmlsafechars($user['username']) . ' ' . $lang['watched_already'] . '<a href="userdetails.php?id=' . $member_whos_been_bad . '" >' . $lang['watched_backto'] . ' ' . htmlsafechars($user['username']) . '\'s ' . $lang['watched_profile'] . '</a>');
        }
        //== ok they are not watched yet let's add the info part 1
        if ($_GET['add'] && $_GET['add'] == 1) {
            $text = "
                <form method='post' action='./staffpanel.php?tool=watched_users&amp;action=watched_users&amp;add=2&amp;id={$member_whos_been_bad}'>
                    <h2>{$lang['watched_add']}{$user['username']}{$lang['watched_towu']}</h2>
                    <div class='has-text-centered'>
                        <span><b>{$lang['watched_pleasefil']}" . format_username($member_whos_been_bad) . " {$lang['watched_userlist']}</b></span>
                    </div>
                    <textarea class='w-100' rows='6' name='reason'>" . htmlsafechars($user['watched_user_reason']) . "</textarea>
                    <input type='submit' class='button_big' value='{$lang['watched_addtowu']}!' />
                </form>";
            $naughty_box = main_div($text);

            stderr('watched Users', $naughty_box);
        }
        //=== all is good, let's enter them \o/
        $watched_user_reason = htmlsafechars($_POST['reason']);
        $modcomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $lang['watched_addedwu'] . " $CURUSER[username].\n" . $user['modcomment'];
        sql_query('UPDATE users SET watched_user = ' . TIME_NOW . ', modcomment=' . sqlesc($modcomment) . ', watched_user_reason = ' . sqlesc($watched_user_reason) . ' WHERE id=' . sqlesc($member_whos_been_bad)) or sqlerr(__FILE__, __LINE__);
        $cache->update_row('MyUser_' . $member_whos_been_bad, [
            'watched_user' => TIME_NOW,
        ], $site_config['expires']['curuser']);
        $cache->update_row('user' . $member_whos_been_bad, [
            'watched_user'        => TIME_NOW,
            'watched_user_reason' => $watched_user_reason,
        ], $site_config['expires']['user_cache']);
        $cache->update_row('user_stats_' . $member_whos_been_bad, [
            'modcomment' => $modcomment,
        ], $site_config['expires']['user_stats']);
    }
    //=== Check if member was added
    if (mysqli_affected_rows($GLOBALS['___mysqli_ston']) > 0) {
        $H1_thingie = '<h1>' . $lang['watched_success'] . '!' . htmlsafechars($user['username']) . ' ' . $lang['watched_isadded'] . '!</h1>';
        write_log('[b]' . $CURUSER['username'] . '[/b] ' . $lang['watched_isadded'] . ' <a href="userdetails.php?id=' . $member_whos_been_bad . '" class="altlink">' . htmlsafechars($user['username']) . '</a> ' . $lang['watched_tothe'] . ' <a href="staffpanel.php?tool=watched_users&amp;action=watched_users" class="altlink">' . $lang['watched_users_list'] . '</a>.');
    }
}
//=== get number of watched members
$watched_users = number_format(get_row_count('users', 'WHERE watched_user != \'0\''));
//=== get sort / asc desc, and be sure it's safe
$good_stuff = [
    'username',
    'watched_user',
    'invited_by',
];
$ORDER_BY = ((isset($_GET['sort']) && in_array($_GET['sort'], $good_stuff, true)) ? $_GET['sort'] . ' ' : 'watched_user ');
$ASC = (isset($_GET['ASC']) ? ($_GET['ASC'] == 'ASC' ? 'DESC' : 'ASC') : 'DESC');
$i = 1;
$HTMLOUT .= $H1_thingie . '<br>
        <form action="staffpanel.php?tool=watched_users&amp;action=watched_users&amp;remove=1" method="post"  name="checkme" onsubmit="return ValidateForm(this,\'wu\')">
        <h1>' . $lang['watched_users'] . '[ ' . $watched_users . ' ]</h1>
    <table border="0" cellspacing="5" cellpadding="5" class="has-text-centered" style="max-width:800px">';
//=== get the member info...
$res = sql_query('SELECT id, username, added, watched_user_reason, watched_user, uploaded, downloaded, warned, suspended, enabled, donor, class, leechwarn, chatpost, pirate, king, invitedby FROM users WHERE watched_user != \'0\' ORDER BY ' . $ORDER_BY . $ASC) or sqlerr(__FILE__, __LINE__);
$how_many = mysqli_num_rows($res);
if ($how_many > 0) {
    $div_link_number = 1;
    $HTMLOUT .= '
    <tr>
        <td class="colhead"><a href="staffpanel.php?tool=watched_users&amp;action=watched_users&amp;sort=watched_user&amp;ASC=' . $ASC . '">' . $lang['watched_isadded'] . '</a></td>
        <td class="colhead"><a href="staffpanel.php?tool=watched_users&amp;action=watched_users&amp;sort=username&amp;ASC=' . $ASC . '">' . $lang['watched_username'] . '</a></td>
        <td class="colhead has-text-left" width="400">' . $lang['watched_suspicion'] . '</td>
        <td class="colhead has-text-centered">' . $lang['watched_stats'] . '</td>
        <td class="colhead has-text-centered"><a href="staffpanel.php?tool=watched_users&amp;action=watched_users&amp;sort=invited_by&amp;ASC=' . $ASC . '">' . $lang['watched_invitedby'] . '</a></td>
        ' . ($CURUSER['class'] >= UC_STAFF ? '<td class="colhead has-text-centered">&#160;</td>' : '') . '
    </tr>';
    while ($arr = @mysqli_fetch_assoc($res)) {
        $invitor_arr = [];
        if ($arr['invitedby'] != 0) {
            $invitor_res = sql_query('SELECT id, username, donor, class, enabled, warned, leechwarn, chatpost, pirate, king, suspended FROM users WHERE id = ' . sqlesc($arr['invitedby'])) or sqlerr(__FILE__, __LINE__);
            $invitor_arr = mysqli_fetch_assoc($invitor_res);
        }
        $the_flip_box = '
        [ <a id="d' . $div_link_number . '_open" class="show_warned" style="font-weight:bold;cursor:pointer;">' . $lang['watched_viewreason'] . '</a> ]
        <div class="has-text-left" id="d' . $div_link_number . '" style="display:none"><p class="top10">' . format_comment($arr['watched_user_reason']) . '</p></div>';
        $HTMLOUT .= '
    <tr>
        <td class="has-text-centered">' . get_date($arr['watched_user'], '') . '</td>
        <td class="has-text-left">' . format_username($arr['id']) . '</td>
        <td class="has-text-left">' . $the_flip_box . '</td>
        <td class="has-text-centered">' . member_ratio($arr['uploaded'], $site_config['ratio_free'] ? '0' : $arr['downloaded']) . '</td>
        <td class="has-text-centered">' . ($invitor_arr['username'] == '' ? '' . $lang['watched_open_sign-ups'] . '' : format_username($invitor_arr)) . '</td>
        ' . ($CURUSER['class'] >= UC_STAFF ? '
        <td class="has-text-centered"><input type="checkbox" name="wu[]" value="' . (int)$arr['id'] . '" /></td>' : '') . '
    </tr>';
        $div_link_number++;
    }
    $div_link_number = 1;
} else {
    $HTMLOUT .= '<tr>
<td class="has-text-centered one"><h1>' . $lang['watched_usrempty'] . '!</h1></td></tr>';
}
$HTMLOUT .= '
<tr>
<td class="has-text-centered" colspan="6" class="colhead"><a class="altlink" href="javascript:SetChecked(1,\'wu[]\')"> ' . $lang['watched_selall'] . '</a> - <a class="altlink" href="javascript:SetChecked(0,\'wu[]\')">un-' . $lang['watched_selall'] . '</a>
        <input type="submit" class="button_big" value="remove selected' . $lang['watched_removedfrom'] . '" /></td></tr></table>
        </form>';

echo stdhead('' . $lang['watched_users'] . '') . $HTMLOUT . stdfoot($stdfoot);
