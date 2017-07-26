<?php

$height = 600;
if (!empty($CURUSER['shoutheight'])) {
    $height = $CURUSER['shoutheight'];
}

$HTMLOUT .= "
    <fieldset class='header'><legend>{$lang['index_ajaxchat']}</legend>
        <div class='container-iframe'>
            <iframe src='{$INSTALLER09['baseurl']}/chat/index.php' width='100%' height='$height' name='ajaxchat' frameborder='0' marginwidth='0' marginheight='0'></iframe>
            <span style='display:inline-block; width: 100%; text-align: center; margin: auto;'>
        </div>
    </fieldset><hr />";
