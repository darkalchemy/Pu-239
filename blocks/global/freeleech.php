<?php
/** freeleech countdown **/
function freeleech_countdown()
{
    global $CURUSER, $mc1, $lang, $INSTALLER09;
    $htmlout = $freetitle = '';
    $cimg = '<img src="pic/cat_free.gif" alt="FREE!" />';
    $freeleech['freeleech_countdown'] = $mc1->get_value('freeleech_countdown');
    if ($freeleech['freeleech_countdown'] === false) {
        $freeleech['freeleech_sql'] = sql_query('SELECT var, amount FROM freeleech WHERE type = "countdown"') or sqlerr(__FILE__, __LINE__);
        $freeleech['freeleech_countdown'] = array();
        if (mysqli_num_rows($freeleech['freeleech_sql']) !== 0) $freeleech['freeleech_countdown'] = mysqli_fetch_assoc($freeleech['freeleech_sql']);
        else {
            //$freeleech_sunday = strtotime('next Sunday');
            $freeleech['freeleech_countdown']['var'] = 0;
            $freeleech['freeleech_countdown']['amount'] = strtotime('next Monday'); // timestamp sunday
            sql_query('UPDATE freeleech SET var = ' . $freeleech['freeleech']['var'] . ', amount = ' . $freeleech['freeleech_countdown']['amount'] . '
                       WHERE type = "countdown"') or sqlerr(__FILE__, __LINE__);
        }
        $mc1->cache_value('freeleech_countdown', $freeleech['freeleech_countdown'], 0);
    }
    if (($freeleech['freeleech_countdown']['var'] !== 0) && (TIME_NOW > ($freeleech['freeleech_countdown']['var']))) { // end of freeleech sunday
        $freeleech['freeleech_countdown']['var'] = 0;
        $freeleech['freeleech_countdown']['amount'] = strtotime('next Monday'); // timestamp sunday
        sql_query('UPDATE freeleech SET var = ' . $freeleech['freeleech_countdown']['var'] . ', amount = ' . $freeleech['freeleech_countdown']['amount'] . ' 
                       WHERE type = "countdown"') or sqlerr(__FILE__, __LINE__);
        $mc1->begin_transaction('freeleech_countdown');
        $mc1->update_row(false, array(
            'var' => $freeleech['freeleech_countdown']['var'],
            'amount' => $freeleech['freeleech_countdown']['amount']
        ));
        $mc1->commit_transaction(0);
    } elseif (TIME_NOW > ($freeleech['freeleech_countdown']['amount'])) { // freeleech sunday!
        if ($freeleech['freeleech_countdown']['var'] == 0) {
            $freeleech['freeleech_countdown']['var'] = strtotime('next Monday');
            $ahead_by = readable_time(($freeleech['freeleech_countdown']['var'] - 86400) - $freeleech['freeleech_countdown']['amount']);
            //'.$ahead_by.'
            sql_query('UPDATE freeleech SET var = ' . $freeleech['freeleech_countdown']['var'] . ' 
                       WHERE type = "countdown"') or sqlerr(__FILE__, __LINE__);
            $mc1->begin_transaction('freeleech_countdown');
            $mc1->update_row(false, array(
                'var' => $freeleech['freeleech_countdown']['var']
            ));
            $mc1->commit_transaction(0);
            $free_message = 'Freeleech is now active! Making for ' . $ahead_by . ' of Freeleech! Thanks to all ' . $INSTALLER09['site_name'] . ' Members!' . 'It will end at Monday 12:00 am GMT';
            //== log shoutbot ircbot
            require_once (INCL_DIR . 'bbcode_functions.php');
        }
        $freetitle = 'Freeleech in effect!';
        $freemessage = '<img src="pic/smilies/w00t.gif" alt="" /> ' . 'All Torrents <b>FREE</b> till ' . date('D F j, g:i a', $freeleech['freeleech_countdown']['var'] + (($CURUSER['time_offset'] + $CURUSER['dst_in_use']) * 60)) . '</span> ' . '<img src="pic/smilies/w00t.gif" alt="" />';
        $freeleech['remaining'] = ($freeleech['freeleech_countdown']['var'] - TIME_NOW);
        $htmlout.= '
         <li>
     <a class="tooltip" href="#"><b class="btn btn-success btn-small">' . $lang['gl_freeleech'] . '</b>
	 <span class="custom info alert alert-success"><em>' . $freetitle . '</em>' . 'ends&nbsp;at ' . date('D F j, g:i a', $freeleech['freeleech_countdown']['var'] + (($CURUSER['time_offset'] + $CURUSER['dst_in_use']) * 60)) . '</span></a></li>';
        return $htmlout;
    }
    $freeleech['remaining'] = ($freeleech['freeleech_countdown']['amount'] - TIME_NOW);
    $htmlout.= '
         <li>
     <a class="tooltip" href="#"><b class="btn btn-info btn-small">Freeleech</b>
	 <span class="custom info alert alert-info"><em>' . $freetitle . '</em> ' . 'starts&nbsp;at ' . date('D F j, g:i a', $freeleech['freeleech_countdown']['amount'] + (($CURUSER['time_offset'] + $CURUSER['dst_in_use']) * 60)) . '<br /></span></a></li>';
    return $htmlout;
}
//$htmlout .= freeleech_countdown();
if ($CURUSER) {
    if (isset($free) && is_array($free) && (count($free) >= 1)) {
        foreach ($free as $fl) {
            switch ($fl['modifier']) {
            case 1:
                $mode = 'All Torrents Free';
                break;

            case 2:
                $mode = 'All Double Upload';
                break;

            case 3:
                $mode = 'All Torrents Free and Double Upload';
                break;

            case 4:
                $mode = 'All Torrents Silver';
                break;

            default:
                $mode = 0;
            }
            $htmlout.= ($fl['modifier'] != 0 && $fl['expires'] > TIME_NOW ? '
     <li>
     <a class="tooltip" href="#"><b class="btn btn-info btn-small">' . $lang['gl_freeleech'] . '</b>
	 <span class="custom info alert alert-info"><em>' . $fl['title'] . '</em>
     ' . $mode . '<br />
     ' . $fl['message'] . ' ' . $lang['gl_freeleech_sb'] . ' ' . $fl['setby'] . '<br />' . ($fl['expires'] != 1 ? '' . $lang['gl_freeleech_u'] . ' ' . get_date($fl['expires'], 'DATE') . ' (' . mkprettytime($fl['expires'] - TIME_NOW) . ' ' . $lang['gl_freeleech_tg'] . ')' : '') . '  
     </span></a></li>' : '');
        }
    }
}
//crazyhour($extra);
//$htmlout.= freeleech_countdown($extra2);

/** crazyhour/freeleech alerts **/
//echo $extra.$extra2.;
//=== free addon end
// End Class
// End File
