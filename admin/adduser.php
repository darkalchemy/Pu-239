<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'password_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $mc1, $site_config;
$mc1->delete_value('userlist_' . $site_config['chatBotID']);
$lang = array_merge($lang, load_language('ad_adduser'));
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $insert = [
        'username'     => '',
        'email'        => '',
        'passhash'     => '',
        'status'       => 'confirmed',
        'added'        => TIME_NOW,
        'last_access'  => TIME_NOW,
        'torrent_pass' => make_torrentpass(),
        'ip'           => ipToStorageFormat('127.0.0.1'),
    ];
    if (isset($_POST['username']) && strlen($_POST['username']) >= 5) {
        $insert['username'] = $_POST['username'];
    } else {
        stderr($lang['std_err'], $lang['err_username']);
    }
    if (isset($_POST['password']) && isset($_POST['password2'])
        && strlen($_POST['password']) > 6
        && trim($_POST['password']) == trim($_POST['password2'])
    ) {
        $insert['passhash'] = make_passhash(trim($_POST['password']));
    } else {
        stderr($lang['std_err'], $lang['err_password']);
    }
    if (isset($_POST['email']) && validemail(trim($_POST['email']))) {
        $insert['email'] = trim($_POST['email']);
    } else {
        stderr($lang['std_err'], $lang['err_email']);
    }
    if (sql_query(sprintf(
        'INSERT INTO users 
                (username, email, passhash, status, added, last_access, torrent_pass, ip) 
                VALUES (%s)', join(', ', array_map('sqlesc', $insert
    ))))) {
        $user_id = 0;
        while ($user_id == 0) {
            usleep(500);
            $user_id = get_one_row('users', 'id', 'WHERE username = ' . sqlesc($insert['username']));
        }

        sql_query(
            'INSERT INTO usersachiev (userid) 
                  VALUES (' . sqlesc($user_id) . ')'
        ) or sqlerr(__FILE__, __LINE__);
        $message = "Welcome New {$site_config['site_name']} Member: [user]" . htmlsafechars($insert['username']) . '[/user]';
        if ($user_id > 2 && $site_config['autoshout_on'] == 1) {
            autoshout($message);
        }
        stderr($lang['std_success'], sprintf($lang['text_user_added'], $user_id));
    } else {
        if (((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_errno($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)) == 1062) {
            $res = sql_query(
                'SELECT id 
                        FROM users 
                        WHERE username = ' . sqlesc($insert['username'])
            ) or sqlerr(__FILE__, __LINE__);
            if (mysqli_num_rows($res)) {
                $arr = mysqli_fetch_assoc($res);
                header(sprintf('refresh:3; url=userdetails.php?id=%d', $arr['id']));
            }
            stderr($lang['std_err'], $lang['err_already_exists']);
        }
        stderr($lang['std_err'], sprintf($lang['err_mysql_err'], ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))));
    }
    die;
}
$HTMLOUT = '
  <h1>' . $lang['std_adduser'] . '</h1><br>
  <form method="post" action="staffpanel.php?tool=adduser&amp;action=adduser">
  <table border="1" cellspacing="0" cellpadding="5">
  <tr><td class="rowhead">' . $lang['text_username'] . '</td><td><input type="text" name="username" size="40" /></td></tr>
  <tr><td class="rowhead">' . $lang['text_password'] . '</td><td><input type="password" name="password" size="40" /></td></tr>
  <tr><td class="rowhead">' . $lang['text_password2'] . '</td><td><input type="password" name="password2" size="40" /></td></tr>
  <tr><td class="rowhead">' . $lang['text_email'] . '</td><td><input type="text" name="email" size="40" /></td></tr>
  <tr><td colspan="2"><input type="submit" value="' . $lang['btn_okay'] . '" class="button" /></td></tr>
  </table>
  </form>';
echo stdhead($lang['std_adduser']) . $HTMLOUT . stdfoot();
