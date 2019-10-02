<?php

declare(strict_types = 1);
global $viewer, $user;

if (has_access($viewer['class'], UC_STAFF, 'coder') || $viewer['id'] === $user['id']) {
    $table_data .= '
    <tr>
        <td class="rowhead"><a id="flush_hash"></a>' . _('Flush torrents') . '</td>
        <td>
            <form method="post" id="form" action="" name="flush_thing" accept-charset="utf-8">
                <input id="id" type="hidden" value="' . (int) $user['id'] . '" name="id">
                <input id="action2" type="hidden" value="flush_torrents" name="action2">
                <span id="success" style="display:none;color:green;font-weight: bold;">' . _('Torrents Flushed from the system. You may now start your client again!') . '</span>
                <span id="flush_error" style="display:none;color:red;font-weight: bold;">' . _('*** Error Torrents not flushed ***') . '<br>' . _('Try again in a few minutes, or wait. The tracker updates every 15 minutes.') . '</span>
                <span id="flush">' . _('Ensure all torrents have been stopped before clicking this button.') . '<br>
                    <input id="flush_button" type="submit" value="' . _('Flush Torrents!') . '" class="button is-small" name="flush_button"><br>
                    <span style="font-size: x-small;color:red;">' . _('*all flushes are logged, please do not abuse this feature*') . '</span>
                </span>
            </form>
        </td>
    </tr>';
}
