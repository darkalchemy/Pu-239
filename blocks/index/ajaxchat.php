<?php
$height = 600;
if (!empty($CURUSER['ajaxchat_height'])) {
    $height = $CURUSER['ajaxchat_height'];
}

$HTMLOUT .= "
    <fieldset class='header'>
        <legend>{$lang['index_ajaxchat']}</legend>
        <div class='container-fluid container-iframe'>
            <iframe src='./chat/index.php' width='100%' height='$height' id='ajaxchat' name='ajaxchat' frameborder='0' marginwidth='0' marginheight='0'></iframe>
            <span style='display:inline-block; width: 100%; text-align: center; margin: auto;'>
        </div>
    </fieldset><hr>";
