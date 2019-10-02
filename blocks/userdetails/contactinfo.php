<?php

declare(strict_types = 1);
global $CURUSER, $site_config, $user;

$HTMLOUT .= (has_access($CURUSER['class'], UC_SYSOP, 'coder') || ((has_access($CURUSER['class'], UC_STAFF, 'coder') || $user['show_email'] === 'yes')) ? '
        <tr>
            <td class="rowhead">' . _('Email') . '</td>
            <td><a class="is-link" href="mailto:' . htmlsafechars($user['email']) . '"  title="' . _('click to email') . '" target="_blank"><i class="icon-mail" aria-hidden="true"><i>' . _(' Send Email') . '</a></td>
        </tr>' : '') . ($user['skype'] !== '' ? '
        <tr>
            <td class="rowhead">' . _('Skype') . '</td>
            <td><a class="is-link" href="' . htmlsafechars((string) $user['skype']) . '" title="' . _('click for Skype') . '"  target="_blank"><img width="16" src="' . $site_config['paths']['images_baseurl'] . 'forums/skype.png" alt="skype">' . _(' Open') . '</a></td>
        </tr>' : '') . ($user['website'] !== '' ? '
        <tr>
            <td class="rowhead">' . _('Website') . '</td>
            <td><a class="is-link" href="' . htmlsafechars((string) $user['website']) . '" target="_blank" title="' . _('click to go to website') . '"><img src="' . $site_config['paths']['images_baseurl'] . 'forums/www.gif" width="18" alt="website"> ' . htmlsafechars((string) $user['website']) . '</a></td>
        </tr>' : '');
