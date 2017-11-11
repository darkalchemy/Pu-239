<?php
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_sitesettings'));
$site_settings = $current_site_settings = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
    $pconf = sql_query('SELECT * FROM site_config') or sqlerr(__FILE__, __LINE__);
    while ($ac = mysqli_fetch_assoc($pconf)) {
        $current_site_settings[$ac[name]] = [value => $ac[value], description => $ac[description]];
    }
    $update = [];
    foreach ($_POST as $key => $value) {
        if ($key != 'new' && ($value["description"] != $current_site_settings[$key]["description"] || $value["value"] != $current_site_settings[$key]["value"])) {
            $update[] = '(' . sqlesc($key) . ', ' . sqlesc(trim($value["value"])) . ', ' . sqlesc(trim($value["description"])) . ')';
        } elseif ($key === 'new' && isset($value["value"]) && $value["value"] != '') {
            extract($value);
            $update[] = '(' . sqlesc(strtolower(str_replace(' ', '_', trim($setting)))) . ', ' . sqlesc(trim($value)) . ', ' . sqlesc(trim($description)) . ')';
        }
    }
    if (!empty($update) && sql_query('INSERT INTO site_config(name, value, description) VALUES ' . join(', ', $update) . ' ON DUPLICATE KEY update value = VALUES(value), description = VALUES(description)')) {
        $mc1->delete_value('site_settings_');
        setSessionVar('is-success', 'Update Successful');
    } else {
        setSessionVar('is-warning', $lang['sitesettings_stderr3']);
    }
}
unset($_POST);
$pconf = sql_query('SELECT * FROM site_config ORDER BY name') or sqlerr(__FILE__, __LINE__);
while ($ac = mysqli_fetch_assoc($pconf)) {
    $site_settings[] = $ac;
}
$HTMLOUT .= "
        <div class='container is-fluid portlet'>
            <h3 class='has-text-centered top20'>{$lang['sitesettings_sitehead']}</h3>
            <form action='./staffpanel.php?tool=site_settings' method='post'>
                <div class='table-wrapper'>
                    <table class='table table-bordered table-striped bottom20 w-100'>";
foreach ($site_settings as $site_setting) {
    extract($site_setting);
    if (is_numeric($value)) {
        $value = (double)$value;
    }
    $var = $name . '[value]';
    $input = "
                        <input type='text' name='{$var}' value='" . htmlsafechars($value) . "' class='w-100' />";
    if (is_numeric($value) && ($value == 0 || $value == 1)) {
        $input = "
                        <div class='level-center'>
                            <label for ='{$var}' class='right10'>{$lang['sitesettings_no']}
                                <input class='table' type='radio' name='{$var}' value='0' " . ((int)$value === 0 ? 'checked' : '') . " />
                            </label>
                            <label for ='{$var}' class='right10'>{$lang['sitesettings_yes']}
                                <input class='table' type='radio' name='{$var}' value='1' " . ((int)$value === 1 ? 'checked' : '') . " />
                            </label>
                        </div>";
    }

    $var = $name . '[description]';
    $HTMLOUT .= "
                    <tr>
                        <td class='w-10'>
                            " . htmlsafechars(ucwords(str_replace('_', ' ', $name))) . "
                        </td>
                        <td class='w-15'>
                            $input
                        </td>
                        <td>
                            <textarea name='{$var}' class='w-100'>" . htmlsafechars($description) . "</textarea>
                        </td>
                    </tr>";
}

$name = 'new[setting]';
$value = 'new[value]';
$descr = 'new[description]';
$HTMLOUT .= "
                    <tr>
                        <td>
                            <input type='text' name='{$name}' value='' class='w-100' placeholder='New Site Setting Name' />
                        </td>
                        <td>
                            <input type='text' name='{$value}' value='' class='w-100' placeholder='Use 0 for false, 1 for true, or anyother int/float as needed.' />
                        </td>
                        <td>
                            <textarea name='{$descr}' class='w-100' placeholder='Description'></textarea>
                        </td>
                    </tr>
                </table>
                </div>
                <div class='has-text-centered top20 bottom20'>
                    <input type='submit' value='{$lang['sitesettings_apply']}' />
                </div>
            </form>
        </div>";

echo stdhead($lang['sitesettings_stdhead']) . $HTMLOUT . stdfoot();
