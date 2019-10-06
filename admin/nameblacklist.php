<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config;

$blacklist = file_exists($site_config['paths']['nameblacklist']) && is_array(json_decode(file_get_contents($site_config['paths']['nameblacklist']), true)) ? json_decode(file_get_contents($site_config['paths']['nameblacklist']), true) : [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $badnames = isset($_POST['badnames']) && !empty($_POST['badnames']) ? trim($_POST['badnames']) : '';
    if (empty($badnames)) {
        stderr(_('Error'), _('I think you forgot the name'));
    }
    if (strpos($badnames, ',')) {
        foreach (explode(',', $badnames) as $badname) {
            $blacklist[$badname] = (int) 1;
        }
    } else {
        $blacklist[$badnames] = (int) 1;
    }
    if (file_put_contents($site_config['paths']['nameblacklist'], json_encode($blacklist))) {
        header('Refresh:2; url=staffpanel.php?tool=nameblacklist');
        stderr(_('Success'), _('The file was written...wait for redirect'));
    } else {
        stderr(_('Error'), _fe('{0} is not writable', $site_config['paths']['nameblacklist']));
    }
} else {
    $out = stdmsg(_('Current words on blacklist'), count($blacklist) ? implode(', ', array_keys($blacklist)) : _('There is no username on the blacklist'));
    $out .= main_div("
    <h2 class='has-text-centered'>" . _('Add word') . "</h2>
    <form action='{$_SERVER['PHP_SELF']}?tool=nameblacklist&amp;action=nameblacklist' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
        <textarea rows='3' name='badnames' class='w-100'></textarea>
        <div class='has-text-centered'>
            <p>" . _('Note: if you want to submit more then one bad nick at a time separate them with a comma') . "</p>
            <input type='submit' value='" . _('Update') . "' class='button is-small margin10'>
        </div>
    </form>", 'top20');
    $title = _('Username Blacklist');
    $breadcrumbs = [
        "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
    echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($out) . stdfoot();
}
