<?php

require_once INCL_DIR . 'function_autopost.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'staff_functions.php';
/**
 * @param int  $class
 * @param bool $staff
 *
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function class_check($class = 0, $staff = true)
{
    global $CURUSER, $site_config, $cache, $topicid, $postid;

    if (!$CURUSER) {
        header("Location: {$site_config['baseurl']}/404.html");
        die();
    }

    if ($CURUSER['class'] >= $class) {
        if ($staff) {
            if (($CURUSER['class'] > UC_MAX) || (!in_array($CURUSER['id'], $site_config['is_staff']))) {
                $ip = getip();
                $body = "User: [url={$site_config['baseurl']}/userdetails.php?id={$CURUSER['id']}][color=user]{$CURUSER['username']}[/color][/url] - {$ip}[br]Class {$CURUSER['class']}[br]Current page: {$_SERVER['PHP_SELF']}[br]Previous page: " . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'no referer') . '[br]Action: ' . $_SERVER['REQUEST_URI'] . '[br] Member has been disabled and demoted by class check system.';
                $subject = 'Warning Class Check System!';
                $added = TIME_NOW;
                if (user_exists($site_config['chatBotID'])) {
                    auto_post($subject, $body);
                    sql_query('UPDATE users SET class = ' . UC_MIN . " WHERE id = {$CURUSER['id']}") or sqlerr(__FILE__, __LINE__);
                    $cache->update_row('user' . $CURUSER['id'], [
                        'class' => 0,
                        'enabled' => 'no',
                    ], $site_config['expires']['user_cache']);

                    write_log('Class Check System Initialized [url=' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topicid . '&amp;page=last#' . $postid . ']VIEW[/url]');
                    $HTMLOUT = doc_head() . "
    <meta property='og:title' content='Error!'>
    <title>Error!</title>
</head>
<body>
    <div style='font-size:18px;color:black;background-color:red;text-align:center;'>Incorrect access<br>Silly Rabbit - Trix are for kids.. You dont have the correct credentials to be here !</div>
</body>
</html>";
                    echo $HTMLOUT;
                    die();
                }
            }
        }
    } else {
        if (!$staff) {
            write_info("{$CURUSER['username']} attempted to access a staff page");
            stderr('ERROR', 'No Permission. Page is for ' . get_user_class_name($class) . 's and above. Read FAQ.');
        } else {
            header("Location: {$site_config['baseurl']}/404.html");
            die();
        }
    }
}

/**
 * @param $script
 *
 * @return array|int|string
 */
function get_access($script)
{
    global $cache;

    $ending = parse_url($script, PHP_URL_QUERY);
    $count = substr_count($ending, '&');
    $i = 0;
    while ($i <= $count) {
        if (strpos($ending, '&')) {
            $ending = substr($ending, 0, strrpos($ending, '&'));
        }
        ++$i;
    }
    $class = $cache->get('av_class_' . $ending);
    if ($class === false || is_null($class)) {
        $classid = sql_query("SELECT av_class FROM staffpanel WHERE file_name LIKE '%$ending'") or sqlerr(__FILE__, __LINE__);
        $classid = mysqli_fetch_assoc($classid);
        $class = (int) $classid['av_class'];
        $cache->set('av_class_' . $ending, $class, 0);
    }

    return $class;
}
