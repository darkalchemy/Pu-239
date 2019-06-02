<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_forum_config'));
global $site_config;

$HTMLOUT = $time_drop_down = $accepted_file_extension = $accepted_file_types = $member_class_drop_down = '';
$settings_saved = false;
//=== be sure to set your id (below) in the DB. as well as setting your upload dir to something unique
$config_id = 1;
if (isset($_POST['do_it'])) {
    $delete_for_real = isset($_POST['delete_for_real']) ? intval($_POST['delete_for_real']) : 0;
    $site_config['forum_config']['min_delete_view_class'] = isset($_POST['min_delete_view_class']) && valid_class($_POST['min_delete_view_class']) ? intval($_POST['min_delete_view_class']) : 0;
    $readpost_expiry = isset($_POST['readpost_expiry']) ? intval($_POST['readpost_expiry']) : 0;
    $min_upload_class = isset($_POST['min_upload_class']) && valid_class($_POST['min_upload_class']) ? intval($_POST['min_upload_class']) : 0;
    $accepted_file_extension = isset($_POST['accepted_file_extension']) ? preg_replace('/\s+/', '|', trim($_POST['accepted_file_extension'])) : '';
    $accepted_file_types = isset($_POST['accepted_file_types']) ? preg_replace('/\s+/', '|', trim($_POST['accepted_file_types'])) : '';
    $max_file_size = isset($_POST['max_file_size']) ? intval($_POST['max_file_size']) : 0;
    $upload_folder = isset($_POST['upload_folder']) ? htmlsafechars(trim($_POST['upload_folder'])) : '';
    sql_query('UPDATE forum_config SET delete_for_real = ' . sqlesc($delete_for_real) . ', min_delete_view_class = ' . sqlesc($site_config['forum_config']['min_delete_view_class']) . ', readpost_expiry = ' . sqlesc($readpost_expiry) . ', min_upload_class = ' . sqlesc($min_upload_class) . ', accepted_file_extension = ' . sqlesc($accepted_file_extension) . ',  accepted_file_types = ' . sqlesc($accepted_file_types) . ', max_file_size = ' . $max_file_size . ', upload_folder = ' . sqlesc($upload_folder) . ' WHERE id=' . sqlesc($config_id));
    $cache->delete('forum_config_');
    header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=forum_config&action=forum_config');
    die();
}
$main_links = "
            <div class='bottom20'>
                <ul class='level-center bg-06'>
                    <li class='is-link margin10'>
                        <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=over_forums&amp;action=over_forums'>{$lang['forum_config_over']}</a>
                    </li>
                    <li class='is-link margin10'>
                        <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=forum_manage&amp;action=forum_manage'>{$lang['forum_config_manager']}</a>
                    </li>
                </ul>
            </div>
            <h1 class='has-text-centered'>{$lang['forum_config_config']}</h1>";

$res = sql_query('SELECT delete_for_real, min_delete_view_class, readpost_expiry, min_upload_class, accepted_file_extension,
                                accepted_file_types, max_file_size, upload_folder FROM forum_config WHERE id=' . sqlesc($config_id));
$arr = mysqli_fetch_array($res);
$weeks = 1;
for ($i = 7; $i <= 365; $i = $i + 7) {
    $time_drop_down .= '<option class="body" value="' . $i . '"' . ($arr['readpost_expiry'] == $i ? ' selected' : '') . '>' . $weeks . ' ' . $lang['forum_config_week'] . plural($weeks) . '</option>';
    $weeks = $weeks + 1;
}
$accepted_file_extension = (!empty($arr['accepted_file_extension'])) ? str_replace('|', ' ', $arr['accepted_file_extension']) : [];
$accepted_file_types = (!empty($arr['accepted_file_types'])) ? str_replace('|', ' ', $arr['accepted_file_types']) : [];
$HTMLOUT .= $main_links . '<form method="post" action="staffpanel.php?tool=forum_config&amp;action=forum_config" accept-charset="utf-8">
            <input type="hidden" name="do_it" value="1">
        <table class="table table-bordered table-striped">
        <tr>
            <td><span class="has-text-weight-bold">' . $lang['forum_config_delete'] . '</span></td>
            <td>
            <input type="radio" name="delete_for_real" value="1" ' . ($arr['delete_for_real'] == 1 ? 'checked' : '') . '>' . $lang['forum_config_yes'] . '
            <input type="radio" name="delete_for_real" value="0" ' . ($arr['delete_for_real'] == 0 ? 'checked' : '') . '>' . $lang['forum_config_no'] . '<br>
            ' . $lang['forum_config_no_desc'] . '</td>
        </tr>
        <tr>
            <td><span class="has-text-weight-bold">' . $lang['forum_config_min'] . '</span></td>
            <td>
            <select name="min_delete_view_class"> ' . member_class_drop_down($arr['min_delete_view_class']) . '</select><br>
            ' . $lang['forum_config_min_desc'] . '<br>' . $lang['forum_config_min_desc1'] . '</td>
        </tr>
        <tr>
            <td><span class="has-text-weight-bold">' . $lang['forum_config_expire'] . '</span></td>
            <td>
            <select name="readpost_expiry"> ' . $time_drop_down . '</select><br>
            ' . $lang['forum_config_expire_desc'] . '</td>
        </tr>
        <tr>
            <td><span class="has-text-weight-bold">' . $lang['forum_config_upload'] . '</span></td>
            <td>
            <select name="min_upload_class"> ' . member_class_drop_down($arr['min_upload_class']) . '</select><br>
            ' . $lang['forum_config_upload_desc'] . '</td>
        </tr>
          <tr>
            <td><span class="has-text-weight-bold">' . $lang['forum_config_accepted'] . '</span>  </td>
            <td>
            <input name="accepted_file_extension" type="text" class="w-100" maxlength="200" value="' . htmlsafechars((string) $accepted_file_extension) . '"><br>
            ' . $lang['forum_config_accepted_desc'] . '</td>
         </tr>
          <tr>
            <td><span class="has-text-weight-bold">' . $lang['forum_config_accepted2'] . '</span>  </td>
            <td>
            <input name="accepted_file_types" type="text" class="w-100" maxlength="200" value="' . htmlsafechars((string) $accepted_file_types) . '"><br>
            ' . $lang['forum_config_accepted2_desc'] . '</td>
         </tr>
          <tr>
            <td><span class="has-text-weight-bold">' . $lang['forum_config_size'] . '</span>  </td>
            <td>
            <input name="max_file_size" type="text" class="w-100" maxlength="200" value="' . intval($arr['max_file_size']) . '"><br>
            ' . $lang['forum_config_size_desc'] . '' . mksize($arr['max_file_size']) . '.</td>
         </tr>
          <tr>
            <td><span class="has-text-weight-bold">' . $lang['forum_config_folder'] . '</span>  </td>
            <td>
            <input name="upload_folder" type="text" class="w-100" maxlength="200" value="' . htmlsafechars($arr['upload_folder']) . '"><br>
            ' . $lang['forum_config_folder_desc'] . '<br>
         </tr>
        <tr>
            <td colspan="2" class="has-text-centered">
            <input type="submit" name="button" class="button is-small margin20" value="' . $lang['forum_config_save'] . '"></td>
        </tr>
        </table></form>';
/**
 * @param $member_class
 *
 * @return string
 */
function member_class_drop_down($member_class)
{
    $member_class_drop_down = '';
    for ($i = 0; $i <= UC_MAX; ++$i) {
        $member_class_drop_down .= '<option class="body" value="' . $i . '"' . ($member_class == $i ? ' selected' : '') . '>' . get_user_class_name($i) . '</option>';
    }

    return $member_class_drop_down;
}

echo stdhead($lang['forum_config_stdhead']) . wrapper($HTMLOUT) . stdfoot();
