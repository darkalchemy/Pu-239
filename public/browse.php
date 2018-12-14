<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'torrenttable_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once INCL_DIR . 'searchcloud_functions.php';
require_once INCL_DIR . 'share_images.php';
require_once CLASS_DIR . 'class_user_options.php';
require_once CLASS_DIR . 'class_user_options_2.php';
check_user_status();
global $CURUSER, $site_config, $fluent, $cache, $fluent, $user_stuffs;

if (isset($_GET['clear_new']) && $_GET['clear_new'] == 1) {
    $set = [
        'last_browse' => TIME_NOW,
    ];
    $user_stuffs->update($set, $CURUSER['id']);
    header("Location: {$site_config['baseurl']}/browse.php");
    die();
}

$count = $fluent->from('torrents AS t')
    ->select(null)
    ->select('COUNT(*) AS count');

$select = $fluent->from('torrents AS t');
$HTMLOUT = $addparam = $new_button = $title = '';
$stdfoot = [
    'js' => [
        get_file_name('browse_js'),
        get_file_name('bookmarks_js'),
        get_file_name('categories_js'),
    ],
];

$lang = array_merge(load_language('global'), load_language('browse'), load_language('torrenttable_functions'), load_language('bookmark'));

$valid_search = [
    'search_name',
    'search_descr',
    'search_genre',
    'search_owner',
    'search_year',
    'search_rating',
    'search_imdb',
    'search_isbn',
];

if (isset($_GET['sort'], $_GET['type'])) {
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
    $column = isset($_GET['sort'], $_valid_sort[(int) $_GET['sort']]) ? $_valid_sort[(int) $_GET['sort']] : $_valid_sort[0];
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
    $select = $select->orderBy("t.{$column} $ascdesc");
    $pagerlink = 'sort=' . intval($_GET['sort']) . "&amp;type={$linkascdesc}&amp;";
} else {
    $select = $select->orderBy('t.staff_picks DESC')->orderBy('t.sticky')->orderBy('t.id');
    $pagerlink = '';
}

$today = 0;
if (!empty($_GET['today']) && $_GET['today']) {
    $count = $count->where('t.added >= :added', [':added' => strtotime('today midnight')]);
    $select = $select->where('t.added >= :added', [':added' => strtotime('today midnight')]);
    $addparam .= 'today=1&amp;';
    $today = 1;
}

$selected = !empty($_GET['incldead']) ? (int) $_GET['incldead'] : '';
if ($selected === 1) {
    $addparam .= 'incldead=1&amp;';
    if (!isset($CURUSER) || $CURUSER['class'] < UC_ADMINISTRATOR) {
        $count = $count->where('t.banned != "yes"');
        $select = $select->where('t.banned != "yes"');
    }
} else {
    if ($selected === 2) {
        $addparam .= 'incldead=2&amp;';
        $count = $count->where('t.visible = "no"');
        $select = $select->where('t.visible = "no"');
    } else {
        $count = $count->where('t.visible = "yes"');
        $select = $select->where('t.visible = "yes"');
    }
}

if (isset($_GET['only_free']) && $_GET['only_free'] == 1) {
    $count = $count->where('t.free >= 1');
    $select = $select->where('t.freee >= 1');
    $addparam .= 'only_free=1&amp;';
}
if (isset($_GET['vip'])) {
    if ($_GET['vip'] == 2) {
        $count = $count->where('t.vip = 1');
        $select = $select->where('t.vip = 1');
    } elseif ($_GET['vip'] == 1) {
        $count = $count->where('t.vip = 0');
        $select = $select->where('t.vip = 0');
    }
    $addparam .= "vip={$_GET['vip']}&amp;";
}

$cats = [];
if (isset($_GET['cats'])) {
    if (is_array($_GET['cats'])) {
        $cats = $_GET['cats'];
    } else {
        $cats = explode(',', $_GET['cats']);
    }
}

if (!empty($cats)) {
    $addparam .= 'cats=' . implode(',', $cats) . '&amp;';
    $count = $count->where('t.category', $cats);
    $select = $select->where('t.category', $cats);
}

foreach ($valid_search as $search) {
    if (!empty($_GET[$search])) {
        $cleaned = searchfield($_GET[$search]);
        $title .= " $cleaned";
        if ($search === 'search_name' || $search === 'search_descr') {
            searchcloud_insert($cleaned, str_replace('search_', '', $search));
        }
        $addparam .= "{$search}=" . urlencode($cleaned) . '&amp;';
        if ($search === 'search_name') {
            $count = $count->where('MATCH (t.name) AGAINST (? IN NATURAL LANGUAGE MODE)', $cleaned);
            $select = $select->where('MATCH (t.name) AGAINST (? IN NATURAL LANGUAGE MODE)', $cleaned);
        } elseif ($search === 'search_descr') {
            $count = $count->where('MATCH (search_text, descr) AGAINST (? IN NATURAL LANGUAGE MODE)', $cleaned);
            $select = $select->where('MATCH (search_text, descr) AGAINST (? IN NATURAL LANGUAGE MODE)', $cleaned);
        } elseif ($search === 'search_genre') {
            $count = $count->where('MATCH (newgenre) AGAINST (? IN NATURAL LANGUAGE MODE)', $cleaned);
            $select = $select->where('MATCH (newgenre) AGAINST (? IN NATURAL LANGUAGE MODE)', $cleaned);
        } elseif ($search === 'search_owner') {
            $count = $count->where('u.username = ?', $cleaned)
                ->leftJoin('users AS u ON t.owner = u.id');
            $select = $select->where('u.username = ?', $cleaned)
                ->leftJoin('users AS u ON t.owner = u.id');
        } elseif ($search === 'search_year') {
            $count = $count->where('t.year >= ?', (int) $cleaned);
            $select = $select->where('t.year >= ?', (int) $cleaned);
        } elseif ($search === 'search_rating') {
            $count = $count->where('t.rating >= ?', (float) $cleaned);
            $select = $select->where('t.rating >= ?', (float) $cleaned);
        } elseif ($search === 'search_imdb') {
            $imdb = preg_match('/(tt\d{7})/', $cleaned, $match);
            if (!empty($match[1])) {
                $count = $count->where('t.imdb_id = ?', $match[1]);
                $select = $select->where('t.imdb_id = ?', $cleaned);
            }
        } elseif ($search === 'search_isbn') {
            $isbn = preg_match('/\d{7,10}/', $cleaned, $match);
            if (!empty($match[1])) {
                $count = $count->where('t.isbn = ?', $match[1]);
                $select = $select->where('t.isbn = ?', $cleaned);
            }
        }
    }
}

if (!empty($title)) {
    $title = $lang['browse_search'] . $title;
}
$count = $count->fetch('count');
$torrentsperpage = $CURUSER['torrentsperpage'];
if (!$torrentsperpage) {
    $torrentsperpage = 15;
}
if ($count > 0) {
    if ($addparam != '') {
        if ($pagerlink != '') {
            if ($addparam[strlen($addparam) - 1] != ';') {
                $addparam = $addparam . '&amp;' . $pagerlink;
            } else {
                $addparam = $addparam . $pagerlink;
            }
        }
    } else {
        $addparam = $pagerlink;
    }
    $pager = pager($torrentsperpage, $count, "{$site_config['baseurl']}/browse.php?" . $addparam);
    $select = $select->limit("{$pager['pdo']}")->fetchAll();
}

if ($CURUSER['opt1'] & user_options::VIEWSCLOUD) {
    $HTMLOUT .= main_div("<div class='cloud round10 padding20'>" . cloud() . '</div>', 'bottom20');
}

$HTMLOUT .= "
                                <form id='catsids' method='get' action='{$site_config['baseurl']}/browse.php'>";
if ($today) {
    $HTMLOUT .= "
                                    <input type='hidden' name='today' value='$today'>";
}

require_once PARTIALS_DIR . 'categories.php';

if ($CURUSER['opt1'] & user_options::CLEAR_NEW_TAG_MANUALLY) {
    $new_button = "
        <a href='{$site_config['baseurl']}/browse.php?clear_new=1'><input type='submit' value='clear new tag' class='button is-small'></a>
        <br>";
} else {
    $set = [
        'last_browse' => TIME_NOW,
    ];
    $user_stuffs->update($set, $CURUSER['id']);
}

$vip = ((isset($_GET['vip'])) ? intval($_GET['vip']) : '');
$vip_box = "
                    <select name='vip' class='w-100'>
                        <option value='0'>{$lang['browse_include_vip']}</option>
                        <option value='1'" . ($vip == 1 ? ' selected' : '') . ">{$lang['browse_no_vip']}</option>
                        <option value='2'" . ($vip == 2 ? ' selected' : '') . ">{$lang['browse_only_vip']}</option>
                    </select>";

$deadcheck = "
                    <select name='incldead' class='w-100'>
                        <option value='0'>{$lang['browse_active']}</option>
                        <option value='1'" . ($selected == 1 ? ' selected' : '') . ">{$lang['browse_inc_dead']}</option>
                        <option value='2'" . ($selected == 2 ? ' selected' : '') . ">{$lang['browse_dead']}</option>
                    </select>";

$only_free = ((isset($_GET['only_free'])) ? intval($_GET['only_free']) : '');
$only_free_box = "
                    <select name='only_free' class='w-100'>
                        <option value='0'>{$lang['browse_all_free']}</option>
                        <option value='1'" . ($only_free == 1 ? ' selected' : '') . ">{$lang['browse_only_free']}</option>
                    </select>";

$HTMLOUT .= main_div("
                <div class='padding20'>
                    <div class='padding10 w-100'>
                        <div class='columns'>
                            <div class='column'>
                                <div class='has-text-centered bottom10'>{$lang['browse_name']}</div>
                                <input id='search' name='search_name' type='text' data-csrf='" . $session->get('csrf_token') . "' placeholder='{$lang['search_name']}' class='search w-100' value='" . (!empty($_GET['search_name']) ? $_GET['search_name'] : '') . "' onkeyup='autosearch()'>
                            </div>
                            <div class='column'>
                                <div class='has-text-centered bottom10'>{$lang['browse_description']}</div>
                                <input name='search_descr' type='text' placeholder='{$lang['search_desc']}' class='search w-100' value='" . (!empty($_GET['search_descr']) ? $_GET['search_descr'] : '') . "'>
                            </div>
                            <div class='column'>
                                <div class='has-text-centered bottom10'>{$lang['browse_uploader']}</div>
                                <input name='search_owner' type='text' placeholder='{$lang['search_uploader']}' class='search w-100' value='" . (!empty($_GET['search_owner']) ? $_GET['search_owner'] : '') . "'>
                            </div>
                        </div>
                        <div class='columns'>
                            <div class='column'>
                                <div class='has-text-centered bottom10'>{$lang['browse_genre']}</div>
                                <input name='search_genre' type='text' placeholder='{$lang['search_genre']}' class='search w-100' value='" . (!empty($_GET['search_genre']) ? $_GET['search_genre'] : '') . "'>
                            </div>
                            <div class='column'>
                                <div class='has-text-centered bottom10'>{$lang['browse_year']}</div>
                                <input name='search_year' type='number' min='1900' max='" . (date('Y') + 1) . "' placeholder='{$lang['search_year']}' class='search w-100' value='" . (!empty($_GET['search_year']) ? $_GET['search_year'] : '') . "'>
                            </div>
                            <div class='column'>
                                <div class='has-text-centered bottom10'>{$lang['browse_rating']}</div>
                                <input name='search_rating' type='number' min='0' max='10' step='0.1' placeholder='{$lang['search_rating']}' class='search w-100' value='" . (!empty($_GET['search_rating']) ? $_GET['search_rating'] : '') . "'>
                            </div>
                        </div>
                        <div class='columns'>
                            <div class='column'>
                                <div class='has-text-centered bottom10'>{$lang['browse_imdb']}</div>
                                <input name='search_imdb' type='text' placeholder='{$lang['search_imdb']}' class='search w-100' value='" . (!empty($_GET['search_imdb']) ? $_GET['search_imdb'] : '') . "'>
                            </div>
                            <div class='column'>
                                <div class='has-text-centered bottom10'>{$lang['browse_isbn']}</div>
                                <input name='search_isbn' type='text' placeholder='{$lang['search_isbn']}' class='search w-100' value='" . (!empty($_GET['search_isbn']) ? $_GET['search_isbn'] : '') . "'>
                            </div>
                            <div class='column'>
                            </div>
                        </div>
                        <div class='columns top20'>
                            <div class='column'>
                                $deadcheck
                            </div>
                            <div class='column'>
                                $vip_box
                            </div>
                            <div class='column'>
                                $only_free_box
                            </div>
                        </div>
                        <div id='autocomplete' class='w-100 bottom10'>
                            <div class='padding20 bg-00 round10 bordered autofill'>
                                <div id='autocomplete_list' class='margin10'>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class='margin10 has-text-centered'>
                        <input type='submit' value='{$lang['search_search_btn']}' class='button is-small'>
                    </div>
                </div>");
$HTMLOUT .= '
            </form>';
$HTMLOUT .= "{$new_button}";

if ($count) {
    $HTMLOUT .= ($count > $torrentsperpage ? "
        <div class='top20'>{$pager['pagertop']}</div>" : '') . "
            <div class='table-wrapper top20'>" . torrenttable($select, 'index') . '</div>' . ($count > $torrentsperpage ? "
        <div class='top20'>{$pager['pagerbottom']}</div>" : '');
} else {
    if (isset($cleansearchstr)) {
        $text = "
                <div class='padding20'>
                    <h2>{$lang['browse_not_found']}</h2>
                    <p>{$lang['browse_tryagain']}</p>
                </div>";
    } else {
        $text = "
                <div class='padding20'>
                    <h2>{$lang['browse_nothing']}</h2>
                    <p>{$lang['browse_sorry']}</p>
                </div>";
    }
    $HTMLOUT .= main_div($text, 'top20 has-text-centered');
}

echo stdhead($title) . wrapper($HTMLOUT) . stdfoot($stdfoot);
