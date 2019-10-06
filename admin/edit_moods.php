<?php

declare(strict_types = 1);

use Pu239\Cache;

require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $container, $site_config, $CURUSER;

$HTMLOUT = '';
if (isset($_POST) || isset($_GET)) {
    $edit_params = array_merge($_GET, $_POST);
}
$edit_mood['action'] = isset($edit_params['action']) ? $edit_params['action'] : 0;
$edit_mood['id'] = isset($edit_params['id']) ? (int) $edit_params['id'] : 0;
$edit_mood['name'] = isset($edit_params['name']) ? $edit_params['name'] : '';
$edit_mood['image'] = isset($edit_params['image']) ? $edit_params['image'] : '';
$edit_mood['bonus'] = isset($edit_params['bonus']) ? 1 : 0;
$cache = $container->get(Cache::class);
if ($edit_mood['action'] === 'added') {
    if ($edit_mood['name'] != 'is example mood' && $edit_mood['image'] != 'smile1.gif') {
        sql_query('INSERT INTO moods (name, image, bonus) VALUES (' . sqlesc($edit_mood['name']) . ', ' . sqlesc($edit_mood['image']) . ', ' . sqlesc($edit_mood['bonus']) . ')') or sqlerr(__FILE__, __LINE__);
        $cache->delete('topmoods');
        write_log('<b>' . _('Mood Added') . '</b> ' . htmlsafechars($CURUSER['username']) . ' - ' . htmlsafechars($edit_mood['name']) . '<img src="' . $site_config['paths']['images_baseurl'] . 'smilies/' . htmlsafechars($edit_mood['image']) . '" alt="">');
    }
} elseif ($edit_mood['action'] === 'edited') {
    sql_query('UPDATE moods SET name = ' . sqlesc($edit_mood['name']) . ', image = ' . sqlesc($edit_mood['image']) . ', bonus = ' . sqlesc($edit_mood['bonus']) . ' WHERE id=' . sqlesc($edit_mood['id'])) or sqlerr(__FILE__, __LINE__);
    $cache->delete('topmoods');
    write_log('<b>' . _('Mood Edited') . '</b> ' . htmlsafechars($CURUSER['username']) . ' - ' . htmlsafechars($edit_mood['name']) . '<img src="' . $site_config['paths']['images_baseurl'] . 'smilies/' . htmlsafechars($edit_mood['image']) . '" alt="">');
}
if ($edit_mood['action'] === 'edit' && $edit_mood['id']) {
    $edit_mood['res'] = sql_query('SELECT * FROM moods WHERE id=' . sqlesc($edit_mood['id'])) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($edit_mood['res'])) {
        $edit_mood['arr'] = mysqli_fetch_assoc($edit_mood['res']);
        $HTMLOUT .= "<h1 class='has-text-centered'>" . _('Edit Mood') . "</h1>
            <form method='post' action='staffpanel.php?tool=edit_moods&amp;action=edited' enctype='multipart/form-data' accept-charset='utf-8'>
            <table class='table table-bordered table-striped'>
            <tr><td class='colhead'>" . _('Name') . "</td>
            <td><input type='text' name='name' size='40' value ='" . htmlsafechars($edit_mood['arr']['name']) . "'></td></tr>
            <tr><td class='colhead'>" . _('Image') . "</td>
            <td><input type='text' name='image' size='40' value ='" . htmlsafechars($edit_mood['arr']['image']) . "'></td></tr>
            <tr><td class='colhead'>" . _('Bonus') . "</td>
            <td><input type='checkbox' name='bonus' " . ($edit_mood['arr']['bonus'] ? 'checked' : '') . "></td></tr>
            <tr><td colspan='2' class='has-text-centered'>
            <input type='hidden' name='id' value='" . (int) $edit_mood['id'] . "'>
            <input type='submit' name='okay' value='" . _('Add') . "' class='button is-small'>
            </td></tr>
            </table></form>";
    }
} else {
    $HTMLOUT .= "<h1 class='has-text-centered'>" . _('Add New Mood') . "</h1>
         <form method='post' action='staffpanel.php?tool=edit_moods&amp;action=added' enctype='multipart/form-data' accept-charset='utf-8'>
         <table class='table table-bordered table-striped'>
         <tr><td class='colhead'>" . _('Name') . "</td>
         <td><input type='text' name='name' size='40' value ='is example mood'></td></tr>
         <tr><td class='colhead'>" . _('Image') . "</td>
         <td><input type='text' name='image' size='40' value ='smiley1.gif'></td></tr>
         <tr><td class='colhead'>" . _('Bonus') . "</td>
         <td><input type='checkbox' name='bonus'></td></tr>
         <tr><td colspan='2' class='has-text-centered'>
         <input type='submit' name='okay' value='" . _('Add') . "' class='button is-small'>
         </td></tr>
         </table></form>";
}
$HTMLOUT .= '<h1 class="has-text-centered">' . _('Current Moods') . '</h1>';
$HTMLOUT .= "<table class='table table-bordered table-striped'>
      <tr><td class='colhead'>" . _('Added') . "</td>
      <td class='colhead'>" . _('Name') . "</td>
      <td class='colhead'>" . _('Image') . "</td>
      <td class='colhead'>" . _('Bonus') . "</td>
      <td class='colhead'>" . _('Edit') . '</td>' . //<td class='colhead'>" . _('Remove') . "</td>
    '</tr>';
$res = sql_query('SELECT * FROM moods ORDER BY id') or sqlerr(__FILE__, __LINE__);
if (mysqli_num_rows($res)) {
    $color = true;
    while ($arr = mysqli_fetch_assoc($res)) {
        $HTMLOUT .= '<tr ' . (($color = !$color) ? ' style="background-color:#000000;"' : 'style="background-color:#0f0f0f;"') . '>
      <td><img src="' . $site_config['paths']['images_baseurl'] . 'smilies/' . htmlsafechars($arr['image']) . '" alt=""></td>
      <td>' . htmlsafechars($arr['name']) . '</td>
      <td>' . htmlsafechars($arr['image']) . '</td>
      <td>' . ($arr['bonus'] != 0 ? _('Yes') . '' : _('No') . '') . '</td>
      <td><a style="color:#FF0000" href="' . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=edit_moods&amp;id=' . (int) $arr['id'] . '&amp;action=edit">' . _('Edit') . '</a></td></tr>' . //<td><a style="color:#FF0000" href="' . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=edit_moods&amp;action=remove$amp;id='.$arr['id'].'&amp;hash='.$form_hash.'>'._('Remove').'</a></td></tr>
            '';
    }
    $HTMLOUT .= '</table>';
}
$title = _('Edit Moods');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
