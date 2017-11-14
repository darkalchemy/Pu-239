<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once CLASS_DIR . 'class_user_options_2.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('getrss'));
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    /**
     * @param $x
     *
     * @return int
     */
    function mkint($x)
    {
        return (int)$x;
    }

    $cats = isset($_POST['cats']) ? array_map('mkint', $_POST['cats']) : [];
    if (count($cats) == 0) {
        setSessionVar('is-warning', $lang['getrss_nocat']);
    } else {
        $feed = isset($_POST['feed']) && $_POST['feed'] == 'dl' ? 'dl' : 'web';
        $bm = isset($_POST['bm']) && is_int($_POST['bm']) ? $_POST['bm'] : 0;
        $counts = [15, 30, 50, 100];
        $count = isset($_POST['count']) && is_int($_POST['count']) && in_array($count, $_POST['count']) ? $_POST['count'] : 15;
        $rsslink = "{$site_config['baseurl']}/rss.php?cats=" . join(',', $cats) . "&amp;type={$feed}&amp;torrent_pass={$CURUSER['torrent_pass']}&amp;count=$count&amp;bm=$bm";
        $HTMLOUT = "
        <div class='container is-fluid portlet has-text-centered'>
            <h2>{$lang['getrss_result']}</h2>
		    <input type='text' class='w-100' readonly='readonly' value='{$rsslink}' onclick='select()' />
	    </div>";
        echo stdhead($lang['getrss_head2']) . $HTMLOUT . stdfoot();
        die();
    }
}

$HTMLOUT = "
        <form action='{$_SERVER['PHP_SELF']}' method='post'>";
$text = "
            <div class='padding20 round10 top20 bottom20 bg-02'>
                <div id='checkbox_container' class='level-center'>";

$catids = genrelist();
if ($CURUSER['opt2'] & user_options_2::BROWSE_ICONS) {
    foreach ($catids as $cat) {
        $text .= "
                    <span class='margin10 mw-50 is-flex bg-02 round10 tooltipper' title='" . htmlsafechars($cat['name']) . "'>
                        <span class='bordered level-center'>
                            <input type='checkbox' name='cats[]' id='cat_" . (int)$cat['id'] . "' value='" . (int)$cat['id'] . "' />
                            <span class='cat-image left10'>
                                <img class='radius-sm' src='{$site_config['pic_base_url']}caticons/{$CURUSER['categorie_icon']}/" . htmlsafechars($cat['image']) . "'alt='" . htmlsafechars($cat['name']) . "' />
                            </span>
                        </span>
                    </span>";
    }
} else {
    foreach ($catids as $cat) {
        $text .= "
                    <span class='margin10 bordered tooltipper' title='" . htmlsafechars($cat['name']) . "'>
                        <label for='c" . (int)$cat['id'] . "'>
                            <input name='c" . (int)$cat['id'] . "' class='styled1' type='checkbox' " . (in_array($cat['id'], $wherecatina) ? " checked" : '') . "value='1' />
                        </label>
                    </span>";
    }
}
$text .= "
                </div>
                <div class='level-center top20'>
                    <label for='checkAll'>
                        <input type='checkbox' id='checkAll' /><span> Select All Categories</span>
                    </label>
                </div>
            </div>";
$HTMLOUT .= main_div($text, 'bottom20');
$HTMLOUT .= main_div("
            <div class='level-center'>
                <li class='has-text-centered w-25 tooltipper' title='Returns only Bookmarked Torrents'>
                    <label for='bm' >Bookmarked Torrents<br>
                        <select name='bm' class='top10 w-100'>
                            <option value='0'>No</option>
                            <option value='1'>Yes - Only bookmarked torrents</option>
                        </select>
                    </label>
                </li>
                <li class='has-text-centered w-25 tooltipper' title='Generate Links to download torrents or to view torrent details.'>
                    <label for='feed'>RSS Link Type<br>
                        <select name='feed' class='top10 w-100'>
                            <option value='dl'>{$lang['getrss_dl']}</option>
                            <option value='web'>{$lang['getrss_web']}</option>
                        </select>
                    </label>
                </li>
                <li class='has-text-centered w-25 tooltipper' title='How many results should be returned in the RSS feed?'>
                    <label for='count'>Results in Feed<br>
                        <select name='count' class='top10 w-100'>
                            <option value='15'>15</option>
                            <option value='30'>30</option>
                            <option value='50'>50</option>
                            <option value='100'>100</option>
                        </select>
                    </label>
                </li>
            </div>
            <div class='level-center top20 bottom20'>
                <input type='submit' class='button' value='{$lang['getrss_btn']}' />
            </div>");
$HTMLOUT .= "
        </form>";
echo stdhead($lang['getrss_head2']) . wrapper($HTMLOUT) . stdfoot();
