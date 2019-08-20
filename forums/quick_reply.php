<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_bbcode.php';

/**
 * @param int $topic_id
 *
 * @return string
 */
function quick_reply(int $topic_id)
{
    global $site_config, $lang;

    $output = "
        <div class='has-text-centered'>
            <form method='post' action='{$site_config['paths']['baseurl']}/forums.php?action=post_reply&amp;topic_id={$topic_id}' enctype='multipart/form-data' enctype='multipart/form-data' accept-charset='utf-8'>
                <h3 class='has-text-centered'><i>Quick Reply</i></h3>" . BBcode('', 'w-50 bottom10', 200) . "
                <input type='submit' name='button' class='button is-small' value='{$lang['fe_post']}'>
            </form>
        </div>";

    return $output;
}
