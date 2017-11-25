<?php
global $lang, $lastseen, $joindate;

$HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_joined']}</td><td width='99%'>{$joindate}</td></tr>
<tr><td class='rowhead'>{$lang['userdetails_seen']}</td><td>{$lastseen}</td></tr>";
