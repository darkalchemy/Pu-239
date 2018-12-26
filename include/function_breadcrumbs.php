<?php

/**
 * @return string|void
 *
 * @throws \Envms\FluentPDO\Exception
 */
function breadcrumbs()
{
    global $site_config, $session, $CURUSER;

    $lang = load_language('breadcrumbs');
    $path = $query = '';
    $queries = [];

    $url = $_SERVER['REQUEST_URI'];
    $parsed_url = parse_url($url);
    extract($parsed_url);
    if (empty($path)) {
        return;
    }
    if (!empty($query)) {
        $queries = explode('&', $query);
    }

    $pre_page = get_prepage($lang, $path);
    if (!empty($pre_page)) {
        $links[] = $pre_page;
    }
    $links[] = get_basepage($lang, $path);
    if (!empty($queries)) {
        $action_page = get_actionpage($lang, $queries, $path);
        if (!empty($action_page)) {
            $links = array_merge($links, $action_page);
            $info_page = get_infopage($lang, $queries, $path);
            if (!empty($info_page)) {
                $links[] = $info_page;
            }
            $secondary_page = get_secondarypage($lang, $queries, $path);
            if (!empty($secondary_page)) {
                $links[] = $secondary_page;
            }
        }
    } else {
        $post_page = get_postpage($lang, $path);
        if (!empty($post_page)) {
            $links[] = $post_page;
        }
    }

    $crumbs = "
                <div class='bottom20'>
                    <nav class='breadcrumb round5' aria-label='breadcrumbs'>
                        <ul>
                            <li><a href='{$site_config['baseurl']}/index.php'>Home</a></li>";
    foreach ($links as $link) {
        if (!empty($link)) {
            $crumbs .= "
                            <li>$link</li>";
        }
    }
    $crumbs .= '
                        </ul>
                    </nav>
                </div>';

    return $crumbs;
}

/**
 * @param $lang
 * @param $url
 *
 * @return bool|string
 */
function get_prepage($lang, $url)
{
    global $CURUSER;

    switch ($url) {
        case '/catalog.php':
        case '/needseed.php':
        case '/offers.php':
        case '/requests.php':
        case '/upload.php':
        case '/details.php':
            return get_basepage($lang, '/browse.php');
        case '/casino.php':
        case '/blackjack.php':
            return get_basepage($lang, '/games.php');
        case '/arcade_top_scores.php':
        case '/flash.php':
            return get_basepage($lang, '/arcade.php');
        case '/lottery.php':
            if ($CURUSER['class'] >= UC_STAFF) {
                return get_basepage($lang, '/staffpanel.php');
            }
            break;
        case '/promo.php':
            if ($CURUSER['class'] >= UC_STAFF) {
                return get_basepage($lang, '/staffpanel.php');
            }
    }

    return false;
}

/**
 * @param $lang
 * @param $url
 *
 * @return bool|string
 */
function get_postpage($lang, $url)
{
    switch ($url) {
        case '/messages.php':
            return get_basepage($lang, 'view_mailbox');
        case '/topten.php':
            return get_basepage($lang, 'top_users');
    }

    return false;
}

/**
 * @param $lang
 * @param $queries
 * @param $path
 *
 * @return bool|string
 *
 * @throws \Envms\FluentPDO\Exception
 */
function get_secondarypage($lang, $queries, $path)
{
    global $site_config;

    if (empty($queries[2])) {
        return false;
    }
    $list = explode('=', $queries[2]);
    $ignore = [
        'topic_id',
        'game_id',
    ];

    if (in_array($list[0], $ignore)) {
        return false;
    }
    if ($list[0] === 'phpinfo') {
        $title = $lang['phpinfo'];
    } elseif ($list[0] === 'box') {
        $title = get_mailbox_name($list[1]);
    } elseif ($list[0] === 'file') {
        $title = urldecode($list[1]);
    } elseif ($list[0] === 'page') {
        if ($list[1] != 'last') {
            $page = $list[1] + 1;
            $title = $lang['page'] . " $page";
        } else {
            $title = $lang['last'];
        }
    }

    if (empty($title)) {
        $title = htmlspecialchars(ucwords(str_replace('_', ' ', $list[1])), ENT_QUOTES, 'UTF-8');
    }

    return "<a href='{$site_config['baseurl']}{$path}?{$queries[0]}&amp;{$queries[1]}&amp;{$queries[2]}'>{$title}</a>";
}

/**
 * @param $lang
 * @param $queries
 * @param $path
 *
 * @return bool|string
 *
 * @throws \Envms\FluentPDO\Exception
 */
function get_infopage($lang, $queries, $path)
{
    global $site_config;

    if (empty($queries[1])) {
        return false;
    }
    $list = explode('=', $queries[1]);
    $ignore1 = [
        'topic_id',
        'post_id',
        'id',
        'gamename',
        'do',
        'forum_id',
        'modtask',
        'deleted',
        'action',
        'userid',
        'receiver',
        'server',
        'op',
        'mode',
        'sent',
        'search_what',
    ];
    $ignore2 = [
        'polls_manager',
        'cleanup_manager',
    ];

    if (!empty($queries[2])) {
        $secondary = explode('=', $queries[2]);
    }

    if (!empty($list[0]) && $list[0] === 'box') {
        $title = get_mailbox_name($list[1]);
    } elseif (!empty($secondary) && $secondary[1] === 'phpinfo') {
        $title = $lang['phpinfo'];
    } elseif ($list[0] === 'mode' && $list[1] === 'news') {
        $title = $lang['add_news'];
    } elseif ($list[0] === 'mode' && $list[1] === 'edit') {
        $title = $lang['edit_news'];
    } elseif (in_array($list[0], $ignore1) || in_array($list[1], $ignore2)) {
        return false;
    } elseif ($list[0] === 'action' && $list[1] === 'app') {
        $title = $lang['app_list'];
    } elseif ($list[0] === 'action' && $list[1] === 'viewapp') {
        $title = $lang['view_app'];
    } elseif ($list[0] === 'name') {
        $title = $lang['index'];
    } elseif ($list[0] === 'page') {
        $page = $list[1] + 1;
        $title = $lang['page'] . " $page";
    } elseif ($list[0] === 'letter') {
        $title = $lang['letter'] . ' ' . strtoupper($list[1]);
    } elseif ($list[0] === 'type') {
        $title = $lang[$list[1]];
    } elseif (!empty($list[0])) {
        $title = $list[0] === 'action' && $list[1] === 'view' ? $lang[$list[1]] : $lang[$list[0]];
    }

    if (empty($title)) {
        $title = htmlspecialchars(ucwords(str_replace('_', ' ', $list[1])), ENT_QUOTES, 'UTF-8');
    }

    return "<a href='{$site_config['baseurl']}{$path}?{$queries[0]}&amp;{$queries[1]}'>{$title}</a>";
}

/**
 * @param $lang
 * @param $queries
 * @param $path
 *
 * @return bool|string
 */
function get_actionpage($lang, $queries, $path)
{
    global $site_config;

    $queries_1 = '';
    $list = explode('=', $queries[0]);
    $ignore = [
        'cats%5B%5D',
        'open',
        'id',
        'search',
        'edited',
        'act',
        'search_name',
        'search_descr',
        'search_genre',
        'search_owner',
        'search_imdb',
        'search_isbn',
        'images',
        'search_year_start',
        'search_year_end',
        'search_rating_start',
        'search_rating_end',
    ];

    if (in_array($list[0], $ignore) || $list[1] === 'bugs' || preg_match('/c\d+/', $list[0])) {
        return false;
    }

    if ($list[0] === 'today') {
        $title = $lang['new_today'];
    } elseif ($list[0] === 'letter') {
        $title = strtoupper($list[1]);
    } elseif ($list[0] === 'gameURI') {
        $title = $lang[$list[1]];
    } elseif ($list[0] === 'view' && $list[1] === 't') {
        $title = $lang['top_torrents'];
    } elseif ($list[0] === 'view' && $list[1] === 'c') {
        $title = $lang['top_countries'];
    } elseif ($list[0] === 'action' && $list[1] === 'new_topic') {
        $title = $lang[$list[1]];
    } elseif ($list[0] === 'page') {
        $title = $lang['page'] . ' ' . ($list[1] + 1);
    } elseif ($list[0] === 'tool' && $list[1] === 'news') {
        $queries_1 = '&amp;mode=news';
    } elseif ($list[0] === 'tool' && $list[1] === 'uploadapps') {
        $queries_1 = '&amp;action=app';
    } elseif ($list[0] === 'tool' && $list[1] === 'warn') {
        $queries_1 = '&amp;mode=warn';
    } elseif ($list[0] === 'action' && $list[1] === 'view_topic') {
        $ids = explode('=', $queries[1]);
        $queries_1 = '&amp;' . $ids[0] . '=' . $ids[1];
    } elseif ($path === '/forums.php' && $list[0] === 'action' && $list[1] === 'search') {
        $title = $lang['search_forum'];
    }
    if (!empty($list[1]) && empty($title)) {
        $title = $lang[$list[1]];
    }
    if (empty($title)) {
        $title = htmlspecialchars(ucwords(str_replace('_', ' ', $list[1])), ENT_QUOTES, 'UTF-8');
    }

    $pages = [
        'memcache',
        'mysql_stats',
        'mysql_overview',
    ];

    if ($list[0] === 'tool' && in_array($list[1], $pages)) {
        $page[] = "<a href='{$site_config['baseurl']}{$path}?{$list[0]}=system_view'>{$lang['system_view']}</a>";
    }

    $page[] = "<a href='{$site_config['baseurl']}{$path}?{$queries[0]}{$queries_1}'>{$title}</a>";

    return $page;
}

/**
 * @param $lang
 * @param $path
 *
 * @return bool|string
 */
function get_basepage($lang, $path)
{
    global $site_config;

    if ($path === '/' || $path === '/index.php' || $path === '/flash.php') {
        return false;
    }

    $title = $lang[trim($path, '/')];
    if (empty($title)) {
        dd('path = ' . $path);
    }

    return "<a href='{$site_config['baseurl']}{$path}'>{$title}</a>";
}

/**
 * @param $mailbox
 *
 * @return string
 *
 * @throws \Envms\FluentPDO\Exception
 */
function get_mailbox_name($mailbox)
{
    global $fluent, $CURUSER;

    switch ((int) $mailbox) {
        case -2:
            return 'Drafts';
        case -1:
            return 'Sent Box';
        case 0:
            return 'Deleted';
        case 1:
            return 'Inbox';
        default:
            $name = $fluent->from('pmboxes')
                ->select(null)
                ->select('name')
                ->where('boxnumber = ?', $mailbox)
                ->where('userid = ?', $CURUSER['id'])
                ->fetch('name');

            return htmlspecialchars(ucwords($name));
    }
}
