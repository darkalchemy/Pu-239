<?php
//=== member contact stuff
$HTMLOUT .= (($CURUSER['class'] >= UC_STAFF || $user['show_email'] === 'yes') ? '
        <tr>
            <td class="rowhead">' . $lang['userdetails_email'] . '</td>
            <td><a class="altlink" href="mailto:' . htmlsafechars($user['email']) . '"  title="' . $lang['userdetails_email_click'] . '" target="_blank"><img src="' . $site_config['pic_baseurl'] . 'email.gif" alt="email" width="25" />' . $lang['userdetails_send_email'] . '</a></td>
        </tr>' : '') . ($user['google_talk'] !== '' ? '
        <tr>
            <td class="rowhead">' . $lang['userdetails_gtalk'] . '</td>
            <td><a class="altlink" href="http://talkgadget.google.com/talkgadget/popout?member=' . htmlsafechars($user['google_talk']) . '" title="' . $lang['userdetails_gtalk_click'] . '"  target="_blank"><img src="' . $site_config['pic_baseurl'] . 'forums/google_talk.gif" alt="google_talk" />' . $lang['userdetails_open'] . '</a></td>
        </tr>' : '') . ($user['msn'] !== '' ? '
        <tr>
            <td class="rowhead">' . $lang['userdetails_msn'] . '</td>
            <td><a class="altlink" href="http://members.msn.com/' . htmlsafechars($user['msn']) . '" target="_blank" title="' . $lang['userdetails_msn_click'] . '"><img src="' . $site_config['pic_baseurl'] . 'forums/msn.gif" alt="msn" />' . $lang['userdetails_open'] . '</a></td>
        </tr>' : '') . ($user['yahoo'] !== '' ? '
        <tr>
            <td class="rowhead">' . $lang['userdetails_yahoo'] . '</td>
            <td><a class="altlink" href="http://webmessenger.yahoo.com/?im=' . htmlsafechars($user['yahoo']) . '" target="_blank" title="' . $lang['userdetails_yahoo_click'] . '"><img src="' . $site_config['pic_baseurl'] . 'forums/yahoo.gif" alt="yahoo" />' . $lang['userdetails_open'] . '</a></td>
        </tr>' : '') . ($user['aim'] !== '' ? '
        <tr>
            <td class="rowhead">' . $lang['userdetails_aim'] . '</td>
            <td><a class="altlink" href="http://aim.search.aol.com/aol/search?s_it=searchbox.webhome&amp;q=' . htmlsafechars($user['aim']) . '" target="_blank" title="' . $lang['userdetails_aim_click'] . '"><img src="' . $site_config['pic_baseurl'] . 'forums/aim.gif" alt="AIM" />' . $lang['userdetails_open'] . '</a></td>
        </tr>' : '') . ($user['icq'] !== '' ? '
        <tr>
            <td class="rowhead">' . $lang['userdetails_icq'] . '</td>
            <td><a class="altlink" href="http://people.icq.com/people/&amp;uin=' . htmlsafechars($user['icq']) . '" title="' . $lang['userdetails_icq_click'] . '" target="_blank"><img src="' . $site_config['pic_baseurl'] . 'forums/icq.gif" alt="icq" />' . $lang['userdetails_open'] . '</a></td>
        </tr>' : '') . ($user['website'] !== '' ? '
        <tr>
            <td class="rowhead">' . $lang['userdetails_website'] . '</td>
            <td><a class="altlink" href="' . htmlsafechars($user['website']) . '" target="_blank" title="' . $lang['userdetails_website_click'] . '"><img src="' . $site_config['pic_baseurl'] . 'forums/www.gif" width="18" alt="website" /> ' . htmlsafechars($user['website']) . '</a></td>
        </tr>' : '');
