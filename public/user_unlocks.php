<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_user_options_2.php';
check_user_status();
global $CURUSER, $site_config, $cache;

$lang = load_language('global');
$id = (isset($_GET['id']) ? $_GET['id'] : $CURUSER['id']);
if (!is_valid_id($id) || $CURUSER['class'] < UC_STAFF) {
    $id = $CURUSER['id'];
}
$got_moods = ($CURUSER['opt2'] & user_options_2::GOT_MOODS) === user_options_2::GOT_MOODS;
if ($CURUSER['class'] < UC_STAFF && $got_moods) {
    stderr('Error', "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.... Yer simply no tall enough.");
    die();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updateset = [];
    $setbits = $clrbits = 0;
    if (isset($_POST['unlock_user_moods'])) {
        $setbits |= bt_options::UNLOCK_MORE_MOODS;
    } // Unlock bonus moods
    else {
        $clrbits |= bt_options::UNLOCK_MORE_MOODS;
    } // lock bonus moods

    if (isset($_POST['perms_stealth'])) {
        $setbits |= bt_options::PERMS_STEALTH; // stealth on
    } else {
        $clrbits |= bt_options::PERMS_STEALTH; // stealth off
    }

    if ($setbits || $clrbits) {
        sql_query('UPDATE users SET perms = ((perms | ' . $setbits . ') & ~' . $clrbits . ') 
                 WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    }
    // grab current data
    $res = sql_query('SELECT perms FROM users
                     WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $row = mysqli_fetch_assoc($res);
    $row['perms'] = (int) $row['perms'];
    $cache->update_row('user' . $id, [
        'perms' => $row['perms'],
    ], $site_config['expires']['user_cache']);
    header('Location: ' . $site_config['baseurl'] . '/user_unlocks.php');
    die();
}
$checkbox_unlock_moods = (($CURUSER['perms'] & bt_options::UNLOCK_MORE_MOODS) ? ' checked' : '');
$checkbox_unlock_stealth = (($CURUSER['perms'] & bt_options::PERMS_STEALTH) ? ' checked' : '');

$HTMLOUT = '
            <div class="bg-02 top20">
                <h1 class="has-text-centered">User Unlock Settings</h1>
                <form action="" method="post">
                    <div class="level-center">
                        <div class="w-20">
                            <span class="bordered level-center bg-02">
                                <div class="w-100">Enable Bonus Moods?</div>
                                <div class="slideThree">
                                    <input type="checkbox" id="unlock_user_moods" name="unlock_user_moods" value="yes"' . $checkbox_unlock_moods . '>
                                    <label for="unlock_user_moods"></label>
                                </div>
                                <div class="w-100">Check this option to unlock bonus mood smilies.</div>
                            </span>
                        </div>
                        <div class="w-20">
                            <span class="bordered level-center bg-02">
                                <div class="w-100">User Stealth Mode?</div>
                                <div class="slideThree">
                                    <input type="checkbox" id="perms_stealth" name="perms_stealth" value="yes"' . $checkbox_unlock_stealth . '>
                                    <label for="perms_stealth"></label>
                                </div>
                                <div class="w-100">Check this option to unlock Stealth Mode.</div>
                            </span>
                        </div>
                    </div>
                    <div class="has-text-centered margin20">
                        <input class="button" type="submit" name="submit" value="Submit" tabindex="2" accesskey="s">
                    </div>
                </form>
            </div>';

echo stdhead('User unlocks') . wrapper($HTMLOUT) . stdfoot();
