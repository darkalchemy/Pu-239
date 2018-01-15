<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'torrenttable_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once INCL_DIR . 'searchcloud_functions.php';
require_once CLASS_DIR . 'class_user_options.php';
require_once CLASS_DIR . 'class_user_options_2.php';
check_user_status();
global $CURUSER, $site_config, $cache;

if (isset($_GET['clear_new']) && $_GET['clear_new'] == 1) {
    sql_query('UPDATE users SET last_browse = ' . TIME_NOW . ' WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $cache->update_row('user' . $CURUSER['id'], [
        'last_browse' => TIME_NOW,
    ], $site_config['expires']['user_cache']);
    header("Location: {$site_config['baseurl']}/browse.php");
}
$stdhead = [
    'css' => [
        get_file_name('browse_css'),
    ],
];
$lang = array_merge(load_language('global'), load_language('browse'), load_language('torrenttable_functions'));
$HTMLOUT = $searchin = $select_searchin = $where = $addparam = $new_button = $vip_box = $only_free = $searchstr = $join = '';
$searchincrt = [];

$catids = genrelist();
if (isset($_GET['search'])) {
    $searchstr = unesc($_GET['search']);
    $cleansearchstr = searchfield($searchstr);
    if (empty($cleansearchstr)) {
        unset($cleansearchstr);
    }
}
$valid_searchin = [
    'title' => [
        'name',
    ],
    'descr' => [
        'descr',
    ],
    'owner' => [
        'owner',
    ],
    'genre' => [
        'newgenre',
    ],
    'all'   => [
        'name',
        'newgenre',
        'descr',
        'owner',
    ],
];
if (isset($_GET['searchin']) && isset($valid_searchin[$_GET['searchin']])) {
    $searchin = $valid_searchin[$_GET['searchin']];
    $select_searchin = $_GET['searchin'];
    $addparam .= sprintf('search=%s&amp;searchin=%s&amp;', $searchstr, $select_searchin);
}
if (isset($_GET['sort']) && isset($_GET['type'])) {
    $column = $ascdesc = '';
    $_valid_sort = [
        'id',
        'name',
        'numfiles',
        'comments',
        'added',
        'size',
        'times_completed',
        'seeders',
        'leechers',
        'owner',
    ];
    $column = isset($_GET['sort']) && isset($_valid_sort[(int)$_GET['sort']]) ? $_valid_sort[(int)$_GET['sort']] : $_valid_sort[0];
    switch (htmlsafechars($_GET['type'])) {
        case 'asc':
            $ascdesc = 'ASC';
            $linkascdesc = 'asc';
            break;

        case 'desc':
            $ascdesc = 'DESC';
            $linkascdesc = 'desc';
            break;

        default:
            $ascdesc = 'DESC';
            $linkascdesc = 'desc';
            break;
    }
    $orderby = "ORDER BY {$column} " . $ascdesc;
    $pagerlink = 'sort=' . intval($_GET['sort']) . "&amp;type={$linkascdesc}&amp;";
} else {
    $orderby = 'ORDER BY sticky ASC, id DESC';
    $pagerlink = '';
}

$wherea = $wherecatina = [];
$today = 0;
if (!empty($_GET['today']) && $_GET['today']) {
    $wherea[] = 't.added >= ' . strtotime('today midnight');
    $addparam .= 'today=1&amp;';
    $today = 1;
}

if (isset($_GET['incldead']) && $_GET['incldead'] == 1) {
    $addparam .= 'incldead=1&amp;';
    if (!isset($CURUSER) || $CURUSER['class'] < UC_ADMINISTRATOR) {
        $wherea[] = "banned != 'yes'";
    }
} else {
    if (isset($_GET['incldead']) && $_GET['incldead'] == 2) {
        $addparam .= 'incldead=2&amp;';
        $wherea[] = "visible = 'no'";
    } else {
        $wherea[] = "visible = 'yes'";
    }
}

if (isset($_GET['only_free']) && $_GET['only_free'] == 1) {
    $wherea[] = XBT_TRACKER ? $wherea[] = "freetorrent >= '1'" : $wherea[] = "free >= '1'";
    $addparam .= 'only_free=1&amp;';
}
if (isset($_GET['vip'])) {
    if ($_GET['vip'] == 2) {
        $wherea[] = "vip = '1'";
    } elseif ($_GET['vip'] == 1) {
        $wherea[] = "vip = '0'";
    }
    $addparam .= "vip={$_GET['vip']}&amp;";
}

$category = (isset($_GET['cat'])) ? (int)$_GET['cat'] : false;
if (!$_GET && $CURUSER['notifs']) {
    foreach ($catids as $cat) {
        if (strpos($CURUSER['notifs'], '[cat' . $cat['id'] . ']') !== false) {
            $wherecatina[] = $cat['id'];
            $addparam .= "c{$cat['id']}=1&amp;";
        }
    }
} elseif ($category) {
    if (!is_valid_id($category)) {
        stderr("{$lang['browse_error']}", "{$lang['browse_invalid_cat']}");
    }
    $wherecatina[] = $cat['id'];
    $addparam .= "cat=$category&amp;";
} else {
    foreach ($catids as $cat) {
        if (isset($_GET["c{$cat['id']}"])) {
            $wherecatina[] = $cat['id'];
            $addparam .= "c{$cat['id']}=1&amp;";
        }
    }
}

if (count($wherecatina) > 1) {
    $wherea[] = 'category IN (' . join(', ', $wherecatina) . ') ';
} elseif (count($wherecatina) == 1) {
    $wherea[] = 'category =' . $wherecatina[0];
}
if (isset($cleansearchstr)) {
    if ($searchstr != '') {
        $addparam .= 'search=' . rawurlencode($searchstr) . '&amp;searchin=' . htmlsafechars($_GET['searchin']) . '&amp;incldead=' . intval($_GET['incldead']) . '&amp;';
        $searchstring = str_replace([
                                        '_',
                                        '.',
                                        '-',
                                    ], ' ', $searchstr);
        $s = [
            '*',
            '?',
            '.',
            '-',
            ' ',
        ];
        $r = [
            '%',
            '_',
            '_',
            '_',
            '_',
        ];

        $searcha = explode(' ', $cleansearchstr);
        searchcloud_insert($cleansearchstr);
        $join = '';
        foreach ($searcha as $foo) {
            foreach ($searchin as $boo) {
                if ($boo === 'owner') {
                    $wherea[] = 'u.username = ' . sqlesc($searchstr);
                    $join = 'LEFT JOIN users AS u ON u.id = t.owner';
                } elseif ($boo === 'newgenre') {
                    $wherea[] = 'newgenre = ' . sqlesc($searchstr);
                } elseif ($boo === 'descr') {
                    $searchincrt[] = 'MATCH (`search_text`, `descr`) AGAINST (' . sqlesc($searchstr) . ' IN NATURAL LANGUAGE MODE)';
                } elseif ($boo === 'name') {
                    $searchincrt[] = 'MATCH (`name`) AGAINST (' . sqlesc($searchstr) . ' IN NATURAL LANGUAGE MODE)';
                } else {
                    $searchincrt[] = 'MATCH (`search_text`, `descr`) AGAINST (' . sqlesc($searchstr) . ' IN NATURAL LANGUAGE MODE)';
                }
            }
        }
        if (count($searchincrt) > 1) {
            $wherea[] = '(' . join(' OR ', $searchincrt) . ')';
        } elseif (count($searchincrt) === 1) {
            $wherea[] = join(' OR ', $searchincrt);
        }
    }
}

$where = count($wherea) ? 'WHERE ' . join(' OR ', $wherea) : '';
$where_key = 'where_' . hash('sha256', $where);
$count = $cache->get($where_key);
if ($count === false || is_null($count)) {
    $res = sql_query("SELECT COUNT(*) FROM torrents AS t $join $where") or sqlerr(__FILE__, __LINE__);
    $row = mysqli_fetch_row($res);
    $count = (int)$row[0];
    $cache->set($where_key, $count, $site_config['expires']['browse_where']);
}
$torrentsperpage = $CURUSER['torrentsperpage'];
if (!$torrentsperpage) {
    $torrentsperpage = 15;
}
if ($count) {
    if ($addparam != '') {
        if ($pagerlink != '') {
            if ($addparam[strlen($addparam) - 1] != ';') { // & = &amp;
                $addparam = $addparam . '&' . $pagerlink;
            } else {
                $addparam = $addparam . $pagerlink;
            }
        }
    } else {
        $addparam = $pagerlink;
    }
    $pager = pager($torrentsperpage, $count, "{$site_config['baseurl']}/browse.php?" . $addparam);

    $query = "SELECT t.id, t.search_text, t.category, t.leechers, t.seeders, t.bump, t.release_group, t.subs, t.name, t.times_completed, t.size, t.added, t.poster, t.descr, t.free, t.freetorrent, t.silver, t.comments, t.numfiles, t.filename, t.anonymous, t.sticky, t.nuked, t.vip, t.nukereason, t.newgenre, t.description, t.owner, t.youtube, t.checked_by, IF(t.nfo <> '', 1, 0) as nfoav," . "IF(t.num_ratings < {$site_config['minvotes']}, NULL, ROUND(t.rating_sum / t.num_ratings, 1)) AS rating, t.checked_when, c.username AS checked_by_username
                FROM torrents AS t
                LEFT JOIN users AS c ON t.checked_by = c.id
                {$join}
                {$where}
                {$orderby}
                {$pager['limit']}";
    $res = sql_query($query) or sqlerr(__FILE__, __LINE__);
} else {
    unset($query);
}

if (isset($cleansearchstr)) {
    $title = "{$lang['browse_search']} $searchstr";
} else {
    $title = '';
}

if ($CURUSER['opt1'] & user_options::VIEWSCLOUD) {
    $HTMLOUT .= main_div("<div class='cloud'>" . cloud() . "</div>", 'bottom20');
}

$HTMLOUT .= "
                                <form id='catsids' method='get' action='{$site_config['baseurl']}/browse.php'>";
if ($today) {
    $HTMLOUT .= "
                                    <input type='hidden' name='today' value='$today' />";
}
$main_div = "
                                        <div id='checkbox_container' class='level-center'>";
if ($CURUSER['opt2'] & user_options_2::BROWSE_ICONS) {
    foreach ($catids as $cat) {
        $main_div .= "
                                            <span class='margin10 mw-50 is-flex tooltipper' title='" . htmlsafechars($cat['name']) . "'>
                                                <span class='bordered level-center bg-02'>
                                                    <input name='c" . (int)$cat['id'] . "' class='styled' type='checkbox' " . (in_array($cat['id'], $wherecatina) ? " checked" : '') . " value='1' />
                                                    <span class='cat-image left10'>
                                                        <a href='{$site_config['baseurl']}/browse.php?c" . (int)$cat['id'] . "'>
                                                            <img class='radius-sm' src='{$site_config['pic_baseurl']}caticons/{$CURUSER['categorie_icon']}/" . htmlsafechars($cat['image']) . "'alt='" . htmlsafechars($cat['name']) . "' />
                                                        </a>
                                                    </span>
                                                </span>
                                            </span>";
    }
} else {
    foreach ($catids as $cat) {
        $main_div .= "
                                            <span class='margin10 bordered tooltipper' title='" . htmlsafechars($cat['name']) . "'>
                                                <label for='c" . (int)$cat['id'] . "'>
                                                    <input name='c" . (int)$cat['id'] . "' class='styled1' type='checkbox' " . (in_array($cat['id'], $wherecatina) ? " checked" : '') . "value='1' />
                                                    <a class='catlink' href='{$site_config['baseurl']}/browse.php?cat=" . (int)$cat['id'] . "'>" . htmlsafechars($cat['name']) . "</a>
                                                </label>
                                            </span>";
    }
}
$main_div .= "
                                        </div>
                                        <div class='has-text-centered'>
                                            <label for='checkAll'>
                                                <input type='checkbox' id='checkAll' /><span> Select All Categories</span>
                                            </label>
                                        </div>";
$HTMLOUT .= main_div($main_div, 'bottom20');

if ($CURUSER['opt1'] & user_options::CLEAR_NEW_TAG_MANUALLY) {
    $new_button = "
        <a href='{$site_config['baseurl']}/browse.php?clear_new=1'><input type='submit' value='clear new tag' class='button is-small' /></a>
        <br>";
} else {
    //== clear new tag automatically
    sql_query('UPDATE users SET last_browse = ' . TIME_NOW . ' WHERE id = ' . $CURUSER['id']);
    $cache->update_row('user' . $CURUSER['id'], [
        'last_browse' => TIME_NOW,
    ], $site_config['expires']['user_cache']);
}

$vip = ((isset($_GET['vip'])) ? intval($_GET['vip']) : '');
$vip_box = "
                    <select name='vip' class='w-100'>
                        <option value='0'>VIP Torrents Included</option>
                        <option value='1'" . ($vip == 1 ? " selected" : '') . ">VIP Torrents Not Included</option>
                        <option value='2'" . ($vip == 2 ? " selected" : '') . ">VIP Torrents Only</option>
                    </select>";

$selected = (isset($_GET['incldead'])) ? (int)$_GET['incldead'] : '';
$deadcheck = "
                    <select name='incldead' class='w-100'>
                        <option value='0'>{$lang['browse_active']}</option>
                        <option value='1'" . ($selected == 1 ? " selected" : '') . ">{$lang['browse_inc_dead']}</option>
                        <option value='2'" . ($selected == 2 ? " selected" : '') . ">{$lang['browse_dead']}</option>
                    </select>";

$only_free = ((isset($_GET['only_free'])) ? intval($_GET['only_free']) : '');
$only_free_box = "
                    <select name='only_free' class='w-100'>
                        <option value='0'>Include Non Free Torrents</option>
                        <option value='1'" . ($only_free == 1 ? " selected" : '') . ">Include Only Free Torrents</option>
                    </select>";

$searchin = '
                    <select name="searchin" class="w-100">';
foreach ([
             'title' => 'Name',
             'descr' => 'Description',
             'genre' => 'Genre',
             'owner' => 'Uploader',
             'all'   => 'All',
         ] as $k => $v) {
    $searchin .= '
                        <option value="' . $k . '"' . ($select_searchin == $k ? ' selected' : '') . '>' . $v . '</option>';
}
$searchin .= '
                    </select>';
$HTMLOUT .= main_div("
                    <div class='padding10' class='w-100'>
                        <input type='text' name='search' placeholder='{$lang['search_search']}' class='search w-100' value='" . (!empty($_GET['search']) ? $_GET['search'] : '') . "' />
                    </div>
                    <div class='level-center'>
                        <div class='padding10 w-25 mw-50'>
                            $searchin
                        </div>
                        <div class='padding10 w-25 mw-50'>
                            $deadcheck
                        </div>
                        <div class='padding10 w-25 mw-50'>
                            $vip_box
                        </div>
                        <div class='padding10 w-25 mw-50'>
                            $only_free_box
                        </div>
                    </div>
                    <div class='margin10 has-text-centered'>
                        <input type='submit' value='{$lang['search_search_btn']}' class='button is-small' />
                    </div>");
$HTMLOUT .= "
            </form>";
$HTMLOUT .= "{$new_button}";

if ($count) {
    $HTMLOUT .= "
                <div class='top20 bottom20'>
                    {$pager['pagertop']}
                </div>
                <div class='table-wrapper'>" . torrenttable($res) . "</div>
                <div class='top20'>
                    {$pager['pagerbottom']}
                </div>";
} else {
    if (isset($cleansearchstr)) {
        $HTMLOUT .= main_div("<h2>{$lang['browse_not_found']}</h2>
                                <p>{$lang['browse_tryagain']}</p>", 'top20 has-text-centered');
    } else {
        $HTMLOUT .= main_div("<h2>{$lang['browse_nothing']}</h2>
                                <p>{$lang['browse_sorry']}(</p>", 'top20 has-text-centered');
    }
}
$ip = getip();
$no_log_ip = ($CURUSER['perms'] & bt_options::PERMS_NO_IP);
if ($no_log_ip) {
    $ip = '127.0.0.1';
}
if (!$no_log_ip) {
    $userid = (int)$CURUSER['id'];
    $added = TIME_NOW;
    $res = sql_query('SELECT * FROM ips WHERE ip = ' . ipToStorageFormat($ip) . ' AND userid = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) == 0) {
        sql_query('INSERT INTO ips (userid, ip, lastbrowse, type) VALUES (' . sqlesc($userid) . ', ' . ipToStorageFormat($ip) . ", $added, 'Browse')") or sqlerr(__FILE__, __LINE__);
        $cache->delete('ip_history_' . $userid);
    } else {
        sql_query("UPDATE ips SET lastbrowse = $added WHERE ip = " . ipToStorageFormat($ip) . ' AND userid = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
        $cache->delete('ip_history_' . $userid);
    }
}
echo stdhead($title, true, $stdhead) . wrapper($HTMLOUT) . stdfoot();
