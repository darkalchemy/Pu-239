<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\Session;
use Pu239\User;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_password.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('signup'));
global $container, $CURUSER, $site_config;

if (!$CURUSER) {
    get_template();
}
$HTMLOUT = '';
$fluent = $container->get(Database::class);
$session = $container->get(Session::class);
$do = isset($_GET['do']) ? $_GET['do'] : (isset($_POST['do']) ? $_POST['do'] : '');
$id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : '0');
$link = isset($_GET['link']) ? $_GET['link'] : (isset($_POST['link']) ? $_POST['link'] : '');
$sure = isset($_GET['sure']) && $_GET['sure'] === 'yes' ? 'yes' : 'no';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $do === 'addpromo') {
    $promoname = isset($_POST['promoname']) ? $_POST['promoname'] : '';
    if (empty($promoname)) {
        stderr('Error', 'No name for the promo');
    }
    $days_valid = isset($_POST['days_valid']) ? (int) $_POST['days_valid'] : 0;
    if ($days_valid === 0) {
        stderr('Error', "Link will be valid for 0 days ? I don't think so!");
    }
    $max_users = isset($_POST['max_users']) ? (int) $_POST['max_users'] : 0;
    if ($max_users === 0) {
        stderr('Error', 'Max users cant be 0 i think you missed that!');
    }
    $bonus_upload = isset($_POST['bonus_upload']) ? (int) $_POST['bonus_upload'] : 0;
    $bonus_invites = isset($_POST['bonus_invites']) ? (int) $_POST['bonus_invites'] : 0;
    $bonus_karma = isset($_POST['bonus_karma']) ? (int) $_POST['bonus_karma'] : 0;
    if ($bonus_upload === 0 && $bonus_invites === 0 && $bonus_karma === 0) {
        stderr('Error', 'No gift for the new users? Give them some gifts :D');
    }
    $token = make_password(32);
    $values = [
        'name' => $promoname,
        'added' => TIME_NOW,
        'days_valid' => $days_valid,
        'max_users' => $max_users,
        'link' => $token,
        'creator' => $CURUSER['id'],
        'bonus_upload' => $bonus_upload,
        'bonus_invites' => $bonus_invites,
        'bonus_karma' => $bonus_karma,
    ];
    $promo_id = $fluent->insertInto('promo')
                       ->values($values)
                       ->execute();
    if (empty($promo_id)) {
        stderr('Error', 'Something wrong happened, please retry');
    } else {
        $session->set('is-success', 'The promo link [b]' . htmlsafechars($promoname) . '[/b] was added!');
        unset($_POST);
    }
} elseif ($do === 'delete' && $id > 0) {
    $r = $fluent->from('promo')
                ->select(null)
                ->select('name')
                ->where('id = ?', $id)
                ->fetch('name');

    if ($sure === 'no') {
        stderr('Sanity check...', 'You are about to delete promo <b>' . htmlsafechars($r) . '</b>, if you are sure click <a href="' . $_SERVER['PHP_SELF'] . '?do=delete&amp;id=' . $id . '&amp;sure=yes"><span class="has-text-danger">here</span></a>');
    } elseif ($sure === 'yes') {
        $deleted = $fluent->deleteFrom('promo')
                          ->where('id = ?', $id)
                          ->execute();
        if (!empty($deleted)) {
            $session->set('is-success', 'Promo was deleted!');
        } else {
            $session->set('is-warning', 'Odd things happned!Contact your coder!');
        }
    }
} elseif ($do === 'addpromo') {
    if ($CURUSER['class'] < UC_STAFF) {
        stderr('Error', 'There is nothing for you here! Go play somewhere else');
    }
    $HTMLOUT .= '
        <h1 class="has-text-centered">Add Promo Link</h1>
        <form action="' . $_SERVER['PHP_SELF'] . '" method="post"  accept-charset="utf-8">';
    $body = "
            <tr>
                <td class='has-text-right'>Promo Name</td>
                <td class='has-text-left' colspan='3'>
                    <input type='text' name='promoname' class='w-100' required>
                </td>
            </tr>
            <tr>
                <td class='has-text-right'>Days valid</td>
                <td class='has-text-left'>
                    <input type='number' name='days_valid' class='w-100' min='1' value='1' required>
                </td>
                <td class='has-text-right'>Max users</td>
                <td class='has-text-left'>
                    <input type='number' name='max_users' class='w-100' min='10' value='10' required>
                </td>
            </tr>

            <tr>
                <td class='has-text-right' colspan='1' rowspan='2'>Bonuses</td>
                <td class='has-text-centered'>Upload</td>
                <td class='has-text-centered'>Invites</td>
                <td class='has-text-centered'>Karma</td>
            </tr>
            <tr>
                <td class='has-text-centered'>
                    <input type='number' name='bonus_upload' class='w-100' placeholder='How many Gigabytes?' min='10' value='10' required>
                </td>
                <td class='has-text-centered'>
                    <input type='number' name='bonus_invites' class='w-100' min='1' value='1' required>
                </td>
                <td class='has-text-centered'>
                    <input type='number' name='bonus_karma' class='w-100' min='1000' value='1000' required>
                </td>
            </tr>
            <tr>
                <td class='has-text-centered' colspan='4'>
                    <input type='hidden' value='addpromo' name='do'>
                    <div class='padding10'>
                        <input type='submit' value='Add Promo!' class='button is-small'>
                    </div>
                </td>
            </tr>";
    $HTMLOUT .= main_table($body) . '
                </form>';
    echo stdhead('Add Promo Link') . wrapper($HTMLOUT) . stdfoot();
    die();
} elseif ($do === 'accounts') {
    if (empty($link)) {
        stderr('Error', 'Invalid Promo ID');
    }
    $accounts = explode('|', urldecode($_GET['userids']));
    if (empty($accounts)) {
        stderr('Error', 'No users have signed up from this promo');
    }
    $name = $fluent->from('promo')
                   ->select(null)
                   ->select('name')
                   ->where('link = ?', $link)
                   ->fetch('name');
    $users_class = $container->get(User::class);
    $body = '
                    <h1 class="has-text-centered">Users list for promo: ' . htmlsafechars($name) . '</h1>
                    <div class="padding20 level-center-center">';
    foreach ($accounts as $ap) {
        $ap = (int) $ap;
        $promo_user = $users_class->getUserFromId($ap);
        if (!empty($promo_user)) {
            $users[] = "<div class='margin20 padding20 bg-02 round10'>" . format_username($ap) . ' joined ' . get_date($promo_user['registered'], 'LONG', 0, 1) . '</div>';
        }
    }
    $body .= implode('', $users) . '
                    </div>';

    $HTMLOUT .= main_div($body);
    echo stdhead('Current Promos') . wrapper($HTMLOUT) . stdfoot();
    die();
}

if (empty($_POST)) {
    $modal = '
        <div class="modal">
            <div class="modal-background"></div>
                <div class="modal-content">
                    <!-- Any other Bulma elements you want -->
                </div>
            <button class="modal-close is-large" aria-label="close"></button>
        </div>';
    if ($CURUSER['class'] < UC_STAFF) {
        stderr('Error', 'There is nothing for you here! Go play somewhere else');
    }
    $r = $fluent->from('promo')
                ->fetchAll();
    if (empty($r)) {
        stderr('Error', 'There is no promo if you want to make one click <a href="' . $_SERVER['PHP_SELF'] . '?do=addpromo">here</a>', 'bottom20');
    } else {
        $HTMLOUT .= '
                <div class="has-text-centered bottom20"> 
                    <h1>Current Promos</h1>
                    <a href="' . $_SERVER['PHP_SELF'] . '?do=addpromo"><span class="size_3">Add promo</span></a>
                </div>';
        $heading = "
            <tr class='has-text-centered'>
                <th class='has-text-centered' rowspan='2'>Promo</th>
                <th class='has-text-centered' rowspan='2'>Added</th>
                <th class='has-text-centered' rowspan='2'>Valid Till</th>
                <th class='has-text-centered' colspan='2'>Users</th>
                <th class='has-text-centered' colspan='3'>Bonuses</th>
                <th class='has-text-centered' rowspan='2'>Added by</th>       
                <th class='has-text-centered' rowspan='2'>Remove</th>       
            </tr>
            <tr>
                <th class='has-text-centered'>max</th>
                <th class='has-text-centered'>till now</th>
                <th class='has-text-centered'>upload</th>
                <th class='has-text-centered'>invites</th>
                <th class='has-text-centered'>karma</th>
            </tr>";
        $body = '';
        foreach ($r as $ar) {
            $active = $ar['max_users'] === $ar['accounts_made'] || $ar['added'] + (86400 * $ar['days_valid']) < TIME_NOW ? false : true;
            $body .= '
            <tr class="tooltipper"' . (!$active ? ' title="This promo has ended"' : '') . '>
                <td>' . (htmlsafechars($ar['name'])) . "<br><input type='text' " . (!$active ? 'disabled' : '') . " value='" . ($site_config['paths']['baseurl'] . '/signup.php?promo=' . $ar['link']) . "' name='" . (htmlsafechars($ar['name'])) . "' onclick='select();' class='w-100'></td>
                <td class='has-text-centered'>" . get_date($ar['added'], 'LONG') . "</td>
                <td class='has-text-centered'>" . get_date($ar['added'] + (86400 * $ar['days_valid']), 'LONG', 1, 0) . "</td>
                <td class='has-text-centered'>" . $ar['max_users'] . "</td>
                <td class='has-text-centered'>" . ($ar['accounts_made'] > 0 ? '<a href="' . $_SERVER['PHP_SELF'] . '?do=accounts&amp;link=' . $ar['link'] . '&amp;userids=' . urldecode($ar['users']) . '">' . $ar['accounts_made'] . '</a>' : 0) . "</td>
                <td class='has-text-centered'>" . mksize($ar['bonus_upload'] * 1073741824) . "</td>
                <td class='has-text-centered'>" . number_format($ar['bonus_invites']) . "</td>
                <td class='has-text-centered'>" . number_format($ar['bonus_karma']) . "</td>
                <td class='has-text-centered'>" . format_username($ar['creator']) . "</a></td>
                <td class='has-text-centered'><a href='" . $_SERVER['PHP_SELF'] . '?do=delete&amp;id=' . $ar['id'] . "'><i class='icon-trash-empty icon has-text-danger'></i></a></td>
            </tr>";
        }
        $HTMLOUT .= main_table($body, $heading);
        echo stdhead('Current Promos') . wrapper($HTMLOUT) . stdfoot();
    }
}
