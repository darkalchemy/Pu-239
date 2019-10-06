<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_html.php';
$user = check_user_status();
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
global $site_config;

if ($user['class'] < UC_MAX) {
    stderr(_('Error'), _("You're not authorized"));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //== The expiry days.
    $days = [
        7 => _('%d Days', 7),
        14 => _('%d Days', 14),
        21 => _('%d Days', 21),
        28 => _('%d Days', 28),
        56 => _('%d Months', 7),
    ];
    //== Usersearch POST data...
    $n_pms = isset($_POST['n_pms']) ? (int) $_POST['n_pms'] : 0;
    $ann_query = isset($_POST['ann_query']) ? rawurldecode(trim($_POST['ann_query'])) : '';
    if (!preg_match('/\\ASELECT.+?FROM.+?WHERE.+?\\z/', $ann_query)) {
        stderr(_('Error'), _('Misformed Query'));
    }
    if (!$n_pms) {
        stderr(_('Error'), _('No recipients'));
    }
    //== POST data ...
    $body = trim((isset($_POST['body']) ? $_POST['body'] : ''));
    $subject = trim((isset($_POST['subject']) ? $_POST['subject'] : ''));
    $expiry = (int) (isset($_POST['expiry']) ? $_POST['expiry'] : 0);
    if ((isset($_POST['buttonval']) && $_POST['buttonval'] === 'Submit')) {
        //== Check values before inserting into row...
        if (empty($body)) {
            stderr(_('Error'), _('No body to announcement'));
        }
        if (empty($subject)) {
            stderr(_('Error'), _('No subject to announcement'));
        }
        unset($flag);
        reset($days);
        foreach ($days as $x) {
            if ($expiry == $x[0]) {
                $flag = 1;
            }
        }
        if (!isset($flag)) {
            stderr(_('Error'), _('Invalid expiry selection'));
        }
        $expires = TIME_NOW + (86400 * $expiry); // 86400 seconds in one day.
        $created = TIME_NOW;
        $query = sprintf('INSERT INTO announcement_main (owner_id, created, expires, sql_query, subject, body) VALUES (%s, %s, %s, %s, %s, %s)', sqlesc($user['id']), sqlesc($created), sqlesc($expires), sqlesc($ann_query), sqlesc($subject), sqlesc($body));
        sql_query($query);
        if (mysqli_affected_rows($mysqli)) {
            stderr('Success', _('Announcement was successfully created'));
        }
        stderr(_('Error'), _('Contact an administrator'));
    }

    $HTMLOUT = '';
    $HTMLOUT .= "<table class='main'>
     <tr>
     <td class='embedded'><div class='has-text-centered'>
     <h1>Create Announcement for " . ($n_pms) . ' user' . ($n_pms > 1 ? 's' : '') . '&#160;!</h1>';
    $HTMLOUT .= "<form name='compose' method='post' action='{$site_config['paths']['baseurl']}/new_announcement.php' enctype='multipart/form-data' accept-charset='utf-8'>
     <table>
     <tr>
     <td colspan='2'><b>" . _('Subject') . ": </b>
     <input name='subject' type='text' size='76' value='" . htmlsafechars($subject) . "'></td>
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

     <input type='submit' name='buttonval' value='Submit' class='button is-small'>
     </td></tr></table>
     <input type='hidden' name='n_pms' value='" . $n_pms . "'>
     <input type='hidden' name='ann_query' value='" . rawurlencode($ann_query) . "'>
     </form><br><br>
     </div></td></tr></table>";
    if ($body) {
        $newtime = TIME_NOW + (86400 * $expiry);
        $HTMLOUT .= "<table class='main'>
     <tr><td class='has-text-centered'><h2><span class='has-text-primary'>" . _('Announcement') . ': 
     ' . htmlsafechars($subject) . "</span></h2></td></tr>
     <tr><td class='text'>
     " . format_comment($body) . '<br><hr>' . _('Expires') . ': ' . get_date((int) $newtime, 'DATE') . '';
        $HTMLOUT .= '</td></tr></table>';
    }
} else {
    header('Location: 404.html');
    die();
}

$title = _('New Announcement');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, $stdhead, 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot($stdfoot);
