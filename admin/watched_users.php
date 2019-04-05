<?php

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $site_config, $lang, $cache, $mysqli, $fluent;

$lang = array_merge($lang, load_language('ad_watchedusers'));
$HTMLOUT = $H1_thingie = $count2 = '';
$count = 0;
if (isset($_GET['remove'])) {
    if ($CURUSER['class'] < UC_STAFF) {
        stderr($lang['watched_stderr'], $lang['watched_stderr1']);
    }
    $remove_me_Ive_been_good = isset($_POST['wu']) ? $_POST['wu'] : (isset($_GET['wu']) ? $_GET['wu'] : '');
    $removed_log = '';
    //=== if single delete use
    if (isset($_GET['wu'])) {
        if (is_valid_id($remove_me_Ive_been_good)) {
            //=== get mod comments for member
            $res = sql_query('SELECT username, modcomment FROM users WHERE id=' . sqlesc($remove_me_Ive_been_good)) or sqlerr(__FILE__, __LINE__);
            $user = mysqli_fetch_assoc($res);
            $modcomment = get_date(TIME_NOW, 'DATE', 1) . " - {$lang['watched_removed']} $CURUSER[username].\n" . $user['modcomment'];
            sql_query('UPDATE users SET watched_user = \'0\', modcomment = ' . sqlesc($modcomment) . ' WHERE id=' . sqlesc($remove_me_Ive_been_good)) or sqlerr(__FILE__, __LINE__);
            $cache->update_row('user_' . $remove_me_Ive_been_good, [
                'watched_user' => 0,
                'modcomment' => $modcomment,
            ], $site_config['expires']['user_cache']);
            $count = 1;
            $removed_log = format_username($remove_me_Ive_been_good);
        }
    } elseif (!empty($remove_me_Ive_been_good)) {
        foreach ($remove_me_Ive_been_good as $id) {
            if (is_valid_id($id)) {
                //=== get mod comments for member
                $res = sql_query('SELECT username, modcomment FROM users WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
                $user = mysqli_fetch_assoc($res);
                $modcomment = get_date(TIME_NOW, 'DATE', 1) . " - {$lang['watched_removed']} $CURUSER[username].\n" . $user['modcomment'];
                sql_query('UPDATE users SET watched_user = \'0\', modcomment = ' . sqlesc($modcomment) . ' WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
                $cache->update_row('user_' . $id, [
                    'watched_user' => 0,
                    'modcomment' => $modcomment,
                ], $site_config['expires']['user_cache']);
                $count = (++$count);
                $removed_log .= format_username($id);
            }
        }
    }
    //=== Check if members were removed
    if (mysqli_affected_rows($mysqli) == 0) {
        stderr($lang['watched_stderr'], '' . $lang['watched_stderr2'] . '!');
    } else {
        write_log('[b]' . $CURUSER['username'] . '[/b] ' . $lang['watched_removed1'] . '<br>' . $removed_log . ' <br>' . $lang['watched_removedfrom'] . '');
    }
    $H1_thingie = '<h1 class="has-text-centered">' . $count . ' ' . $lang['watched_member'] . '' . ($count == 1 ? '' : 's') . ' ' . $lang['watched_removelist'] . '</h1>';
}
//=== to add members to the watched user list... all staff!
if (isset($_GET['add'])) {
    $member_whos_been_bad = (int) $_GET['id'];
    if (is_valid_id($member_whos_been_bad)) {
        //=== make sure they are not being watched...
        $res = sql_query('SELECT modcomment, watched_user, watched_user_reason, username FROM users WHERE id=' . sqlesc($member_whos_been_bad)) or sqlerr(__FILE__, __LINE__);
        $user = mysqli_fetch_assoc($res);
        if ($user['watched_user'] > 0) {
            stderr($lang['watched_stderr'], htmlsafechars($user['username']) . ' ' . $lang['watched_already'] . ' ' . $lang['watched_backto'] . ' ' . format_username($user['id']) . ' ' . $lang['watched_profile']);
        }
        //== ok they are not watched yet let's add the info part 1
        if ($_GET['add'] && $_GET['add'] == 1) {
            $text = "
                <form method='post' action='./staffpanel.php?tool=watched_users&amp;action=watched_users&amp;add=2&amp;id={$member_whos_been_bad}' accept-charset='utf-8'>
                    <h2>{$lang['watched_add']}{$user['username']}{$lang['watched_towu']}</h2>
                    <div class='has-text-centered'>
                        <span><b>{$lang['watched_pleasefil']}" . format_username($member_whos_been_bad) . " {$lang['watched_userlist']}</b></span>
                    </div>
                    <textarea class='w-100' rows='6' name='reason'>" . htmlsafechars($user['watched_user_reason']) . "</textarea>
                    <input type='submit' class='button is-small' value='{$lang['watched_addtowu']}!'>
                </form>";
            $naughty_box = main_div($text);

            stderr('watched Users', $naughty_box);
        }
        //=== all is good, let's enter them \o/
        $watched_user_reason = htmlsafechars($_POST['reason']);
        $modcomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $lang['watched_addedwu'] . " $CURUSER[username].\n" . $user['modcomment'];
        sql_query('UPDATE users SET watched_user = ' . TIME_NOW . ', modcomment = ' . sqlesc($modcomment) . ', watched_user_reason = ' . sqlesc($watched_user_reason) . ' WHERE id=' . sqlesc($member_whos_been_bad)) or sqlerr(__FILE__, __LINE__);
        $cache->update_row('user_' . $member_whos_been_bad, [
            'watched_user' => TIME_NOW,
            'watched_user_reason' => $watched_user_reason,
            'modcomment' => $modcomment,
        ], $site_config['expires']['user_cache']);
    }
    //=== Check if member was added
    if (mysqli_affected_rows($mysqli) > 0) {
        $H1_thingie = '<h1 class="has-text-centered">' . $lang['watched_success'] . '!' . htmlsafechars($user['username']) . ' ' . $lang['watched_isadded'] . '!</h1>';
        write_log('[b]' . $CURUSER['username'] . '[/b] ' . $lang['watched_isadded'] . ' ' . format_username($member_whos_been_bad) . ' ' . $lang['watched_tothe'] . ' <a href="' . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=watched_users&amp;action=watched_users" class="altlink">' . $lang['watched_users_list'] . '</a>.');
    }
}
//=== get number of watched members
$watched_users = $fluent->from('users')
    ->select(null)
    ->select('COUNT(*) AS count')
    ->where('watched_user != 0')
    ->fetch('count');
$watched_users = number_format($watched_users);

//=== get sort / asc desc, and be sure it's safe
$good_stuff = [
    'username',
    'watched_user',
    'invited_by',
];
$ORDER_BY = ((isset($_GET['sort']) && in_array($_GET['sort'], $good_stuff, true)) ? $_GET['sort'] . ' ' : 'watched_user ');
$ASC = (isset($_GET['ASC']) ? ($_GET['ASC'] === 'ASC' ? 'DESC' : 'ASC') : 'DESC');
$i = 1;
$HTMLOUT .= $H1_thingie;

$res = sql_query('SELECT id, username, added, watched_user_reason, watched_user, uploaded, downloaded, warned, suspended, enabled, donor, class, leechwarn, chatpost, pirate, king, invitedby FROM users WHERE watched_user != \'0\' ORDER BY ' . $ORDER_BY . $ASC) or sqlerr(__FILE__, __LINE__);
$how_many = mysqli_num_rows($res);
if ($how_many > 0) {
    $HTMLOUT .= '
        <form action="' . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=watched_users&amp;action=watched_users&amp;remove=1" method="post"  name="checkme" accept-charset="utf-8">
        <h1 class="has-text-centered">' . $lang['watched_users'] . '[ ' . $watched_users . ' ]</h1>
    <table class="table table-bordered table-striped">';
    $HTMLOUT .= '
    <tr>
        <td><a href="' . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=watched_users&amp;action=watched_users&amp;sort=watched_user&amp;ASC=' . $ASC . '">' . $lang['watched_isadded'] . '</a></td>
        <td><a href="' . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=watched_users&amp;action=watched_users&amp;sort=username&amp;ASC=' . $ASC . '">' . $lang['watched_username'] . '</a></td>
        <td class="has-text-left" width="400">' . $lang['watched_suspicion'] . '</td>
        <td class="has-text-centered">' . $lang['watched_stats'] . '</td>
        <td class="has-text-centered"><a href="' . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=watched_users&amp;action=watched_users&amp;sort=invited_by&amp;ASC=' . $ASC . '">' . $lang['watched_invitedby'] . '</a></td>
        ' . ($CURUSER['class'] >= UC_STAFF ? '
        <td class="has-text-centered">
            <input type="checkbox" id="checkThemAll" class="tooltipper" title="Select All">
        </td>' : '') . '
    </tr>';
    while ($arr = @mysqli_fetch_assoc($res)) {
        $invitor_arr = [];
        if ($arr['invitedby'] != 0) {
            $invitor_res = sql_query('SELECT id, username, donor, class, enabled, warned, leechwarn, chatpost, pirate, king, suspended FROM users WHERE id=' . sqlesc($arr['invitedby'])) or sqlerr(__FILE__, __LINE__);
            $invitor_arr = mysqli_fetch_assoc($invitor_res);
        }
        $the_flip_box = '
        <p>' . format_comment($arr['watched_user_reason']) . '</p>';
        $HTMLOUT .= '
    <tr>
        <td class="has-text-centered">' . get_date($arr['watched_user'], '') . '</td>
        <td class="has-text-left">' . format_username($arr['id']) . '</td>
        <td class="has-text-left">' . $the_flip_box . '</td>
        <td class="has-text-centered">' . member_ratio($arr['uploaded'], $site_config['site']['ratio_free'] ? '0' : $arr['downloaded']) . '</td>
        <td class="has-text-centered">' . ($invitor_arr['username'] == '' ? '' . $lang['watched_open_sign-ups'] . '' : format_username($arr['invitedby'])) . '</td>
        ' . ($CURUSER['class'] >= UC_STAFF ? '
        <td class="has-text-centered"><input type="checkbox" name="wu[]" value="' . (int) $arr['id'] . '"></td>' : '') . '
    </tr>';
    }

    $HTMLOUT .= '
        <tr>
            <td class="has-text-centered" colspan="6">
                <input type="submit" class="button is-small" value="remove selected ' . $lang['watched_removedfrom'] . '">
            </td>
        </tr>
    </table>
</form>';
} else {
    $HTMLOUT .= stdmsg('', $lang['watched_usrempty']);
}

echo stdhead('' . $lang['watched_users'] . '') . wrapper($HTMLOUT) . stdfoot();
