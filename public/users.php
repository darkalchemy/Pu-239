<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('users'));
$search = isset($_GET['search']) ? strip_tags(trim($_GET['search'])) : '';
$class = isset($_GET['class']) ? $_GET['class'] : '-';
$letter = '';
$q1 = '';
if ($class == '-' || !ctype_digit($class)) {
    $class = '';
}
if ($search != '' || $class) {
    $query1 = 'username LIKE ' . sqlesc("%$search%") . " AND status='confirmed'";
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
    $query1 = "username LIKE '$letter%' AND status='confirmed'";
    $q1 = "letter=$letter";
}
if (ctype_digit($class)) {
    $query1 .= " AND class=$class";
    $q1 .= ($q1 ? '&amp;' : '') . "class=$class";
}
$HTMLOUT = '';
$HTMLOUT .= "<fieldset class='header'><legend>{$lang['head_users']}</legend>\n";
$HTMLOUT .= "<form method='get' action='users.php?'>\n";
$HTMLOUT .= "{$lang['form_search']} <input type='text' size='30' name='search' />\n";
$HTMLOUT .= "<select name='class'>\n";
$HTMLOUT .= "<option value='-'>(any class)</option>\n";
for ($i = 0; ; ++$i) {
    if ($c = get_user_class_name($i)) {
        $HTMLOUT .= "<option value='$i'" . (ctype_digit($class) && $class == $i ? " selected='selected'" : '') . ">$c</option>\n";
    } else {
        break;
    }
}
$HTMLOUT .= "</select>\n";
$HTMLOUT .= "<input type='submit' value='{$lang['form_btn']}' class='btn' />\n";
$HTMLOUT .= "</form>\n";
$HTMLOUT .= "<br>\n";
$aa = range('0', '9');
$bb = range('a', 'z');
$cc = array_merge($aa, $bb);
unset($aa, $bb);
$HTMLOUT .= "<div>";
$count = 0;
foreach ($cc as $L) {
    $HTMLOUT .= ($count == 10) ? '<br><br>' : '';
    if (!strcmp($L, $letter)) {
        $HTMLOUT .= "<span class='btn'>" . strtoupper($L) . "</span>\n";
    } else {
        $HTMLOUT .= "<a href='users.php?letter=$L'><span class='btn'>" . strtoupper($L) . "</span></a>\n";
    }
    ++$count;
}
$HTMLOUT .= '</div>';
$HTMLOUT .= "<br>\n";
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
            $pagemenu .= "&#160;<span class='btn'>$i</span>\n";
        } else {
            $pagemenu .= "&#160;<a href='users.php?$q1&amp;page=$i'><span class='btn'>$i</span></a>\n";
        }
        if ($PageNo > ($page + 3)) {
            break;
        }
    }
    if ($page == 1) {
        $browsemenu .= "<span class='btn'>&lsaquo;</span>$pagemenu";
    } else {
        $browsemenu .= "<a href='users.php?$q1&amp;page=1' title='{$lang['pager_first']}(1)'><span class='btn'>&laquo;</span></a>&#160;<a href='users.php?$q1&amp;page=" . ($page - 1) . "'><span class='btn'>&lsaquo;</span></a>$pagemenu";
    }
    if ($page == $pages) {
        $browsemenu .= "<span class='btn'>&rsaquo;</span>";
    } else {
        $browsemenu .= "<a href='users.php?$q1&amp;page=" . ($page + 1) . "'><span class='btn'>&rsaquo;</span></a>&#160;<a href='users.php?$q1&amp;page=" . $pages . "' title='{$lang['pager_last']}($pages)'><span class='btn'>&raquo;</span></a>";
    }
}
$offset = ($page * $perpage) - $perpage;
if ($arr[0] > 0) {
    $res = sql_query("SELECT users.*, countries.name, countries.flagpic FROM users FORCE INDEX ( username ) LEFT JOIN countries ON country = countries.id WHERE $query1 ORDER BY username LIMIT $offset,$perpage") or sqlerr(__FILE__, __LINE__);
    $HTMLOUT .= "<div class='container-fluid text-center'>";
    $HTMLOUT .= "<table class='table table-bordered table-striped'>\n";
    $HTMLOUT .= "<tr><td class='colhead'>{$lang['users_username']}</td><td class='colhead'>{$lang['users_regd']}</td><td class='colhead'>{$lang['users_la']}</td><td class='colhead'>{$lang['users_class']}</td><td class='colhead'>{$lang['users_country']}</td></tr>\n";
    while ($row = mysqli_fetch_assoc($res)) {
        $country = ($row['name'] != null) ? "<td><img src='{$site_config['pic_base_url']}flag/" . htmlsafechars($row['flagpic']) . "' alt='" . htmlsafechars($row['name']) . "' /></td>" : "<td>---</td>";
        $HTMLOUT .= "<tr><td><a href='userdetails.php?id=" . (int)$row['id'] . "'><b>" . htmlsafechars($row['username']) . '</b></a>' . ($row['donor'] > 0 ? "<img src='{$site_config['pic_base_url']}star.gif' border='0' alt='{$lang['users_donor']}' />" : '') . '</td>' . '<td>' . get_date($row['added'], '') . '</td><td>' . get_date($row['last_access'], '') . '</td>' . "<td>" . get_user_class_name($row['class']) . "</td>$country</tr>\n";
    }
    $HTMLOUT .= "</table></div>\n";
}
$HTMLOUT .= ($arr[0] > $perpage) ? "<div><p>$browsemenu</p></div>" : '<br>';
$HTMLOUT .= '</fieldset>';
echo stdhead($lang['head_users']) . $HTMLOUT . stdfoot();
die;
