<?php
/**
 * @param      $id
 * @param bool $stealth
 */
function stealth($id, $stealth = true)
{
    global $CURUSER, $cache, $site_config;
    $setbits = $clrbits = 0;
    if ($stealth) {
        $display = 'is';
        $setbits |= bt_options::PERMS_STEALTH; // stealth on
    } else {
        $display = 'is not';
        $clrbits |= bt_options::PERMS_STEALTH; // stealth off
    }
    // update perms
    if ($setbits || $clrbits) {
        sql_query('UPDATE users SET perms = ((perms | ' . $setbits . ') & ~' . $clrbits . ') 
                 WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    }
    // grab current data
    $res = sql_query('SELECT username, perms, modcomment FROM users 
                     WHERE id = ' . sqlesc($id) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
    $row = mysqli_fetch_assoc($res);
    $row['perms'] = (int)$row['perms'];
    $modcomment = get_date(TIME_NOW, '', 1) . ' - ' . $display . ' in Stealth Mode thanks to ' . $CURUSER['username'] . "\n" . $row['modcomment'];
    sql_query('UPDATE users SET modcomment = ' . sqlesc($modcomment) . ' WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    // update caches
    $cache->update_row('user' . $id, [
        'perms' => $row['perms'],
    ], $site_config['expires']['user_cache']);
    $cache->update_row('MyUser_' . $id, [
        'perms' => $row['perms'],
    ], $site_config['expires']['curuser']);
    $cache->update_row('user_stats_' . $id, [
        'modcomment' => $modcomment,
    ], $site_config['expires']['user_stats']);
    if ($id == $CURUSER['id']) {
        $cache->update_row('user' . $CURUSER['id'], [
            'perms' => $row['perms'],
        ], $site_config['expires']['user_cache']);
        $cache->update_row('MyUser_' . $CURUSER['id'], [
            'perms' => $row['perms'],
        ], $site_config['expires']['curuser']);
        $cache->update_row('user_stats_' . $CURUSER['id'], [
            'modcomment' => $modcomment,
        ], $site_config['expires']['user_stats']);
    }
    write_log('Member [b][url=userdetails.php?id=' . $id . ']' . (htmlsafechars($row['username'])) . '[/url][/b] ' . $display . ' in Stealth Mode thanks to [b]' . $CURUSER['username'] . '[/b]');
    // header ouput
    $cache->set('display_stealth' . $CURUSER['id'], $display, 5);
    header('Location: userdetails.php?id=' . $id);
    exit();
}
