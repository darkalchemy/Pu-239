<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'function_users.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('formats'));
echo stdhead("{$lang['formats_download_title']}");
?>
<table class='main' width='750'>
    <tr>
        <td class='embedded'>
            <h2><?php

                echo $lang['formats_guide_heading']; ?></h2>
            <table width='100%'>
                <tr>
                    <td class='text'>
                        <?php

                        echo $lang['formats_guide_body']; ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<table class='main' width='750'>
    <tr>
        <td class='embedded'>
            <h2><?php

                echo $lang['formats_compression_title']; ?></h2>
            <table width='100%'>
                <tr>
                    <td class='text'>
                        <?php

                        echo $lang['formats_compression_body']; ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<table class='main' width='750'>
    <tr>
        <td class='embedded'>
            <h2><?php

                echo $lang['formats_multimedia_title']; ?></h2>
            <table width='100%'>
                <tr>
                    <td class='text'>
                        <?php

                        echo $lang['formats_multimedia_body']; ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<table class='main' width='750'>
    <tr>
        <td class='embedded'>
            <h2><?php

                echo $lang['formats_image_title']; ?></h2>
            <table width='100%'>
                <tr>
                    <td class='text'>
                        <?php

                        echo $lang['formats_image_body']; ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<table class='main' width='750'>
    <tr>
        <td class='embedded'>
            <h2><?php

                echo $lang['formats_other_title']; ?></h2>
            <table width='100%'>
                <tr>
                    <td class='text'>
                        <?php

                        echo $lang['formats_other_body']; ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<table class='main' width='750'>
    <tr>
        <td class='embedded'>
            <table width='100%'>
                <tr>
                    <td class='text'>
                        <?php

                        echo $lang['formats_questions']; ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<?php

echo stdfoot();
?>
