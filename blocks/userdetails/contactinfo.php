<?php

declare(strict_types = 1);
global $CURUSER, $lang, $site_config, $user;

$HTMLOUT .= (has_access($CURUSER['class'], UC_SYSOP, 'coder') || ((has_access($CURUSER['class'], UC_STAFF, 'coder') || $user['show_email'] === 'yes')) ? '
        <tr>
            <td class="rowhead">' . $lang['userdetails_email'] . '</td>
            <td><a class="is-link" href="mailto:' . htmlsafechars($user['email']) . '"  title="' . $lang['userdetails_email_click'] . '" target="_blank"><i class="icon-mail" aria-hidden="true"><i>' . $lang['userdetails_send_email'] . '</a></td>
        </tr>' : '') . ($user['skype'] !== '' ? '
        <tr>
            <td class="rowhead">' . $lang['userdetails_skype'] . '</td>
            <td><a class="is-link" href="' . htmlsafechars((string) $user['skype']) . '" title="' . $lang['userdetails_skype_click'] . '"  target="_blank"><img width="16" src="' . $site_config['paths']['images_baseurl'] . 'forums/skype.png" alt="skype">' . $lang['userdetails_open'] . '</a></td>
        </tr>' : '') . ($user['website'] !== '' ? '
        <tr>
            <td class="rowhead">' . $lang['userdetails_website'] . '</td>
            <td><a class="is-link" href="' . htmlsafechars((string) $user['website']) . '" target="_blank" title="' . $lang['userdetails_website_click'] . '"><img src="' . $site_config['paths']['images_baseurl'] . 'forums/www.gif" width="18" alt="website"> ' . htmlsafechars((string) $user['website']) . '</a></td>
        </tr>' : '');
