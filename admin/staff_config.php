<?php
if (!defined('IN_site_config_ADMIN')) {
    setSessionVar('error', 'Access Not Allowed');
    header("Location: {$site_config['baseurl']}/index.php");
    exit();
}
require_once INCL_DIR . 'user_functions.php';
require_once CLASS_DIR . 'class_check.php';
class_check(UC_MAX);
$lang = array_merge($lang, load_language('ad_staff_config'));
function write_staffs2()
{
    global $lang;
    //==ids
    $t = '$site_config';
    $iconfigfile = '<' . "?php\n/**\n{$lang['staffcfg_file_created']}" . date('M d Y H:i:s') . ".\n{$lang['staffcfg_mod_by']}\n**/\n";
    $ri = sql_query('SELECT id, username, class FROM users WHERE class BETWEEN ' . UC_STAFF . ' AND ' . UC_MAX . ' ORDER BY id ASC') or sqlerr(__FILE__, __LINE__);
    $iconfigfile .= '' . $t . "['allowed_staff']['id'] = array(";
    while ($ai = mysqli_fetch_assoc($ri)) {
        $ids[] = $ai['id'];
        $usernames[] = "'" . $ai['username'] . "' => 1";
    }
    $iconfigfile .= '' . join(',', $ids);
    $iconfigfile .= ');';
    $iconfigfile .= "\n?" . '>';
    $filenum = fopen('./cache/staff_settings.php', 'w');
    ftruncate($filenum, 0);
    fwrite($filenum, $iconfigfile);
    fclose($filenum);
    //==names
    $t = '$site_config';
    $nconfigfile = '<' . "?php\n/**\n{$lang['staffcfg_file_created']}" . date('M d Y H:i:s') . ".\n{$lang['staffcfg_mod_by']}\n**/\n";
    $nconfigfile .= '' . $t . "['staff']['allowed'] = array(";
    $nconfigfile .= '' . join(',', $usernames);
    $nconfigfile .= ');';
    $nconfigfile .= "\n?" . '>';
    $filenum1 = fopen('./cache/staff_settings2.php', 'w');
    ftruncate($filenum1, 0);
    fwrite($filenum1, $nconfigfile);
    fclose($filenum1);
    stderr($lang['staffcfg_success'], $lang['staffcfg_updated']);
}

write_staffs2();
echo stdhead($lang['staffcfg_stdhead']) . stdfoot();
