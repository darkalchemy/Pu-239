<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'user_functions.php';
dbconn(false);
loggedinorreturn();
$stdfoot = [
    /* include js **/
    'js' => [
        'custom-form-elements',
    ],
];
$stdhead = [
    /* include css **/
    'css' => [
        'user_blocks',
        'checkbox',
        'hide',
    ],
];

$lang = load_language('global');
$id = (isset($_GET['id']) ? $_GET['id'] : $CURUSER['id']);
if (!is_valid_id($id) || $CURUSER['class'] < UC_STAFF) {
    $id = $CURUSER['id'];
}
if ($CURUSER['class'] < UC_STAFF && $CURUSER['got_moods'] == 'no') {
    stderr('Error', "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.... Yer simply no tall enough.");
    die;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
                     WHERE id = ' . sqlesc($id) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
    $row = mysqli_fetch_assoc($res);
    $row['perms'] = (int)$row['perms'];
    $mc1->begin_transaction('MyUser_' . $id);
    $mc1->update_row(false, [
        'perms' => $row['perms'],
    ]);
    $mc1->commit_transaction($INSTALLER09['expires']['curuser']);
    $mc1->begin_transaction('user_' . $id);
    $mc1->update_row(false, [
        'perms' => $row['perms'],
    ]);
    $mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
    header('Location: ' . $INSTALLER09['baseurl'] . '/user_unlocks.php');
    exit();
}
$checkbox_unlock_moods = (($CURUSER['perms'] & bt_options::UNLOCK_MORE_MOODS) ? ' checked="checked"' : '');
$checkbox_unlock_stealth = (($CURUSER['perms'] & bt_options::PERMS_STEALTH) ? ' checked="checked"' : '');
$HTMLOUT = '';
$HTMLOUT .= '
<div class="container">
    <form action="" method="post">        
        <fieldset><legend>User Unlock Settings</legend>
        <div class="row-fluid">
            <div class="span3 offset1">
                <table class="table table-bordered">
                    <tr>
                        <td>
                            <b>Enable Bonus Moods?</b>
                            <div class="slideThree"> <input type="checkbox" id="unlock_user_moods" name="unlock_user_moods" value="yes"' . $checkbox_unlock_moods . ' />
                                <label for="unlock_user_moods"></label>
                            </div>
                            <div><hr style="color:#A83838;" size="1" /></div>
                            <span>Check this option to unlock bonus mood smilies.</span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="span3 offset0">
                <table class="table table-bordered">
                    <tr>
                        <td>
                            <b>User Stealth Mode<?</b>
                            <div class="slideThree"> <input type="checkbox" id="perms_stealth" name="perms_stealth" value="yes"' . $checkbox_unlock_stealth . ' />
                                <label for="perms_stealth"></label>
                            </div>
                            <div><hr style="color:#A83838;" size="1" /></div>
                            <span>Check this option to unlock Stealth Mode.</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        </fieldset>
        <div class="span7 offset1">
            <input class="btn btn-primary" type="submit" name="submit" value="Submit" tabindex="2" accesskey="s" />
        </div>
    </form>
</div>';

echo stdhead('User unlocks', true, $stdhead) . $HTMLOUT . stdfoot($stdfoot);
