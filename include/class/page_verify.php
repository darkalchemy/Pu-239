<?php
// session so that repeated access of this page cannot happen without the calling script.
//
// You use the create function with the sending script, and the check function with the
// receiving script...
//
// You need to pass the value of $task from the calling script to the receiving script. While
// this may appear dangerous, it still only allows a one shot at the receiving script, which
// effectively stops flooding.
// page verify by retro
class page_verify
{
    public function __construct()
    {
        if (session_id() == '') {
            sessionStart();
        }
    }

    public function check($task_name = 'Default')
    {
        global $CURUSER, $INSTALLER09, $lang, $_SESSION;
        $returl = (!empty($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : $INSTALLER09['baseurl'] . '/login.php');
        $returl = str_replace('&amp;', '&', $returl);
        if (isset($_SESSION[$INSTALLER09['sessionKeyPrefix'] . 'HTTP_USER_AGENT']) && $_SESSION[$INSTALLER09['sessionKeyPrefix'] . 'HTTP_USER_AGENT'] != $_SERVER['HTTP_USER_AGENT']) {
            stderr('Error', "Please resubmit the form. <a href='" . $returl . "'>Click HERE</a>", false);
        }
        if (isset($_SESSION[$INSTALLER09['sessionKeyPrefix'] . 'Task']) && $_SESSION[$INSTALLER09['sessionKeyPrefix'] . 'Task'] != md5('user_id:' . $CURUSER['id'] . '::taskname-' . $task_name . '::' . $_SESSION[$INSTALLER09['sessionKeyPrefix'] . 'Task_Time'])) {
            stderr('Error', "Please resubmit the form. <a href='" . $returl . "'>Click HERE</a>", false);
        }
        $this->create();
    }

    public function create($task_name = 'Default')
    {
        global $INSTALLER09, $CURUSER, $_SESSION;
        setSessionVar('Task_Time', TIME_NOW);
        setSessionVar('Task', md5('user_id:' . $CURUSER['id'] . '::taskname-' . $task_name . '::' . $_SESSION[$INSTALLER09['sessionKeyPrefix'] . 'Task_Time']));
        setSessionVar('HTTP_USER_AGENT', !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
    }
}
