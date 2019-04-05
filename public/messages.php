<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_user_options.php';
require_once CLASS_DIR . 'class_user_options_2.php';
check_user_status();
global $CURUSER, $site_config, $cache, $session, $fluent;

$stdhead = [
    'css' => [
        get_file_name('sceditor_css'),
    ],
];
$stdfoot = [
    'js' => [
        get_file_name('sceditor_js'),
        get_file_name('user_search_js'),
    ],
];
$lang = array_merge(load_language('global'), load_language('takesignup'), load_language('pm'));
$HTMLOUT = $count2 = $other_box_info = $maxpic = $maxbox = '';

$maxbox = 100 * ($CURUSER['class'] + 1);
$maxboxes = 5 * ($CURUSER['class'] + 1);

$returnto = !empty($_GET['returnto']) ? $_GET['returnto'] : !empty($_POST['returnto']) ? $_POST['returnto'] : '/index.php';
$possible_actions = [
    'view_mailbox',
    'use_draft',
    'new_draft',
    'save_or_edit_draft',
    'view_message',
    'move',
    'forward',
    'forward_pm',
    'edit_mailboxes',
    'delete',
    'search',
    'move_or_delete_multi',
    'send_message',
];
$action = (isset($_GET['action']) ? htmlsafechars($_GET['action']) : (isset($_POST['action']) ? htmlsafechars($_POST['action']) : 'view_mailbox'));
if (!in_array($action, $possible_actions)) {
    stderr($lang['pm_error'], $lang['pm_error_ruffian']);
}

$change_pm_number = (isset($_GET['change_pm_number']) ? intval($_GET['change_pm_number']) : (isset($_POST['change_pm_number']) ? intval($_POST['change_pm_number']) : 0));
$page = (isset($_GET['page']) ? intval($_GET['page']) : 0);
$perpage = (isset($_GET['perpage']) ? intval($_GET['perpage']) : ($CURUSER['pms_per_page'] > 0 ? $CURUSER['pms_per_page'] : 15));
$mailbox = (isset($_GET['box']) ? intval($_GET['box']) : (isset($_POST['box']) ? intval($_POST['box']) : 1));
$pm_id = (isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0));
$save = ((isset($_POST['save']) && $_POST['save'] === 1) ? '1' : '0');
$urgent = ((isset($_POST['urgent']) && $_POST['urgent'] === 'yes') ? 'yes' : 'no');
$desc_asc = (isset($_GET['ASC']) ? '&amp;DESC=1' : (isset($_GET['DESC']) ? '&amp;ASC=1' : ''));
$desc_asc_2 = (isset($_GET['DESC']) ? 'ascending' : 'descending');
$spacer = '&#160;&#160;&#160;&#160;';
$good_order_by = [
    'username',
    'added',
    'subject',
    'id',
];
$order_by = (isset($_GET['order_by']) ? htmlsafechars($_GET['order_by']) : 'added');
if (!in_array($order_by, $good_order_by)) {
    stderr($lang['pm_error'], $lang['pm_error_temp']);
}

$top_links = '
    <div class="bottom20">
        <ul class="level-center bg-06">
            <li class="altlink margin10"><a href="' . $site_config['paths']['baseurl'] . '/messages.php?action=search">' . $lang['pm_search'] . '</a></li>
            <li class="altlink margin10"><a href="' . $site_config['paths']['baseurl'] . '/messages.php?action=edit_mailboxes">' . $lang['pm_manager'] . '</a></li>
            <li class="altlink margin10"><a href="' . $site_config['paths']['baseurl'] . '/messages.php?action=send_message">Send Message</a></li>
            <li class="altlink margin10"><a href="' . $site_config['paths']['baseurl'] . '/messages.php?action=new_draft">' . $lang['pm_write_new'] . '</a></li>
            <li class="altlink margin10"><a href="' . $site_config['paths']['baseurl'] . '/messages.php?action=view_mailbox">' . $lang['pm_in_box'] . '</a></li>
        </ul>
    </div>';

if (isset($_GET['change_pm_number'])) {
    $change_pm_number = (isset($_GET['change_pm_number']) ? intval($_GET['change_pm_number']) : 20);
    sql_query('UPDATE users SET pms_per_page = ' . sqlesc($change_pm_number) . ' WHERE id=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $cache->update_row('user_' . $CURUSER['id'], [
        'pms_per_page' => $change_pm_number,
    ], $site_config['expires']['user_cache']);
    if (isset($_GET['edit_mail_boxes'])) {
        header('Location: messages.php?action=edit_mailboxes&pm=1');
    } else {
        header('Location: messages.php?action=view_mailbox&pm=1&box=' . $mailbox);
    }
    die();
}

if (isset($_GET['show_pm_avatar'])) {
    $setbits = $clrbits = 0;
    if ($_GET['show_pm_avatar'] === 'yes') {
        $setbits |= user_options_2::SHOW_PM_AVATAR;
    } else {
        $clrbits |= user_options_2::SHOW_PM_AVATAR;
    }

    if ($setbits || $clrbits) {
        $sql = 'UPDATE users SET opt2 = ((opt2 | ' . $setbits . ') & ~' . $clrbits . ') WHERE id=' . sqlesc($CURUSER['id']);
        sql_query($sql) or sqlerr(__FILE__, __LINE__);
    }
    $opt2 = $fluent->from('users')
        ->select(null)
        ->select('opt2')
        ->where('id=?', $CURUSER['id'])
        ->fetch('opt2');

    $cache->update_row('user_' . $CURUSER['id'], [
        'opt2' => $opt2,
    ], $site_config['expires']['user_cache']);

    if (isset($_GET['edit_mail_boxes'])) {
        header('Location: messages.php?action=edit_mailboxes&avatar=1');
    } else {
        header('Location: messages.php?action=view_mailbox&avatar=1&box=' . $mailbox);
    }
    die();
}

isset($_GET['deleted']) ? $session->set('is-success', $lang['pm_deleted']) : null;
isset($_GET['avatar']) ? $session->set('is-success', $lang['pm_avatar']) : null;
isset($_GET['pm']) ? $session->set('is-success', $lang['pm_changed']) : null;
isset($_GET['singlemove']) ? $session->set('is-success', $lang['pm_moved']) : null;
isset($_GET['multi_move']) ? $session->set('is-success', $lang['pm_moved_s']) : null;
isset($_GET['multi_delete']) ? $session->set('is-success', $lang['pm_deleted_s']) : null;
isset($_GET['forwarded']) ? $session->set('is-success', $lang['pm_forwarded']) : null;
isset($_GET['boxes']) ? $session->set('is-success', $lang['pm_box_added']) : null;
isset($_GET['name']) ? $session->set('is-success', $lang['pm_box_updated']) : null;
isset($_GET['new_draft']) ? $session->set('is-success', $lang['pm_draft_saved']) : null;
isset($_GET['sent']) ? $session->set('is-success', $lang['pm_msg_sent']) : null;
isset($_GET['pms']) ? $session->set('is-success', $lang['pm_msg_sett']) : null;

$mailbox_name = ($mailbox === $site_config['pm']['inbox'] ? $lang['pm_inbox'] : ($mailbox === $site_config['pm']['sent'] ? $lang['pm_sentbox'] : ($mailbox === $site_config['pm']['deleted'] ? $lang['pm_deleted'] : $lang['pm_drafts'])));
switch ($action) {
    case 'view_mailbox':
        require_once PM_DIR . 'view_mailbox.php';
        break;

    case 'view_message':
        require_once PM_DIR . 'view_message.php';
        break;

    case 'send_message':
        require_once PM_DIR . 'send_message.php';
        break;

    case 'move':
        require_once PM_DIR . 'move.php';
        break;

    case 'delete':
        require_once PM_DIR . 'delete.php';
        break;

    case 'move_or_delete_multi':
        require_once PM_DIR . 'move_or_delete_multi.php';
        break;

    case 'forward':
        require_once PM_DIR . 'forward.php';
        break;

    case 'forward_pm':
        require_once PM_DIR . 'forward_pm.php';
        break;

    case 'new_draft':
        require_once PM_DIR . 'new_draft.php';
        break;

    case 'save_or_edit_draft':
        require_once PM_DIR . 'save_or_edit_draft.php';
        break;

    case 'use_draft':
        require_once PM_DIR . 'use_draft.php';
        break;

    case 'search':
        require_once PM_DIR . 'search.php';
        break;

    case 'edit_mailboxes':
        require_once PM_DIR . 'edit_mailboxes.php';
        break;
}

/**
 * @param int $box
 *
 * @return array|string
 *
 * @throws \Envms\FluentPDO\Exception
 */
function get_all_boxes($box = 1)
{
    global $CURUSER, $site_config, $lang, $cache, $fluent;

    $get_all_boxes = $cache->get('get_all_boxes_' . $CURUSER['id']);
    if ($get_all_boxes === false || is_null($get_all_boxes)) {
        $get_all_boxes = $fluent->from('pmboxes')
            ->select(null)
            ->select('boxnumber')
            ->select('name')
            ->where('userid=?', $CURUSER['id'])
            ->orderBy('boxnumber')
            ->fetchAll();

        $cache->set('get_all_boxes_' . $CURUSER['id'], $get_all_boxes, $site_config['expires']['get_all_boxes']);
    }
    $box = (int) $box;
    $boxes = "
        <select name='boxx' class='right10'>
            <option value='10000'>{$lang['pm_search_move_to']}</option>" . ($box !== 1 ? "
            <option value='1'>{$lang['pm_inbox']}</option>" : '') . ($box !== -1 ? "
            <option value='-1'>{$lang['pm_sentbox']}</option>" : '') . ($box !== -2 ? "
            <option value='-2'>{$lang['pm_drafts']}</option>" : '') . ($box !== 0 ? "
            <option value='0'>{$lang['pm_deleted']}</option>" : '');
    if (!empty($get_all_boxes)) {
        foreach ($get_all_boxes as $boxx) {
            $boxes .= $box === (int) $boxx['boxnumber'] ? '' : "
            <option value='{$boxx['boxnumber']}'>" . htmlsafechars($boxx['name']) . '</option>';
        }
    }
    $boxes .= '
        </select>';

    return $boxes;
}

/**
 * @param $mailbox
 *
 * @return array|string
 */
function insertJumpTo($mailbox)
{
    global $CURUSER, $site_config, $lang, $cache;

    $cache->delete('insertJumpTo_' . $CURUSER['id']);
    $insertJumpTo = $cache->get('insertJumpTo_' . $CURUSER['id']);
    if ($insertJumpTo === false || is_null($insertJumpTo)) {
        $res = sql_query('SELECT boxnumber,name FROM pmboxes WHERE userid=' . sqlesc($CURUSER['id']) . ' ORDER BY boxnumber') or sqlerr(__FILE__, __LINE__);
        $insertJumpTo = '
            <form action="messages.php" method="get" accept-charset="utf-8">
                <input type="hidden" name="action" value="view_mailbox">
                <label for="box">' . $lang['pm_jump_to'] . '
                    <select name="box" onchange="location=this.options[this.selectedIndex].value;">
                        <option value="' . $site_config['paths']['baseurl'] . '/messages.php?action=view_mailbox&amp;box=1"' . ($mailbox === 1 ? ' selected' : '') . '>' . $lang['pm_inbox'] . '</option>
                        <option value="' . $site_config['paths']['baseurl'] . '/messages.php?action=view_mailbox&amp;box=-1"' . ($mailbox === -1 ? ' selected' : '') . '>' . $lang['pm_sentbox'] . '</option>
                        <option value="' . $site_config['paths']['baseurl'] . '/messages.php?action=view_mailbox&amp;box=-2"' . ($mailbox === -2 ? ' selected' : '') . '>' . $lang['pm_drafts'] . '</option>
                        <option value="' . $site_config['paths']['baseurl'] . '/messages.php?action=view_mailbox&amp;box=0"' . ($mailbox === 0 ? ' selected' : '') . '>' . $lang['pm_deleted'] . '</option>';
        while ($row = mysqli_fetch_assoc($res)) {
            $insertJumpTo .= '
                        <option value="' . $site_config['paths']['baseurl'] . '/messages.php?action=view_mailbox&amp;box=' . (int) $row['boxnumber'] . '"' . ($mailbox === (int) $row['boxnumber'] ? ' selected' : '') . '>' . htmlsafechars($row['name']) . '</option>';
        }
        $insertJumpTo .= '
                    </select>
                </label>
            </form>';
        $cache->set('insertJumpTo_' . $CURUSER['id'], $insertJumpTo, $site_config['expires']['insertJumpTo']);
    }

    return $insertJumpTo;
}

echo stdhead($lang['pm_stdhead'], $stdhead) . wrapper($HTMLOUT, 'has-text-centered') . stdfoot($stdfoot);
