<?php
/**
 * @param int  $class
 * @param bool $staff
 * @param bool $pin
 */
function class_check($class = 0, $staff = true, $pin = false)
{
    global $CURUSER, $site_config, $cache;

    if (!$CURUSER) {
        header("Location: {$site_config['baseurl']}/404.html");
        die();
    }

    if ($CURUSER['class'] >= $class) {
        if ($pin) {
            if (!in_array($CURUSER['id'], $site_config['is_staff']['allowed'])) {
                header("Location: {$site_config['baseurl']}/404.html");
                die();
            }
            $passed = false;
            if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_USER'] === ($CURUSER['username'])) {
                $hash = md5($site_config['site']['salt2'].$_SERVER['PHP_AUTH_PW'].$CURUSER['secret']);
                if (md5($site_config['site']['salt2'].$site_config['staff']['staff_pin'].$CURUSER['secret']) === $hash) {
                    $passed = true;
                }
            }
            if (!$passed) {
                header('WWW-Authenticate: Basic realm="Administration"');
                header('HTTP/1.0 401 Unauthorized');
                $HTMLOUT = "<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>ERROR</title>
</head>
<body>
<h1>ERROR</h1>
<p>Sorry! Access denied!</p>
</body>
</html>";
                echo $HTMLOUT;
                die();
            }
        }
        if ($staff) {
            if (($CURUSER['class'] > UC_MAX) || (!in_array($CURUSER['id'], $site_config['is_staff']['allowed']))) {
                $ip = getip();
                /** file ban them **/
                // @fclose(@fopen(INCL_DIR.'bans/'.$ip, 'w'));

                /** SQL ban them **/
                //require_once(INCL_DIR.'bans.php');
                //make_bans($ip, $_SERVER['REMOTE_ADDR'], 'Bad Class. Join IRC for assistance.');

                /** auto post to forums**/
                $body = sqlesc('User '.$CURUSER['username'].' - '.$ip."\n Class ".$CURUSER['class']."\n Current page: ".$_SERVER['PHP_SELF'].', Previous page: '.(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'no referer').', Action: '.$_SERVER['REQUEST_URI']."\n Member has been disabled and demoted by class check system.");
                $topicid = (int) $site_config['staff']['forumid'];
                $added = TIME_NOW;
                $icon = 'topic_normal';
                if (user_exists($site_config['chatBotID'])) {
                    sql_query('INSERT INTO posts (topic_id, user_id, added, body, icon) '."VALUES ($topicid , ".$site_config['chatBotID'].", $added, $body, ".sqlesc($icon).')') or sqlerr(__FILE__, __LINE__);
                    /** get mysql_insert_id(); **/
                    $res = sql_query("SELECT id FROM posts WHERE topic_id = $topicid
                                        ORDER BY id DESC LIMIT 1") or sqlerr(__FILE__, __LINE__);
                    $arr = mysqli_fetch_row($res) or die('No staff post found');
                    $postid = $arr[0];
                    sql_query("UPDATE topics SET last_post = $postid WHERE id = $topicid") or sqlerr(__FILE__, __LINE__);
                    /** PM Owner **/
                    $subject = sqlesc('Warning Class Check System!');
                    sql_query('INSERT INTO messages (sender, receiver, added, subject, msg)
                                VALUES (0, '.$site_config['site']['owner'].", $added, $subject, $body)") or sqlerr(__FILE__, __LINE__);
                    /* punishments **/
                    //sql_query("UPDATE users SET enabled = 'no', class = 1 WHERE id = {$CURUSER['id']}") or sqlerr(__file__, __line__);
                    sql_query("UPDATE users SET class = 1 WHERE id = {$CURUSER['id']}") or sqlerr(__FILE__, __LINE__);
                    /* remove caches **/
                    $cache->update_row('user'.$CURUSER['id'], [
                        'class' => 1,
                    ], $site_config['expires']['user_cache']);
                    //==

                    /* log **/
                    //write_log("<span style='color:#FA0606;'>Class Check System Initialized</span><a href='forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=last#$postid'>VIEW</a>", UC_SYSOP, false);
                    write_log('Class Check System Initialized [url='.$site_config['baseurl'].'/forums.php?action=view_topic&amp;topic_id='.$topicid.'&amp;page=last#'.$postid.']VIEW[/url]');
                    //require_once(INCL_DIR.'user_functions.php');
                    $HTMLOUT = '';
                    $HTMLOUT .= "
<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
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
            stderr('ERROR', 'No Permission. Page is for '.get_user_class_name($class).'s and above. Read FAQ.');
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
    if (false == ($class = $cache->get('av_class_'.$ending))) {
        $classid = sql_query("SELECT av_class FROM staffpanel WHERE file_name LIKE '%$ending%'") or sqlerr(__FILE__, __LINE__);
        $classid = mysqli_fetch_assoc($classid);
        $class = (int) $classid['av_class'];
        $cache->set('av_class_'.$ending, $class, 0);
    }

    return $class;
}
