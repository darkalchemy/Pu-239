<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Database;
use Pu239\Topic;

/**
 * @throws Exception
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return string|void
 */
function breadcrumbs()
{
    global $site_config;

    $lang = load_language('breadcrumbs');
    $array = parse_url($_SERVER['REQUEST_URI']);
    $path = !empty($array['path']) ? $array['path'] : '';
    $query = isset($array['query']) ? $array['query'] : '';
    if (empty($path)) {
        return;
    }
    $queries = [];
    if (!empty($query)) {
        $queries = explode('&', $query);
    }
    $referer = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $pre_page = get_prepage($lang, $path, $referer);
    if (!empty($pre_page)) {
        if (is_array($pre_page)) {
            foreach ($pre_page as $page) {
                $links[] = $page;
            }
        } else {
            $links[] = $pre_page;
        }
    }
    $links[] = get_basepage($lang, $path);
    if ($path === '/forums.php') {
        $post_page = get_postpage($lang, $path);
        if (!empty($post_page)) {
            $links[] = $post_page;
        }
    }
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
                            <li><a href='{$site_config['paths']['baseurl']}/index.php'>Home</a></li>";
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
 * @param $referer
 *
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return array|bool|string
 */
function get_prepage($lang, $url, $referer)
{
    global $CURUSER;

    switch ($url) {
        case '/viewnfo.php':
        case '/filelist.php':
        case '/peerlist.php':
        case '/snatches.php':
            $links[] = get_basepage($lang, '/browse.php');
            $array = parse_url($referer);
            $path = $array['path'];
            $query = isset($array['query']) ? $array['query'] : '';
            if (!empty($path) && $path != '/browse.php') {
                $links[] = get_basepage($lang, $path, $query);
            }

            return $links;
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
            if (has_access($CURUSER['class'], UC_STAFF, 'coder')) {
                return get_basepage($lang, '/staffpanel.php');
            }
    }

    return false;
}

/**
 * @param $lang
 * @param $url
 *
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 *
 * @return bool|mixed|string
 */
function get_postpage($lang, $url)
{
    global $container;

    switch ($url) {
        case '/messages.php':
            return get_basepage($lang, 'view_mailbox');
        case '/topten.php':
            return get_basepage($lang, 'top_users');
        case '/forums.php':
            $queries = explode('&', $_SERVER['QUERY_STRING']);
            $topic_class = $container->get(Topic::class);
            foreach ($queries as $param) {
                if (strstr($param, 'topic_id=')) {
                    $ids = explode('=', $param);
                    $forum_id = $topic_class->get_forum_id_from_topic_id((int) $ids[1]);
                    if (!empty($forum_id)) {
                        $link = get_basepage($lang, '/forums.php', 'action=view_forum&forum_id=' . $forum_id);

                        return str_replace($lang['forums.php'], $lang['view_forum'], $link);
                    }
                }
            }
    }

    return false;
}

/**
 * @param $lang
 * @param $queries
 * @param $path
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return bool|string
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
        'userids',
        'userid',
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
        $title = htmlsafechars(ucwords(str_replace('_', ' ', $list[1])));
    }

    return "<a href='{$site_config['paths']['baseurl']}{$path}?{$queries[0]}&amp;{$queries[1]}&amp;{$queries[2]}'>{$title}</a>";
}

/**
 * @param $lang
 * @param $queries
 * @param $path
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return bool|string
 */
function get_infopage($lang, $queries, $path)
{
    global $site_config;

    if (empty($queries[1])) {
        return false;
    }
    $list = explode('=', $queries[1]);
    $ignore1 = [
        'mailsent',
        'topic_id',
        'post_id',
        'id',
        'userid',
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
        'link',
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
    } elseif ($list[0] === 'page' && is_numeric($list[1])) {
        $page = $list[1] + 1;
        $title = $lang['page'] . " $page";
    } elseif ($list[0] === 'letter') {
        $title = $lang['letter'] . ' ' . strtoupper($list[1]);
    } elseif ($list[0] === 'type') {
        $title = $lang[$list[1]];
    } elseif (!empty($list[0])) {
        $title = $list[0] === 'action' && $list[1] === 'view' ? (!empty($lang[$list[1]]) ? $lang[$list[1]] : '') : (!empty($lang[$list[0]]) ? $lang[$list[0]] : '');
    }

    if (empty($title)) {
        $title = htmlsafechars(ucwords(str_replace('_', ' ', $list[1])));
    }

    return "<a href='{$site_config['paths']['baseurl']}{$path}?{$queries[0]}&amp;{$queries[1]}'>{$title}</a>";
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
        'selector',
        'cats%5B%5D',
        'open',
        'id',
        'userid',
        'search',
        'edited',
        'act',
        'sna',
        'sns',
        'sd',
        'sg',
        'so',
        'si',
        'sn',
        'images',
        'sys',
        'sye',
        'srs',
        'sre',
        'sp',
        'spf',
        'sr',
        'st',
        'sort',
        's',
        'w',
    ];
    if (in_array($list[0], $ignore) || preg_match('/c\d+/', $list[0]) || (isset($list[1]) && ($list[1] === 'view_page' || $list[1] === 'bugs' || preg_match('/\d+/', $list[1])))) {
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
    } elseif ($list[1] === 'sort') {
        $ids = explode('=', $queries[1]);
        //$queries_1 = '&amp;' . $ids[0] . '=' . $ids[1];
        $queries[0] = '';
    }
    if (!empty($list[1]) && empty($title)) {
        $title = !empty($lang[$list[1]]) ? $lang[$list[1]] : '';
    }
    if (empty($title)) {
        $title = htmlsafechars(ucwords(str_replace('_', ' ', $list[1])));
    }

    $pages = [
        'memcache',
        'mysql_stats',
        'mysql_overview',
    ];

    if ($list[0] === 'tool' && in_array($list[1], $pages)) {
        $page[] = "<a href='{$site_config['paths']['baseurl']}{$path}?{$list[0]}=system_view'>{$lang['system_view']}</a>";
    }

    $page[] = "<a href='{$site_config['paths']['baseurl']}{$path}?{$queries[0]}{$queries_1}'>{$title}</a>";

    return $page;
}

/**
 * @param array  $lang
 * @param string $path
 * @param string $query
 *
 * @return bool|string
 */
function get_basepage(array $lang, string $path, string $query = '')
{
    global $site_config;

    $ignore = [
        '/',
        '/index.php',
        '/flash.php',
    ];
    if (in_array($path, $ignore)) {
        return false;
    }
    $title = $lang[trim($path, '/')];
    if (empty($title)) {
        die('path = ' . $path);
    }
    $query = !empty($query) ? '?' . $query : '';

    return "<a href='{$site_config['paths']['baseurl']}{$path}{$query}'>{$title}</a>";
}

/**
 * @param $mailbox
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return mixed|string
 */
function get_mailbox_name($mailbox)
{
    global $container, $CURUSER;

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
            $fluent = $container->get(Database::class);
            $name = $fluent->from('pmboxes')
                           ->select(null)
                           ->select('name')
                           ->where('boxnumber = ?', $mailbox)
                           ->where('userid = ?', $CURUSER['id'])
                           ->fetch('name');

            return htmlsafechars(ucwords($name));
    }
}
