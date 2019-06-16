<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Cache;
use Pu239\Session;
use Pu239\User;

/**
 * @param      $id
 * @param bool $invincible
 * @param bool $bypass_bans
 *
 * @throws UnbegunTransaction
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function invincible($id, $invincible = true, $bypass_bans = true)
{
    global $container, $CURUSER;

    $setbits = $clrbits = 0;
    if ($invincible) {
        $display = 'now';
        $setbits |= bt_options::PERMS_NO_IP;
        if ($bypass_bans) {
            $setbits |= bt_options::PERMS_BYPASS_BAN;
        } else {
            $clrbits |= bt_options::PERMS_BYPASS_BAN;
            $display = 'now bypass bans off and';
        }
    } else {
        $display = 'no longer';
        $clrbits |= bt_options::PERMS_NO_IP;
        $clrbits |= bt_options::PERMS_BYPASS_BAN;
    }
    if ($setbits || $clrbits) {
        sql_query('UPDATE users SET perms = ((perms | ' . $setbits . ') & ~' . $clrbits . ') 
                 WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    }
    $res = sql_query('SELECT username, torrent_pass, perms, modcomment FROM users 
                     WHERE id = ' . sqlesc($id) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
    $row = mysqli_fetch_assoc($res);
    $row['perms'] = (int) $row['perms'];
    sql_query('DELETE FROM `ips` WHERE userid = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $cache = $container->get(Cache::class);
    $cache->delete('ip_history_' . $id);
    $cache->delete('u_passkey_' . $row['torrent_pass']);
    $modcomment = get_date((int) TIME_NOW, '', 1) . ' - ' . $display . ' invincible thanks to ' . $CURUSER['username'] . "\n" . $row['modcomment'];
    $set = [
        'modcomment' => $modcomment,
        'perms' => $row['perms'],
    ];
    $users_class = $container->get(User::class);
    $users_class->update($set, $id);
    write_log('Member [b][url=userdetails.php?id=' . $id . ']' . (htmlsafechars($row['username'])) . '[/url][/b] is ' . $display . ' invincible thanks to [b]' . $CURUSER['username'] . '[/b]');
    $session = $container->get(Session::class);
    $session->set('is-info', "{$CURUSER['username']} is $display Invincible");
    header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $id);
    die();
}
