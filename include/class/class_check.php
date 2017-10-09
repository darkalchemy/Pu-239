<?php
function class_check($class = 0, $staff = true, $pin = false)
{
    global $CURUSER, $site_config, $mc1;
    if (!$CURUSER) {
        header("Location: {$site_config['baseurl']}/404.html");
        exit();
    }

    if ($CURUSER['class'] >= $class) {
        if ($pin) {
            if (!in_array($CURUSER['id'], $site_config['is_staff']['allowed'])) {
                header("Location: {$site_config['baseurl']}/404.html");
                exit();
            }
            $passed = false;
            if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_USER'] === ($CURUSER['username'])) {
                $hash = md5($site_config['site']['salt2'] . $_SERVER['PHP_AUTH_PW'] . $CURUSER['secret']);
                if (md5($site_config['site']['salt2'] . $site_config['staff']['staff_pin'] . $CURUSER['secret']) === $hash) {
                    $passed = true;
                }
            }
            if (!$passed) {
                header('WWW-Authenticate: Basic realm="Administration"');
                header('HTTP/1.0 401 Unauthorized');
                $HTMLOUT = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
    <title>ERROR</title>
</head>
<body>
<h1>ERROR</h1>
<p>Sorry! Access denied!</p>
</body>
</html>';
                echo $HTMLOUT;
                exit();
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
                $body = sqlesc('User ' . $CURUSER['username'] . ' - ' . $ip . "\n Class " . $CURUSER['class'] . "\n Current page: " . $_SERVER['PHP_SELF'] . ', Previous page: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'no referer') . ', Action: ' . $_SERVER['REQUEST_URI'] . "\n Member has been disabled and demoted by class check system.");
                /*
                $body2 = sqlesc("User ".$CURUSER['username']." - ".$ip.
                               " Class ".$CURUSER['class'].
                               " Current page: ".$_SERVER['PHP_SELF'].
                               ", Previous page: ".(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'no referer').
                               ", Action: ".$_SERVER['REQUEST_URI'].
                               " Member has been disabled and demoted by class check system. - Kill the fuX0r");
                */
                $topicid = (int)$site_config['staff']['forumid'];
                $added = TIME_NOW;
                $icon = 'topic_normal';
                if (user_exists($site_config['chatBotID'])) {
                    sql_query('INSERT INTO posts (topic_id, user_id, added, body, icon) ' . "VALUES ($topicid , " . $site_config['chatBotID'] . ", $added, $body, " . sqlesc($icon) . ')') or sqlerr(__FILE__, __LINE__);
                    /** get mysql_insert_id(); **/
                    $res = sql_query("SELECT id FROM posts WHERE topic_id = $topicid
                                        ORDER BY id DESC LIMIT 1") or sqlerr(__FILE__, __LINE__);
                    $arr = mysqli_fetch_row($res) or die('No staff post found');
                    $postid = $arr[0];
                    sql_query("UPDATE topics SET last_post = $postid WHERE id = $topicid") or sqlerr(__FILE__, __LINE__);
                    /** PM Owner **/
                    $subject = sqlesc('Warning Class Check System!');
                    sql_query('INSERT INTO messages (sender, receiver, added, subject, msg)
                                VALUES (0, ' . $site_config['site']['owner'] . ", $added, $subject, $body)") or sqlerr(__FILE__, __LINE__);
                    /* punishments **/
                    //sql_query("UPDATE users SET enabled = 'no', class = 1 WHERE id = {$CURUSER['id']}") or sqlerr(__file__, __line__);
                    sql_query("UPDATE users SET class = 1 WHERE id = {$CURUSER['id']}") or sqlerr(__FILE__, __LINE__);
                    /* remove caches **/
                    $mc1->begin_transaction('user' . $CURUSER['id']);
                    $mc1->update_row(false, [
                        'class' => 1,
                    ]);
                    $mc1->commit_transaction($site_config['expires']['user_cache']);
                    $mc1->begin_transaction('MyUser_' . $CURUSER['id']);
                    $mc1->update_row(false, [
                        'class' => 1,
                    ]);
                    $mc1->commit_transaction($site_config['expires']['curuser']);
                    //==

                    /* log **/
                    //write_log("<span style='color:#FA0606;'>Class Check System Initialized</span><a href='forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=last#$postid'>VIEW</a>", UC_SYSOP, false);
                    write_log('Class Check System Initialized [url=' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topicid . '&amp;page=last#' . $postid . ']VIEW[/url]');
                    //require_once(INCL_DIR.'user_functions.php');
                    //autoshout($body2);
                    $HTMLOUT = '';
                    $HTMLOUT .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
                        \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
                        <html xmlns='http://www.w3.org/1999/xhtml'>
                        <head>
                        <title>Error!</title>
                        </head>
                        <body>
                      <div style='font-size:18px;color:black;background-color:red;text-align:center;'>Incorrect access<br>Silly Rabbit - Trix are for kids.. You dont have the correct credentials to be here !</div>
                      </body></html>";
                    echo $HTMLOUT;
                    exit();
                    //die('No access!'); // give em some Output
                }
            }
        }
    } else {
        if (!$staff) {
            stderr('ERROR', 'No Permission. Page is for ' . get_user_class_name($class) . 's and above. Read FAQ.');
        } else {
            header("Location: {$site_config['baseurl']}/404.html");
            exit();
        }
    }
}

function get_access($script)
{
    global $CURUSER, $site_config, $mc1;
    $ending = parse_url($script, PHP_URL_QUERY);
    $count = substr_count($ending, '&');
    $i = 0;
    while ($i <= $count) {
        if (strpos($ending, '&')) {
            $ending = substr($ending, 0, strrpos($ending, '&'));
        }
        ++$i;
    }
    if (($class = $mc1->get_value('av_class_' . $ending)) == false) {
        $classid = sql_query("SELECT av_class FROM staffpanel WHERE file_name LIKE '%$ending%'") or sqlerr(__FILE__, __LINE__);
        $classid = mysqli_fetch_assoc($classid);
        $class = (int)$classid['av_class'];
        $mc1->cache_value('av_class_' . $ending, $class, 0);
    }

    return $class;
}
