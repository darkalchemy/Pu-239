<?php
$height = 600;
if (!empty($CURUSER['ajaxchat_height'])) {
    $height = $CURUSER['ajaxchat_height'];
}

$HTMLOUT .= "
    <a id='ajaxchat-hash'></a>
    <fieldset id='ajaxchat' class='header'>
        <legend class='flipper'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['index_ajaxchat']}</legend>
        <div class='cite text-center container-iframe'>
            <iframe src='./ajaxchat.php' height='$height' id='ajaxchat' name='ajaxchat'></iframe>
            <span style='display:inline-block; width: 100%; text-align: center; margin: auto;'>
        </div>
    </fieldset>";
