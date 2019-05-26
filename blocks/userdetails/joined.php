<?php

declare(strict_types = 1);
global $lastseen, $joindate, $lang;
$HTMLOUT .= "
    <tr>
        <td class='rowhead'>{$lang['userdetails_joined']}</td>
        <td>{$joindate}</td>
    </tr>
    <tr>
        <td class='rowhead'>{$lang['userdetails_seen']}</td>
        <td>{$lastseen}</td>
    </tr>";
