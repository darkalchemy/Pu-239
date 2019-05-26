<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Mood;
use Pu239\Session;
use Pu239\User;
use Spatie\Image\Exceptions\InvalidManipulation;

/**
 * @param        $rows
 * @param string $variant
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws InvalidManipulation
 * @throws Exception
 *
 * @return string|null
 */
function commenttable($rows, $variant = 'torrent')
{
    global $container, $CURUSER, $site_config;

    $user_stuffs = $container->get(User::class);
    require_once INCL_DIR . 'function_users.php';
    require_once INCL_DIR . 'function_html.php';
    $lang = load_language('torrenttable_functions');
    $count = 0;
    $variant_options = [
        'torrent' => 'comment',
        'request' => 'requests',
        'offer' => 'offers',
        'usercomment' => 'usercomment',
    ];
    if (isset($variant_options[$variant])) {
        $type = $variant_options[$variant];
    } else {
        return null;
    }
    $extra_link = ($variant === 'request' ? '&amp;type=request' : ($variant === 'offer' ? '&amp;type=offer' : ''));
    $delete = ($variant === 'request' || $variant === 'offer') ? 'action=delete_comment' : 'action=delete';
    $htmlout = '';
    $i = 0;
    $variantc = '';
    foreach ($rows as $row) {
        $cid = $row['id'];
        if ($variant === 'torrent') {
            $variantc = 'comment';
        }
        $usersdata = $user_stuffs->getUserFromId($row['user']);
        $this_text = '';
        $mood = $container->get(Mood::class);
        $moods = $mood->get();
        $moodname = (isset($moods['name'][$usersdata['mood']]) ? htmlsafechars($moods['name'][$usersdata['mood']]) : 'is feeling neutral');
        $moodpic = (isset($moods['image'][$usersdata['mood']]) ? htmlsafechars($moods['image'][$usersdata['mood']]) : 'noexpression.gif');
        $this_text .= "
            <div>
                <span class='level-left padding10'>#{$row['id']} {$lang['commenttable_by']} ";
        $likes = $att_str = '';
        $likers = $user_likes = [];
        if ($row['user_likes'] > 0) {
            $cache = $container->get(Cache::class);
            $user_likes = $cache->get("{$type}_user_likes_" . $cid);
            if ($user_likes === false || is_null($user_likes)) {
                $fluent = $container->get(Database::class);
                $likes = $fluent->from('likes')
                                ->select(null)
                                ->select('user_id')
                                ->where("{$variantc}_id = ?", $cid);
                foreach ($likes as $like) {
                    $user_likes[] = $like['user_id'];
                }
                $cache->set("{$type}_user_likes_" . $cid, $user_likes, 86400);
            }
            if ($user_likes) {
                foreach ($user_likes as $userid) {
                    $likers[] = format_username((int) $userid);
                }
                $likes = implode(', ', $likers);
                $count = count($user_likes);
            }
        }
        if (!empty($likes) && $count > 0) {
            if (in_array($CURUSER['id'], $user_likes)) {
                if ($count === 1) {
                    $att_str = "<span class='chg'>You like this</span>";
                } else {
                    $att_str = "<span class='chg'>you and " . ($count - 1) . ' other' . !plural($count - 1) . ' like' . plural($count - 1) . ' this</span>';
                }
            } else {
                $att_str = $likes . ' like' . plural($count) . ' this';
            }
        }
        $wht = $count > 0 && in_array($CURUSER['id'], $user_likes) ? 'unlike' : 'like';
        if (isset($row['user'])) {
            if ($row['anonymous'] === 'yes') {
                $this_text .= ($CURUSER['class'] >= UC_STAFF ? get_anonymous_name() . ' - Posted by: ' . format_username((int) $row['user']) : get_anonymous_name());
            } else {
                $title = empty($usersdata['title']) ? get_user_class_name($usersdata['class']) : htmlsafechars($usersdata['title']);
                $this_text .= "<span class='left5 tooltipper' title='$title'>" . format_username((int) $row['user']) . '</span>';
                $this_text .= '
                    <a href="javascript:;" onclick="PopUp(\'usermood.php\',\'Mood\',530,500,1,1);" class="left5">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/' . $moodpic . '" alt="' . $moodname . '" class="tooltipper" title="' . ($row['anonymous'] === 'yes' ? '<i>' . get_anonymous_name() . '</i>' : htmlsafechars($usersdata['username'])) . ' ' . $moodname . '!">
                    </a>';
            }
        } else {
            $this_text .= '<i>(' . $lang['commenttable_orphaned'] . ')</i></a>';
        }
        $this_text .= "<span class='left5'>" . get_date((int) $row['added'], '') . '</span>';
        $row['id'] = (int) $row['id'];
        $tid = !empty($row[$variant]) ? "&amp;tid={$row[$variant]}" : '';
        $session = $container->get(Session::class);
        $this_text .= ($row['user'] == $CURUSER['id'] || $CURUSER['class'] >= UC_STAFF ? "
                    <a href='{$site_config['paths']['baseurl']}/{$type}.php?action=edit&amp;cid={$row['id']}{$extra_link}{$tid}' class='button is-small left10'>{$lang['commenttable_edit']}</a>" : '') . ($CURUSER['class'] >= UC_VIP ? "
                    <a href='{$site_config['paths']['baseurl']}/report.php?type=Comment&amp;id={$row['id']}' class='button is-small left10'>Report this Comment</a>" : '') . ($CURUSER['class'] >= UC_STAFF ? "
                    <a href='{$site_config['paths']['baseurl']}/{$type}.php?{$delete}&amp;cid={$row['id']}{$extra_link}{$tid}' class='button is-small left10'>{$lang['commenttable_delete']}</a>" : '') . ($row['editedby'] && $CURUSER['class'] >= UC_STAFF ? "
                    <a href='{$site_config['paths']['baseurl']}/{$type}.php?action=vieworiginal&amp;cid={$row['id']}{$extra_link}{$tid}' class='button is-small left10'>{$lang['commenttable_view_original']}</a>" : '') . "
                    <span data-id='{$cid}' data-type='{$variant}' class='mlike button is-small left10'>" . ucfirst($wht) . "</span>
                    <span class='tot-{$cid} left10'>{$att_str}</span>
                </span>
            </div>";
        $avatar = get_avatar($row);
        $text = format_comment($row['text']);
        if ($row['editedby']) {
            $text = "
            <div class='flex-vertical comments h-100'>
                <div>$text</div>
                <div class='size_3'>{$lang['commenttable_last_edited_by']} " . format_username((int) $row['editedby']) . " {$lang['commenttable_last_edited_at']} " . get_date((int) $row['editedat'], 'DATE') . '</div>
            </div>';
        }
        $top = $i++ >= 1 ? 'top20' : '';
        $htmlout .= main_div("
            <a id='comm{$row['id']}'></a>
            $this_text
            <div class='columns is-marginless'>
                <span class='round10 bg-02 column is-one-fifth has-text-centered img-avatar'>
                    {$avatar}
                    <div>" . get_reputation($row['user'], 'comments', true, 0, $row['anonymous']) . "</div>
                </span>
                <span class='bg-02 round10 column'>
                    $text
                </span>
            </div>", $top);
    }

    return $htmlout;
}
