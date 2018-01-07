<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
global $site_config;

$lang = array_merge(load_language('global'), load_language('users'));
$search = isset($_GET['search']) ? strip_tags(trim($_GET['search'])) : '';
$class = isset($_GET['class']) ? $_GET['class'] : '-';
$letter = '';
$q1 = '';
if ($class == '-' || !ctype_digit($class)) {
    $class = '';
}
if ($search != '' || $class) {
    $query1 = 'username LIKE ' . sqlesc("%$search%") . " AND status = 'confirmed' AND anonymous_until = 0";
    if ($search) {
        $q1 = 'search=' . htmlsafechars($search);
    }
} else {
    $letter = isset($_GET['letter']) ? trim((string)$_GET['letter']) : '';
    if (strlen($letter) > 1) {
        die;
    }
    if ($letter == '' || strpos('abcdefghijklmnopqrstuvwxyz0123456789', $letter) === false) {
        $letter = '';
    }
    $query1 = "username LIKE '$letter%' AND status = 'confirmed' AND anonymous_until = 0";
    $q1 = "letter=$letter";
}
if (ctype_digit($class)) {
    $query1 .= " AND class=$class";
    $q1 .= ($q1 ? '&amp;' : '') . "class=$class";
}
$HTMLOUT = '';
$HTMLOUT .= "
    <h1 class='has-text-centered'>Search {$lang['head_users']}</h1>";
$div = "
    <div class='has-text-centered'>
        <form method='get' action='users.php?'>
            <span class='right10 top20'>{$lang['form_search']}</span>
            <input type='text' name='search' class='w-25 top20' />
            <select name='class' class='left10 top20'>";
$div .= "
                <option value='-'>(any class)</option>";
for ($i = 0; ; ++$i) {
    if ($c = get_user_class_name($i)) {
        $div .= "
                <option value='$i'" . (ctype_digit($class) && $class == $i ? " selected" : '') . ">$c</option>";
    } else {
        break;
    }
}
$div .= "
            </select>
            <input type='submit' value='{$lang['form_btn']}' class='button is-small left10 top20' />
        </form>
    </div>";

$aa = range('0', '9');
$bb = range('a', 'z');
$cc = [$aa, $bb];
foreach ($cc as $aa) {
    $div .= "
    <div class='tabs is-small is-centered top20'>
        <ul>";
    foreach ($aa as $L) {
        if (!strcmp($L, $letter)) {
            $div .= "
            <li>" . strtoupper($L) . "</li>";
        } else {
            $div .= "
            <li><a href='users.php?letter=$L'>" . strtoupper($L) . "</a></li>";
        }
    }
    $div .= "
        </ul>
    </div>";
}

$HTMLOUT .= main_div($div, 'bottom20');

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perpage = 25;
$browsemenu = '';
$pagemenu = '';
$res = sql_query('SELECT COUNT(*) FROM users WHERE ' . $query1) or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_row($res);
if ($arr[0] > $perpage) {
    $pages = floor($arr[0] / $perpage);
    if ($pages * $perpage < $arr[0]) {
        ++$pages;
    }
    if ($page < 1) {
        $page = 1;
    } elseif ($page > $pages) {
        $page = $pages;
    }
    for ($i = 1; $i <= $pages; ++$i) {
        $PageNo = $i + 1;
        if ($PageNo < ($page - 2)) {
            continue;
        }
        if ($i == $page) {
            $pagemenu .= "&#160;<span class='button is-small'>$i</span>\n";
        } else {
            $pagemenu .= "&#160;<a href='users.php?$q1&amp;page=$i'><span class='button is-small'>$i</span></a>\n";
        }
        if ($PageNo > ($page + 3)) {
            break;
        }
    }
    if ($page == 1) {
        $browsemenu .= "<span class='button is-small'>&lsaquo;</span>$pagemenu";
    } else {
        $browsemenu .= "<a href='users.php?$q1&amp;page=1' title='{$lang['pager_first']}(1)'><span class='button is-small'>&laquo;</span></a>&#160;<a href='users.php?$q1&amp;page=" . ($page - 1) . "'><span class='button is-small'>&lsaquo;</span></a>$pagemenu";
    }
    if ($page == $pages) {
        $browsemenu .= "<span class='button is-small'>&rsaquo;</span>";
    } else {
        $browsemenu .= "<a href='users.php?$q1&amp;page=" . ($page + 1) . "'><span class='button is-small'>&rsaquo;</span></a>&#160;<a href='users.php?$q1&amp;page=" . $pages . "' title='{$lang['pager_last']}($pages)'><span class='button is-small'>&raquo;</span></a>";
    }
}
$offset = ($page * $perpage) - $perpage;
if ($arr[0] > 0) {
    $res = sql_query("SELECT users.*, countries.name, countries.flagpic FROM users FORCE INDEX ( username ) LEFT JOIN countries ON country = countries.id WHERE $query1 ORDER BY username LIMIT $offset,$perpage") or sqlerr(__FILE__, __LINE__);
    $HTMLOUT .= "<div class='container is-fluid portlet has-text-centered'>";
    $HTMLOUT .= "<table class='table table-bordered table-striped'>\n";
    $HTMLOUT .= "<tr><td class='colhead'>{$lang['users_username']}</td><td class='colhead'>{$lang['users_regd']}</td><td class='colhead'>{$lang['users_la']}</td><td class='colhead'>{$lang['users_class']}</td><td class='colhead'>{$lang['users_country']}</td></tr>\n";
    while ($row = mysqli_fetch_assoc($res)) {
        $country = ($row['name'] != null) ? "<td><img src='{$site_config['pic_base_url']}flag/" . htmlsafechars($row['flagpic']) . "' alt='" . htmlsafechars($row['name']) . "' /></td>" : "<td>---</td>";
        $HTMLOUT .= "<tr><td><a href='userdetails.php?id=" . (int)$row['id'] . "'><b>" . htmlsafechars($row['username']) . '</b></a>' . ($row['donor'] > 0 ? "<img src='{$site_config['pic_base_url']}star.gif' border='0' alt='{$lang['users_donor']}' />" : '') . '</td>' . '<td>' . get_date($row['added'], '') . '</td><td>' . get_date($row['last_access'], '') . '</td>' . "<td>" . get_user_class_name($row['class']) . "</td>$country</tr>\n";
    }
    $HTMLOUT .= "</table></div>\n";
}
$HTMLOUT .= ($arr[0] > $perpage) ? "<div class='has-text-centered margin20'><p>$browsemenu</p></div>" : '<br>';
$HTMLOUT .= '</fieldset>';
echo stdhead($lang['head_users']) . wrapper($HTMLOUT) . stdfoot();
die;
