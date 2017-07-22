<?php
$refreshbutton = "<span><a class='btn btn-mini' href='shoutbox.php' target='shoutbox'><i class='icon-refresh'></i>&nbsp;{$lang['index_shoutbox_refresh']}</a></span>\n";
$HTMLOUT .= "
    <fieldset class='header'><legend>{$lang['index_trivia']}</legend>
        <div class='container-fluid'>
            <iframe src='{$INSTALLER09['baseurl']}/trivia.php' width='100%' height='250' name='triviabox' frameborder='0' marginwidth='0' marginheight='0'></iframe>
            <span style='display:inline-block; width: 100%; text-align: center; margin: auto;'>
                <a class='btn btn-mini' href='trivia.php' target='triviabox'>
                    <i class='icon-refresh'></i>&nbsp;{$lang['index_shoutbox_refresh']}
                </a>
            </span>
        </div>
    </fieldset><hr />";
