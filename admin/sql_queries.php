<?php
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'pager_functions.php';

$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);

global $site_config, $lang, $CURUSER;
$lang = array_merge($lang, load_language('ad_class_config'));

if (!in_array($CURUSER['id'], $site_config['is_staff']['allowed'])) {
    stderr($lang['classcfg_error'], $lang['classcfg_denied']);
}

if (!empty($_POST['ids']) && $_POST['delete'] === 'delete') {
    $cnt = count($_POST['ids']);
    $count = $cnt == 1 ? 'query' : 'queries';
    $sql = "DELETE FROM queries WHERE id IN (" . implode(', ', $_POST['ids']) . ")";
    sql_query($sql) or sqlerr(__FILE__, __LINE__);
    unset($_POST);
    setSessionVar('is-success', "$cnt $count deleted");
}
$HTMLOUT = $body = $header = '';
$list = [];
$count = get_row_count('queries');
$pager = pager(25, $count, './staffpanel.php?tool=sql_queries&amp;');

$sql = "SELECT * FROM queries ORDER BY id DESC {$pager['limit']}";
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
while ($item = mysqli_fetch_assoc($res)) {
    $list[] = $item;
}

$HTMLOUT .= $pager['pagertop'] . "
        <form method='post' action='./staffpanel.php?tool=sql_queries'>";
$header = "
                <th>ID</th>
                <th>Query</th>
                <th>Date</th>
                <th><input type='checkbox' id='checkThemAll' class='tooltipper' title='Select All' /></th>";

if ($count >= 1) {
    foreach ($list as $details) {
        $id = $query = $dateTime = '';
        extract($details);
        $body .= "
            <tr>
                <td>$id</td>
                <td>$query</td>
                <td>$dateTime</td>
                <td>
                    <input type='checkbox' name='ids[]' class='tooltipper' title='Delete' value='$id' />
                </td>
            </tr>";
    }
} else {
    $body .= "
            <tr>
                <td colspan='4'>No queries in the log</td>
            </tr>";
}

$HTMLOUT .= main_table($body, $header);
if ($count >= 1) {
    $HTMLOUT .= "
            <div class='has-text-centered top20 bottom20 level-center flex-center'>
                <input type='hidden' name='delete' value='delete' />
                <input type='submit' class='button is-small' value='Delete Selected' />
            </div>
        </form>" . $pager['pagerbottom'];
}

$HTMLOUT = wrapper("<h1>MySQL Queries</h1>$HTMLOUT");

echo stdhead('MySQL Queries') . $HTMLOUT . stdfoot();
