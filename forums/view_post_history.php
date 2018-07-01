<?php

global $lang, $user_stuffs;

$post_id = (isset($_GET['post_id']) ? intval($_GET['post_id']) : (isset($_POST['post_id']) ? intval($_POST['post_id']) : 0));
$forum_id = (isset($_GET['forum_id']) ? intval($_GET['forum_id']) : (isset($_POST['forum_id']) ? intval($_POST['forum_id']) : 0));
$topic_id = (isset($_GET['topic_id']) ? intval($_GET['topic_id']) : (isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0));
if (!is_valid_id($post_id) || !is_valid_id($forum_id) || !is_valid_id($topic_id)) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}
$res = sql_query('SELECT p.added, p.body, p.edited_by, p.user_id AS poster_id, p.edit_date, p.post_title, p.icon, p.post_history, p.bbcode, p.anonymous, t.topic_name AS topic_name, f.name AS forum_name, u.id, u.username, u.class, u.donor, u.suspended, u.warned, u.enabled, u.avatar, u.offensive_avatar, u.chatpost, u.leechwarn, u.pirate, u.king FROM posts AS p LEFT JOIN topics AS t ON p.topic_id = t.id LEFT JOIN forums AS f ON t.forum_id = f.id LEFT JOIN users AS u ON p.user_id = u.id WHERE ' . ($CURUSER['class'] < UC_STAFF ? 'p.status = \'ok\' AND t.status = \'ok\' AND' : ($CURUSER['class'] < $min_delete_view_class ? 'p.status != \'deleted\' AND t.status != \'deleted\'  AND' : '')) . ' p.id = ' . sqlesc($post_id)) or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_array($res);
$arr_edited = $user_stuffs->getUserFromId($arr['edited_by']);
$icon = htmlsafechars($arr['icon']);
$post_title = htmlsafechars($arr['post_title'], ENT_QUOTES);
$location_bar = '<h1><a class="altlink" href="' . $site_config['baseurl'] . '/forums.php">' . $lang['fe_forums'] . '</a> <img src="' . $site_config['pic_baseurl'] . 'arrow_next.gif" alt="&#9658;" title="&#9658;" class="emoticon">
        <a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_forum&amp;forum_id=' . $forum_id . '">' . htmlsafechars($arr['forum_name'], ENT_QUOTES) . '</a>
        <img src="' . $site_config['pic_baseurl'] . 'arrow_next.gif" alt="&#9658;" title="&#9658;" class="emoticon">
        <a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '">' . htmlsafechars($arr['topic_name'], ENT_QUOTES) . '</a></h1>
        <span>' . $mini_menu . '</span><br><br>';
$HTMLOUT .= $location_bar;
$HTMLOUT .= '<h1>' . ($arr['anonymous'] === 'yes' ? '<i>' . get_anonymous_name() . '</>' : format_username($arr['id'])) . '\'s ' . $lang['vph_final_edit_post'] . '. ' . $lang['vph_last_edit_by'] . ': ' . ($arr['anonymous'] === 'yes' ? '<i>' . get_anonymous_name() . '</i>' : format_username($arr_edited['id'])) . '</h1>
    <table border="0" cellspacing="5" cellpadding="10" width="90%">
    <tr>
    <td class="forum_head" align="left" width="120px" valign="middle">
    <span style="white-space:nowrap;">#' . $post_id . '
    <span style="font-weight: bold;">' . ($arr['anonymous'] === 'yes' ? '<i>' . get_anonymous_name() . '</i>' : htmlsafechars($arr['username'])) . '</span></span></td>
    <td class="forum_head" align="left" valign="middle">
    <span style="white-space:nowrap;"> ' . $lang['fe_posted_on'] . ': ' . get_date($arr['added'], '') . ' [' . get_date($arr['added'], '', 0, 1) . '] GMT ' . (!empty($post_title) ? '&nbsp;&nbsp;&nbsp;&nbsp; ' . $lang['fe_title'] . ': <span style="font-weight: bold;">' . $post_title . '</span>' : '') . (!empty($icon) ? ' <img src="' . $site_config['pic_baseurl'] . 'smilies/' . $icon . '.gif" alt="' . $icon . '" title="' . $icon . '" class="emoticon">' : '') . '</span>
    </td></tr>
    <tr>
    <td class="has-text-centered w-15 mw-150" valign="top">' . get_avatar($arr) . '<br>' . ($arr['anonymous'] === 'yes' ? '<i>' . get_anonymous_name() . '</i>' : format_username($arr['id'])) . '</td>
    <td align="left" valign="top" colspan="2">' . ($arr['bbcode'] === 'yes' ? format_comment($arr['body']) : format_comment_no_bbcode($arr['body'])) . '</td>
    </tr>
    </table><br><h1>' . $lang['fe_post_history'] . '</h1>[ ' . $lang['vph_all_post_edits_date'] . '. ]<br><br>' . htmlspecialchars_decode($arr['post_history']) . '<br>' . $location_bar;
