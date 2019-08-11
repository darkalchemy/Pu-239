<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Message;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_freeusers'));
global $container, $site_config, $CURUSER;

$dt = TIME_NOW;
$HTMLOUT = '';
$remove = (isset($_GET['remove']) ? (int) $_GET['remove'] : 0);
if ($remove) {
    if (empty($remove)) {
        die($lang['freeusers_wtf']);
    }
    $res = sql_query('SELECT id, username, class FROM users WHERE free_switch != 0 AND id=' . sqlesc($remove)) or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = $usernames = $msgs_ids = [];
    if (mysqli_num_rows($res) > 0) {
        $msg = sqlesc($lang['freeusers_msg'] . $CURUSER['username'] . $lang['freeusers_period']);
        while ($arr = mysqli_fetch_assoc($res)) {
            $modcomment = sqlesc(get_date((int) $dt, 'DATE', 1) . $lang['freeusers_mod1'] . $CURUSER['username'] . " \n");
            $msgs_buffer[] = [
                'receiver' => $arr['id'],
                'added' => $dt,
                'msg' => $msg,
                'subject' => $lang['freeusers_msg_buffer'],
            ];
            $users_buffer[] = '(' . $arr['id'] . ',0,' . $modcomment . ')';
            $msgs_ids[] = $arr['id'];
            $usernames[] = $arr['username'];
        }
        if (count($msgs_buffer) > 0) {
            $messages_class = $container->get(Message::class);
            $messages_class->insert($msgs_buffer);
            sql_query('INSERT INTO users (id, free_switch, modcomment) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE KEY UPDATE free_switch = VALUES(free_switch), modcomment=concat(VALUES(modcomment),modcomment)') or sqlerr(__FILE__, __LINE__);
            foreach ($usernames as $username) {
                write_log("{$lang['freeusers_log1']} $remove ($username) {$lang['freeusers_log2']} $CURUSER[username]");
            }
            $cache = $container->get(Cache::class);
            foreach ($msgs_ids as $msg_id) {
                $cache->delete('user_' . $msg_id['id']);
            }
        }
    } else {
        die($lang['freeusers_fail']);
    }
}
$res2 = sql_query('SELECT id, username, class, free_switch FROM users WHERE free_switch != 0 ORDER BY username') or sqlerr(__FILE__, __LINE__);
$count = mysqli_num_rows($res2);
$perpage = 25;
$pager = pager($perpage, $count, "{$site_config['paths']['baseurl']}/staffpanel.php?tool=freeusers&amp;");
$res2 = sql_query('SELECT id, username, class, free_switch FROM users WHERE free_switch != 0 ORDER BY username ' . $pager['limit']) or sqlerr(__FILE__, __LINE__);

$HTMLOUT .= "<h1 class='has-text-centered'>{$lang['freeusers_head']} ($count)</h1>";
if ($count == 0) {
    $HTMLOUT .= main_div($lang['freeusers_nothing'], null, 'padding20 has-text-centered');
} else {
    $heading = "
        <tr>
            <th>{$lang['freeusers_username']}</th>
            <th>{$lang['freeusers_class']}</th>
            <th>{$lang['freeusers_expires']}</th>
            <th>{$lang['freeusers_remove']}</th>
        </tr>";
    $body = '';
    while ($arr2 = mysqli_fetch_assoc($res2)) {
        $body .= '
        <tr>
            <td>' . format_username((int) $arr2['id']) . '</td>
            <td>' . get_user_class_name((int) $arr2['class']);
        if (!has_access((int) $arr2['class'], UC_ADMINISTRATOR, 'coder') && $arr2['id'] != $CURUSER['id']) {
            $body .= "</td>
            <td>{$lang['freeusers_until']}" . get_date((int) $arr2['free_switch'], 'DATE') . '(' . mkprettytime($arr2['free_switch'] - $dt) . "{$lang['freeusers_togo']})" . "</td>
            <td><span class='has-text-danger'>{$lang['freeusers_notallowed']}</span></td>
        </tr>";
        } else {
            $body .= "</td>
            <td>{$lang['freeusers_until']}" . get_date((int) $arr2['free_switch'], 'DATE') . '(' . mkprettytime($arr2['free_switch'] - $dt) . "{$lang['freeusers_togo']})" . "</td>
            <td><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=freeusers&amp;action=freeusers&amp;remove=" . (int) $arr2['id'] . "' onclick=\"return confirm('{$lang['freeusers_confirm']}')\">{$lang['freeusers_rem']}</a></td>
        </tr>";
        }
    }
    $HTMLOUT .= ($count > $perpage ? $pager['pagertop'] : '') . main_table($body, $heading) . ($count > $perpage ? $pager['pagerbottom'] : '');
}
echo stdhead($lang['freeusers_stdhead']) . wrapper($HTMLOUT) . stdfoot();
die();
