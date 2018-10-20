<?php

function breadcrumbs()
{
    global $site_config, $session, $CURUSER;

    $lang = load_language('breadcrumbs');
    $path = $query = '';
    $queries = [];

    $url = $_SERVER['REQUEST_URI'];
    $parsed_url = parse_url($_SERVER['REQUEST_URI']);
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
            $links[] = $action_page;
            $info_page = get_infopage($lang, $queries, $path);
            if (!empty($info_page)) {
                $links[] = $info_page;
            }
        }
    } else {
        $post_page = get_postpage($lang, $path);
        if (!empty($post_page)) {
            $links[] = $post_page;
        }
    }

    $crumbs = "
                <div class='container bottom20'>
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

function get_prepage($lang, $url)
{
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
    }

    return false;
}

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
    ];

    $ignore2 = [
        'polls_manager',
        'cleanup_manager',
    ];

    if (!empty($list[0]) && $list[0] === 'box') {
        $title = get_mailbox_name($list[1]);
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
    } elseif ($list[0] === 'page') {
        $page = $list[1] + 1;
        $title = $lang['page'] . " $page";
    } elseif (!empty($list[0])) {
        $title = $list[0] === 'action' && $list[1] === 'view' ? $lang[$list[1]] : $lang[$list[0]];
    }

    if (empty($title)) {
        $title = htmlspecialchars(ucwords(str_replace('_', ' ', $list[1])), ENT_QUOTES, 'UTF-8');
    }

    return "<a href='{$site_config['baseurl']}{$path}?{$queries[0]}&amp;{$queries[1]}'>{$title}</a>";
}

function get_actionpage($lang, $queries, $path)
{
    global $site_config;

    $queries_1 = '';
    $list = explode('=', $queries[0]);

    if ($list[0] === 'id' || $list[0] === 'search') {
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
    } elseif ($list[0] === 'tool' && $list[1] === 'news') {
        $queries_1 = '&amp;mode=news';
    } elseif ($list[0] === 'tool' && $list[1] === 'uploadapps') {
        $queries_1 = '&amp;action=app';
    } elseif ($list[0] === 'tool' && $list[1] === 'warn') {
        $queries_1 = '&amp;mode=warn';
    }

    if (!empty($list[1]) && empty($title)) {
        $title = $lang[$list[1]];
    }
    if (empty($title)) {
        $title = htmlspecialchars(ucwords(str_replace('_', ' ', $list[1])), ENT_QUOTES, 'UTF-8');
    }

    return "<a href='{$site_config['baseurl']}{$path}?{$queries[0]}{$queries_1}'>{$title}</a>";
}

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
    if (empty($title)) {
        $title = htmlspecialchars(ucfirst(basename(trim($path, '/'), '.php')), ENT_QUOTES, 'UTF-8');
    }

    return "<a href='{$site_config['baseurl']}{$path}'>{$title}</a>";
}

function get_mailbox_name($mailbox)
{
    global $fluent;

    switch ($mailbox) {
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
