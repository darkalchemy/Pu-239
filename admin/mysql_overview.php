<?php

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'function_html.php';
class_check(UC_MAX);
global $site_config, $lang, $fluent;

$lang = array_merge($lang, load_language('ad_mysql_overview'));
if (isset($_GET['Do']) && $_GET['Do'] === 'optimize' && isset($_GET['table'])) {
    $table = htmlspecialchars(strip_tags($_GET['table']));
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
        header("Location: {$site_config['paths']['baseurl']}/staffpanel.php?tool=mysql_overview&action=mysql_overview");
        exit;
    }
}

$HTMLOUT = "
    <h1 class='has-text-centered'>{$lang['mysql_over_title']}</h1>";

$heading = "
        <tr>
            <th>{$lang['mysql_over_name']}</th>
            <th class='has-text-centered'>{$lang['mysql_over_rows']}</th>
            <th class='has-text-centered'>{$lang['mysql_over_avg_row']}</th>
            <th class='has-text-centered'>{$lang['mysql_over_data_length']}</th>
            <th class='has-text-centered'>{$lang['mysql_over_index_length']}</th>
            <th class='has-text-centered'>{$lang['mysql_over_table_length']}</th>
            <th class='has-text-centered'>{$lang['mysql_over_overhead']}</th>
            <th class='has-text-centered'>{$lang['mysql_over_auto_increment']}</th>
            <th class='has-text-centered'>{$lang['mysql_over_rf']}</th>
            <th class='has-text-centered'>{$lang['mysql_over_collation']}</th>
            <th class='has-text-centered'>{$lang['mysql_over_ct']}</th>
            <th class='has-text-centered'>{$lang['mysql_over_ut']}</th>
            <th class='has-text-centered'>{$lang['mysql_over_chkt']}</th>
        </tr>";

$count = 0;
$tables = $fluent->getPdo()
                 ->prepare('SHOW TABLE STATUS');
$tables->execute();
$query = $tables->fetchAll();

$body = '';
if (!empty($query)) {
    foreach ($query as $row) {
        //dd($row);
        $avg_length = mksize($row['Avg_row_length']);
        $data_length = mksize($row['Data_length']);
        $index_length = mksize($row['Index_length']);
        $data_free = mksize($row['Data_free']);
        $tablesize = $row['Data_length'] + $row['Index_length'];
        $table_length = mksize($tablesize);
        $update_time = !empty($row['Update_time']) ? $row['Update_time'] : 'null';
        $check_time = !empty($row['Check_time']) ? $row['Check_time'] : 'null';
        $autoincrement = !empty($row['Auto_increment']) ? $row['Auto_increment'] : 'null';
        $thispage = '&amp;Do=optimize&amp;table=' . urlencode($row['Name']);
        $overhead = ($row['Data_free'] > 1024 * 1024 * 10) ? "
                <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=mysql_overview&amp;action=mysql_overview$thispage'>
                    <span class='has-text-danger has-text-weight-bold'>$data_free</span>
                </a>" : $data_free;
        $body .= '
            <tr>
                <td>' . strtoupper($row['Name']) . "</td>
                <td class='has-text-centered'>{$row['Rows']}</td>
                <td class='has-text-centered'>{$avg_length}</td>
                <td class='has-text-centered'>{$data_length}</td>
                <td class='has-text-centered'>{$index_length}</td>
                <td class='has-text-centered'>{$table_length}</td>
                <td class='has-text-centered'>{$overhead}</td>
                <td class='has-text-centered'>{$autoincrement}</td>
                <td class='has-text-centered'>{$row['Row_format']}</td>
                <td class='has-text-centered'>{$row['Collation']}</td>
                <td class='has-text-centered'>{$row['Create_time']}</td>
                <td class='has-text-centered'>{$update_time}</td>
                <td class='has-text-centered'>{$check_time}</td>
            </tr>";
        ++$count;
    }
}
$body .= "
        <tr>
            <td><b>{$lang['mysql_over_tables']} {$count}</b></td>
            <td colspan='12'>{$lang['mysql_over_if']} <span class='has-text-danger has-text-weight-bold'>{$lang['mysql_over_red']}</span>{$lang['mysql_over_it_needs']}</td>
        </tr>";

$HTMLOUT .= main_table($body, $heading);

echo stdhead($lang['mysql_over_stdhead']) . wrapper($HTMLOUT) . stdfoot();
