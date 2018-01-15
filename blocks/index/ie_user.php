<?php
global $site_config, $lang;

$browser = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
if (preg_match('/MSIE/i', $browser)) { //browser is IE
    $HTMLOUT .= "
   <fieldset class='header'>
       <legend>{$lang['index_ie_warn']}</legend>
        <div class='bordered'>
            <div class='alt_bordered bg-00 has-text-centered'>
               {$lang['index_ie_not']}
               {$site_config['site_name']}{$lang['index_ie_suggest']}<a href='http://browsehappy.com'><b>{$lang['index_ie_bhappy']}</b></a>{$lang['index_ie_consider']}
               <br><br>
                <div class='has-text-centered'>
                    <a href='http://www.mozilla.com/firefox'><img border='0' alt='{$lang['index_ie_firefox']}' title='{$lang['index_ie_firefox']}' src='{$site_config['pic_baseurl']}getfirefox.gif' /></a>
                    <br><strong>{$lang['index_ie_get']}
                </div>
           </div>
        </div>
   </fieldset>";
}
