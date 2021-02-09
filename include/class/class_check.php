<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Roles;
use Pu239\Session;
use Pu239\User;

require_once INCL_DIR . 'function_autopost.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_staff.php';

/**
 * @param int $class
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws Exception
 */
function class_check(int $class = UC_STAFF)
{
    global $container, $site_config;

    $user = check_user_status();
    if (empty($user)) {
        header("Location: {$site_config['paths']['baseurl']}/404.html");
        die();
    }
    $auth = $container->get(Auth::class);
    if ($auth->isRemembered()) {
        $session = $container->get(Session::class);
        $session->set('is-danger', _('Please confirm your password.'));
        header("Location: {$site_config['paths']['baseurl']}/verify.php?page=" . urlencode($_SERVER['REQUEST_URI']));
        die();
    }
    $userid = $user['id'];
    if (!has_access($user['class'], $class, 'coder')) {
        write_info("{$user['username']} attempted to access a staff page");
        stderr(_('Error'), 'No Permission. Page is for ' . get_user_class_name((int) $class) . ' and above. Read the FAQ.');
    }
    if ($user['class'] > UC_MAX || (!in_array($user['id'], $site_config['is_staff']) && (!$user['roles_mask'] & Roles::CODER))) {
        $ip = getip($user['id']);
        $body = "User: [url={$site_config['paths']['baseurl']}/userdetails.php?id={$user['id']}][color=user]{$user['username']}[/color][/url] - {$ip}[br]Class {$user['class']}[br]Current page: {$_SERVER['PHP_SELF']}[br]Previous page: " . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'no referer') . '[br]Action: ' . $_SERVER['REQUEST_URI'] . '[br] Member has been disabled and demoted by class check system.';
        $subject = 'Warning Class Check System!';
        if (user_exists($site_config['chatbot']['id'])) {
            $post_info = auto_post($subject, $body);
            $update = [
                'class' => UC_MIN,
                'status' => 2,
            ];
            $users_class = $container->get(User::class);
            $users_class->update($update, $userid);
            write_log('Class Check System Initialized [url=' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $post_info['topicid'] . '&amp;page=last#' . $post_info['postid'] . ']VIEW[/url]');
            stderr(_('Error'), _('You dont have the correct credentials to be here!'));
        }
    }
}

/**
 * @param $script
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
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
        $fluent = $container->get(Database::class);
        $class = $fluent->from('staffpanel')
                        ->select('av_class')
                        ->where('file_name LIKE ?', "%$ending")
                        ->fetch('av_class');

        if (empty($class)) {
            return UC_MAX;
        }
        $cache->set('av_class_' . $ending, $class, 0);
    }

    return $class;
}
