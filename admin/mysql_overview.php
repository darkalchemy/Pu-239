<?php

declare(strict_types = 1);

use Pu239\Database;

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'function_html.php';
class_check(UC_MAX);
$lang = array_merge($lang, load_language('ad_mysql_overview'));
global $site_config;

if (isset($_GET['Do']) && $_GET['Do'] === 'optimize' && isset($_GET['table'])) {
    $table = htmlsafechars(strip_tags($_GET['table']));
    if (!preg_match('/[^A-Za-z_]+/', $table)) {
        $Table = "`{$table}`";
    } else {
        stderr($lang['mysql_over_error'], $lang['mysql_over_pg']);
    }
    $sql = "OPTIMIZE TABLE $Table";
    if (preg_match('@^(CHECK|ANALYZE|REPAIR|OPTIMIZE)[[:space:]]TABLE[[:space:]]' . $Table . '$@i', $sql)) {
        $query = $fluent->getPdo()
                        ->prepare($sql);
        $query->execute();
        header("Location: {$_SERVER['PHP_SELF']}?tool=mysql_overview&action=mysql_overview");
        exit;
    }
}

$HTMLOUT = "
    <h1 class='has-text-centered is-wrapped'>{$lang['mysql_over_title']}</h1>";

$count = 0;
$fluent = $container->get(Database::class);
$tables = $fluent->getPdo()
                 ->prepare('SHOW TABLE STATUS');
$tables->execute();
$query = $tables->fetchAll();
$innodb = true;
foreach ($query as $row) {
    if ($row['Engine'] !== 'InnoDB') {
        $innodb = false;
    }
}
$heading = "
        <tr>
            <th>{$lang['mysql_over_name']}</th>
            <th class='has-text-centered is-wrapped'>{$lang['mysql_over_rows']}</th>
            <th class='has-text-centered is-wrapped'>{$lang['mysql_over_avg_row']}</th>
            <th class='has-text-centered is-wrapped'>{$lang['mysql_over_data_length']}</th>
            <th class='has-text-centered is-wrapped'>{$lang['mysql_over_index_length']}</th>
            <th class='has-text-centered is-wrapped'>{$lang['mysql_over_table_length']}</th>
            <th class='has-text-centered is-wrapped'>{$lang['mysql_over_overhead']}</th>
            <th class='has-text-centered is-wrapped'>{$lang['mysql_over_auto_increment']}</th>
            <th class='has-text-centered is-wrapped'>{$lang['mysql_over_rf']}</th>
            <th class='has-text-centered is-wrapped'>{$lang['mysql_over_collation']}</th>
            <th class='has-text-centered is-wrapped'>{$lang['mysql_over_ct']}</th>" . (!$innodb ? "
            <th class='has-text-centered is-wrapped'>{$lang['mysql_over_ut']}</th>
            <th class='has-text-centered is-wrapped'>{$lang['mysql_over_chkt']}</th>" : '') . '
        </tr>';
$body = '';
if (!empty($query)) {
    foreach ($query as $row) {
        $avg_length = mksize($row['Avg_row_length'], 0);
        $data_length = mksize($row['Data_length'], 0);
        $index_length = mksize($row['Index_length'], 0);
        $data_free = mksize($row['Data_free'], 0);
        $tablesize = $row['Data_length'] + $row['Index_length'];
        $table_length = mksize($tablesize, 0);
        $update_time = isset($row['Update_time']) ? $row['Update_time'] : 'null';
        $check_time = isset($row['Check_time']) ? $row['Check_time'] : 'null';
        $autoincrement = isset($row['Auto_increment']) ? number_format($row['Auto_increment']) : 'null';
        $thispage = '&amp;Do=optimize&amp;table=' . urlencode($row['Name']);
        $overhead = ($row['Data_free'] > 1024 * 1024 * 10) ? "
                <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=mysql_overview&amp;action=mysql_overview$thispage'>
                    <span class='has-text-danger has-text-weight-bold'>$data_free</span>
                </a>" : $data_free;
        $body .= "
            <tr>
                <td>{$row['Name']}</td>
                <td class='has-text-centered is-wrapped'>{$row['Rows']}</td>
                <td class='has-text-centered is-wrapped'>{$avg_length}</td>
                <td class='has-text-centered is-wrapped'>{$data_length}</td>
                <td class='has-text-centered is-wrapped'>{$index_length}</td>
                <td class='has-text-centered is-wrapped'>{$table_length}</td>
                <td class='has-text-centered is-wrapped'>{$overhead}</td>
                <td class='has-text-centered is-wrapped'>{$autoincrement}</td>
                <td class='has-text-centered is-wrapped'>{$row['Engine']}::{$row['Row_format']}</td>
                <td class='has-text-centered is-wrapped'>" . str_replace('_', ' ', $row['Collation']) . "</td>
                <td class='has-text-centered is-wrapped'>{$row['Create_time']}</td>" . (!$innodb ? "
                <td class='has-text-centered is-wrapped'>{$update_time}</td>
                <td class='has-text-centered is-wrapped'>{$check_time}</td>" : '') . '
            </tr>';
        ++$count;
    }
}
$body .= "
        <tr>
            <td><b>{$lang['mysql_over_tables']} {$count}</b></td>
            <td colspan='12'>{$lang['mysql_over_if']} <span class='has-text-danger has-text-weight-bold'>{$lang['mysql_over_red']}</span>{$lang['mysql_over_it_needs']}<p>{$lang['mysql_innodb']}</p></td>
        </tr>";

$HTMLOUT .= main_table($body, $heading);

echo stdhead($lang['mysql_over_stdhead']) . wrapper($HTMLOUT) . stdfoot();
