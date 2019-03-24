<?php

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_password.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $lang, $cache, $session, $user_stuffs;

$cache->delete('chat_users_list');

$stdfoot = [
    'js' => [
        get_file_name('check_username_js'),
    ],
];

$lang = array_merge($lang, load_language('ad_adduser'));
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $values = [
        'username' => '',
        'email' => '',
        'passhash' => '',
        'status' => 'confirmed',
        'added' => TIME_NOW,
        'last_access' => TIME_NOW,
        'torrent_pass' => make_password(32),
        'auth' => make_password(32),
        'apikey' => make_password(32),
        'ip' => inet_pton('127.0.0.1'),
    ];
    if (isset($_POST['username']) && strlen($_POST['username']) >= 3 && valid_username($_POST['username'])) {
        $values['username'] = $_POST['username'];
    } else {
        stderr($lang['std_err'], $lang['err_username']);
    }
    if (isset($_POST['password'], $_POST['password2']) && strlen($_POST['password']) > 6 && trim($_POST['password']) == trim($_POST['password2'])) {
        $values['passhash'] = make_passhash(trim($_POST['password']));
    } else {
        stderr($lang['std_err'], $lang['err_password']);
    }
    if (isset($_POST['email']) && validemail(trim($_POST['email']))) {
        $values['email'] = trim($_POST['email']);
    } else {
        stderr($lang['std_err'], $lang['err_email']);
    }
    $user_id = $user_stuffs->add($values);
    if ($user_id) {
        sql_query('INSERT INTO usersachiev (userid) VALUES (' . sqlesc($user_id) . ')') or sqlerr(__FILE__, __LINE__);
        $cache->delete('all_users_');
        $cache->set('latestuser_', (int) $user_id, $site_config['expires']['latestuser']);

        $message = "Welcome New {$site_config['site_name']} Member: [user]" . htmlsafechars($values['username']) . '[/user]';
        if ($user_id > 2 && $site_config['autoshout_on']) {
            autoshout($message);
        }
        if ($user_id === 2) {
            $session->set('is-success', '[p]Pu-239 Install Complete![/p]');
            header('Location: index.php');
        } else {
            stderr($lang['std_success'], sprintf($lang['text_user_added'], $user_id));
        }
    } else {
        $dupe = $user_stuffs->getUserIdFromName($values['username']);
        if ($dupe) {
            stderr($lang['std_err'], $lang['err_already_exists']);
        }
    }
    die();
}
$HTMLOUT = '
    <h1 class="has-text-centered">' . $lang['std_adduser'] . '</h1>
    <form method="post" action="staffpanel.php?tool=adduser&amp;action=adduser">';
$HTMLOUT .= main_table('
        <tr>
            <td class="w-25">' . $lang['text_username'] . '</td>
            <td>
                <input type="text" name="username" id="wantusername" class="w-100" onblur="checkit();" autocomplete="on" required pattern="[\p{L}\p{N}_-]{3,64}">
                <div id="namecheck"></div>
            </td>
        </tr>
        <tr><td>' . $lang['text_password'] . '</td><td><input type="password" name="password" class="w-100" required></td></tr>
        <tr><td>' . $lang['text_password2'] . '</td><td><input type="password" name="password2" class="w-100" required></td></tr>
        <tr>
            <td>' . $lang['text_email'] . '</td><td><input type="text" name="email" class="w-100" required></td>
        </tr>');
$HTMLOUT .= '
        <div class="has-text-centered margin20">
            <input type="submit" value="' . $lang['btn_okay'] . '" class="button is-small">
        </div>
  </form>';

echo stdhead($lang['std_adduser']) . wrapper($HTMLOUT) . stdfoot($stdfoot);
