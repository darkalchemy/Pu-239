<?php

declare(strict_types = 1);
global $lastseen, $joindate;

$HTMLOUT .= "
    <tr>
        <td class='rowhead'>" . _('Join Date') . "</td>
        <td>{$joindate}</td>
    </tr>
    <tr>
        <td class='rowhead'>" . _('Last Seen') . "</td>
        <td>{$lastseen}</td>
    </tr>";
