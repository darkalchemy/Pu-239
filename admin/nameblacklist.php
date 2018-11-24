<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $lang;

$lang = array_merge($lang, load_language('ad_nameblacklist'));
$blacklist = file_exists($site_config['nameblacklist']) && is_array(unserialize(file_get_contents($site_config['nameblacklist']))) ? unserialize(file_get_contents($site_config['nameblacklist'])) : [];
//dd($blacklist);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $badnames = isset($_POST['badnames']) && !empty($_POST['badnames']) ? trim($_POST['badnames']) : '';
    if (empty($badnames)) {
        stderr($lang['name_hmm'], $lang['name_think']);
    }
    if (strpos($badnames, ',')) {
        foreach (explode(',', $badnames) as $badname) {
            $blacklist[$badname] = (int) 1;
        }
    } else {
        $blacklist[$badnames] = (int) 1;
    }
    if (file_put_contents($site_config['nameblacklist'], serialize($blacklist))) {
        header('Refresh:2; url=staffpanel.php?tool=nameblacklist');
        stderr($lang['name_success'], $lang['name_file']);
    } else {
        stderr($lang['name_err'], ' ' . $lang['name_hmm'] . '<b>' . $site_config['nameblacklist'] . '</b>' . $lang['name_is'] . '');
    }
} else {
    $out = stdmsg($lang['name_curr'], count($blacklist) ? implode(', ', array_keys($blacklist)) : $lang['name_no']);
    $out .= main_div("
    <h2 class='has-text-centered'>{$lang['name_add']}</h2>
    <form action='{$site_config['baseurl']}/staffpanel.php?tool=nameblacklist&amp;action=nameblacklist' method='post'>
        <textarea rows='3' name='badnames' class='w-100'></textarea>
        <div class='has-text-centered'>
            <p>{$lang['name_note']}</p>
            <input type='submit' value='{$lang['name_update']}' class='button is-small margin10'>
        </div>
    </form>", 'top20');
    echo stdhead($lang['name_stdhead']) . wrapper($out) . stdfoot();
}
