<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
check_user_status();
global $CURUSER, $site_config, $mysqli;

$stdhead = [
    'css' => [
        get_file_name('sceditor_css'),
    ],
];
$stdfoot = [
    'js' => [
        get_file_name('sceditor_js'),
    ],
];
$lang = load_language('global');
if ($CURUSER['class'] < UC_ADMINISTRATOR) {
    stderr('Error', 'Your not authorised');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //== The expiry days.
    $days = [
        [
            7,
            '7 Days',
        ],
        [
            14,
            '14 Days',
        ],
        [
            21,
            '21 Days',
        ],
        [
            28,
            '28 Days',
        ],
        [
            56,
            '2 Months',
        ],
    ];
    //== Usersearch POST data...
    $n_pms = (isset($_POST['n_pms']) ? (int) $_POST['n_pms'] : 0);
    $ann_query = (isset($_POST['ann_query']) ? rawurldecode(trim($_POST['ann_query'])) : '');
    $ann_hash = (isset($_POST['ann_hash']) ? trim($_POST['ann_hash']) : '');
    if (hashit($ann_query, $n_pms) != $ann_hash) {
        die();
    } // Validate POST...
    if (!preg_match('/\\ASELECT.+?FROM.+?WHERE.+?\\z/', $ann_query)) {
        stderr('Error', 'Misformed Query');
    }
    if (!$n_pms) {
        stderr('Error', 'No recipients');
    }
    //== POST data ...
    $body = trim((isset($_POST['body']) ? $_POST['body'] : ''));
    $subject = trim((isset($_POST['subject']) ? $_POST['subject'] : ''));
    $expiry = (int) (isset($_POST['expiry']) ? $_POST['expiry'] : 0);
    if ((isset($_POST['buttonval']) && $_POST['buttonval'] === 'Submit')) {
        //== Check values before inserting into row...
        if (empty($body)) {
            stderr('Error', 'No body to announcement');
        }
        if (empty($subject)) {
            stderr('Error', 'No subject to announcement');
        }
        unset($flag);
        reset($days);
        foreach ($days as $x) {
            if ($expiry == $x[0]) {
                $flag = 1;
            }
        }
        if (!isset($flag)) {
            stderr('Error', 'Invalid expiry selection');
        }
        $expires = TIME_NOW + (86400 * $expiry); // 86400 seconds in one day.
        $created = TIME_NOW;
        $query = sprintf('INSERT INTO announcement_main (owner_id, created, expires, sql_query, subject, body) VALUES (%s, %s, %s, %s, %s, %s)', sqlesc($CURUSER['id']), sqlesc($created), sqlesc($expires), sqlesc($ann_query), sqlesc($subject), sqlesc($body));
        sql_query($query);
        if (mysqli_affected_rows($mysqli)) {
            stderr('Success', 'Announcement was successfully created');
        }
        stderr('Error', 'Contact an administrator');
    }
    $HTMLOUT = '';
    $HTMLOUT .= "<table class='main' width='750' >
     <tr>
     <td class='embedded'><div class='has-text-centered'>
     <h1>Create Announcement for " . ($n_pms) . ' user' . ($n_pms > 1 ? 's' : '') . '&#160;!</h1>';
    $HTMLOUT .= "<form name='compose' method='post' action='{$site_config['baseurl']}/new_announcement.php'>
     <table >
     <tr>
     <td colspan='2'><b>Subject: </b>
     <input name='subject' type='text' size='76' value='" . htmlsafechars($subject) . "' /></td>
     </tr>
     <tr><td colspan='2'><div class='has-text-centered'>
                       " . BBcode() . '
  </div></td></tr>';
    $HTMLOUT .= "<tr><td colspan='2' class='has-text-centered'>";
    $HTMLOUT .= "<select name='expiry'>";
    reset($days);
    foreach ($days as $x) {
        $HTMLOUT .= '<option value="' . $x[0] . '"' . (($expiry == $x[0] ? '' : '')) . '>' . $x[1] . '</option>';
    }
    $HTMLOUT .= "</select>

     <input type='submit' name='buttonval' value='Submit' class='button is-small' />
     </td></tr></table>
     <input type='hidden' name='n_pms' value='" . $n_pms . "' />
    <input type='hidden' name='ann_query' value='" . rawurlencode($ann_query) . "' />
     <input type='hidden' name='ann_hash' value='" . $ann_hash . "' />
     </form><br><br>
     </div></td></tr></table>";
    if ($body) {
        $newtime = TIME_NOW + (86400 * $expiry);
        $HTMLOUT .= "<table width='700' class='main' >
     <tr><td class='has-text-centered'><h2><font class='has-text-white'>Announcement: 
     " . htmlsafechars($subject) . "</font></h2></td></tr>
     <tr><td class='text'>
     " . format_comment($body) . '<br><hr>Expires: ' . get_date($newtime, 'DATE') . '';
        $HTMLOUT .= '</td></tr></table>';
    }
} else { // Shouldn't be here
    header('HTTP/1.0 404 Not Found');
    $HTMLOUT = '';
    $HTMLOUT .= '<html><h1>Not Found</h1><p>The requested URL ' . htmlsafechars($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], '/') + 1) . " was not found on this server.</p>
<hr>
<address>{$_SERVER['SERVER_SOFTWARE']} Server at {$site_config['baseurl']} Port 80</address></body></html>\n";
    echo $HTMLOUT;
    die();
}
echo stdhead('New Announcement', $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
