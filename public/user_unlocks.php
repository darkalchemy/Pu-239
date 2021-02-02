<?php

declare(strict_types = 1);

use Pu239\Cache;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_user_options_2.php';
$user = check_user_status();
global $container, $site_config;

$id = (isset($_GET['id']) ? $_GET['id'] : $user['id']);
if (!is_valid_id($id) || $user['class'] < UC_STAFF) {
    $id = $user['id'];
}
$got_moods = $user['opt2'] & class_user_options_2::GOT_MOODS === class_user_options_2::GOT_MOODS;
if ($user['class'] < UC_STAFF && $got_moods) {
    stderr(_('Error'), "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.... Yer simply no tall enough.");
    die();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updateset = [];
    $setbits = $clrbits = 0;
    if (isset($_POST['unlock_user_moods'])) {
        $setbits |= UNLOCK_MORE_MOODS; // Unlock bonus moods
    } else {
        $clrbits |= UNLOCK_MORE_MOODS; // lock bonus moods
    }

    if (isset($_POST['perms_stealth'])) {
        $setbits |= PERMS_STEALTH; // stealth on
    } else {
        $clrbits |= PERMS_STEALTH; // stealth off
    }

    if ($setbits || $clrbits) {
        sql_query('UPDATE users SET perms = ((perms | ' . $setbits . ') & ~' . $clrbits . ') 
                 WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    }
    // grab current data
    $res = sql_query('SELECT perms FROM users
                     WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $row = mysqli_fetch_assoc($res);
    $row['perms'] = (int) $row['perms'];
    $cache = $container->get(Cache::class);
    $cache->update_row('user_' . $id, [
        'perms' => $row['perms'],
    ], $site_config['expires']['user_cache']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    die();
}
$checkbox_unlock_moods = $user['perms'] & UNLOCK_MORE_MOODS ? 'checked' : '';
$checkbox_unlock_stealth = $user['perms'] & PERMS_STEALTH ? 'checked' : '';

$HTMLOUT = '
            <div class="bg-02 top20">
                <h1 class="has-text-centered">User Unlock Settings</h1>
                <form action="" method="post" accept-charset="utf-8">
                    <div class="level-center">
                        <div class="w-20">
                            <div class="bordered level-center bg-02">
                                <div class="w-100">Enable Bonus Moods?</div>
                                <div class="slideThree">
                                    <input type="checkbox" id="unlock_user_moods" name="unlock_user_moods" value="yes" ' . $checkbox_unlock_moods . '>
                                    <label for="unlock_user_moods"></label>
                                </div>
                                <div class="w-100">Check this option to unlock bonus mood smilies.</div>
                            </div>
                        </div>
                        <div class="w-20">
                            <span class="bordered level-center bg-02">
                                <div class="w-100">User Stealth Mode?</div>
                                <div class="slideThree">
                                    <input type="checkbox" id="perms_stealth" name="perms_stealth" value="yes" ' . $checkbox_unlock_stealth . '>
                                    <label for="perms_stealth"></label>
                                </div>
                                <div class="w-100">Check this option to unlock Stealth Mode.</div>
                            </span>
                        </div>
                    </div>
                    <div class="has-text-centered margin20">
                        <input class="button is-small" type="submit" name="submit" value="Submit" tabindex="2" accesskey="s">
                    </div>
                </form>
            </div>';
$title = _('User Unlocks');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
