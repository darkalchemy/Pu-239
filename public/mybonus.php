<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();

$lang = array_merge(load_language('global'), load_language('mybonus'));
$stdhead = [
    'css' => [
    ],
];
if ($site_config['seedbonus_on'] == 0) {
    stderr('Information', 'The Karma bonus system is currently offline for maintainance work');
}

$HTMLOUT = '';

/**
 * @param $var
 */
function I_smell_a_rat($var)
{
    if (((int)$var) == 1) {
        $var = (int)$var;
    } else {
        stderr('Error', 'I smell a rat!');
    }
}

$ratio = get_one_row('users', 'uploaded / downloaded', 'WHERE id = ' . sqlesc($CURUSER['id']));

/////////freeleech
if (isset($_GET['freeleech_success']) && $_GET['freeleech_success']) {
    $freeleech_success = (int)$_GET['freeleech_success'];
    if ($freeleech_success != '1' && $freeleech_success != '2') {
        stderr('Error', 'I smell a rat on freeleech!');
    }
    if ($freeleech_success == '1') {
        if ($_GET['norefund'] != '0') {
            $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr><tr>" .
                "<td><img src='{$site_config['pic_base_url']}/smilies/karma.gif' alt='good_karma' title='Good Karma' /></td><td><b>Congratulations! </b>
{$CURUSER['username']} you have set the tracker <b>Free Leech !</b> <img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='W00t' /><br><br>Remaining " . htmlsafechars($_GET['norefund']) . ' points have been contributed towards the next freeleech period automatically!' .
                "<br> click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br><br>" .
                '</td></tr></table>';
            echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        } else {
            $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr><tr>" .
                "<td><img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good Karma' /></td><td><b>Congratulations! </b>
{$CURUSER['username']} you have set the tracker <b>Free Leech !</b> <img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='W00t' /><br>" .
                "<br> click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br><br>" .
                '</td></tr></table>';
            echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        }

        die;
    }
    if ($freeleech_success == '2') {
        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr><tr>" .
            "<td><img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good Karma' /></td><td><b>Congratulations! </b>" .
            "{$CURUSER['username']} you have contributed towards making the tracker Free Leech ! <img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='W00t' /><br>" .
            "<br> click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br><br>" .
            '</td></tr></table>';
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;
    }
}
////////doubleup
if (isset($_GET['doubleup_success']) && $_GET['doubleup_success']) {
    $doubleup_success = (int)$_GET['doubleup_success'];
    if ($doubleup_success != '1' && $doubleup_success != '2') {
        setSessionVar('is-danger', 'I smell a rat on freeleech!');
    }
    if ($doubleup_success == '1') {
        if ($_GET['norefund'] != '0') {
            setSessionVar('is-success', "<img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' class='icon' title='Good Karma' /><b>Congratulations! </b>{$CURUSER['username']} you have set the tracker <b>Double Up!</b> <img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='W00t' />Remaining " . htmlsafechars($_GET['norefund']) . " points have been contributed towards the next doubleup period automatically!");
        } else {
            setSessionVar('is-success', "<img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' class='icon' title='Good Karma' /><b>Congratulations! </b>{$CURUSER['username']} you have set the tracker <b>Double Up!</b> <img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='W00t' />");
        }

        die;
    }
    if ($doubleup_success == '2') {
        setSessionVar('is-success', "<img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' class='icon' title='Good Karma' /><b>Congratulations! </b>{$CURUSER['username']} you have contributed towards making the tracker Double Upload! <img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='W00t' />");
    }
}
/////////Halfdownload
if (isset($_GET['halfdown_success']) && $_GET['halfdown_success']) {
    $halfdown_success = (int)$_GET['halfdown_success'];
    if ($halfdown_success != '1' && $halfdown_success != '2') {
        stderr('Error', 'I smell a rat on halfdownload!');
    }
    if ($halfdown_success == '1') {
        if ($_GET['norefund'] != '0') {
            $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr><tr>" .
                "<td><img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good Karma' /></td><td><b>Congratulations! </b>
{$CURUSER['username']} you have set the tracker <b>Half Download !</b> <img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='W00t' /><br><br>Remaining " . htmlsafechars($_GET['norefund']) . ' points have been contributed towards the next Half download period automatically!' .
                "<br> click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br><br>" .
                '</td></tr></table>';
            echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        } else {
            $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr><tr>" .
                "<td><img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good Karma' /></td><td><b>Congratulations! </b>
{$CURUSER['username']} you have set the tracker <b>Half Download !</b> <img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='W00t' /><br>" .
                "<br> click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br><br>" .
                '</td></tr></table>';
            echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        }

        die;
    }
    if ($halfdown_success == '2') {
        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr><tr>" .
            "<td><img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good Karma' /></td><td><b>Congratulations! </b>" .
            "{$CURUSER['username']} you have contributed towards making the tracker Half Download ! <img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='W00t' /><br>" .
            "<br> click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br><br>" .
            '</td></tr></table>';
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;
    }
}
//////////

switch (true) {
    case isset($_GET['up_success']):
        I_smell_a_rat($_GET['up_success']);

        $amt = (float)$_GET['amt'];

        switch ($amt) {
            case $amt == 275:
                $amt = '1 GB';
                break;
            case $amt == 350:
                $amt = '2.5 GB';
                break;
            case $amt == 550:
                $amt = '5 GB';
                break;
            case $amt == 1000:
                $amt = '10 GB';
                break;
            case $amt == 2000:
                $amt = '25 GB';
                break;
            case $amt == 4000:
                $amt = '50 GB';
                break;
            case $amt == 8000:
                $amt = '100 GB';
                break;
            case $amt == 40000:
                $amt = '520 GB';
                break;
            default:
                $amt = '1 TB';
        }

        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr><tr>
<td><img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td>
<td><b>Congratulations ! </b>" . $CURUSER['username'] . ' you have just increased your upload amount by ' . $amt . "!
<img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br><br><br><br> click to go back to your
<a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['anonymous_success']): {
        I_smell_a_rat($_GET['anonymous_success']);
    }
        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr><tr>
<td><img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td>
<td><b>Congratulations ! </b>" . $CURUSER['username'] . " you have just purchased Anonymous profile for 14 days!
<img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br><br><br><br> click to go back to your
<a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['parked_success']): {
        I_smell_a_rat($_GET['parked_success']);
    }
        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr><tr>
<td><img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td>
<td><b>Congratulations ! </b>" . $CURUSER['username'] . " you have just purchased parked option for your profile !
<img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br><br><br><br> click to go back to your
<a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['freeyear_success']): {
        I_smell_a_rat($_GET['freeyear_success']);
    }
        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr><tr>
<td><img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td>
<td><b>Congratulations ! </b>" . $CURUSER['username'] . " you have just purchased freeleech for one year!
<img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br><br><br><br> click to go back to your
<a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['freeslots_success']): {
        I_smell_a_rat($_GET['freeslots_success']);
    }

        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr><tr>
<td><img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td>
<td><b>Congratulations ! </b>" . $CURUSER['username'] . " you have got your self 3 freeleech slots!!
<img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br><br><br><br> click to go back to your
<a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['itrade_success']): {
        I_smell_a_rat($_GET['itrade_success']);
    }

        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr><tr>
<td><img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td>
<td><b>Congratulations ! </b>" . $CURUSER['username'] . " you have got your self 200 points !!
<img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br><br><br><br> click to go back to your
<a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['itrade2_success']): {
        I_smell_a_rat($_GET['itrade2_success']);
    }

        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr><tr>
<td><img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good Karma' /></td>
<td><b>Sorry ! </b>" . $CURUSER['username'] . " you just got yourself 2 freeslots !!
<img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='W00t' title='W00t' /><br><br><br><br> click to go back to your
<a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['pirate_success']): {
        I_smell_a_rat($_GET['pirate_success']);
    }
        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr>
<tr><td><img src='{$site_config['pic_base_url']}smilies/pirate2.gif' alt='good_karma' title='Good karma' /></td><td>
<b>Congratulations! </b>" . $CURUSER['username'] . " you have got yourself Pirate Status and Freeleech for two weeks! <img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br>
<br> Click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Points</a> page.<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['king_success']): {
        I_smell_a_rat($_GET['king_success']);
    }
        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr>
<tr><td><img src='{$site_config['pic_base_url']}smilies/king.gif' alt='good_karma' title='Good karma' /></td><td>
<b>Congratulations! </b>" . $CURUSER['username'] . " you have got yourself King Status and Freeleech for one month! <img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br>
<br> Click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Points</a> page.<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['dload_success']): {
        I_smell_a_rat($_GET['dload_success']);
    }

        $amt = (float)$_GET['amt'];

        switch ($amt) {
            case $amt == 150:
                $amt = '1 GB';
                break;
            case $amt == 300:
                $amt = '2.5 GB';
                break;
            default:
                $amt = '5 GB';
        }

        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr><tr>" .
            "<td class='one><img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good Karma' /></td>" .
            "<td><b>Congratulations ! </b>" . $CURUSER['username'] . ' you have just decreased your download amount by ' . $amt . '!' .
            "<img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' /><br><br><br><br> click to go back to your " .
            "<a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['class_success']):
        I_smell_a_rat($_GET['class_success']);

        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr>
<tr><td><img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td><td>
<b>Congratulations! </b>" . $CURUSER['username'] . " you have got yourself VIP Status for one month! <img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br>
<br> Click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Points</a> page.<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['smile_success']):
        I_smell_a_rat($_GET['smile_success']);

        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr>
<tr><td><img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td><td>
<b>Congratulations! </b>" . $CURUSER['username'] . " you have got yourself a set of custom smilies for one month! <img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br>
<br> Click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Points</a> page.<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['warning_success']):
        I_smell_a_rat($_GET['warning_success']);

        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr>
<tr><td><img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td><td>
<b>Congratulations! </b>" . $CURUSER['username'] . " you have removed your warning for the low price of 1000 points!! <img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br>
<br> Click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Points</a> page.<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['invite_success']):
        I_smell_a_rat($_GET['invite_success']);

        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr><tr><td>
<img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td><td>
<b>Congratulations! </b>" . $CURUSER['username'] . " you have got your self 3 new invites! <img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br><br>
click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['freeslots_success']):
        I_smell_a_rat($_GET['freeslots_success']);

        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr><tr><td>
<img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td><td>
<b>Congratulations! </b>" . $CURUSER['username'] . " you have got your self 3 freeleech slots! <img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br><br>
click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['title_success']):
        I_smell_a_rat($_GET['title_success']);

        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr><tr>
<td><img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td><td>
<b>Congratulations! </b>" . $CURUSER['username'] . ' you are now known as <b>' . $CURUSER['title'] . "</b>! <img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br>
<br> click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['ratio_success']):
        I_smell_a_rat($_GET['ratio_success']);

        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr><tr>
<td><img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td><td><b>Congratulations! </b> " . $CURUSER['username'] . " you
have gained a 1 to 1 ratio on the selected torrent, and the difference in MB has been added to your total upload! <img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br>
<br> click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br><br>
</td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['gift_fail']):
        I_smell_a_rat($_GET['gift_fail']);

        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Huh?</h1></td></tr><tr><td>
<img src='{$site_config['pic_base_url']}smilies/cry.gif' alt='bad_karma' title='Bad karma' /></td><td><b>Not so fast there Mr. fancy pants!</b><br>
<b>" . $CURUSER['username'] . "...</b> you can not spread the karma to yourself...<br>If you want to spread the love, pick another user! <br>
<br> click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['gift_fail_user']):
        I_smell_a_rat($_GET['gift_fail_user']);

        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Error</h1></td></tr><tr><td>
<img src='{$site_config['pic_base_url']}smilies/cry.gif' alt='bad_karma' title='Bad karma' /></td><td><b>Sorry " . $CURUSER['username'] . "...</b>
<br> No User with that username <br><br> click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.
<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['bump_success']) && $_GET['bump_success'] == 1:
        $res_free = sql_query('SELECT id, name
                                FROM torrents
                                WHERE id = ' . sqlesc((int)$_GET['t_name'])) or sqlerr(__FILE__, __LINE__);
        $arr_free = mysqli_fetch_assoc($res_free);
        stderr('Success!', '<img src="./images/smilies/karma.gif" alt="good karma" /> <b>Congratulations ' . $CURUSER['username'] . '!!!</b> <img src="./images/smilies/karma.gif" alt="good karma" /><br> you have ReAnimated the torrent <b><a class="altlink" href="details.php?id=' . $arr_free['id'] . '">' . htmlsafechars($arr_free['name']) . '</a></b>! Bringing it back to page one! <img src="./images/smilies/w00t.gif" alt="w00t" /><br><br>
Click to go back to your <a class="altlink" href="mybonus.php">Karma Points</a> page.<br><br>');
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['gift_fail_points']):
        I_smell_a_rat($_GET['gift_fail_points']);

        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Oops!</h1></td></tr><tr><td>
<img src='{$site_config['pic_base_url']}smilies/cry.gif' alt='oups' title='Bad karma' /></td><td><b>Sorry </b>" . $CURUSER['username'] . " you dont have enough Karma points
<br> go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['gift_success']):
        I_smell_a_rat($_GET['gift_success']);

        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr><tr><td>
<img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td><td><b>Congratulations! " . $CURUSER['username'] . ' </b>
you have spread the Karma well.<br><br>Member <b>' . htmlsafechars($_GET['usernamegift']) . '</b> will be pleased with your kindness!<br><br>This is the message that was sent:<br>
<b>Subject:</b> Someone Loves you!<br> <p>You have been given a gift of <b>' . ((int)$_GET['gift_amount_points']) . '</b> Karma points by ' . $CURUSER['username'] . "</p><br>
You may also <a class='altlink' href='{$site_config['baseurl']}/pm_system.php?action=send_message&amp;receiver=" . ((int)$_GET['gift_id']) . "'>send " . htmlsafechars($_GET['usernamegift']) . " a message as well</a>, or go back to your <a class='altlink' href='mybonus.php'>Karma Bonus Point</a> page.<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['bounty_success']): {
        I_smell_a_rat($_GET['bounty_success']);
    }
        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr>
<tr><td><img src='{$site_config['pic_base_url']}smilies/pirate2.gif' alt='good_karma' title='Good karma' /></td><td>
<b>Congratulations! </b>" . $CURUSER['username'] . " you have got yourself bounty and robbed many users of there reputation points! <img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br>
<br> Click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Points</a> page.<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['reputation_success']):
        I_smell_a_rat($_GET['reputation_success']);

        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr><tr><td>
<img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td><td>
<b>Congratulations! </b>" . $CURUSER['username'] . " you have got your 100 rep points! <img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br><br>
click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['immunity_success']):
        I_smell_a_rat($_GET['immunity_success']);

        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr><tr><td>
<img src='{$site_config['pic_base_url']}smilies/yay.gif' alt='good_karma' title='Good karma' /></td><td>
<b>Congratulations! </b>" . $CURUSER['username'] . " you have got yourself immuntiy from auto hit and run warnings and auto leech warnings ! <img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br><br>
click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['userblocks_success']):
        I_smell_a_rat($_GET['userblocks_success']);

        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr><tr><td>
<img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td><td>
<b>Congratulations! </b>" . $CURUSER['username'] . " you have got yourself access to control the site user blocks! <img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br><br>
click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;

    case isset($_GET['user_unlocks_success']):
        I_smell_a_rat($_GET['user_unlocks_success']);

        $HTMLOUT .= "<table class='table table-bordered table-striped'><tr><td class='colhead' colspan='2'><h1>Success!</h1></td></tr><tr><td>
<img src='{$site_config['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td><td>
<b>Congratulations! </b>" . $CURUSER['username'] . " you have got yourself unlocked bonus moods for use on site! <img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br><br>
click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br><br></td></tr></table>";
        echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
        die;
}

//=== exchange
if (isset($_GET['exchange'])) {
    I_smell_a_rat($_GET['exchange']);

    $userid = (int)$CURUSER['id'];
    if (!is_valid_id($userid)) {
        stderr('Error', 'That is not your user ID!');
    }

    $option = (int)$_POST['option'];

    $res_points = sql_query('SELECT *
                                FROM bonus WHERE id = ' . sqlesc($option)) or sqlerr(__FILE__, __LINE__);
    $arr_points = mysqli_fetch_assoc($res_points);

    $art = htmlsafechars($arr_points['art']);
    $points = (float)$arr_points['points'];
    $minpoints = (float)$arr_points['minpoints'];

    if ($CURUSER['seedbonus'] <= 0) {
        stderr('Error', 'I smell a rat!');
    }

    if ($points <= 0) {
        stderr('Error', 'I smell a rat!');
    }

    $sql = sql_query('SELECT uploaded, downloaded, seedbonus, bonuscomment, free_switch, warned, invites, freeslots, reputation
                        FROM users
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
    $User = mysqli_fetch_assoc($sql);

    $bonus = (float)$User['seedbonus'];
    $seedbonus = ($bonus - $points);
    $upload = (float)$User['uploaded'];
    $download = (float)$User['downloaded'];
    $bonuscomment = htmlsafechars($User['bonuscomment']);
    $free_switch = (int)$User['free_switch'];
    $warned = (int)$User['warned'];
    $reputation = (int)$User['reputation'];

    if ($bonus < $minpoints) {
        stderr('Sorry', 'you do not have enough Karma points!');
    }

    switch ($art) {
        case 'traffic':
//=== trade for one upload credit
            $up = $upload + $arr_points['menge'];
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for upload bonus.\n " . $bonuscomment;
            sql_query('UPDATE users
                        SET uploaded = ' . sqlesc($upload + $arr_points['menge']) . ', seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $mc1->begin_transaction('userstats_' . $userid);
            $mc1->update_row(false, ['uploaded' => $upload + $arr_points['menge'], 'seedbonus' => $seedbonus]);
            $mc1->commit_transaction($site_config['expires']['u_stats']);
            $mc1->begin_transaction('user_stats_' . $userid);
            $mc1->update_row(false, ['uploaded' => $upload + $arr_points['menge'], 'seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?up_success=1&amt=$points");
            die;
            break;

        case 'reputation':
//=== trade for reputation
            if ($CURUSER['class'] < UC_POWER_USER || $User['reputation'] >= 5000) {
                stderr('Error', "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides...Sorry your not a Power User or you already have to many rep points :-P<br>go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page and think that one over.");
            }
            $rep = $reputation + $arr_points['menge'];
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for 100 rep points.\n " . $bonuscomment;
            sql_query('UPDATE users
                        SET reputation = ' . sqlesc($rep) . ', seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $mc1->begin_transaction('user' . $userid);
            $mc1->update_row(false, ['reputation' => $rep]);
            $mc1->commit_transaction($site_config['expires']['user_cache']);
            $mc1->begin_transaction('MyUser_' . $userid);
            $mc1->update_row(false, ['reputation' => $rep]);
            $mc1->commit_transaction($site_config['expires']['curuser']);
            $mc1->begin_transaction('userstats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus]);
            $mc1->commit_transaction($site_config['expires']['u_stats']);
            $mc1->begin_transaction('user_stats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?reputation_success=1");
            die;
            break;

        case 'immunity':
//=== trade for immunity
            if ($CURUSER['class'] < UC_POWER_USER || $User['reputation'] < 3000) {
                stderr('Error', "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides...Sorry your not a Power User or you dont have enough rep :-P<br>go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page and think that one over.");
            }
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for 1 years immunity status.\n " . $bonuscomment;
            $immunity = (86400 * 365 + TIME_NOW);
            sql_query('UPDATE users
                        SET immunity = ' . sqlesc($immunity) . ', seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $mc1->begin_transaction('user' . $userid);
            $mc1->update_row(false, ['immunity' => $immunity]);
            $mc1->commit_transaction($site_config['expires']['user_cache']);
            $mc1->begin_transaction('MyUser_' . $userid);
            $mc1->update_row(false, ['immunity' => $immunity]);
            $mc1->commit_transaction($site_config['expires']['curuser']);
            $mc1->begin_transaction('userstats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus]);
            $mc1->commit_transaction($site_config['expires']['u_stats']);
            $mc1->begin_transaction('user_stats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?immunity_success=1");
            die;
            break;

        case 'userblocks':
//=== trade for userblock access
            $reputation = $User['reputation'];
            if ($CURUSER['class'] < UC_POWER_USER || $User['reputation'] < 50) {
                stderr('Error', "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides...Sorry your not a Power User or you dont have enough rep points yet - Minimum 50 required :-P<br>go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page and think that one over.");
            }
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for user blocks access.\n " . $bonuscomment;
            sql_query("UPDATE users
                        SET got_blocks = 'yes', seedbonus = " . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $mc1->begin_transaction('user' . $userid);
            $mc1->update_row(false, ['got_blocks' => 'yes']);
            $mc1->commit_transaction($site_config['expires']['user_cache']);
            $mc1->begin_transaction('MyUser_' . $userid);
            $mc1->update_row(false, ['got_blocks' => 'yes']);
            $mc1->commit_transaction($site_config['expires']['curuser']);
            $mc1->begin_transaction('userstats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus]);
            $mc1->commit_transaction($site_config['expires']['u_stats']);
            $mc1->begin_transaction('user_stats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?userblocks_success=1");
            die;
            break;

        case 'userunlock':
//=== trade for user_unlocks access
            $reputation = $User['reputation'];
            if ($CURUSER['class'] < UC_POWER_USER || $User['reputation'] < 50) {
                stderr('Error', "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides...Sorry your not a Power User or you dont have enough rep points yet - Minimum 50 required :-P<br>go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page and think that one over.");
            }
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for user unlocks access.\n " . $bonuscomment;
            sql_query("UPDATE users
                        SET got_moods = 'yes', seedbonus = " . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $mc1->begin_transaction('user' . $userid);
            $mc1->update_row(false, ['got_moods' => 'yes']);
            $mc1->commit_transaction($site_config['expires']['user_cache']);
            $mc1->begin_transaction('MyUser_' . $userid);
            $mc1->update_row(false, ['got_moods' => 'yes']);
            $mc1->commit_transaction($site_config['expires']['curuser']);
            $mc1->begin_transaction('userstats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus]);
            $mc1->commit_transaction($site_config['expires']['u_stats']);
            $mc1->begin_transaction('user_stats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?user_unlocks_success=1");
            die;
            break;

        case 'anonymous':
//=== trade for 14 days Anonymous profile
            $anonymous_until = (86400 * 14 + TIME_NOW);
            if ($CURUSER['anonymous_until'] >= 1) {
                stderr('Error', "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.");
            }
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for 14 days Anonymous profile.\n " . $bonuscomment;
            sql_query('UPDATE users
                        SET anonymous_until = ' . sqlesc($anonymous_until) . ', seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $mc1->begin_transaction('user' . $userid);
            $mc1->update_row(false, ['anonymous_until' => $anonymous_until]);
            $mc1->commit_transaction($site_config['expires']['user_cache']);
            $mc1->begin_transaction('MyUser_' . $userid);
            $mc1->update_row(false, ['anonymous_until' => $anonymous_until]);
            $mc1->commit_transaction($site_config['expires']['curuser']);
            $mc1->begin_transaction('userstats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus]);
            $mc1->commit_transaction($site_config['expires']['u_stats']);
            $mc1->begin_transaction('user_stats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?anonymous_success=1");
            die;
            break;

        case 'parked':
//=== trade for parked option
            $parked_until = 1;
            if ($CURUSER['parked_until'] == 1) {
                stderr('Error', "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.");
            }
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for 14 days Anonymous profile.\n " . $bonuscomment;
            sql_query('UPDATE users
                        SET parked_until = ' . sqlesc($parked_until) . ', seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $mc1->begin_transaction('user' . $userid);
            $mc1->update_row(false, ['parked_until' => $parked_until]);
            $mc1->commit_transaction($site_config['expires']['user_cache']);
            $mc1->begin_transaction('MyUser_' . $userid);
            $mc1->update_row(false, ['parked_until' => $parked_until]);
            $mc1->commit_transaction($site_config['expires']['curuser']);
            $mc1->begin_transaction('userstats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus]);
            $mc1->commit_transaction($site_config['expires']['u_stats']);
            $mc1->begin_transaction('user_stats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?parked_success=1");
            die;
            break;

        case 'traffic2':
//=== trade for download credit
            $down = $download - $arr_points['menge'];
            if ($CURUSER['downloaded'] == 0) {
                stderr('Error', "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.");
            }
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for download credit removal.\n " . $bonuscomment;
            sql_query('UPDATE users
                        SET downloaded = ' . sqlesc($download - $arr_points['menge']) . ', seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $mc1->begin_transaction('userstats_' . $userid);
            $mc1->update_row(false, ['downloaded' => $download - $arr_points['menge'], 'seedbonus' => $seedbonus]);
            $mc1->commit_transaction($site_config['expires']['u_stats']);
            $mc1->begin_transaction('user_stats_' . $userid);
            $mc1->update_row(false, ['downloaded' => $download - $arr_points['menge'], 'seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?dload_success=1&amt=$points");
            die;
            break;

        case 'freeyear':
//=== trade for years freeleech
            $free_switch = (365 * 86400 + TIME_NOW);
            if ($User['free_switch'] != 0) {
                stderr('Error', "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.");
            }
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for One year of freeleech.\n " . $bonuscomment;
            sql_query('UPDATE users
                        SET free_switch = ' . sqlesc($free_switch) . ', seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $mc1->begin_transaction('user' . $userid);
            $mc1->update_row(false, ['free_switch' => $free_switch]);
            $mc1->commit_transaction($site_config['expires']['user_cache']);
            $mc1->begin_transaction('MyUser_' . $userid);
            $mc1->update_row(false, ['free_switch' => $free_switch]);
            $mc1->commit_transaction($site_config['expires']['curuser']);
            $mc1->begin_transaction('userstats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus]);
            $mc1->commit_transaction($site_config['expires']['u_stats']);
            $mc1->begin_transaction('user_stats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?freeyear_success=1");
            die;
            break;

        case 'freeslots':
//=== trade for freeslots
            $freeslots = (int)$User['freeslots'];
            $slots = $freeslots + $arr_points['menge'];
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for freeslots.\n " . $bonuscomment;
            sql_query('UPDATE users
                        SET freeslots = ' . sqlesc($slots) . ', seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $mc1->begin_transaction('user' . $userid);
            $mc1->update_row(false, ['freeslots' => $slots]);
            $mc1->commit_transaction($site_config['expires']['user_cache']);
            $mc1->begin_transaction('MyUser_' . $userid);
            $mc1->update_row(false, ['freeslots' => $slots]);
            $mc1->commit_transaction($site_config['expires']['curuser']);
            $mc1->begin_transaction('userstats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus]);
            $mc1->commit_transaction($site_config['expires']['u_stats']);
            $mc1->begin_transaction('user_stats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?freeslots_success=1");
            die;
            break;

        case 'itrade':
//=== trade for points
            $invites = (int)$User['invites'];
            $karma = $User['seedbonus'] + 200;
            $inv = $invites - 1;
            if ($CURUSER['invites'] == 0) {
                stderr('Error', "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.");
            }
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " invites for bonus points.\n" . $bonuscomment;
            sql_query('UPDATE users
                        SET invites = ' . sqlesc($inv) . ', seedbonus = ' . sqlesc($karma) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                        WHERE id = ' . sqlesc($userid) . ' AND invites =' . sqlesc($invites)) or sqlerr(__FILE__, __LINE__);
            $mc1->begin_transaction('user' . $userid);
            $mc1->update_row(false, ['invites' => $inv]);
            $mc1->commit_transaction($site_config['expires']['user_cache']);
            $mc1->begin_transaction('MyUser_' . $userid);
            $mc1->update_row(false, ['invites' => $inv]);
            $mc1->commit_transaction($site_config['expires']['curuser']);
            $mc1->begin_transaction('userstats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $karma]);
            $mc1->commit_transaction($site_config['expires']['u_stats']);
            $mc1->begin_transaction('user_stats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $karma, 'bonuscomment' => $bonuscomment]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?itrade_success=1");
            die;
            break;

        case 'itrade2':
//=== trade for slots
            $invites = (int)$User['invites'];
            $slots = (int)$User['freeslots'];
            $inv = $invites - 1;
            $fslot = $slots + 2;
            if ($CURUSER['invites'] == 0) {
                stderr('Error', "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.");
            }
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " invites for bonus points.\n" . $bonuscomment;
            sql_query('UPDATE users
                        SET invites = ' . sqlesc($inv) . ', freeslots =' . sqlesc($fslot) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                        WHERE id = ' . sqlesc($userid) . ' AND invites = ' . sqlesc($invites)) or sqlerr(__FILE__, __LINE__);
            $mc1->begin_transaction('user' . $userid);
            $mc1->update_row(false, ['invites' => $inv, 'freeslots' => $fslot]);
            $mc1->commit_transaction($site_config['expires']['user_cache']);
            $mc1->begin_transaction('MyUser_' . $userid);
            $mc1->update_row(false, ['invites' => $inv, 'freeslots' => $fslot]);
            $mc1->commit_transaction($site_config['expires']['curuser']);
            $mc1->begin_transaction('userstats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus]);
            $mc1->commit_transaction($site_config['expires']['u_stats']);
            $mc1->begin_transaction('user_stats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?itrade2_success=1");
            die;
            break;

        case 'pirate':
//=== trade for 2 weeks pirate status
            if ($CURUSER['pirate'] != 0 or $CURUSER['king'] != 0) {
                stderr('Error', "Now why would you want to add what you already have?<br>go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page and think that one over.");
            }
            $pirate = (86400 * 14 + TIME_NOW);
            $free_switch = (14 * 86400 + TIME_NOW);
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for 2 weeks Pirate + freeleech Status.\n " . $bonuscomment;
            sql_query('UPDATE users
                        SET free_switch = ' . sqlesc($free_switch) . ', pirate = ' . sqlesc($pirate) . ', seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $mc1->begin_transaction('user' . $userid);
            $mc1->update_row(false, ['free_switch' => $free_switch, 'pirate' => $pirate]);
            $mc1->commit_transaction($site_config['expires']['user_cache']);
            $mc1->begin_transaction('MyUser_' . $userid);
            $mc1->update_row(false, ['free_switch' => $free_switch, 'pirate' => $pirate]);
            $mc1->commit_transaction($site_config['expires']['curuser']);
            $mc1->begin_transaction('userstats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus]);
            $mc1->commit_transaction($site_config['expires']['u_stats']);
            $mc1->begin_transaction('user_stats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?pirate_success=1");
            die;
            break;

        case 'bounty':
//=== trade for pirates bounty
            $thief_id = $CURUSER['id'];
            $thief_name = $CURUSER['username'];
            $thief_rep = (int)$User['reputation'];
            $thief_bonus = (float)$User['seedbonus'];
            $rep_to_steal = $points / 1000;
            $new_bonus = $thief_bonus - $points;

            $pm = [];
            $pm['subject'] = sqlesc('You just got robbed by %s');
            $pm['subject_thief'] = sqlesc('Theft summary');
            $pm['message'] = sqlesc("Hey\nWe are sorry to announce that you have been robbed by [url=" . $site_config['baseurl'] . "/userdetails.php?id=%d]%s[/url]\nNow your total reputation is [b]%d[/b]\n[color=#ff0000]This is normal and you should not worry, if you have enough bonus points you can rob other people[/color]");
            $pm['message_thief'] = sqlesc("Hey %s\nYou robbed:\n%s\nYour total reputation is now [b]%d[/b] but you lost [b]%d[/b] karma points ");
            $foo = [50 => 3, 100 => 3, 150 => 4, 200 => 5, 250 => 5, 300 => 6];
            $user_limit = isset($foo[ $rep_to_steal ]) ? $foo[ $rep_to_steal ] : 0;
            $qr = sql_query('SELECT id, username, reputation, seedbonus
                                FROM users
                                WHERE id <> ' . $thief_id . ' AND reputation >= ' . $rep_to_steal . '
                                ORDER BY RAND() LIMIT ' . $user_limit) or sqlerr(__FILE__, __LINE__);
            $update_users = $pms = $robbed_user = [];
            while ($ar = mysqli_fetch_assoc($qr)) {
                $new_rep = $ar['reputation'] - $rep_to_steal;
                $update_users[] = '(' . $ar['id'] . ',' . ($ar['reputation'] - $rep_to_steal) . ',' . $ar['seedbonus'] . ')';
                $pms[] = '(' . $site_config['chatBotID'] . ',' . $ar['id'] . ',' . TIME_NOW . ',' . sprintf($pm['subject'], $thief_name) . ',' . sprintf($pm['message'], $thief_id, $thief_name, $new_rep) . ')';
                $robbed_users[] = sprintf('[url=' . $site_config['baseurl'] . '/userdetails.php?id=%d]%s[/url]', $ar['id'], $ar['username']);
                //== cache updates ???
                $mc1->begin_transaction('MyUser_' . $ar['id']);
                $mc1->update_row(false, ['reputation' => $ar['reputation'] - $rep_to_steal]);
                $mc1->commit_transaction($site_config['expires']['curuser']);
                $mc1->begin_transaction('user' . $ar['id']);
                $mc1->update_row(false, ['reputation' => $ar['reputation'] - $rep_to_steal]);
                $mc1->commit_transaction($site_config['expires']['user_cache']);

                $mc1->begin_transaction('userstats_' . $ar['id']);
                $mc1->update_row(false, ['seedbonus' => $ar['seedbonus']]);
                $mc1->commit_transaction($site_config['expires']['u_stats']);
                $mc1->begin_transaction('user_stats_' . $ar['id']);
                $mc1->update_row(false, ['seedbonus' => $ar['seedbonus']]);
                $mc1->commit_transaction($site_config['expires']['user_stats']);
                //$mc1->delete_value('inbox_new_'.$pms);
                //$mc1->delete_value('inbox_new_sb_'.$pms);
                // end
            }
            if (count($update_users)) {
                $new_bonus = $thief_bonus - $points;
                $new_rep = $thief_rep + ($user_limit * $rep_to_steal);
                $update_users[] = '(' . $thief_id . ',' . $new_rep . ',' . $new_bonus . ')';
                $pms[] = '(0,' . $thief_id . ',' . TIME_NOW . ',' . $pm['subject_thief'] . ',' . sprintf($pm['message_thief'], $thief_name, join("\n", $robbed_users), $new_rep, $points) . ')';
                sql_query('INSERT INTO users(id,reputation,seedbonus)
                            VALUES ' . join(',', $update_users) . '
                            ON DUPLICATE KEY UPDATE reputation=values(reputation),seedbonus=values(seedbonus) ') or sqlerr(__FILE__, __LINE__);
                sql_query('INSERT INTO messages(sender,receiver,added,subject,msg)
                            VALUES ' . join(',', $pms)) or sqlerr(__FILE__, __LINE__);
                //== cache updates ???
                $mc1->begin_transaction('MyUser_' . $thief_id);
                $mc1->update_row(false, ['reputation' => $new_rep]);
                $mc1->commit_transaction($site_config['expires']['curuser']);
                $mc1->begin_transaction('user' . $thief_id);
                $mc1->update_row(false, ['reputation' => $new_rep]);
                $mc1->commit_transaction($site_config['expires']['user_cache']);

                $mc1->begin_transaction('userstats_' . $thief_id);
                $mc1->update_row(false, ['seedbonus' => $new_bonus]);
                $mc1->commit_transaction($site_config['expires']['u_stats']);
                $mc1->begin_transaction('user_stats_' . $thief_id);
                $mc1->update_row(false, ['seedbonus' => $new_bonus]);
                $mc1->commit_transaction($site_config['expires']['user_stats']);
                //$mc1->delete_value('inbox_new_'.$pms);
                //$mc1->delete_value('inbox_new_sb_'.$pms);
            }
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?bounty_success=1");
            die;
            break;

        case 'king':
//=== trade for one month king status
            if ($CURUSER['king'] != 0 or $CURUSER['pirate'] != 0) {
                stderr('Error', "Now why would you want to add what you already have?<br>go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page and think that one over.");
            }
            $king = (86400 * 30 + TIME_NOW);
            $free_switch = (30 * 86400 + TIME_NOW);
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for 1 month King + freeleech Status.\n " . $bonuscomment;
            sql_query('UPDATE users
                        SET free_switch = ' . sqlesc($free_switch) . ', king = ' . sqlesc($king) . ', seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $mc1->begin_transaction('user' . $userid);
            $mc1->update_row(false, ['free_switch' => $free_switch, 'king' => $king]);
            $mc1->commit_transaction($site_config['expires']['user_cache']);
            $mc1->begin_transaction('MyUser_' . $userid);
            $mc1->update_row(false, ['free_switch' => $free_switch, 'king' => $king]);
            $mc1->commit_transaction($site_config['expires']['curuser']);
            $mc1->begin_transaction('userstats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus]);
            $mc1->commit_transaction($site_config['expires']['u_stats']);
            $mc1->begin_transaction('user_stats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?king_success=1");
            die;
            break;

//--- Freeleech
        case 'freeleech':
            $pointspool = (int)$arr_points['pointspool'];
            $points2 = $points - $pointspool;
            $donation = (int)$_POST['donate'];
            $seedbonus = ($bonus - $donation);
            if ($bonus < $donation || $donation <= 0 || $donation > $points2) {
                stderr('Error', ' <br>Points: ' . (float)$donation . ' <br> Bonus: ' . (float)$bonus . ' <br> Donation: ' . (float)$donation . " <br>Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.<br> Click to go back to your <a class='altlink' href='./mybonus.php'>Karma Bonus Point</a> page.<br>");
                die;
            }
            if (($pointspool + $donation) >= $arr_points['points']) {
                $now = TIME_NOW;
                $end = (86400 * 3 + TIME_NOW);
                $message = sqlesc('FreeLeech [ON]');
                sql_query('INSERT INTO events (userid, overlayText, startTime, endTime, displayDates, freeleechEnabled)
                            VALUES (' . sqlesc($userid) . ", $message, $now, $end, 1, 1)") or sqlerr(__FILE__, __LINE__);
                $norefund = ($donation + $pointspool) % $points;
                $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $donation . " Points contributed for freeleech.\n " . $bonuscomment;
                sql_query('UPDATE users
                            SET seedbonus = ' . sqlesc($seedbonus) . ',  bonuscomment = ' . sqlesc($bonuscomment) . '
                            WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                sql_query('UPDATE bonus
                            SET pointspool = ' . sqlesc($norefund) . "
                            WHERE id = '11' LIMIT 1") or sqlerr(__FILE__, __LINE__);
                $mc1->begin_transaction('userstats_' . $userid);
                $mc1->update_row(false, ['seedbonus' => $seedbonus]);
                $mc1->commit_transaction($site_config['expires']['u_stats']);
                $mc1->begin_transaction('user_stats_' . $userid);
                $mc1->update_row(false, ['seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
                $mc1->commit_transaction($site_config['expires']['user_stats']);
                $mc1->delete_value('freecontribution_');
                $mc1->delete_value('top_donators_');
                $mc1->delete_value('freeleech_counter');
                $mc1->delete_value('freeleech_counter_alerts_');
                $mc1->delete_value('freecontribution_datas_');
                $mc1->delete_value('freecontribution_datas_alerts_');
                write_bonus_log($CURUSER['id'], $donation, $type = 'freeleech');
                $msg = $CURUSER['username'] . ' Donated ' . $donation . ' karma point' . ($donation > 1 ? 's' : '') . ' into the freeleech contribution pot and has activated freeleech for 3 days ' . $donation . '/' . $points . '';
                autoshout($msg);
                header("Refresh: 0; url={$site_config['baseurl']}//mybonus.php?freeleech_success=1&norefund=$norefund");
                die;
            } else {
                // add to the pool
                sql_query('UPDATE bonus
                            SET pointspool = pointspool + ' . sqlesc($donation) . "
                            WHERE id = '11' LIMIT 1") or sqlerr(__FILE__, __LINE__);
                $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $donation . " Points contributed for freeleech.\n " . $bonuscomment;
                sql_query('UPDATE users SET
                            seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                            WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                $mc1->begin_transaction('userstats_' . $userid);
                $mc1->update_row(false, ['seedbonus' => $seedbonus]);
                $mc1->commit_transaction($site_config['expires']['u_stats']);
                $mc1->begin_transaction('user_stats_' . $userid);
                $mc1->update_row(false, ['seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
                $mc1->commit_transaction($site_config['expires']['user_stats']);
                $mc1->delete_value('freecontribution_');
                $mc1->delete_value('top_donators_');
                $mc1->delete_value('freeleech_counter');
                $mc1->delete_value('freeleech_counter_alerts_');
                $mc1->delete_value('freecontribution_datas_');
                $mc1->delete_value('freecontribution_datas_alerts_');
                write_bonus_log($CURUSER['id'], $donation, $type = 'freeleech');
                $Remaining = ($arr_points['points'] - $arr_points['pointspool'] - $donation);
                $msg = $CURUSER['username'] . ' Donated ' . $donation . ' karma point' . ($donation > 1 ? 's' : '') . ' into the freeleech contribution pot ! * Only [b]' . htmlsafechars($Remaining) . '[/b] more karma point' . ($Remaining > 1 ? 's' : '') . " to go! * [color=green][b]Freeleech contribution:[/b][/color] [url={$site_config['baseurl']}/mybonus.php]" . $donation . '/' . $points . '[/url]';
                autoshout($msg);
                header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?freeleech_success=2");
                die;
            }
            die;
            break;

//--- doubleupload
        case 'doubleup':
            $pointspool = (int)$arr_points['pointspool'];
            $points2 = $points - $pointspool;
            $donation = (int)$_POST['donate'];
            $seedbonus = ($bonus - $donation);
            if ($bonus < $donation || $donation <= 0 || $donation > $points2) {
                stderr('Error', ' <br>Points: ' . (float)$donation . ' <br> Bonus: ' . (float)$bonus . ' <br> Donation: ' . (float)$donation . " <br>Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.<br> Click to go back to your <a class='altlink' href='./mybonus.php'>Karma Bonus Point</a> page.<br>");
                die;
            }
            if (($pointspool + $donation) >= $arr_points['points']) {
                $now = TIME_NOW;
                $end = (86400 * 3 + TIME_NOW);
                $message = sqlesc('DoubleUpload [ON]');
                sql_query('INSERT INTO events(userid, overlayText, startTime, endTime, displayDates, duploadEnabled)
                            VALUES (' . sqlesc($userid) . ", $message, $now, $end, 1, 1)") or sqlerr(__FILE__, __LINE__);
                $norefund = ($donation + $pointspool) % $points;
                $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $donation . " Points contributed for doubleupload.\n " . $bonuscomment;
                sql_query('UPDATE users
                            SET seedbonus = ' . sqlesc($seedbonus) . ',  bonuscomment = ' . sqlesc($bonuscomment) . '
                            WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                sql_query('UPDATE bonus
                            SET pointspool = ' . sqlesc($norefund) . "
                            WHERE id = '12' LIMIT 1") or sqlerr(__FILE__, __LINE__);
                $mc1->begin_transaction('userstats_' . $userid);
                $mc1->update_row(false, ['seedbonus' => $seedbonus]);
                $mc1->commit_transaction($site_config['expires']['u_stats']);
                $mc1->begin_transaction('user_stats_' . $userid);
                $mc1->update_row(false, ['seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
                $mc1->commit_transaction($site_config['expires']['user_stats']);
                $mc1->delete_value('freecontribution_');
                $mc1->delete_value('top_donators2_');
                $mc1->delete_value('doubleupload_counter');
                $mc1->delete_value('doubleupload_counter_alerts_');
                $mc1->delete_value('freecontribution_datas_');
                $mc1->delete_value('freecontribution_datas_alerts_');
                write_bonus_log($CURUSER['id'], $donation, $type = 'doubleupload');
                $msg = $CURUSER['username'] . ' Donated ' . $donation . ' karma point' . ($donation > 1 ? 's' : '') . ' into the double upload contribution pot and has activated Double Upload for 3 days ' . $donation . '/' . $points . '';
                autoshout($msg);
                header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?doubleup_success=1&norefund=$norefund");
                die;
            } else {
                // add to the pool
                sql_query('UPDATE bonus
                            SET pointspool = pointspool + ' . sqlesc($donation) . "
                            WHERE id = '12' LIMIT 1") or sqlerr(__FILE__, __LINE__);
                $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $donation . " Points contributed for doubleupload.\n " . $bonuscomment;
                sql_query('UPDATE users
                            SET seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                            WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                $mc1->begin_transaction('userstats_' . $userid);
                $mc1->update_row(false, ['seedbonus' => $seedbonus]);
                $mc1->commit_transaction($site_config['expires']['u_stats']);
                $mc1->begin_transaction('user_stats_' . $userid);
                $mc1->update_row(false, ['seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
                $mc1->commit_transaction($site_config['expires']['user_stats']);
                $mc1->delete_value('freecontribution_');
                $mc1->delete_value('top_donators2_');
                $mc1->delete_value('doubleupload_counter');
                $mc1->delete_value('doubleupload_counter_alerts_');
                $mc1->delete_value('freecontribution_datas_');
                $mc1->delete_value('freecontribution_datas_alerts_');
                write_bonus_log($CURUSER['id'], $donation, $type = 'doubleupload');
                $Remaining = ($arr_points['points'] - $arr_points['pointspool'] - $donation);
                $msg = $CURUSER['username'] . ' Donated ' . $donation . ' karma point' . ($donation > 1 ? 's' : '') . ' into the double upload contribution pot ! * Only [b]' . htmlsafechars($Remaining) . '[/b] more karma point' . ($Remaining > 1 ? 's' : '') . " to go! * [color=green][b]Double upload contribution:[/b][/color] [url={$site_config['baseurl']}/mybonus.php]" . $donation . '/' . $points . '[/url]';
                autoshout($msg);
                header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?doubleup_success=2");
                die;
            }
            die;
            break;

//---Halfdownload
        case 'halfdown':
            $pointspool = (int)$arr_points['pointspool'];
            $points2 = $points - $pointspool;
            $donation = (int)$_POST['donate'];
            $seedbonus = ($bonus - $donation);
            if ($bonus < $donation || $donation <= 0 || $donation > $points2) {
                stderr('Error', ' <br>Points: ' . (float)$donation . ' <br> Bonus: ' . (float)$bonus . ' <br> Donation: ' . (float)$donation . " <br>Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.<br> Click to go back to your <a class='altlink' href='./mybonus.php'>Karma Bonus Point</a> page.<br>");
                die;
            }
            if (($pointspool + $donation) >= $arr_points['points']) {
                $now = TIME_NOW;
                $end = (86400 * 3 + TIME_NOW);
                $message = sqlesc('HalfDownload [ON]');
                sql_query('INSERT INTO events(userid, overlayText, startTime, endTime, displayDates, hdownEnabled)
                            VALUES (' . sqlesc($userid) . ", $message, $now, $end, 1, 1)") or sqlerr(__FILE__, __LINE__);
                $norefund = ($donation + $pointspool) % $points;
                $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $donation . " Points contributed for Halfdownload.\n " . $bonuscomment;
                sql_query('UPDATE users
                            SET seedbonus = ' . sqlesc($seedbonus) . ',  bonuscomment = ' . sqlesc($bonuscomment) . '
                            WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                sql_query('UPDATE bonus
                            SET pointspool = ' . sqlesc($norefund) . "
                            WHERE id = '13' LIMIT 1") or sqlerr(__FILE__, __LINE__);
                $mc1->begin_transaction('userstats_' . $userid);
                $mc1->update_row(false, ['seedbonus' => $seedbonus]);
                $mc1->commit_transaction($site_config['expires']['u_stats']);
                $mc1->begin_transaction('user_stats_' . $userid);
                $mc1->update_row(false, ['seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
                $mc1->commit_transaction($site_config['expires']['user_stats']);
                $mc1->delete_value('freecontribution_');
                $mc1->delete_value('top_donators3_');
                $mc1->delete_value('halfdownload_counter');
                $mc1->delete_value('halfdownload_counter_alerts_');
                $mc1->delete_value('freecontribution_datas_');
                $mc1->delete_value('freecontribution_datas_alerts_');
                write_bonus_log($CURUSER['id'], $donation, $type = 'halfdownload');
                $msg = $CURUSER['username'] . ' Donated ' . $donation . ' karma point' . ($donation > 1 ? 's' : '') . ' into the half download contribution pot and has activated half download for 3 days ' . $donation . '/' . $points . '';
                autoshout($msg);
                header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?halfdown_success=1&norefund=$norefund");
                die;
            } else {
                // add to the pool
                sql_query('UPDATE bonus
                            SET pointspool = pointspool + ' . sqlesc($donation) . "
                            WHERE id = '13' LIMIT 1") or sqlerr(__FILE__, __LINE__);
                $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points contributed for halfdownload.\n " . $bonuscomment;
                sql_query('UPDATE users
                            SET seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                            WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                $mc1->begin_transaction('userstats_' . $userid);
                $mc1->update_row(false, ['seedbonus' => $seedbonus]);
                $mc1->commit_transaction($site_config['expires']['u_stats']);
                $mc1->begin_transaction('user_stats_' . $userid);
                $mc1->update_row(false, ['seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
                $mc1->commit_transaction($site_config['expires']['user_stats']);
                $mc1->delete_value('freecontribution_');
                $mc1->delete_value('top_donators3_');
                $mc1->delete_value('halfdownload_counter');
                $mc1->delete_value('halfdownload_counter_alerts_');
                $mc1->delete_value('freecontribution_datas_');
                $mc1->delete_value('freecontribution_datas_alerts_');
                write_bonus_log($CURUSER['id'], $donation, $type = 'halfdownload');
                $Remaining = ($arr_points['points'] - $arr_points['pointspool'] - $donation);
                $msg = $CURUSER['username'] . ' Donated ' . $donation . ' karma point' . ($donation > 1 ? 's' : '') . ' into the half download contribution pot ! * Only [b]' . htmlsafechars($Remaining) . '[/b] more karma point' . ($Remaining > 1 ? 's' : '') . " to go! * [color=green][b]Half download contribution:[/b][/color] [url={$site_config['baseurl']}/mybonus.php]" . $donation . '/' . $points . '[/url]';
                autoshout($msg);
                header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?halfdown_success=2");
                die;
            }
            die;
            break;

        case 'ratio':
//=== trade for one torrent 1:1 ratio
            $torrent_number = (int)$_POST['torrent_id'];
            $res_snatched = sql_query('SELECT s.uploaded, s.downloaded, t.name
                                        FROM snatched AS s
                                        LEFT JOIN torrents AS t ON t.id = s.torrentid
                                        WHERE s.userid = ' . sqlesc($userid) . ' AND torrentid = ' . sqlesc($torrent_number) . '
                                        LIMIT 1') or sqlerr(__FILE__, __LINE__);
            $arr_snatched = mysqli_fetch_assoc($res_snatched);
            if ($arr_snatched['size'] > 6442450944) {
                stderr('Error', "One to One ratio only works on torrents smaller then 6GB!<br><br>Back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Points</a> page.");
            }
            if ($arr_snatched['name'] == '') {
                stderr('Error', "No torrent with that ID!<br>Back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Points</a> page.");
            }
            if ($arr_snatched['uploaded'] >= $arr_snatched['downloaded']) {
                stderr('Error', "Your ratio on that torrent is fine, you must have selected the wrong torrent ID.<br>Back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Points</a> page.");
            }
            sql_query('UPDATE snatched
                        SET uploaded = ' . sqlesc($arr_snatched['downloaded']) . '
                        WHERE userid = ' . sqlesc($userid) . ' AND torrentid = ' . sqlesc($torrent_number)) or sqlerr(__FILE__, __LINE__);
            $difference = $arr_snatched['downloaded'] - $arr_snatched['uploaded'];
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . ' Points for 1 to 1 ratio on torrent: ' . htmlsafechars($arr_snatched['name']) . ' ' . $torrent_number . ', ' . $difference . " added .\n " . $bonuscomment;
            sql_query('UPDATE users
                        SET uploaded = ' . sqlesc($upload + $difference) . ', bonuscomment = ' . sqlesc($bonuscomment) . ', seedbonus = ' . sqlesc($seedbonus) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $mc1->begin_transaction('userstats_' . $userid);
            $mc1->update_row(false, ['uploaded' => $upload + $difference, 'seedbonus' => $seedbonus]);
            $mc1->commit_transaction($site_config['expires']['u_stats']);
            $mc1->begin_transaction('user_stats_' . $userid);
            $mc1->update_row(false, ['uploaded' => $upload + $difference, 'seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?ratio_success=1");
            die;
            break;

        case 'bump':
//=== Reanimate a torrent
            $torrent_number = isset($_POST['torrent_id']) ? intval($_POST['torrent_id']) : 0;
            $res_free = sql_query('SELECT name
                                    FROM torrents
                                    WHERE id = ' . sqlesc($torrent_number)) or sqlerr(__FILE__, __LINE__);
            $arr_free = mysqli_fetch_assoc($res_free);
            if ($arr_free['name'] == '') {
                stderr('Error', 'No torrent with that ID!<br><br>Back to your <a class="altlink" href="karma_bonus.php">Karma Points</a> page.');
            }
            $free_time = (7 * 86400 + TIME_NOW);
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . ' Points to Reanimate torrent: ' . $arr_free['name'] . ".\n " . $bonuscomment;
            sql_query('UPDATE users
                        SET bonuscomment = ' . sqlesc($bonuscomment) . ', seedbonus = ' . sqlesc($seedbonus) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            sql_query('UPDATE torrents
                        SET bump = "yes", free = ' . sqlesc($free_time) . ', added = ' . TIME_NOW . '
                        WHERE id = ' . sqlesc($torrent_number)) or sqlerr(__FILE__, __LINE__);
            $mc1->begin_transaction('userstats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus]);
            $mc1->commit_transaction($site_config['expires']['u_stats']);
            $mc1->begin_transaction('user_stats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            $mc1->begin_transaction('torrent_details_' . $torrent_number);
            $mc1->update_row(false, ['added' => TIME_NOW, 'bump' => 'yes', 'free' => $free_time]);
            $mc1->commit_transaction(0);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?bump_success=1&t_name={$torrent_number}");
            die;
            break;

        case 'class':
//=== trade for one month VIP status
            if ($CURUSER['class'] > UC_VIP) {
                stderr('Error', "Now why would you want to lower yourself to VIP?<br>go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page and think that one over.");
            }
            $vip_until = (86400 * 28 + TIME_NOW);
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for 1 month VIP Status.\n " . $bonuscomment;
            sql_query('UPDATE users
                        SET class = ' . sqlesc(UC_VIP) . ", vip_added = 'yes', vip_until = " . sqlesc($vip_until) . ', seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $mc1->begin_transaction('user' . $userid);
            $mc1->update_row(false, ['class' => 2, 'vip_added' => 'yes', 'vip_until' => $vip_until]);
            $mc1->commit_transaction($site_config['expires']['user_cache']);
            $mc1->begin_transaction('MyUser_' . $userid);
            $mc1->update_row(false, ['class' => 2, 'vip_added' => 'yes', 'vip_until' => $vip_until]);
            $mc1->commit_transaction($site_config['expires']['curuser']);
            $mc1->begin_transaction('userstats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus]);
            $mc1->commit_transaction($site_config['expires']['u_stats']);
            $mc1->begin_transaction('user_stats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?class_success=1");
            die;
            break;

        case 'warning':
//=== trade for removal of warning :P
            if ($CURUSER['warned'] == 0) {
                stderr('Error', "How can we remove a warning that isn't there?<br>go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page and think that one over.");
            }
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for removing warning.\n " . $bonuscomment;
            $res_warning = sql_query('SELECT modcomment
                                        FROM users
                                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $arr = mysqli_fetch_assoc($res_warning);
            $modcomment = htmlsafechars($arr['modcomment']);
            $modcomment = get_date(TIME_NOW, 'DATE', 1) . " - Warning removed by - Bribe with Karma.\n" . $modcomment;
            $modcom = sqlesc($modcomment);
            sql_query("UPDATE users
                        SET warned = 0, seedbonus = " . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . ', modcomment = ' . sqlesc($modcom) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $dt = sqlesc(TIME_NOW);
            $subject = sqlesc('Warning removed by Karma.');
            $msg = sqlesc("Your warning has been removed by the big Karma payoff... Please keep on your best behaviour from now on.\n");
            sql_query('INSERT INTO messages (sender, receiver, added, msg, subject)
                        VALUES (0, ' . sqlesc($userid) . ", $dt, $msg, $subject)") or sqlerr(__FILE__, __LINE__);
            $mc1->begin_transaction('user' . $userid);
            $mc1->update_row(false, ['warned' => 0]);
            $mc1->commit_transaction($site_config['expires']['user_cache']);
            $mc1->begin_transaction('MyUser_' . $userid);
            $mc1->update_row(false, ['warned' => 0]);
            $mc1->commit_transaction($site_config['expires']['curuser']);
            $mc1->begin_transaction('userstats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus]);
            $mc1->commit_transaction($site_config['expires']['u_stats']);
            $mc1->begin_transaction('user_stats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment, 'modcomment' => $modcomment]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            $mc1->delete_value('inbox_new_' . $userid);
            $mc1->delete_value('inbox_new_sb_' . $userid);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?warning_success=1");
            die;
            break;

        case 'smile':
//=== trade for one month special smilies :P
            $smile_until = (86400 * 28 + TIME_NOW);
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for 1 month of custom smilies.\n " . $bonuscomment;
            sql_query('UPDATE users
                        SET smile_until = ' . sqlesc($smile_until) . ', seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $mc1->begin_transaction('user' . $userid);
            $mc1->update_row(false, ['smile_until' => $smile_until]);
            $mc1->commit_transaction($site_config['expires']['user_cache']);
            $mc1->begin_transaction('MyUser_' . $userid);
            $mc1->update_row(false, ['smile_until' => $smile_until]);
            $mc1->commit_transaction($site_config['expires']['curuser']);
            $mc1->begin_transaction('userstats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus]);
            $mc1->commit_transaction($site_config['expires']['u_stats']);
            $mc1->begin_transaction('user_stats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?smile_success=1");
            die;
            break;

        case 'invite':
//=== trade for invites
            $invites = (int)$User['invites'];
            $inv = $invites + 3;
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for invites.\n " . $bonuscomment;
            sql_query('UPDATE users
                        SET invites = ' . sqlesc($inv) . ', seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $mc1->begin_transaction('user' . $userid);
            $mc1->update_row(false, ['invites' => $inv]);
            $mc1->commit_transaction($site_config['expires']['user_cache']);
            $mc1->begin_transaction('MyUser_' . $userid);
            $mc1->update_row(false, ['invites' => $inv]);
            $mc1->commit_transaction($site_config['expires']['curuser']);
            $mc1->begin_transaction('userstats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus]);
            $mc1->commit_transaction($site_config['expires']['u_stats']);
            $mc1->begin_transaction('user_stats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?invite_success=1");
            die;
            break;

        case 'title':
//=== trade for special title
            /**** the $words array are words that you DO NOT want the user to have... use to filter "bad words" & user class...
             * the user class is just for show, but what the hell :p Add more or edit to your liking.
             *note if they try to use a restricted word, they will recieve the special title "I just wasted my karma" *****/
            $title = strip_tags(htmlsafechars($_POST['title']));
            $title = str_replace($site_config['bad_words'], 'I just wasted my karma', $title);
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for custom title. Old title was {$CURUSER['title']} new title is " . $title . ".\n " . $bonuscomment;
            sql_query('UPDATE users
                        SET title = ' . sqlesc($title) . ', seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $mc1->begin_transaction('user' . $userid);
            $mc1->update_row(false, ['title' => $title]);
            $mc1->commit_transaction($site_config['expires']['user_cache']);
            $mc1->begin_transaction('MyUser_' . $userid);
            $mc1->update_row(false, ['title' => $title]);
            $mc1->commit_transaction($site_config['expires']['curuser']);
            $mc1->begin_transaction('userstats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus]);
            $mc1->commit_transaction($site_config['expires']['u_stats']);
            $mc1->begin_transaction('user_stats_' . $userid);
            $mc1->update_row(false, ['seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?title_success=1");
            die;
            break;

        case 'gift_1':
//=== trade for giving the gift of karma
            $points = (int)$_POST['bonusgift'];
            $usernamegift = htmlsafechars($_POST['username']);
            $res = sql_query('SELECT id, seedbonus, bonuscomment, username
                                FROM users
                                WHERE username = ' . sqlesc($usernamegift)) or sqlerr(__FILE__, __LINE__);
            $arr = mysqli_fetch_assoc($res);
            $useridgift = (int)$arr['id'];
            $userseedbonus = (float)$arr['seedbonus'];
            $bonuscomment_gift = htmlsafechars($arr['bonuscomment']);
            $usernamegift = htmlsafechars($arr['username']);

            $check_me = [100, 200, 300, 400, 500, 1000, 5000, 10000, 20000, 50000, 100000];
            if (!in_array($points, $check_me)) {
                stderr('Error', 'I smell a rat!');
            }

            if ($bonus >= $points) {
                $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points as gift to $usernamegift .\n " . $bonuscomment;
                $bonuscomment_gift = get_date(TIME_NOW, 'DATE', 1) . ' - recieved ' . $points . " Points as gift from {$CURUSER['username']} .\n " . $bonuscomment_gift;
                $seedbonus = $bonus - $points;
                $giftbonus1 = $userseedbonus + $points;
                if ($userid == $useridgift) {
                    header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?gift_fail=1");
                    die;
                }
                if (!$useridgift) {
                    header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?gift_fail_user=1");
                    die;
                }
                sql_query('SELECT bonuscomment, id
                            FROM users
                            WHERE id = ' . sqlesc($useridgift)) or sqlerr(__FILE__, __LINE__);
                //=== and to post to the person who gets the gift!
                sql_query('UPDATE users
                            SET seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                            WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                sql_query('UPDATE users
                            SET seedbonus = ' . sqlesc($giftbonus1) . ', bonuscomment = ' . sqlesc($bonuscomment_gift) . '
                            WHERE id = ' . sqlesc($useridgift)) or sqlerr(__FILE__, __LINE__);
                $mc1->begin_transaction('userstats_' . $userid);
                $mc1->update_row(false, ['seedbonus' => $seedbonus]);
                $mc1->commit_transaction($site_config['expires']['u_stats']);
                $mc1->begin_transaction('user_stats_' . $userid);
                $mc1->update_row(false, ['seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment]);
                $mc1->commit_transaction($site_config['expires']['user_stats']);
                $mc1->begin_transaction('userstats_' . $useridgift);
                $mc1->update_row(false, ['seedbonus' => $giftbonus1]);
                $mc1->commit_transaction($site_config['expires']['u_stats']);
                $mc1->begin_transaction('user_stats_' . $useridgift);
                $mc1->update_row(false, ['seedbonus' => $giftbonus1, 'bonuscomment' => $bonuscomment_gift]);
                $mc1->commit_transaction($site_config['expires']['user_stats']);
                //===send message
                $subject = sqlesc('Someone Loves you');
                $added = sqlesc(TIME_NOW);
                $msg = sqlesc("You have been given a gift of $points Karma points by " . $CURUSER['username']);
                sql_query("INSERT INTO messages (sender, subject, receiver, msg, added)
                            VALUES (0, $subject, $useridgift, $msg, $added)") or sqlerr(__FILE__, __LINE__);
                $mc1->delete_value('inbox_new_' . $useridgift);
                $mc1->delete_value('inbox_new_sb_' . $useridgift);
                header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?gift_success=1&gift_amount_points=$points&usernamegift=$usernamegift&gift_id=$useridgift");
                die;
            } else {
                header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?gift_fail_points=1");
                die;
            }
            break;
    }
}

//==== This is the default page
$HTMLOUT .= "
    <div class='container is-fluid portlet'>
        <div class='has-text-centered size_6 top20 bottom20'>Karma Bonus Point's System</div>";
$fpoints = $dpoints = $hpoints = $freeleech_enabled = $double_upload_enabled = $half_down_enabled = $top_donators = $top_donators2 = $top_donators3 = $count1 = '';
// eZER0's mod for bonus contribution
// Limited this to 3 because of performance reasons and i wanted to go through last 3 events, anyway the most we can have
// is that halfdownload is enabled, double upload is enabled as well as freeleech!
if (XBT_TRACKER == false) {
    if (($scheduled_events = $mc1->get_value('freecontribution_datas_')) === false) {
        $scheduled_events = mysql_fetch_all('SELECT * FROM `events` ORDER BY `startTime` DESC LIMIT 3;', []);
        $mc1->cache_value('freecontribution_datas_', $scheduled_events, 3 * 86400);
    }

    if (is_array($scheduled_events)) {
        foreach ($scheduled_events as $scheduled_event) {
            if (is_array($scheduled_event) && array_key_exists('startTime', $scheduled_event) &&
                array_key_exists('endTime', $scheduled_event)) {
                $startTime = 0;
                $endTime = 0;
                $startTime = $scheduled_event['startTime'];
                $endTime = $scheduled_event['endTime'];
                if (TIME_NOW < $endTime && TIME_NOW > $startTime) {
                    if (array_key_exists('freeleechEnabled', $scheduled_event)) {
                        $freeleechEnabled = $scheduled_event['freeleechEnabled'];
                        if ($scheduled_event['freeleechEnabled']) {
                            $freeleech_start_time = $scheduled_event['startTime'];
                            $freeleech_end_time = $scheduled_event['endTime'];
                            $freeleech_enabled = true;
                        }
                    }
                    if (array_key_exists('duploadEnabled', $scheduled_event)) {
                        $duploadEnabled = $scheduled_event['duploadEnabled'];
                        if ($scheduled_event['duploadEnabled']) {
                            $double_upload_start_time = $scheduled_event['startTime'];
                            $double_upload_end_time = $scheduled_event['endTime'];
                            $double_upload_enabled = true;
                        }
                    }
                    if (array_key_exists('hdownEnabled', $scheduled_event)) {
                        $hdownEnabled = $scheduled_event['hdownEnabled'];
                        if ($scheduled_event['hdownEnabled']) {
                            $half_down_start_time = $scheduled_event['startTime'];
                            $half_down_end_time = $scheduled_event['endTime'];
                            $half_down_enabled = true;
                        }
                    }
                }
            }
        }
    }
    if (($freeleech_counter = $mc1->get_value('freeleech_counter')) === false) {
        $total_fl = sql_query('SELECT SUM(pointspool) AS pointspool, points
                                FROM bonus
                                WHERE id = 11') or sqlerr(__FILE__, __LINE__);
        $fl_total_row = mysqli_fetch_assoc($total_fl);
        $percent_fl = number_format($fl_total_row['pointspool'] / $fl_total_row['points'] * 100, 2);
        $mc1->cache_value('freeleech_counter', $percent_fl, 0);
    } else {
        $percent_fl = $freeleech_counter;
    }

    switch ($percent_fl) {
        case $percent_fl >= 90:
            $font_color_fl = '<span style="color: green">' . number_format($percent_fl) . ' %</span>';
            break;
        case $percent_fl >= 80:
            $font_color_fl = '<span style="color: lightgreen">' . number_format($percent_fl) . ' %</span>';
            break;
        case $percent_fl >= 70:
            $font_color_fl = '<span style="color: jade">' . number_format($percent_fl) . ' %</span>';
            break;
        case $percent_fl >= 50:
            $font_color_fl = '<span style="color: turquoise">' . number_format($percent_fl) . ' %</span>';
            break;
        case $percent_fl >= 40:
            $font_color_fl = '<span style="color: lightblue">' . number_format($percent_fl) . ' %</span>';
            break;
        case $percent_fl >= 30:
            $font_color_fl = '<span style="color: yellow">' . number_format($percent_fl) . ' %</span>';
            break;
        case $percent_fl >= 20:
            $font_color_fl = '<span style="color: orange">' . number_format($percent_fl) . ' %</span>';
            break;
        case $percent_fl < 20:
            $font_color_fl = '<span style="color: red">' . number_format($percent_fl) . ' %</span>';
            break;
    }
    //$mc1->delete_value('freeleech_counter');
    //=== get total points
    //$target_du = 30000;
    if (($doubleupload_counter = $mc1->get_value('doubleupload_counter')) === false) {
        $total_du = sql_query('SELECT SUM(pointspool) AS pointspool, points
                                FROM bonus
                                WHERE id = 12') or sqlerr(__FILE__, __LINE__);
        $du_total_row = mysqli_fetch_assoc($total_du);
        $percent_du = number_format($du_total_row['pointspool'] / $du_total_row['points'] * 100, 2);
        $mc1->cache_value('doubleupload_counter', $percent_du, 0);
    } else {
        $percent_du = $doubleupload_counter;
    }
    switch ($percent_du) {
        case $percent_du >= 90:
            $font_color_du = '<span style="color: green">' . number_format($percent_du) . ' %</span>';
            break;
        case $percent_du >= 80:
            $font_color_du = '<span style="color: lightgreen">' . number_format($percent_du) . ' %</span>';
            break;
        case $percent_du >= 70:
            $font_color_du = '<span style="color: jade">' . number_format($percent_du) . ' %</span>';
            break;
        case $percent_du >= 50:
            $font_color_du = '<span style="color: turquoise">' . number_format($percent_du) . ' %</span>';
            break;
        case $percent_du >= 40:
            $font_color_du = '<span style="color: lightblue">' . number_format($percent_du) . ' %</span>';
            break;
        case $percent_du >= 30:
            $font_color_du = '<span style="color: yellow">' . number_format($percent_du) . ' %</span>';
            break;
        case $percent_du >= 20:
            $font_color_du = '<span style="color: orange">' . number_format($percent_du) . ' %</span>';
            break;
        case $percent_du < 20:
            $font_color_du = '<span style="color: red">' . number_format($percent_du) . ' %</span>';
            break;
    }
    //=== get total points
    //$target_hd = 30000;
    if (($halfdownload_counter = $mc1->get_value('halfdownload_counter')) === false) {
        $total_hd = sql_query('SELECT SUM(pointspool) AS pointspool, points
                                FROM bonus
                                WHERE id = 13') or sqlerr(__FILE__, __LINE__);
        $hd_total_row = mysqli_fetch_assoc($total_hd);
        $percent_hd = number_format($hd_total_row['pointspool'] / $hd_total_row['points'] * 100, 2);
        $mc1->cache_value('halfdownload_counter', $percent_hd, 0);
    } else {
        $percent_hd = $halfdownload_counter;
    }
    switch ($percent_hd) {
        case $percent_hd >= 90:
            $font_color_hd = '<span style="color: green">' . number_format($percent_hd) . ' %</span>';
            break;
        case $percent_hd >= 80:
            $font_color_hd = '<span style="color: lightgreen">' . number_format($percent_hd) . ' %</span>';
            break;
        case $percent_hd >= 70:
            $font_color_hd = '<span style="color: jade">' . number_format($percent_hd) . ' %</span>';
            break;
        case $percent_hd >= 50:
            $font_color_hd = '<span style="color: turquoise">' . number_format($percent_hd) . ' %</span>';
            break;
        case $percent_hd >= 40:
            $font_color_hd = '<span style="color: lightblue">' . number_format($percent_hd) . ' %</span>';
            break;
        case $percent_hd >= 30:
            $font_color_hd = '<span style="color: yellow">' . number_format($percent_hd) . ' %</span>';
            break;
        case $percent_hd >= 20:
            $font_color_hd = '<span style="color: orange">' . number_format($percent_hd) . ' %</span>';
            break;
        case $percent_hd < 20:
            $font_color_hd = '<span style="color: red">' . number_format($percent_hd) . ' %</span>';
            break;
    }

    if ($freeleech_enabled) {
        $fstatus = "<span style='color: green'> ON </span>";
    } else {
        $fstatus = $font_color_fl . '';
    }
    if ($double_upload_enabled) {
        $dstatus = "<span style='color: green'> ON </span>";
    } else {
        $dstatus = $font_color_du . '';
    }
    if ($half_down_enabled) {
        $hstatus = "<span style='color: green'> ON </span>";
    } else {
        $hstatus = $font_color_hd . '';
    }
}
//==09 Ezeros freeleech contribution top 10 - pdq.Bigjoos
if (($top_donators = $mc1->get_value('top_donators_')) === false) {
    $a = sql_query("SELECT b.id, SUM(b.donation) AS total, u.username, u.id AS userid, u.pirate, u.king, u.class, u.donor, u.warned, u.leechwarn, u.enabled, u.chatpost
                        FROM bonuslog AS b
                        LEFT JOIN users AS u ON b.id = u.id
                        WHERE b.type = 'freeleech'
                        GROUP BY b.id
                        ORDER BY total DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
    while ($top_donator = mysqli_fetch_assoc($a)) {
        $top_donators[] = $top_donator;
    }
    $mc1->cache_value('top_donators_', $top_donators, 0);
}
if (count($top_donators) > 0) {
    $top_donator = "<h4>Top 10 Contributors </h4>\n";
    if ($top_donators) {
        foreach ($top_donators as $a) {
            $top_donator .= format_username($a['id']) . "  [" . number_format($a['total']) . "<br>";
        }
    } else {
        if (empty($top_donators)) {
            $top_donator .= 'Nobodys contibuted yet !!';
        }
    }
}
//$mc1->delete_value('top_donators_');
//==
if (($top_donators2 = $mc1->get_value('top_donators2_')) === false) {
    $b = sql_query("SELECT b.id, SUM(b.donation) AS total, u.username, u.id AS userid, u.pirate, u.king, u.class, u.donor, u.warned, u.leechwarn, u.enabled, u.chatpost
                        FROM bonuslog AS b
                        LEFT JOIN users AS u ON b.id = u.id
                        WHERE b.type = 'doubleupload'
                        GROUP BY b.id
                        ORDER BY total DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
    while ($top_donator2 = mysqli_fetch_assoc($b)) {
        $top_donators2[] = $top_donator2;
    }
    $mc1->cache_value('top_donators2_', $top_donators2, 0);
}
if (count($top_donators2) > 0) {
    $top_donator2 = "<h4>Top 10 Contributors </h4>\n";
    if ($top_donators2) {
        foreach ($top_donators2 as $b) {
            $top_donator2 .= format_username($b['id']) . " [" . number_format($b['total']) . "]<br>";
        }
    } else {
        if (empty($top_donators2)) {
            $top_donator2 .= 'Nobodys contibuted yet !!';
        }
    }
}
//$mc1->delete_value('top_donators2_');
//==
if (($top_donators3 = $mc1->get_value('top_donators3_')) === false) {
    $c = sql_query("SELECT b.id, SUM(b.donation) AS total, u.username, u.id AS userid, u.pirate, u.king, u.class, u.donor, u.warned, u.leechwarn, u.enabled, u.chatpost
                        FROM bonuslog AS b
                        LEFT JOIN users AS u ON b.id = u.id
                        WHERE b.type = 'halfdownload'
                        GROUP BY b.id
                        ORDER BY total DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
    while ($top_donator3 = mysqli_fetch_assoc($c)) {
        $top_donators3[] = $top_donator3;
    }
    $mc1->cache_value('top_donators3_', $top_donators3, 0);
}
if (count($top_donators3) > 0) {
    $top_donator3 = "<h4>Top 10 Contributors </h4>\n";
    if ($top_donators3) {
        foreach ($top_donators3 as $c) {
            $top_donator3 .= format_username($c['id']) . " [" . number_format($c['total']) . "]<br>";
        }
    } else {
        if (empty($top_donators3)) {
            $top_donator3 .= 'Nobodys contibuted yet!';
        }
    }
}
//$mc1->delete_value('top_donators3_');
//==End
if (XBT_TRACKER == false) {
    //== Show the percentages
    $HTMLOUT .= "<div class='has-text-centered size_5'> FreeLeech [ ";
    if ($freeleech_enabled) {
        $HTMLOUT .= '<span style="color: green;"><strong> ON</strong></span> ' . get_date($freeleech_start_time, 'DATE') . ' - ' . get_date($freeleech_end_time, 'wDATE');
    } else {
        $HTMLOUT .= "<strong>{$fstatus}</strong>";
    }
    $HTMLOUT .= ' ]';

    $HTMLOUT .= ' DoubleUpload [ ';
    if ($double_upload_enabled) {
        $HTMLOUT .= '<span style="color: green"><strong> ON</strong></span> ' . get_date($double_upload_start_time, 'DATE') . ' - ' . get_date($double_upload_end_time, 'DATE');
    } else {
        $HTMLOUT .= "<strong>{$dstatus}</strong>";
    }
    $HTMLOUT .= ' ]';

    $HTMLOUT .= ' Half Download [ ';
    if ($half_down_enabled) {
        $HTMLOUT .= '<span style="color: green"><strong> ON</strong></span> ' . get_date($half_down_start_time, 'DATE') . ' - ' . get_date($half_down_end_time, 'DATE');
    } else {
        $HTMLOUT .= "<strong>{$hstatus}</strong>";
    }
    $HTMLOUT .= ' ]</div>';
    //==End
}
$bonus = (float)$CURUSER['seedbonus'];
$HTMLOUT .= "
            <div class='bordered has-text-centered top20'>
                <span class='size_5'>Exchange your <span class='has-text-primary'>" . number_format($bonus) . "</span> Karma Bonus Points for goodies!</span>
                <br>
                <span class='size_2'>
                    [ If no buttons appear, you have not earned enough bonus points to trade. ]
                </span>
            </div>
            <table class='table table-bordered table-striped top20 bottom20'>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Points</th>
                        <th>Trade</th>
                    </tr>
                </thead>";

$res = sql_query("SELECT *
                    FROM bonus
                    WHERE enabled = 'yes'
                    ORDER BY id ASC") or sqlerr(__FILE__, __LINE__);
while ($gets = mysqli_fetch_assoc($res)) {
    $otheroption = "
            <div><b>Username:</b>
            <input type='text' name='username' size='20' maxlength='24' /></div>
            <div> <b>to be given: </b>
            <select name='bonusgift'>
            <option value='100'> 100</option>
            <option value='200'> 200</option>
            <option value='300'> 300</option>
            <option value='400'> 400</option>
            <option value='500'> 500</option>
            <option value='1000'> 1,000</option>
            <option value='5000'> 5,000</option>
            <option value='10000'> 10,000</option>
            <option value='20000'> 20,000</option>
            <option value='50000'> 50,000</option>
            <option value='100000'> 100,000</option>
            </select> Karma points!</div>";

    switch (true) {
        case $gets['id'] == 5:
            $HTMLOUT .= "<tr><td><form action='{$site_config['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='" . (int)$gets['id'] . "' /><input type='hidden' name='art' value='" . htmlsafechars($gets['art']) . "' /><h1>" . htmlsafechars($gets['bonusname']) . '</h1>' . htmlsafechars($gets['description']) . "<br><br>Enter the <b>Special Title</b> you would like to have <input type='text' name='title' size='30' maxlength='30' /> click Exchange! </td><td>" . (float)$gets['points'] . '</td>';
            break;
        case $gets['id'] == 7:
            $HTMLOUT .= "<tr><td><form action='{$site_config['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='" . (int)$gets['id'] . "' /> <input type='hidden' name='art' value='" . htmlsafechars($gets['art']) . "' /><h1>" . htmlsafechars($gets['bonusname']) . '</h1>' . htmlsafechars($gets['description']) . '<br><br>Enter the <b>username</b> of the person you would like to send karma to, and select how many points you want to send and click Exchange!<br>' . $otheroption . "</td><td>min.<br>" . (float)$gets['points'] . '<br>max.<br>100000</td>';
            break;
        case $gets['id'] == 9:
            $HTMLOUT .= "<tr><td><form action='{$site_config['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='" . (int)$gets['id'] . "' /> <input type='hidden' name='art' value='" . htmlsafechars($gets['art']) . "' /><h1>" . htmlsafechars($gets['bonusname']) . '</h1>' . htmlsafechars($gets['description']) . "</td><td>min.<br>" . (float)$gets['points'] . '</td>';
            break;
        case $gets['id'] == 10:
            $HTMLOUT .= "<tr><td><form action='{$site_config['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='" . (int)$gets['id'] . "' /> <input type='hidden' name='art' value='" . htmlsafechars($gets['art']) . "' /><h1>" . htmlsafechars($gets['bonusname']) . '</h1>' . htmlsafechars($gets['description']) . "<br><br>Enter the <b>ID number of the Torrent:</b> <input type='text' name='torrent_id' size='4' maxlength='8' /> you would like to buy a 1 to 1 ratio on.</td><td>min.<br>" . (float)$gets['points'] . '</td>';
            break;
        case $gets['id'] == 11:
            $HTMLOUT .= "<tr><td><form action='{$site_config['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='" . (int)$gets['id'] . "' /> <input type='hidden' name='art' value='" . htmlsafechars($gets['art']) . "' /><h1>" . htmlsafechars($gets['bonusname']) . '</h1>' . htmlsafechars($gets['description']) . '<br>' . $top_donator . "<br>Enter the <b>amount to contribute</b><input type='text' name='donate' size='10' maxlength='10' /></td><td>" . (float)$gets['minpoints'] . ' <br></td>';
            break;
        case $gets['id'] == 12:
            $HTMLOUT .= "<tr><td><form action='{$site_config['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='" . (int)$gets['id'] . "' /> <input type='hidden' name='art' value='" . htmlsafechars($gets['art']) . "' /><h1>" . htmlsafechars($gets['bonusname']) . '</h1>' . htmlsafechars($gets['description']) . '<br>' . $top_donator2 . "<br>Enter the <b>amount to contribute</b><input type='text' name='donate' size='10' maxlength='10' /></td><td>" . (float)$gets['minpoints'] . ' <br></td>';
            break;
        case $gets['id'] == 13:
            $HTMLOUT .= "<tr><td><form action='{$site_config['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='" . (int)$gets['id'] . "' /><input type='hidden' name='art' value='" . htmlsafechars($gets['art']) . "' /><h1>" . htmlsafechars($gets['bonusname']) . '</h1>' . htmlsafechars($gets['description']) . '<br>' . $top_donator3 . "<br>Enter the <b>amount to contribute</b><input type='text' name='donate' size='10' maxlength='10' /></td><td>" . (float)$gets['minpoints'] . ' <br></td>';
            break;
        case $gets['id'] == 34:
            $HTMLOUT .= "<tr><td><form action='{$site_config['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='" . (int)$gets['id'] . "' /><input type='hidden' name='art' value='" . htmlsafechars($gets['art']) . "' /><h1>" . htmlsafechars($gets['bonusname']) . '</h1>' . htmlsafechars($gets['description']) . "<br><br>Enter the <b>ID number of the Torrent:</b> <input type='text' name='torrent_id' size='4' maxlength='8' /> you would like to bump.</td><td>min.<br>" . (float)$gets['points'] . '</td>';
            break;
        default:
            $HTMLOUT .= "<tr><td><form action='{$site_config['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='" . (int)$gets['id'] . "' /><input type='hidden' name='art' value='" . htmlsafechars($gets['art']) . "' /><h1>" . htmlsafechars($gets['bonusname']) . '</h1>' . htmlsafechars($gets['description']) . "</td><td>" . (float)$gets['points'] . '</td>';
    }

    if ($bonus >= $gets['points'] or $bonus >= $gets['minpoints']) {
        switch (true) {
            case $gets['id'] == 7:
                $HTMLOUT .= "<td><input class='button' type='submit' name='submit' value='Karma Gift!' /></td></form>";
                break;
            case $gets['id'] == 11:
                $HTMLOUT .= "<td>" . ((float)$gets['points'] - (float)$gets['pointspool']) . " <br>Points needed! <br><input class='button' type='submit' name='submit' value='Contribute!' /></td></form>";
                break;
            case $gets['id'] == 12:
                $HTMLOUT .= "<td>" . ((float)$gets['points'] - (float)$gets['pointspool']) . " <br>Points needed! <br><input class='button' type='submit' name='submit' value='Contribute!' /></td></form>";
                break;
            case $gets['id'] == 13:
                $HTMLOUT .= "<td>" . ((float)$gets['points'] - (float)$gets['pointspool']) . " <br>Points needed! <br><input class='button' type='submit' name='submit' value='Contribute!' /></td></form>";
                break;
            default:
                $HTMLOUT .= "<td><input class='button' type='submit' name='submit' value='Exchange!' /></td></form>";
        }
    } else {
        $HTMLOUT .= "<td><b>Not Enough Karma</b></td>";
    }
}

$bpt = get_one_row('site_config', 'value', 'WHERE name = "bonus_per_duration"');
$bmt = get_one_row('site_config', 'value', 'WHERE name = "bonux_max_torrents"');
$bonus_per_comment = get_one_row('site_config', 'value', 'WHERE name = "bonus_per_comment"');
$bonus_per_rating = get_one_row('site_config', 'value', 'WHERE name = "bonus_per_rating"');
$bonus_per_post = get_one_row('site_config', 'value', 'WHERE name = "bonus_per_post"');
$bonus_per_topic = get_one_row('site_config', 'value', 'WHERE name = "bonus_per_topic"');

$at = get_row_count('peers', 'where seeder = "yes" and connectable = "yes" and userid = ' . $CURUSER['id']);
$at = $at >= $bmt ? $bmt : $at;

$atform = number_format($at);
$activet = number_format($at * $bpt * 2, 2);
//crap
$HTMLOUT .= "</tr></table></div>
    <div class='container is-fluid portlet'>
        <h2 class='top20'>What the hell are these Karma Bonus points, and how do I get them?</h2>
        <div class='bordered bottom10'>
            <div class='alt_bordered bg-00'>
                <h4>
                    For every hour that you seed a torrent, you are awarded with " . number_format($bpt * 2, 2) . " Karma Bonus Point...
                </h4>
                <p>
                    If you save up enough of them, you can trade them in for goodies like bonus GB(s) to increase your upload stats, also to get more invites, or doing the real Karma booster... give them to another user!<br>
                    This is awarded on a per torrent basis (max of $bmt) even if there are no leechers on the Torrent you are seeding! <br>
                    Seeding Torrents Based on Connectable Status =
                    <span>
                        <span class='tooltipper' title='Seeding $atform torrents'> $atform </span>*
                        <span class='tooltipper' title='$bpt per announce period'> $bpt </span>*
                        <span class='tooltipper' title='2 announce periods per hour'> 2 </span>= $activet
                    </span>
                    karma per hour
                </p>
            </div>
        </div>

        <div class='bordered bottom10'>
            <div class='alt_bordered bg-00'>
                <h4>Other things that will get you karma points:</h4>
                <p>
                    Uploading a new torrent = 15 points<br>
                    Filling a request = 10 points<br>
                    Comment on torrent = 3 points<br>
                    Saying thanks = 2 points<br>
                    Rating a torrent = 2 points<br>
                    Making a post = 1 point<br>
                    Starting a topic = 2 points
                </p>
            </div>
        </div>

        <div class='bordered bottom10'>
            <div class='alt_bordered bg-00'>
                <h4>Some things that will cost you karma points:</h4>
                <p>
                    Upload credit<br>
                    Custom title<br>
                    One month VIP status<br>
                    A 1:1 ratio on a torrent<br>
                    Buying off your warning<br>
                    One month custom smilies for the forums and comments<br>
                    Getting extra invites<br>
                    Getting extra freeslots<br>
                    Giving a gift of karma points to another user<br>
                    Asking for a re-seed<br>
                    Making a request<br>
                    Freeleech, Doubleupload, Halfdownload contribution<br>
                    Anonymous profile<br>
                    Download reduction<br>
                    Freeleech for a year<br>
                    Pirate or King status<br>
                    Unlocking parked option<br>
                    Pirates bounty<br>
                    Reputation points<br>
                    Userblocks<br>
                    Bump a torrent<br>
                    User immuntiy<br>
                    User unlocks<br>
                </p>
                <p>
                    But keep in mind that everything that can get you karma can also be lost...<br>
                </p>
                <p>
                    ie: If you up a torrent then delete it, you will gain and then lose 15 points, making a post and having it deleted will do the same... and there are other hidden bonus karma points all over the site which is another way to help out your ratio !
                </p>
                <span>
                    *Please note, staff can give or take away points for breaking the rules, or doing good for the community.
                </span>
            </div>
        </div>
    </div>";

echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
