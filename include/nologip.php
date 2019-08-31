<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Envms\FluentPDO\Literal;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Session;
use Pu239\User;

/**
 * @param int  $userid
 * @param bool $nologip
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws UnbegunTransaction
 * @throws \Envms\FluentPDO\Exception
 */
function nologip(int $userid, bool $nologip = true)
{
    global $container, $CURUSER;

    $users_class = $container->get(User::class);
    $user = $users_class->getUserFromId($userid);
    $setbits = $clrbits = 0;
    if ($nologip) {
        $display = 'no longer';
        $setbits |= PERMS_NO_IP;
    } else {
        $display = 'now';
        $clrbits |= PERMS_NO_IP;
    }
    if ($setbits > 0 || $clrbits > 0) {
        $update = [
            'perms' => new Literal('((perms | ' . $setbits . ') & ~' . $clrbits . ')'),
            'modcomment' => get_date((int) TIME_NOW, '', 1) . ' - ' . $display . ' Logging IP thanks to ' . $CURUSER['username'] . "\n" . $user['modcomment'],
        ];
        $users_class->update($update, $userid, false);
    }
    $fluent = $container->get(Database::class);
    $fluent->deleteFrom('ips')
           ->where('userid = ?', $userid)
           ->execute();
    $cache = $container->get(Cache::class);
    $cache->delete('ip_history_' . $userid);
    write_log('Member [b][url=userdetails.php?id=' . $userid . ']' . (htmlsafechars($user['username'])) . '[/url][/b] is ' . $display . ' Logging IP thanks to [b]' . $CURUSER['username'] . '[/b]');
    $session = $container->get(Session::class);
    $session->set('is-info', "{$user['username']} is $display Logging IP");
    header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $userid);
    die();
}
