<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Session;
use Pu239\User;

require_once INCL_DIR . 'function_autopost.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_staff.php';

/**
 * @param int  $class
 * @param bool $staff
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws Exception
 */
function class_check(int $class = UC_STAFF, bool $staff = true)
{
    global $container, $site_config;

    $auth = $container->get(Auth::class);
    if ($auth->isRemembered()) {
        $auth->logOutEverywhere();
        $session = $container->get(Session::class);
        $session->set('is-danger', 'Please confirm your identity.');
        header("Location: {$site_config['paths']['baseurl']}/{$_SERVER['REQUEST_URI']}");
        die();
    }
    $user_class = $container->get(User::class);
    $userid = $auth->getUserId();
    if (empty($userid)) {
        header("Location: {$site_config['paths']['baseurl']}/404.html");
        die();
    }

    $user = $user_class->getUserFromId($userid);
    if (empty($user)) {
        header("Location: {$site_config['paths']['baseurl']}/404.html");
        die();
    }
    if ($user['class'] >= $class) {
        if ($staff) {
            if (($user['class'] > UC_MAX) || (!in_array($user['id'], $site_config['is_staff']))) {
                $ip = getip();
                $body = "User: [url={$site_config['paths']['baseurl']}/userdetails.php?id={$user['id']}][color=user]{$user['username']}[/color][/url] - {$ip}[br]Class {$user['class']}[br]Current page: {$_SERVER['PHP_SELF']}[br]Previous page: " . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'no referer') . '[br]Action: ' . $_SERVER['REQUEST_URI'] . '[br] Member has been disabled and demoted by class check system.';
                $subject = 'Warning Class Check System!';
                if (user_exists($site_config['chatbot']['id'])) {
                    $post_info = auto_post($subject, $body);
                    $update = [
                        'class' => UC_MIN,
                        'enabled' => 'no',
                    ];
                    $user_class->update($update, $userid);
                    write_log('Class Check System Initialized [url=' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $post_info['topicid'] . '&amp;page=last#' . $post_info['postid'] . ']VIEW[/url]');
                    $HTMLOUT = doc_head() . "
    <meta property='og:title' content='Error!'>
    <title>Error!</title>
</head>
<body>
    <div style='font-size:18px;color:black;background-color:red;text-align:center;'>Incorrect access<br>Silly Rabbit - Trix are for kids.. You dont have the correct credentials to be here !</div>
</body>
</html>";
                    echo $HTMLOUT;
                    die();
                }
            }
        }
    } else {
        if (!$staff) {
            write_info("{$user['username']} attempted to access a staff page");
            stderr('ERROR', 'No Permission. Page is for ' . get_user_class_name((int) $class) . 's and above. Read FAQ.');
        } else {
            header("Location: {$site_config['paths']['baseurl']}/404.html");
            die();
        }
    }
}

/**
 * @param $script
 *
 * @throws NotFoundException
 * @throws DependencyException
 *
 * @return int
 */
function get_access($script)
{
    $ending = parse_url($script, PHP_URL_QUERY);
    if (empty($ending)) {
        return UC_MAX;
    }
    $count = substr_count($ending, '&');
    $i = 0;
    while ($i <= $count) {
        if (strpos($ending, '&')) {
            $ending = substr($ending, 0, strrpos($ending, '&'));
        }
        ++$i;
    }
    global $container;

    $cache = $container->get(Cache::class);
    $class = $cache->get('av_class_' . $ending);
    if ($class === false || is_null($class)) {
        $classid = sql_query("SELECT av_class FROM staffpanel WHERE file_name LIKE '%$ending'") or sqlerr(__FILE__, __LINE__);
        $classid = mysqli_fetch_assoc($classid);
        $class = (int) $classid['av_class'];
        $cache->set('av_class_' . $ending, $class, 0);
    }

    return $class;
}
