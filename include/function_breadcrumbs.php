<?php

/**
 * @param string $separator
 * @param string $home
 *
 * @return string
 */
function breadcrumbs($separator = '', $home = 'Home')
{
    global $site_config, $session;

    $path = array_filter(explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
    $query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
    $base = $site_config['baseurl'] . '/';
    $breadcrumbs = ["<li><a href='$base'>$home</a></li>"];
    $keys = array_keys($path);
    $last = end($keys);
    $action = [];

    if (!empty($query)) {
        $action = explode('=', $query);
    }
    if (!empty($action[0]) && $action[0] === 'action') {
        $last = '';
    }

    foreach ($path as $x => $crumb) {
        $title = ucwords(str_replace([
            '.php',
            '_',
        ], [
            '',
            ' ',
        ], $crumb));
        if ($x != $last) {
            $breadcrumbs[] = "<li><a href='$base$crumb'>$title</a></li>";
        } else {
            $breadcrumbs[] = $title;
        }
    }

    if (!empty($action[0]) && $action[0] === 'action') {
        $type = explode('&', str_replace([
            '-',
            '_',
        ], ' ', $action[1]));
        $breadcrumbs[] = ucwords($type[0]);
    }

    //    if (!empty($query)) {
    //        $query_str = '';
    //        if ($session->get('query_str')) {
    //            $query_str = $session->get('query_str');
    //        }
    //    }

    /*
    if (!empty($query)) {
    $query_str = '';
    if ($session->get('query_str')) {
        $query_str = $session->get('query_str');
    }

    $action = explode('=', $query);
    if ($action[0] === 'action') {
        if ($action[1] === 'view_topic&topic_id') {
            $breadcrumbs[] = "<a href='{$base}forums.php?{$query_str}'>Forum</a>";
        } elseif ($action[1] === 'add') {
            $breadcrumbs[] = "<a href='{$base}staffpanel.php?{$query_str}'>Forum</a>";
        }
        $type = explode('&', str_replace('view', '', $action[1]));
        if (!empty($action[2])) {
            $breadcrumbs[] = ucwords(str_replace(['_', '-'], ' ', $type[0])) . ' #' . $action[2];
        } else {
            array_pop($breadcrumbs);
            $breadcrumbs[] = ucwords(str_replace(['_', '-'], ' ', $type[0]));
        }
    } elseif ($action[0] === 'tool') {
        $type = explode('&', str_replace('&mode', '', $action[1]));
        $breadcrumbs[] = ucwords(str_replace(['_', '-'], ' ', $type[0]));
    } elseif ($action[0] === 'id') {
        if (in_array('details.php', $path)) {
            array_pop($breadcrumbs);
            $breadcrumbs[] = "<a href='{$base}browse.php?{$query_str}'>Browse</a>";
            $breadcrumbs[] = "Torrent Details";
        } elseif (in_array('userdetails.php', $path)) {
            array_pop($breadcrumbs);
            $breadcrumbs[] = "User Details";
        }
    } elseif ($action[0] === 'search' || strpos($query, 'searchin')) {
        array_pop($breadcrumbs);
        $breadcrumbs[] = "Browse";
    } elseif ($action[0] === 'do') {
        array_pop($breadcrumbs);
        $breadcrumbs[] = "Invite";
    }
    }
*/
    $current = "<li class='is-active'><a href='#' aria-current='page'><span class='has-text-white'>" . end($breadcrumbs) . '</span></a></li>';
    array_pop($breadcrumbs);
    $breadcrumbs[] = $current;

    $session->set('query_str', parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY));

    return implode($separator, $breadcrumbs);
}
