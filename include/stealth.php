<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Cache;
use Pu239\Session;
use Pu239\User;

/**
 * @param int  $userid
 * @param bool $stealth
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws UnbegunTransaction
 * @throws \Envms\FluentPDO\Exception
 */
function stealth(int $userid, bool $stealth = true)
{
    global $container, $site_config, $CURUSER;

    $users_class = $container->get(User::class);
    $username = $users_class->get_item('username', $userid);
    $setbits = $clrbits = 0;
    if ($stealth) {
        $display = 'is';
        $setbits |= PERMS_STEALTH;
    } else {
        $display = 'is not';
        $clrbits |= PERMS_STEALTH;
    }

    if ($setbits || $clrbits) {
        sql_query('UPDATE users SET perms = ((perms | ' . $setbits . ') & ~' . $clrbits . ') WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
    }
    $res = sql_query('SELECT username, perms, modcomment FROM users WHERE id = ' . sqlesc($userid) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
    $row = mysqli_fetch_assoc($res);
    $row['perms'] = (int) $row['perms'];
    $modcomment = get_date((int) TIME_NOW, '', 1) . ' - ' . $display . ' in Stealth Mode thanks to ' . $CURUSER['username'] . "\n" . $row['modcomment'];
    sql_query('UPDATE users SET modcomment = ' . sqlesc($modcomment) . ' WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
    $cache = $container->get(Cache::class);
    $cache->update_row('user_' . $userid, [
        'perms' => $row['perms'],
        'modcomment' => $modcomment,
    ], $site_config['expires']['user_cache']);
    if ($userid === $CURUSER['id']) {
        $cache->update_row('user_' . $CURUSER['id'], [
            'perms' => $row['perms'],
            'modcomment' => $modcomment,
        ], $site_config['expires']['user_cache']);
    }
    write_log('Member [b][url=userdetails.php?id=' . $userid . ']' . (htmlsafechars($row['username'])) . '[/url][/b] ' . $display . ' in Stealth Mode thanks to [b]' . $CURUSER['username'] . '[/b]');
    $session = $container->get(Session::class);
    $session->set('is-info', "{$username} $display Stealthy");
    header("Location: {$site_config['paths']['baseurl']}/userdetails.php?id=$userid");
    die();
}
