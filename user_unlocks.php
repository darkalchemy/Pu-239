<?php
/**
 |--------------------------------------------------------------------------|
 |   https://github.com/Bigjoos/                			    |
 |--------------------------------------------------------------------------|
 |   Licence Info: GPL			                                    |
 |--------------------------------------------------------------------------|
 |   Copyright (C) 2010 U-232 V4					    |
 |--------------------------------------------------------------------------|
 |   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.   |
 |--------------------------------------------------------------------------|
 |   Project Leaders: Mindless,putyn.					    |
 |--------------------------------------------------------------------------|
  _   _   _   _   _     _   _   _   _   _   _     _   _   _   _
 / \ / \ / \ / \ / \   / \ / \ / \ / \ / \ / \   / \ / \ / \ / \
( U | - | 2 | 3 | 2 )-( S | o | u | r | c | e )-( C | o | d | e )
 \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/
 */
/*
+------------------------------------------------
|   $Date$ 10022011
|   $Revision$ 1.0
|   $Author$ pdq,Bigjoos
|   $User unlocks
|   
+------------------------------------------------
*/
require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php');
require_once (INCL_DIR . 'html_functions.php');
require_once (INCL_DIR . 'user_functions.php');
dbconn(false);
loggedinorreturn();
$stdfoot = array(
    /** include js **/
    'js' => array(
        'custom-form-elements'
    )
);
$stdhead = array(
    /** include css **/
    'css' => array(
        'user_blocks',
        'checkbox',
        'hide'
    )
);

$lang = load_language('global');
$id = (isset($_GET['id']) ? $_GET['id'] : $CURUSER['id']);
if (!is_valid_id($id) || $CURUSER['class'] < UC_STAFF) $id = $CURUSER['id'];
if ($CURUSER['got_moods'] == 'no') {
    stderr("Error", "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.... Yer simply no tall enough.");
    die;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $updateset = array();
    $setbits = $clrbits = 0;
    if (isset($_POST['unlock_user_moods'])) $setbits|= bt_options::UNLOCK_MORE_MOODS; // Unlock bonus moods
    else $clrbits|= bt_options::UNLOCK_MORE_MOODS; // lock bonus moods
    if (isset($_POST['disable_beep'])) $setbits|= bt_options::NOFKNBEEP; // Unlock bonus moods
    else $clrbits|= bt_options::NOFKNBEEP; // lock bonus moods
    //if (isset($_POST['perms_stealth']))
    //$setbits |= bt_options::PERMS_STEALTH; // stealth on
    //else
    //$clrbits |= bt_options::PERMS_STEALTH; // stealth off
    //if ($setbits)
    //$updateset[] = 'perms = (perms | '.$setbits.')';
    //if ($clrbits)
    //$updateset[] = 'perms = (perms & ~'.$clrbits.')';
    //if (count($updateset))
    //sql_query('UPDATE users SET '.implode(',', $updateset).' WHERE id = '.$id) or sqlerr(__FILE__, __LINE__);
    // update perms
    //print_r($_POST);
    //exit();
    if ($setbits || $clrbits) sql_query('UPDATE users SET perms = ((perms | ' . $setbits . ') & ~' . $clrbits . ') 
                 WHERE id = ' . sqlesc($id)) or sqlerr(__file__, __line__);
    //if ($id == $CURUSER['id']) {
    // grab current data
    $res = sql_query('SELECT perms FROM users 
                     WHERE id = ' . sqlesc($id) . ' LIMIT 1') or sqlerr(__file__, __line__);
    $row = mysqli_fetch_assoc($res);
    $row['perms'] = (int)$row['perms'];
    $mc1->begin_transaction('MyUser_' . $id);
    $mc1->update_row(false, array(
        'perms' => $row['perms']
    ));
    $mc1->commit_transaction($INSTALLER09['expires']['curuser']);
    $mc1->begin_transaction('user_' . $id);
    $mc1->update_row(false, array(
        'perms' => $row['perms']
    ));
    $mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
    //}
    header('Location: ' . $INSTALLER09['baseurl'] . '/user_unlocks.php');
    exit();
}
$checkbox_unlock_moods = (($CURUSER['perms'] & bt_options::UNLOCK_MORE_MOODS) ? ' checked="checked"' : '');
$checkbox_unlock_stealth = (($CURUSER['perms'] & bt_options::PERMS_STEALTH) ? ' checked="checked"' : '');
$checkbox_unlock_nofknbeep = (($CURUSER['perms'] & bt_options::NOFKNBEEP) ? ' checked="checked"' : '');
$HTMLOUT = '';
$HTMLOUT.= begin_frame();
$HTMLOUT.= '<div class="container"><form action="" method="post">        
        <fieldset><legend>User Unlock Settings</legend></fieldset>
        <div class="row-fluid">
        <div class="span3 offset1">
        <table class="table table-bordered">
	<tr>
        <td>
        <b>Enable Bonus Moods?</b>
        <div class="slideThree"> <input type="checkbox" id="unlock_user_moods" name="unlock_user_moods" value="yes"' . $checkbox_unlock_moods . ' />
        <label for="unlock_user_moods"></label></div>
        <div><hr style="color:#A83838;" size="1" /></div>
        <span>Check this option to unlock bonus mood smilies.</span>
	</td>
	</tr>
	</table>
	</div>
        <!--<div><h1>Unlock User Moods</h1></div>
        <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
        <td width="50%">
        <b>Enable Bonus Moods?</b>
        <div style="color: gray;">Check this option to unlock bonus mood smilies.</div></td>
        <td width="30%"><div style="width: auto;" align="right">
        <input class="styled" type="checkbox" name="unlock_user_moods" value="yes"' . $checkbox_unlock_moods . ' />
        </div></td>
        </tr></table>-->
        <div class="span3 offset1">
        <table class="table table-bordered">
	<tr>
        <td>
        <b>Enable Username Shout Alert?</b>
        <div class="slideThree"> <input type="checkbox" id="disable_beep" name="disable_beep" value="yes"' . $checkbox_unlock_nofknbeep . ' />
        <label for="disable_beep"></label></div>
        <div><hr style="color:#A83838;" size="1" /></div>
        <span>Check this option to unlock shout beep option.</span>
	</td>
	</tr>
	</table>
	</div>
        <!--<div><h1>Username Shout Beep Enable</h1></div>
        <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
        <td width="50%">
        <b>Enable Username Shout Alert?</b>
        <div style="color: gray;">Check this option to unlock shout beep option.</div></td>
        <td width="30%"><div style="width: auto;" align="right">
        <input class="styled" type="checkbox" name="disable_beep" value="yes"' . $checkbox_unlock_nofknbeep . ' />
        </div></td>
        </tr></table>-->
        <!--
        <div><h1>User Stealth Mode</h1></div>
        <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
        <td width="50%">
        <b>Enable Stealth?</b>
        <div style="color: gray;">Check this option to unlock Stealth Mode.</div></td>
        <td width="30%"><div style="width: auto;" align="right">
        <input class="styled" type="checkbox" name="perms_stealth" value="yes"' . $checkbox_unlock_stealth . ' /></div></td>
        </tr></table>-->';
$HTMLOUT.= '<div class="span7 offset1">
<input class="btn btn-primary" type="submit" name="submit" value="Submit" tabindex="2" accesskey="s" /></div></div></form></div>';

$HTMLOUT.= end_frame();
echo stdhead("User unlocks", true, $stdhead) . $HTMLOUT . stdfoot($stdfoot);
?>
