<?php

//==Rep
$member_reputation = get_reputation($user, 'users');
$HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_rep']}</td><td width='99%'>{$member_reputation}<br></td></tr>";
