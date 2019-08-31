<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Envms\FluentPDO\Literal;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Session;
use Pu239\User;

/**
 * @param int  $userid
 * @param bool $invincible
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws UnbegunTransaction
 * @throws \Envms\FluentPDO\Exception
 */
function invincible(int $userid, bool $invincible = true)
{
    global $container, $CURUSER;

    $users_class = $container->get(User::class);
    $user = $users_class->getUserFromId($userid);
    $setbits = $clrbits = 0;
    if ($invincible) {
        $display = 'now';
        $setbits |= PERMS_BYPASS_BAN;
    } else {
        $display = 'no longer';
        $clrbits |= PERMS_BYPASS_BAN;
    }
    if ($setbits || $clrbits) {
        $update = [
            'perms' => new Literal('((perms | ' . $setbits . ') & ~' . $clrbits . ')'),
            'modcomment' => get_date((int) TIME_NOW, '', 1) . ' - ' . $display . ' invincible thanks to ' . $CURUSER['username'] . "\n" . $user['modcomment'],
        ];
        $users_class->update($update, $userid, false);
    }
    write_log('Member [b][url=userdetails.php?id=' . $userid . ']' . (htmlsafechars($user['username'])) . '[/url][/b] is ' . $display . ' invincible thanks to [b]' . $CURUSER['username'] . '[/b]');
    $session = $container->get(Session::class);
    $session->set('is-info', "{$user['username']} is $display Invincible");
    header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $userid);
    die();
}
