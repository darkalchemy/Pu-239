<?php
$browser = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
if (preg_match("/MSIE/i", $browser)) //browser is IE
{
    $HTMLOUT.= "
   <fieldset class='header'>
	   <legend>{$lang['index_ie_warn']}</legend>
		   <div class='container-fluid'>
		   {$lang['index_ie_not']}
		   {$INSTALLER09['site_name']}{$lang['index_ie_suggest']}<a href='http://browsehappy.com'><b>{$lang['index_ie_bhappy']}</b></a>{$lang['index_ie_consider']}
		   <br /><br /><center><a href='http://www.mozilla.com/firefox'><img border='0' alt='{$lang['index_ie_firefox']}' title='{$lang['index_ie_firefox']}' src='{$INSTALLER09['pic_base_url']}getfirefox.gif' /></a>
		   <br /><strong>{$lang['index_ie_get']}</center>
		   </div>
   </fieldset>
   <hr />";
}
//==
// End Class
// End File
