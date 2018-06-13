<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'pager_new.php';
require_once CLASS_DIR . 'class_user_options.php';
require_once CLASS_DIR . 'class_user_options_2.php';
check_user_status();
global $CURUSER, $site_config, $cache, $session;

/*
 *
 */
define('PM_DELETED', 0); // Message was deleted
/*
 *
 */
define('PM_INBOX', 1); // Message located in Inbox for reciever
/*
 *
 */
define('PM_SENTBOX', -1); // GET value for sent box
/*
 *
 */
define('PM_DRAFTS', -2); //  new drafts folder
$lang    = array_merge(load_language('global'), load_language('takesignup'), load_language('pm'));
$stdhead = [
    'css' => [
    ],
];
$HTMLOUT = $count2 = $other_box_info = $maxpic = $maxbox = '';

$maxbox   = 100 * ($CURUSER['class'] + 1);
$maxboxes = 5 * ($CURUSER['class'] + 1);

$returnto         = isset($_GET['returnto']) ? $_GET['returnto'] : isset($_POST['returnto']) ? $_POST['returnto'] : '/index.php';
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
$page             = (isset($_GET['page']) ? intval($_GET['page']) : 0);
$perpage          = (isset($_GET['perpage']) ? intval($_GET['perpage']) : ($CURUSER['pms_per_page'] > 0 ? $CURUSER['pms_per_page'] : 20));
$mailbox          = (isset($_GET['box']) ? intval($_GET['box']) : (isset($_POST['box']) ? intval($_POST['box']) : 1));
$pm_id            = (isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0));
$save = ((isset($_POST['save']) && $_POST['save'] === 1) ? '1' : '0');
$urgent = ((isset($_POST['urgent']) && $_POST['urgent'] === 'yes') ? 'yes' : 'no');
$desc_asc         = (isset($_GET['ASC']) ? '&amp;DESC=1' : (isset($_GET['DESC']) ? '&amp;ASC=1' : ''));
$desc_asc_2       = (isset($_GET['DESC']) ? 'ascending' : 'descending');
$spacer           = '&#160;&#160;&#160;&#160;';
$good_order_by    = [
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
            <li class="altlink margin20"><a href="' . $site_config['baseurl'] . '/pm_system.php?action=search">' . $lang['pm_search'] . '</a></li>
            <li class="altlink margin20"><a href="' . $site_config['baseurl'] . '/pm_system.php?action=edit_mailboxes">' . $lang['pm_manager'] . '</a></li>
            <li class="altlink margin20"><a href="' . $site_config['baseurl'] . '/pm_system.php?action=send_message">Send Message</a></li>
            <li class="altlink margin20"><a href="' . $site_config['baseurl'] . '/pm_system.php?action=new_draft">' . $lang['pm_write_new'] . '</a></li>
            <li class="altlink margin20"><a href="' . $site_config['baseurl'] . '/pm_system.php?action=view_mailbox">' . $lang['pm_in_box'] . '</a></li>
        </ul>
    </div>';

if (isset($_GET['change_pm_number'])) {
    $change_pm_number = (isset($_GET['change_pm_number']) ? intval($_GET['change_pm_number']) : 20);
    sql_query('UPDATE users SET pms_per_page = ' . sqlesc($change_pm_number) . ' WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $cache->update_row('user' . $CURUSER['id'], [
        'pms_per_page' => $change_pm_number,
    ], $site_config['expires']['user_cache']);
    if (isset($_GET['edit_mail_boxes'])) {
        header('Location: pm_system.php?action=edit_mailboxes&pm=1');
    } else {
        header('Location: pm_system.php?action=view_mailbox&pm=1&box=' . $mailbox);
    }
    die();
}

if (isset($_GET['show_pm_avatar'])) {
    $show_pm_avatar = ($_GET['show_pm_avatar'] === 'yes' ? 'yes' : 'no');
    sql_query('UPDATE users SET show_pm_avatar = ' . sqlesc($show_pm_avatar) . ' WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $cache->update_row('user' . $CURUSER['id'], [
        'show_pm_avatar' => $show_pm_avatar,
    ], $site_config['expires']['user_cache']);
    if (isset($_GET['edit_mail_boxes'])) {
        header('Location: pm_system.php?action=edit_mailboxes&avatar=1');
    } else {
        header('Location: pm_system.php?action=view_mailbox&avatar=1&box=' . $mailbox);
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

$mailbox_name = ($mailbox === PM_INBOX ? $lang['pm_inbox'] : ($mailbox === PM_SENTBOX ? $lang['pm_sentbox'] : $lang['pm_drafts']));
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
 */
function get_all_boxes($box = 1)
{
    global $CURUSER, $site_config, $lang, $cache;

    $get_all_boxes = $cache->get('get_all_boxes_' . $CURUSER['id']);
    if ($get_all_boxes === false || is_null($get_all_boxes)) {
        $res = sql_query('SELECT boxnumber, name FROM pmboxes WHERE userid = ' . sqlesc($CURUSER['id']) . ' ORDER BY boxnumber') or sqlerr(__FILE__, __LINE__);
        while ($row = mysqli_fetch_assoc($res)) {
            $get_all_boxes[] = $row;
        }
        $cache->set('get_all_boxes_' . $CURUSER['id'], $get_all_boxes, $site_config['expires']['get_all_boxes']);
    }
    $boxes = "
        <select name='box' class='right10'>
            <option value='1'" . ($box === 1 ? 'selected' : '') . ">{$lang['pm_inbox']}</option>
            <option value='-1'" . ($box === -1 ? 'selected' : '') . ">{$lang['pm_sentbox']}</option>
            <option value='-2'" . ($box === -2 ? 'selected' : '') . ">{$lang['pm_drafts']}</option>";
    if (!empty($get_all_boxes)) {
        foreach ($get_all_boxes as $box) {
            $boxes .= "
            <option value='{$box['boxnumber']}'" . ($box === (int) $box['boxnumber'] ? 'selected' : '') . '>' . htmlsafechars($box['name']) . '</option>';
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

    $insertJumpTo = $cache->get('insertJumpTo' . $CURUSER['id']);
    if ($insertJumpTo === false || is_null($insertJumpTo)) {
        $res          = sql_query('SELECT boxnumber,name FROM pmboxes WHERE userid=' . sqlesc($CURUSER['id']) . ' ORDER BY boxnumber') or sqlerr(__FILE__, __LINE__);
        $insertJumpTo = '<form action="pm_system.php" method="get">
                                    <input type="hidden" name="action" value="view_mailbox" />
                                    <select name="box" onchange="location = this.options[this.selectedIndex].value;">
                                    <option class="head" value="">' . $lang['pm_jump_to'] . '</option>
                                    <option value="pm_system.php?action=view_mailbox&amp;box=1" ' . ($mailbox == '1' ? 'selected' : '') . '>' . $lang['pm_inbox'] . '</option>
                                    <option value="pm_system.php?action=view_mailbox&amp;box=-1" ' . ($mailbox == '-1' ? 'selected' : '') . '>' . $lang['pm_sentbox'] . '</option>
                                    <option value="pm_system.php?action=view_mailbox&amp;box=-2" ' . ($mailbox == '-2' ? 'selected' : '') . '>' . $lang['pm_drafts'] . '</option>';
        while ($row = mysqli_fetch_assoc($res)) {
            $insertJumpTo .= '<option value="pm_system.php?action=view_mailbox&amp;box=' . (int) $row['boxnumber'] . '" ' . ((int) $row['boxnumber'] == $mailbox ? 'selected' : '') . '>' . htmlsafechars($row['name']) . '</option>';
        }
        $insertJumpTo .= '</select></form>';
        $cache->set('insertJumpTo' . $CURUSER['id'], $insertJumpTo, $site_config['expires']['insertJumpTo']);
    }

    return $insertJumpTo;
}

echo stdhead($lang['pm_stdhead'], true, $stdhead) . wrapper($HTMLOUT, 'has-text-centered') . stdfoot();
