<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_comments'));
global $site_config;

$view = isset($_GET['view']) ? htmlsafechars($_GET['view']) : '';
$queryString = explode('=', $_SERVER['QUERY_STRING']);
$queryString = array_reverse($queryString);
$nav = "
                <div class='bottom10'>
                    <ul class='tabs'>
                        <li><a" . ($queryString[0] === 'comments' ? " class='active'" : '') . " href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=comments'>{$lang['text_overview']}</a></li>
                        <li><a" . ($queryString[0] === 'allComments' ? " class='active'" : '') . " href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=comments&amp;view=allComments'>{$lang['text_all']}</a></li>
                        <li><a" . ($queryString[0] === 'search' || $queryString[0] === 'results' ? " class='active'" : '') . " href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=comments&amp;view=search'>{$lang['text_search']}</a></li>
                    </ul>
                </div>";

$heading = "
                <tr>
                    <th>{$lang['text_comm_id']}</th>
                    <th>{$lang['text_user_id']}</th>
                    <th>{$lang['text_torr_id']}</th>
                    <th>{$lang['text_comm']}</th>
                    <th>{$lang['text_comm_ori']}</th>
                    <th>{$lang['text_user']}</th>
                    <th>{$lang['text_torr']}</th>
                    <th>{$lang['text_added']}</th>
                    <th>{$lang['text_actions']}</th>
                </tr>";

/**
 * @param $comment
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return string
 */
function format_data($comment)
{
    global $site_config, $lang;

    $comment = [
        'user' => (int) $comment['user'],
        'torrent' => (int) $comment['torrent'],
        'id' => (int) $comment['id'],
        'text' => format_comment($comment['text']),
        'ori_text' => format_comment($comment['ori_text']),
        'username' => format_comment($comment['username']),
        'name' => format_comment($comment['name']),
        'added' => (int) $comment['added'],
    ];

    return "
                <tr>
                    <td><a href='{$site_config['paths']['baseurl']}/details.php?id={$comment['torrent']}#comm{$comment['id']}'>{$comment['id']}</a> (<a href='{$site_config['paths']['baseurl']}/comment.php?action=vieworiginal&amp;cid={$comment['id']}'>{$lang['text_view_ori_comm']}</a>)</td>
                    <td>{$comment['user']}</td>
                    <td>{$comment['torrent']}</td>
                    <td>{$comment['text']}</td>
                    <td>{$comment['ori_text']}</td>
                    <td>" . format_username((int) $comment['user']) . " [<a href='{$site_config['paths']['baseurl']}/messages.php?action=send_message&amp;receiver={$comment['user']}'>{$lang['text_msg']}</a>]</td>
                    <td><a href='{$site_config['paths']['baseurl']}/details.php?id={$comment['torrent']}'>{$comment['name']}</a></td>
                    <td>" . get_date((int) $comment['added'], 'DATE') . "</td>
                    <td><a href='{$site_config['paths']['baseurl']}/comment.php?action=edit&amp;cid={$comment['id']}'>{$lang['text_edit']}</a> - <a href='{$site_config['paths']['baseurl']}/comment.php?action=delete&amp;cid={$comment['id']}'>{$lang['text_delete']}</a></td>
                </tr>";
}

switch ($view) {
    case 'allComments':
        $sql = 'SELECT c.id, c.user, c.torrent, c.text, c.ori_text, c.added, t.name, u.username FROM comments AS c JOIN users AS u ON u.id=c.user JOIN torrents AS t ON  c.torrent = t.id ORDER BY c.id DESC';
        $query = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $rows = mysqli_num_rows($query);

        $HTMLOUT = "
                <h1 class='has-text-centered'>{$lang['text_all_comm']}</h1>" . $nav;

        $body = '';
        while ($comment = mysqli_fetch_assoc($query)) {
            $body .= format_data($comment);
        }

        if ($rows == 0) {
            $body .= "
                <tr>
                    <td colspan='9'><div class='padding20'>{$lang['text_no_rows']}</div></td>
                </tr>";
        }

        $HTMLOUT .= main_table($body, $heading);

        echo stdhead($lang['text_all_comm']) . wrapper($HTMLOUT) . stdfoot();
        die();
        break;

    case 'search':
        $HTMLOUT = "
        <form method='post' action='{$_SERVER['PHP_SELF']}?tool=comments&amp;view=results' enctype='multipart/form-data' accept-charset='utf-8'>
            <h1 class='has-text-centered'>{$lang['text_search']}</h1>" . $nav;

        $body = "
            <tr>
                <td>{$lang['text_keywords']}</td>
                <td>
                    <input type='text' name='keywords' class='w-100'>
                </td>
            </tr>
            <tr>
                <td colspan='2' class='has-text-centered'>
                    <input type='submit' value='{$lang['text_submit']}' class='button is-small'>
                </td>
            </tr>";
        $HTMLOUT .= main_table($body) . '
        </form>';

        echo stdhead($lang['text_search']) . wrapper($HTMLOUT) . stdfoot();
        die();
        break;

    case 'results':
        $sql = 'SELECT c.id, c.user, c.torrent, c.text, c.added, t.name, u.username FROM comments AS c JOIN users AS u ON u.id=c.user JOIN torrents AS t ON c.torrent = t.id WHERE c.text LIKE ' . sqlesc("%{$_POST['keywords']}%") . ' ORDER BY c.added DESC';
        $query = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $rows = mysqli_num_rows($query);

        $HTMLOUT = "
                <h1 class='has-text-centered'>{$lang['text_results']}: " . format_comment($_POST['keywords']) . '</h1>' . $nav;

        $body = '';
        while ($comment = mysqli_fetch_assoc($query)) {
            $body .= format_data($comment);
        }

        if ($rows == 0) {
            $body .= "
                <tr>
                    <td colspan='9'><div class='padding20'>{$lang['text_no_rows']}</div></td>
                </tr>";
        }

        $HTMLOUT .= main_table($body, $heading);

        echo stdhead($lang['text_results'] . $_POST['keywords']) . wrapper($HTMLOUT) . stdfoot();
        die();
        break;
}

$sql = 'SELECT c.id, c.user, c.torrent, c.text, c.ori_text, c.added, c.checked_by, c.checked_when, t.name, u.username FROM comments AS c JOIN users AS u ON u.id=c.user JOIN torrents AS t ON  c.torrent = t.id ORDER BY c.id DESC LIMIT 10';
$query = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$rows = mysqli_num_rows($query);

$HTMLOUT = "
                <h1 class='has-text-centered'>{$lang['text_all_comm']}</h1>" . $nav;

$body = '';
while ($comment = mysqli_fetch_assoc($query)) {
    $body .= format_data($comment);
}

if ($rows == 0) {
    $body .= "
                <tr>
                    <td colspan='9'><div class='padding20'>{$lang['text_no_rows']}</div></td>
                </tr>";
}

$HTMLOUT .= main_table($body, $heading);

echo stdhead($lang['text_overview']) . wrapper($HTMLOUT) . stdfoot();
