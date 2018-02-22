<?php
global $site_config;

if (!XBT_TRACKER and $site_config['crazy_hour'] == true) {
    /**
     * @return string
     */
    function crazyhour()
    {
        global $CURUSER, $site_config, $lang;

$cache = new DarkAlchemy\Pu239\Cache();
        $htmlout = $cz = '';
        $crazy_hour = (TIME_NOW + 3600);
        $crazyhour['crazyhour'] = $cache->get('crazyhour');
        if ($crazyhour['crazyhour'] === false || is_null($crazyhour['crazyhour'])) {
            $crazyhour['crazyhour_sql'] = sql_query('SELECT var, amount FROM freeleech WHERE type = "crazyhour"') or sqlerr(__FILE__, __LINE__);
            $crazyhour['crazyhour'] = [];
            if (mysqli_num_rows($crazyhour['crazyhour_sql']) !== 0) {
                $crazyhour['crazyhour'] = mysqli_fetch_assoc($crazyhour['crazyhour_sql']);
            } else {
                $crazyhour['crazyhour']['var'] = random_int(TIME_NOW, (TIME_NOW + 86400));
                $crazyhour['crazyhour']['amount'] = 0;
                sql_query('UPDATE freeleech SET var = ' . $crazyhour['crazyhour']['var'] . ', amount = ' . $crazyhour['crazyhour']['amount'] . ' WHERE type = "crazyhour"') or sqlerr(__FILE__, __LINE__);
            }
            $cache->set('crazyhour', $crazyhour['crazyhour'], 0);
        }
        $cimg = '<img src="' . $site_config['pic_baseurl'] . 'cat_free.gif" alt="FREE!" />';
        if ($crazyhour['crazyhour']['var'] < TIME_NOW) { // if crazyhour over
            $cz_lock = $cache->add('crazyhour_lock', 1, 10);
            if ($cz_lock !== false) {
                $crazyhour['crazyhour_new'] = mktime(23, 59, 59, date('m'), date('d'), date('y'));
                $crazyhour['crazyhour']['var'] = random_int($crazyhour['crazyhour_new'], ($crazyhour['crazyhour_new'] + 86400));
                $crazyhour['crazyhour']['amount'] = 0;
                $crazyhour['remaining'] = ($crazyhour['crazyhour']['var'] - TIME_NOW);
                sql_query('UPDATE freeleech SET var = ' . $crazyhour['crazyhour']['var'] . ', amount = ' . $crazyhour['crazyhour']['amount'] . ' WHERE type = "crazyhour"') or sqlerr(__FILE__, __LINE__);
                $cache->set('crazyhour', $crazyhour['crazyhour'], 0);
                write_log('Next [color=#FFCC00][b]Crazyhour[/b][/color] is at ' . get_date($crazyhour['crazyhour']['var'] + ($CURUSER['time_offset'] - 3600), 'LONG') . '');
                $msg = 'Next [color=orange][b]Crazyhour[/b][/color] is at ' . get_date($crazyhour['crazyhour']['var'] + ($CURUSER['time_offset'] - 3600), 'LONG');
                autoshout($msg);
            }
        } elseif (($crazyhour['crazyhour']['var'] < $crazy_hour) && ($crazyhour['crazyhour']['var'] >= TIME_NOW)) { // if crazyhour
            if ($crazyhour['crazyhour']['amount'] !== 1) {
                $crazyhour['crazyhour']['amount'] = 1;
                $cz_lock = $cache->add('crazyhour_lock', 1, 10);
                if ($cz_lock !== false) {
                    sql_query('UPDATE freeleech SET amount = ' . $crazyhour['crazyhour']['amount'] . ' WHERE type = "crazyhour"') or sqlerr(__FILE__, __LINE__);
                    $cache->set('crazyhour', $crazyhour['crazyhour'], 0);
                    write_log('w00t! It\'s [color=#FFCC00][b]Crazyhour[/b][/color]!');
                    $msg = 'w00t! It\'s [color=orange][b]Crazyhour[/b][/color] :w00t:';
                    autoshout($msg);
                }
            }
            $crazyhour['remaining'] = ($crazyhour['crazyhour']['var'] - TIME_NOW);
            $crazytitle = $lang['gl_crazy_title'];
            $crazymessage = $lang['gl_crazy_message'] . ' <b> ' . $lang['gl_crazy_message1'] . '</b> ' . $lang['gl_crazy_message2'] . ' <strong> ' . $lang['gl_crazy_message3'] . '</strong>!';
            $htmlout .= '<li>
<a class="tooltip" href="#"><b class="button is-success is-small">' . $lang['gl_crazy_on'] . '<span class="custom info alert alert-success"><em>' . $lang['gl_crazy_'] . '</em>
' . $crazytitle . '...' . $crazymessage . ' ' . ' ' . $lang['gl_crazy_ends'] . ' ' . mkprettytime($crazyhour['remaining']) . '&#160;' . $lang['gl_crazy_at'] . ' ' . get_date($crazyhour['crazyhour']['var'], 'LONG') . '</span></b></a></li>';

            return $htmlout;
        }
        $htmlout .= '<li>
<a class="tooltip" href="#"><b class="button is-info is-small">' . $lang['gl_crazy_'] . '</b>
<span class="custom info alert alert-info"><em>' . $lang['gl_crazy_'] . '</em>
' . $lang['gl_crazy_message4'] . '<br>' . $lang['gl_crazy_message5'] . '<br> ' . '' . $lang['gl_crazy_message6'] . ' ' . mkprettytime($crazyhour['crazyhour']['var'] - 3600 - TIME_NOW) . '&#160;' . $lang['gl_crazy_at'] . ' ' . get_date($crazyhour['crazyhour']['var'] + ($CURUSER['time_offset'] - 3600), 'LONG') . '</span></a></li>';

        return $htmlout;
    }

    $htmlout .= crazyhour();
}
