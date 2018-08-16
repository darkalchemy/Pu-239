<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $lang;

$lang = array_merge($lang, load_language('ad_floodlimit'));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $limits = isset($_POST['limit']) && is_array($_POST['limit']) ? $_POST['limit'] : 0;
    foreach ($limits as $class => $limit) {
        if ($limit == 0) {
            unset($limits[$class]);
        }
    }
    if (file_put_contents($site_config['flood_file'], serialize($limits))) {
        header('Refresh: 2; url=/staffpanel.php?tool=floodlimit');
        stderr($lang['floodlimit_success'], $lang['floodlimit_saved']);
    } else {
        stderr($lang['floodlimit_stderr'], $lang['floodlimit_wentwrong'] . $_file . $lang['floodlimit_exist']);
    }
} else {
    if (!file_exists($site_config['flood_file']) || !is_array($limit = unserialize(file_get_contents($site_config['flood_file'])))) {
        $limit = [];
    }
    $out = begin_main_frame() . begin_frame($lang['floodlimit_editflood']);
    $out .= '<form method=\'post\' action=\'\' ><table width=\'60%\' align=\'center\'><tr><td class=\'colhead\'>' . $lang['floodlimit_userclass'] . '</td><td class=\'colhead\'>' . $lang['floodlimit_limit'] . '</td></tr>';
    for ($i = UC_MIN; $i <= UC_MAX; ++$i) {
        $out .= '<tr><td align=\'left\'>' . get_user_class_name($i) . '</td><td><input name=\'limit[' . $i . ']\' type=\'text\' size=\'10\' value=\'' . (isset($limit[$i]) ? $limit[$i] : 0) . '\'/></td></tr>';
    }
    $out .= '<tr><td colspan=\'2\'>' . $lang['floodlimit_note'] . '</td></tr><tr><td colspan=\'2\' class=\'colhead\'><input type=\'submit\' value=\'' . $lang['floodlimit_save'] . '\' /></td></tr>';
    $out .= '</table></form>' . end_frame() . end_main_frame();
    echo stdhead($lang['floodlimit_std']) . wrapper($out) . stdfoot();
}
