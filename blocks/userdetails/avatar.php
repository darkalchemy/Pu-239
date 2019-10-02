<?php

declare(strict_types = 1);

if ($user['avatar']) {
    $HTMLOUT .= "
    <tr>
        <td class='rowhead'>" . _('Avatar') . "</td>
        <td><img src='" . url_proxy($user['avatar'], true, 250) . "' alt='" . _('Avatar') . "'></td>
    </tr>";
}
