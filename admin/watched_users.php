<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $container, $CURUSER, $site_config;

$HTMLOUT = $H1_thingie = $count2 = '';
$count = 0;
$fluent = $container->get(Database::class);
if (isset($_GET['remove'])) {
    if ($CURUSER['class'] < UC_STAFF) {
        stderr(_('Error'), _('Only the Staff can remove members from the list!'));
    }
    $remove_me_Ive_been_good = isset($_POST['wu']) ? (int) $_POST['wu'] : (isset($_GET['wu']) ? (int) $_GET['wu'] : '');
    $removed_log = '';
    //=== if single delete use
    if (!empty($remove_me_Ive_been_good)) {
        if (!is_array($remove_me_Ive_been_good)) {
            if (is_valid_id($remove_me_Ive_been_good)) {
                $res = sql_query('SELECT username, modcomment FROM users WHERE id = ' . sqlesc($remove_me_Ive_been_good)) or sqlerr(__FILE__, __LINE__);
                $user = mysqli_fetch_assoc($res);
                $modcomment = get_date((int) TIME_NOW, 'DATE', 1) . ' - ' . _('Removed from watched users by') . " $CURUSER[username].\n" . $user['modcomment'];
                sql_query('UPDATE users SET watched_user = \'0\', modcomment = ' . sqlesc($modcomment) . ' WHERE id=' . sqlesc($remove_me_Ive_been_good)) or sqlerr(__FILE__, __LINE__);
                $cache = $container->get(Cache::class);
                $cache->update_row('user_' . $remove_me_Ive_been_good, [
                    'watched_user' => 0,
                    'modcomment' => $modcomment,
                ], $site_config['expires']['user_cache']);
                $count = 1;
                $removed_log = format_username((int) $remove_me_Ive_been_good);
            }
        } else {
            foreach ($remove_me_Ive_been_good as $id) {
                $id = (int) $id;
                if (is_valid_id($id)) {
                    //=== get mod comments for member
                    $res = sql_query('SELECT username, modcomment FROM users WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
                    $user = mysqli_fetch_assoc($res);
                    $modcomment = get_date((int) TIME_NOW, 'DATE', 1) . ' - ' . _('Removed from watched users by') . " $CURUSER[username].\n" . $user['modcomment'];
                    sql_query('UPDATE users SET watched_user = \'0\', modcomment = ' . sqlesc($modcomment) . ' WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
                    $cache->update_row('user_' . $id, [
                        'watched_user' => 0,
                        'modcomment' => $modcomment,
                    ], $site_config['expires']['user_cache']);
                    $count = (++$count);
                    $removed_log .= format_username((int) $id);
                }
            }
        }
    }
    //=== Check if members were removed
    $mysqli = $container->get(mysqli::class);
    if (mysqli_affected_rows($mysqli) == 0) {
        stderr(_('Error'), _('No one was deleted') . '!');
    } else {
        write_log('[b]' . $CURUSER['username'] . '[/b] ' . _('Removed:') . '<br>' . $removed_log . ' <br>' . _('from watched users') . '');
    }
    $H1_thingie = '<h1 class="has-text-centered">' . _pfe('{0, number) Member removed from the list', '{0, number) Members removed from the list', $count) . '</h1>';
}
//=== to add members to the watched user list... all staff!
if (isset($_GET['add'])) {
    $member_whos_been_bad = (int) $_GET['id'];
    if (is_valid_id($member_whos_been_bad)) {
        //=== make sure they are not being watched...
        $res = sql_query('SELECT modcomment, watched_user, watched_user_reason, username FROM users WHERE id=' . sqlesc($member_whos_been_bad)) or sqlerr(__FILE__, __LINE__);
        $user = mysqli_fetch_assoc($res);
        if ($user['watched_user'] > 0) {
            stderr(_('Error'), _fe("{0} is on the watched user list already! back to {1}'s profile", htmlsafechars($user['username']), format_username((int) $user['id'])));
        }
        //== ok they are not watched yet let's add the info part 1
        if ($_GET['add'] && $_GET['add'] == 1) {
            $text = "
                <form method='post' action='./staffpanel.php?tool=watched_users&amp;action=watched_users&amp;add=2&amp;id={$member_whos_been_bad}' enctype='multipart/form-data' accept-charset='utf-8'>
                    <h2>" . _fe('Add {0} to the Watched Users List', $user['username']) . "</h2>
                    <div class='has-text-centered'>
                        <span><b>" . _fe('please fill in the reason for adding {0} to the watched user list.', format_username((int) $member_whos_been_bad)) . "</b></span>
                    </div>
                    <textarea class='w-100' rows='6' name='reason'>" . htmlsafechars($user['watched_user_reason']) . "</textarea>
                    <input type='submit' class='button is-small' value='" . _('add to watched users!') . "'>
                </form>";
            $naughty_box = main_div($text);

            stderr('watched Users', $naughty_box);
        }
        //=== all is good, let's enter them \o/
        $watched_user_reason = htmlsafechars($_POST['reason']);
        $modcomment = get_date((int) TIME_NOW, 'DATE', 1) . ' - ' . _fe('Added to watched users by {0}', $CURUSER[username]) . "\n" . $user['modcomment'];
        sql_query('UPDATE users SET watched_user = ' . TIME_NOW . ', modcomment = ' . sqlesc($modcomment) . ', watched_user_reason = ' . sqlesc($watched_user_reason) . ' WHERE id=' . sqlesc($member_whos_been_bad)) or sqlerr(__FILE__, __LINE__);
        $cache->update_row('user_' . $member_whos_been_bad, [
            'watched_user' => TIME_NOW,
            'watched_user_reason' => $watched_user_reason,
            'modcomment' => $modcomment,
        ], $site_config['expires']['user_cache']);
    }
    //=== Check if member was added
    if (mysqli_affected_rows($mysqli) > 0) {
        $H1_thingie = '<h1 class="has-text-centered">' . _fe('Success! {0} Added to the Watched Users List!', format_comment($user['username'])) . '</h1>';
        write_log(_fe('{0} Added {1} to the {2} watched users list{4}.', format_username($CURUSER['id']), format_username((int) $member_whos_been_bad), "<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=watched_users&amp;action=watched_users' class='is-link'>", '</a>'));
    }
}
$watched_users = $fluent->from('users')
                        ->select(null)
                        ->select('COUNT(id) AS count')
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

$res = sql_query('SELECT id, username, registered, watched_user_reason, watched_user, uploaded, downloaded, warned, status, donor, class, leechwarn, chatpost, pirate, king, invitedby FROM users WHERE watched_user != \'0\' ORDER BY ' . $ORDER_BY . $ASC) or sqlerr(__FILE__, __LINE__);
$how_many = mysqli_num_rows($res);
if ($how_many > 0) {
    $HTMLOUT .= '
        <form action="' . $_SERVER['PHP_SELF'] . '?tool=watched_users&amp;action=watched_users&amp;remove=1" method="post"  name="checkme" accept-charset="utf-8">
        <h1 class="has-text-centered">' . _('Watched Users') . '[ ' . $watched_users . ' ]</h1>
    <table class="table table-bordered table-striped">';
    $HTMLOUT .= '
    <tr>
        <td><a href="' . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=watched_users&amp;action=watched_users&amp;sort=watched_user&amp;ASC=' . $ASC . '">' . _('Added') . '</a></td>
        <td><a href="' . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=watched_users&amp;action=watched_users&amp;sort=username&amp;ASC=' . $ASC . '">' . _('Username') . '</a></td>
        <td class="has-text-left">' . _('Suspicion') . '</td>
        <td class="has-text-centered">' . _('Stats') . '</td>
        <td class="has-text-centered"><a href="' . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=watched_users&amp;action=watched_users&amp;sort=invited_by&amp;ASC=' . $ASC . '">' . _('Invited By') . '</a></td>
        ' . ($CURUSER['class'] >= UC_STAFF ? '
        <td class="has-text-centered">
            <input type="checkbox" id="checkThemAll" class="tooltipper" title="Select All">
        </td>' : '') . '
    </tr>';
    while ($arr = @mysqli_fetch_assoc($res)) {
        $invitor_arr = [];
        if ($arr['invitedby'] != 0) {
            $invitor_res = sql_query('SELECT id, username, donor, class, status, warned, leechwarn, chatpost, pirate, king FROM users WHERE id=' . sqlesc($arr['invitedby'])) or sqlerr(__FILE__, __LINE__);
            $invitor_arr = mysqli_fetch_assoc($invitor_res);
        }
        $the_flip_box = '
        <p>' . format_comment($arr['watched_user_reason']) . '</p>';
        $HTMLOUT .= '
    <tr>
        <td class="has-text-centered">' . get_date((int) $arr['watched_user'], '') . '</td>
        <td class="has-text-left">' . format_username((int) $arr['id']) . '</td>
        <td class="has-text-left">' . $the_flip_box . '</td>
        <td class="has-text-centered">' . member_ratio((float) $arr['uploaded'], (float) $arr['downloaded']) . '</td>
        <td class="has-text-centered">' . (empty($invitor_arr['username']) ? _('open sign-ups') : format_username((int) $arr['invitedby'])) . '</td>
        ' . ($CURUSER['class'] >= UC_STAFF ? '
        <td class="has-text-centered"><input type="checkbox" name="wu[]" value="' . (int) $arr['id'] . '"></td>' : '') . '
    </tr>';
    }

    $HTMLOUT .= '
        <tr>
            <td class="has-text-centered" colspan="6">
                <input type="submit" class="button is-small" value="' . _('remove selected from watched users') . '">
            </td>
        </tr>
    </table>
</form>';
} else {
    $HTMLOUT .= stdmsg(_('Error'), _('The watched members list is empty'));
}
$title = _('Watched Users');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
