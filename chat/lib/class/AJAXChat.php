<?php

declare(strict_types = 1);

/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */

use Delight\Auth\Auth;
use Delight\Auth\AuthError;
use Delight\Auth\NotLoggedInException;
use DI\DependencyException;
use DI\NotFoundException;
use Envms\FluentPDO\Literal;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Message;
use Pu239\Session;
use Pu239\User;
use Spatie\Image\Exceptions\InvalidManipulation;

/**
 * Class AJAXChat.
 */
class AJAXChat
{
    public $db;

    protected $_config;
    protected $_requestVars;
    protected $_infoMessages;
    protected $_channels;
    protected $_allChannels;
    protected $_view;
    protected $_lang;
    protected $_invitations;
    protected $_customVars;
    protected $_onlineUsersData;
    protected $_session;
    protected $_user;
    protected $_cache;
    protected $_fluent;
    protected $_siteConfig;
    protected $_message;
    protected $_auth;

    /**
     * AJAXChat constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        global $site_config, $container;

        $this->_siteConfig = $site_config;
        $this->_session = $container->get(Session::class);
        $this->_cache = $container->get(Cache::class);
        $this->_fluent = $container->get(Database::class);
        $this->_user = $container->get(User::class);
        $this->_message = $container->get(Message::class);
        $this->_auth = $container->get(Auth::class);
        $this->initialize();
    }

    /**
     * @throws Exception
     */
    public function initialize()
    {
        // Initialize configuration settings:
        $this->initConfig();

        // Initialize the DataBase connection:
        $this->initDataBaseConnection();

        // Initialize request variables:
        $this->initRequestVars();

        // Initialize the chat session:
        $this->initSession();

        // Handle the browser request and send the response content:
        $this->handleRequest();
    }

    /**
     * @throws AuthError
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotLoggedInException
     * @throws UnbegunTransaction
     * @throws \Envms\FluentPDO\Exception
     */
    public function initConfig()
    {
        $config = null;
        if (!include(AJAX_CHAT_PATH . 'lib' . DIRECTORY_SEPARATOR . '/config.php')) {
            echo '<strong>Error:</strong> Could not find a config.php file in "' . AJAX_CHAT_PATH . 'lib' . DIRECTORY_SEPARATOR . '". Check to make sure the file exists.';
            die();
        }
        $this->_config = &$config;

        $this->initCustomConfig();
    }

    /**
     * @throws AuthError
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotLoggedInException
     * @throws UnbegunTransaction
     * @throws \Envms\FluentPDO\Exception
     */
    public function initCustomConfig()
    {
        check_user_status();
    }

    public function initDataBaseConnection()
    {
    }

    public function initRequestVars()
    {
        $this->_requestVars = [];
        $this->_requestVars['ajax'] = isset($_REQUEST['ajax']) ? true : false;
        $this->_requestVars['userID'] = isset($_REQUEST['userID']) ? (int) $_REQUEST['userID'] : null;
        $this->_requestVars['userName'] = isset($_REQUEST['userName']) ? $_REQUEST['userName'] : null;
        $this->_requestVars['channelID'] = isset($_REQUEST['channelID']) ? (int) $_REQUEST['channelID'] : null;
        $this->_requestVars['channelName'] = isset($_REQUEST['channelName']) ? $_REQUEST['channelName'] : null;
        $this->_requestVars['text'] = isset($_POST['text']) ? $_POST['text'] : null;
        $this->_requestVars['lastID'] = isset($_REQUEST['lastID']) ? (int) $_REQUEST['lastID'] : 0;
        $this->_requestVars['login'] = isset($_REQUEST['login']) ? true : false;
        $this->_requestVars['logout'] = isset($_REQUEST['logout']) ? true : false;
        $this->_requestVars['password'] = isset($_REQUEST['password']) ? $_REQUEST['password'] : null;
        $this->_requestVars['view'] = isset($_REQUEST['view']) ? $_REQUEST['view'] : null;
        $this->_requestVars['year'] = isset($_REQUEST['year']) ? (int) $_REQUEST['year'] : null;
        $this->_requestVars['month'] = isset($_REQUEST['month']) ? (int) $_REQUEST['month'] : null;
        $this->_requestVars['day'] = isset($_REQUEST['day']) ? (int) $_REQUEST['day'] : null;
        $this->_requestVars['hour'] = isset($_REQUEST['hour']) ? (int) $_REQUEST['hour'] : null;
        $this->_requestVars['search'] = isset($_REQUEST['search']) ? $_REQUEST['search'] : null;
        $this->_requestVars['getInfos'] = isset($_REQUEST['getInfos']) ? $_REQUEST['getInfos'] : null;
        $this->_requestVars['lang'] = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : null;
        $this->_requestVars['delete'] = isset($_REQUEST['delete']) ? (int) $_REQUEST['delete'] : null;
        $this->_requestVars['token'] = isset($_REQUEST['token']) ? $_REQUEST['token'] : null;

        $this->initCustomRequestVars();
    }

    public function initCustomRequestVars()
    {
    }

    /**
     * @throws Exception
     */
    public function initSession()
    {
        if (!$this->canChat()) {
            return;
        }
        if (!$this->isChatOpen()) {
            if ($this->isLoggedIn() && $this->getRequestVar('logout')) {
                $this->logout();

                return;
            }

            return;
        }
        $this->login();
        $this->initView();
        if ($this->getView() == 'chat') {
            $this->initChatViewSession();
        }
    }

    /**
     * @throws \Envms\FluentPDO\Exception
     *
     * @return bool
     */
    public function canChat()
    {
        $user = $this->_user->getUserFromId($this->getUserID());
        if ($user['chatpost'] !== 1 || $user['status'] !== 0) {
            $this->addInfoMessage('errorBanned');

            return false;
        }

        return true;
    }

    /**
     * @return int
     */
    public function getUserID()
    {
        return (int) $this->_auth->getUserId();
    }

    /**
     * @param        $info
     * @param string $type
     */
    public function addInfoMessage($info, $type = 'error')
    {
        if (!isset($this->_infoMessages)) {
            $this->_infoMessages = [];
        }
        if (!isset($this->_infoMessages[$type])) {
            $this->_infoMessages[$type] = [];
        }
        if (!in_array($info, $this->_infoMessages[$type])) {
            array_push($this->_infoMessages[$type], $info);
        }
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotLoggedInException
     * @throws \Envms\FluentPDO\Exception
     * @throws AuthError
     *
     * @return bool
     */
    public function isChatOpen()
    {
        if (!$this->_siteConfig['site']['online']) {
            return false;
        }
        if (has_access((int) $this->getUserRole(), UC_ADMINISTRATOR, 'coder')) {
            return true;
        }
        if ($this->getConfig('chatClosed')) {
            return false;
        }
        $time = TIME_NOW;
        if ($this->getConfig('timeZoneOffset') !== null) {
            // Subtract the server timezone offset and add the config timezone offset:
            $time -= date('Z', $time);
            $time += $this->getConfig('timeZoneOffset');
        }

        if ($this->getConfig('openingHour') < $this->getConfig('closingHour')) {
            if (($this->getConfig('openingHour') > date('G', $time)) || ($this->getConfig('closingHour') <= date('G', $time))) {
                return false;
            }
        } elseif (($this->getConfig('openingHour') > date('G', $time)) && ($this->getConfig('closingHour') <= date('G', $time))) {
            return false;
        }

        if (!in_array(date('w', $time), $this->getConfig('openingWeekDays'))) {
            return false;
        }

        return true;
    }

    /**
     * @throws NotLoggedInException
     * @throws \Envms\FluentPDO\Exception
     * @throws AuthError
     *
     * @return mixed|null
     */
    public function getUserRole()
    {
        $userRole = $this->_session->get('UserRole');
        if ($userRole === null) {
            $this->_user->logout($this->getUserID(), true);
        }

        return $userRole;
    }

    /**
     * @param      $key
     * @param null $subkey
     *
     * @return mixed
     */
    public function getConfig($key, $subkey = null)
    {
        if ($subkey) {
            return $this->_config[$key][$subkey];
        } else {
            return $this->_config[$key];
        }
    }

    /**
     * @param $key
     * @param $subkey
     * @param $value
     */
    public function setConfig($key, $subkey, $value)
    {
        if ($subkey) {
            if (!isset($this->_config[$key])) {
                $this->_config[$key] = [];
            }
            $this->_config[$key][$subkey] = $value;
        } else {
            $this->_config[$key] = $value;
        }
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return (bool) $this->_auth->isLoggedIn();
    }

    /**
     * @param $key
     *
     * @return string|null
     */
    public function getRequestVar($key)
    {
        if ($this->_requestVars && isset($this->_requestVars[$key])) {
            return $this->_requestVars[$key];
        }

        return null;
    }

    /**
     * @throws AuthError
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotLoggedInException
     * @throws \Envms\FluentPDO\Exception
     */
    public function logout()
    {
        if ($this->isUserOnline()) {
            $this->chatViewLogout();
        }
        $this->setLoggedIn(false);

        $this->initView();
    }

    /**
     * @param null $userID
     *
     * @throws \Envms\FluentPDO\Exception
     *
     * @return bool
     */
    public function isUserOnline($userID = null)
    {
        $userID = ($userID === null) ? $this->getUserID() : $userID;
        $userDataArray = $this->getOnlineUsersData(null, 'userID', $userID);
        if ($userDataArray && count($userDataArray) > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param null $channelIDs
     * @param null $key
     * @param null $value
     *
     * @throws \Envms\FluentPDO\Exception
     *
     * @return array|null
     */
    public function getOnlineUsersData($channelIDs = null, $key = null, $value = null)
    {
        if ($this->_onlineUsersData === null) {
            $this->_onlineUsersData = [];

            $sql = $this->_fluent->from('ajax_chat_online AS o')
                                 ->select(null)
                                 ->select('o.userID')
                                 ->select('o.userName')
                                 ->select('o.userRole')
                                 ->select('o.channel')
                                 ->select('UNIX_TIMESTAMP(o.dateTime) AS timeStamp')
                                 ->select('u.perms')
                                 ->select('u.anonymous_until')
                                 ->leftJoin('users AS u ON o.userID = u.id')
                                 ->orderBy('o.userRole DESC')
                                 ->orderBy('LOWER(o.userName)');
            $userid = $this->getUserID();
            foreach ($sql as $row) {
                if ($userid === $row['userID'] || ($row['perms'] < PERMS_STEALTH && $row['anonymous_until'] < TIME_NOW)) {
                    $row['pmCount'] = $this->_message->get_count($row['userID'], $this->_siteConfig['pm']['inbox'], true);
                    array_push($this->_onlineUsersData, $row);
                }
            }
        }

        if ($channelIDs || $key) {
            $onlineUsersData = [];
            foreach ($this->_onlineUsersData as $userData) {
                if ($channelIDs && !in_array($userData['channel'], $channelIDs)) {
                    continue;
                }
                if ($key) {
                    if (!isset($userData[$key])) {
                        return $onlineUsersData;
                    }
                    if ($value !== null) {
                        if ($userData[$key] == $value) {
                            array_push($onlineUsersData, $userData);
                        } else {
                            continue;
                        }
                    } else {
                        array_push($onlineUsersData, $userData[$key]);
                    }
                } else {
                    array_push($onlineUsersData, $userData);
                }
            }

            return $onlineUsersData;
        }

        return $this->_onlineUsersData;
    }

    /**
     * @throws \Envms\FluentPDO\Exception
     */
    public function chatViewLogout()
    {
        $this->removeFromOnlineList($this->getUserID());
    }

    /**
     * @param $userID
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function removeFromOnlineList($userID)
    {
        $this->_fluent->deleteFrom('ajax_chat_online')
                      ->where('userID = ?', $this->getUserID())
                      ->execute();

        $this->removeUserFromOnlineUsersData($userID);
    }

    /**
     * @param null $userID
     */
    public function removeUserFromOnlineUsersData($userID = null)
    {
        if (!$this->_onlineUsersData) {
            return;
        }
        $userID = ($userID === null) ? $this->getUserID() : $userID;
        if (!empty($this->_onlineUsersData)) {
            for ($i = 0; $i < count($this->_onlineUsersData); ++$i) {
                if ($this->_onlineUsersData[$i]['userID'] === $userID) {
                    unset($this->_onlineUsersData[$i]);
                    break;
                }
            }
        }
    }

    /**
     * @param $bool
     */
    public function setLoggedIn($bool)
    {
        $this->_session->set('LoggedIn', $bool);
    }

    /**
     * @throws AuthError
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotLoggedInException
     * @throws \Envms\FluentPDO\Exception
     */
    public function initView()
    {
        $this->_view = null;
        // "chat" is the default view:
        $view = ($this->getRequestVar('view') === null) ? 'chat' : $this->getRequestVar('view');

        if ($this->hasAccessTo($view)) {
            $this->_view = $view;
        }
    }

    /**
     * @param $view
     *
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotLoggedInException
     * @throws \Envms\FluentPDO\Exception
     * @throws AuthError
     *
     * @return bool
     */
    public function hasAccessTo($view)
    {
        switch ($view) {
            case 'chat':
            case 'teaser':
                if ($this->isLoggedIn()) {
                    return true;
                }

                return false;
            case 'logs':
                if ($this->isLoggedIn() && (has_access((int) $this->getUserRole(), UC_ADMINISTRATOR, 'coder') || ($this->getConfig('logsUserAccess') && (has_access((int) $this->getUserRole(), UC_MIN, ''))))) {
                    return true;
                }

                return false;
            default:
                return false;
        }
    }

    /**
     * @throws Exception
     *
     * @return bool
     */
    public function login()
    {
        if (!$this->_auth->isLoggedIn() || !$this->isChatOpen() || !$this->canChat()) {
            $this->_session->unset('Channel');
            $this->addInfoMessage('errorInvalidUser');

            return false;
        }

        if ($this->isUserOnline($this->_auth->getUserId()) || $this->isUserNameInUse($this->_auth->getUsername())) {
            if ($this->getUserRole() >= UC_MIN) {
                $this->removeInactive();
            } else {
                $this->_session->unset('Channel');
                $this->addInfoMessage('errorUserInUse');

                return false;
            }
        }

        if (!has_access((int) $this->getUserRole(), UC_STAFF, 'coder') && $this->isMaxUsersLoggedIn()) {
            $this->_session->unset('Channel');
            $this->addInfoMessage('errorMaxUsersLoggedIn');

            return false;
        }

        $this->setLoggedIn(true);
        $this->setLoginTimeStamp(TIME_NOW);
        $this->addInfoMessage($this->getUserID(), 'userID');
        $this->addInfoMessage($this->getUserName(), 'userName');
        $this->addInfoMessage($this->getUserRole(), 'userRole');

        // Purge logs:
        if ($this->getConfig('logsPurgeLogs')) {
            $this->purgeLogs();
        }

        return true;
    }

    /**
     * @param null $userName
     *
     * @throws \Envms\FluentPDO\Exception
     *
     * @return bool
     */
    public function isUserNameInUse($userName = null)
    {
        $userName = ($userName === null) ? $this->getUserName() : $userName;
        $userDataArray = $this->getOnlineUsersData(null, 'userName', $userName);
        if ($userDataArray && count($userDataArray) > 0) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed|null |null
     */
    public function getUserName()
    {
        return $this->_auth->getUsername();
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws \Envms\FluentPDO\Exception
     */
    public function removeInactive()
    {
        $sql = 'SELECT
                    userID,
                    userName,
                    channel
                FROM
                    ajax_chat_online
                WHERE
                    dateTime < DATE_SUB(NOW(), INTERVAL ' . $this->getConfig('inactiveTimeout') . ' MINUTE);';

        // Create a new SQL query:
        $result = sql_query($sql) or sqlerr(__FILE__, __LINE__);

        if (mysqli_num_rows($result) > 0) {
            $condition = '';

            while ($row = mysqli_fetch_array($result)) {
                if (!empty($condition)) {
                    $condition .= ' OR ';
                }
                // Add userID to condition for removal:
                $condition .= 'userID = ' . sqlesc($row['userID']);
                $this->removeUserFromOnlineUsersData($row['userID']);
            }

            $sql = 'DELETE FROM
                        ajax_chat_online
                    WHERE
                        ' . $condition . ';';

            // Create a new SQL query:
            sql_query($sql) or sqlerr(__FILE__, __LINE__);
        }
    }

    /**
     * @throws \Envms\FluentPDO\Exception
     *
     * @return bool
     */
    public function isMaxUsersLoggedIn()
    {
        if (!empty($this->getOnlineUsersData()) && count($this->getOnlineUsersData()) >= $this->getConfig('maxUsersLoggedIn')) {
            return true;
        }

        return false;
    }

    /**
     * @param $time
     */
    public function setLoginTimeStamp($time)
    {
        $this->_session->set('LoginTimeStamp', $time);
    }

    /**
     * @throws \Envms\FluentPDO\Exception
     */
    public function purgeLogs()
    {
        $this->_fluent->deleteFrom('ajax_chat_messages')
                      ->where('dateTime < ?', gmdate('Y-m-d H:i:s', TIME_NOW - ($this->getConfig('logsPurgeTimeDiff') * 86400)))
                      ->execute();
    }

    /**
     * @return mixed
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws \Envms\FluentPDO\Exception
     * @throws Exception
     */
    public function initChatViewSession()
    {
        if ($this->getChannel() !== null) {
            if (!$this->isUserOnline()) {
                $this->addToOnlineList();
            }
            if ($this->getRequestVar('ajax')) {
                $this->initChannel();
                $this->updateOnlineStatus();
                $this->checkAndRemoveInactive();
            }
        } elseif ($this->getRequestVar('ajax')) {
            $this->chatViewLogin();
        }
    }

    /**
     * @return mixed|null |null
     */
    public function getChannel()
    {
        return $this->_session->get('Channel');
    }

    /**
     * @throws Exception
     */
    public function addToOnlineList()
    {
        $values = [
            'userID' => $this->getUserID(),
            'userName' => $this->getUserName(),
            'userRole' => $this->getUserRole(),
            'channel' => $this->getChannel(),
            'dateTime' => gmdate('Y-m-d H:i:s', TIME_NOW),
        ];

        $update = [
            'userName' => $this->getUserName(),
            'userRole' => $this->getUserRole(),
            'channel' => $this->getChannel(),
            'dateTime' => gmdate('Y-m-d H:i:s', TIME_NOW),
        ];
        $this->_fluent->insertInto('ajax_chat_online', $values)
                      ->onDuplicateKeyUpdate($update)
                      ->execute();

        $this->resetOnlineUsersData();
    }

    public function resetOnlineUsersData()
    {
        $this->_onlineUsersData = null;
    }

    /**
     * @throws Exception
     */
    public function initChannel()
    {
        $channelID = $this->getRequestVar('channelID');
        $channelName = $this->getRequestVar('channelName');
        if ($channelID !== null) {
            $this->switchChannel($this->getChannelNameFromChannelID($channelID));
        } elseif ($channelName !== null) {
            if ($this->getChannelIDFromChannelName($channelName) === null) {
                $channelName = $this->trimChannelName($channelName, $this->getConfig('contentEncoding'));
            }
            $this->switchChannel($channelName);
        }
    }

    /**
     * @param $channelName
     *
     * @throws Exception
     */
    public function switchChannel($channelName)
    {
        $channelID = $this->getChannelIDFromChannelName($channelName);

        if ($channelID !== null && $this->getChannel() == $channelID) {
            return;
        }

        if (!$this->validateChannel($channelID)) {
            $text = '/error InvalidChannelName ' . $channelName;
            $this->insertChatBotMessage($this->getPrivateMessageID(), $text);

            return;
        }

        $this->setChannel($channelID);
        $this->updateOnlineList();

        $this->addInfoMessage($channelName, 'channelSwitch');
        $this->addInfoMessage($channelID, 'channelID');
        $this->_requestVars['lastID'] = 0;
    }

    /**
     * @param $channelName
     *
     * @throws \Envms\FluentPDO\Exception
     *
     * @return mixed|null
     */
    public function getChannelIDFromChannelName($channelName)
    {
        if (!$channelName) {
            return null;
        }
        $channels = $this->getAllChannels();

        if (array_key_exists($channelName, $channels)) {
            return $channels[$channelName];
        }
        $channelID = null;
        if ($channelName == $this->getPrivateChannelName()) {
            return $this->getPrivateChannelID();
        }
        $strlenChannelName = $this->stringLength($channelName);
        $strlenPrefix = $this->stringLength($this->getConfig('privateChannelPrefix'));
        $strlenSuffix = $this->stringLength($this->getConfig('privateChannelSuffix'));
        if ($this->subString($channelName, 0, $strlenPrefix) == $this->getConfig('privateChannelPrefix') && $this->subString($channelName, $strlenChannelName - $strlenSuffix) == $this->getConfig('privateChannelSuffix')) {
            $userName = $this->subString($channelName, $strlenPrefix, $strlenChannelName - ($strlenPrefix + $strlenSuffix));
            $userID = $this->getIDFromName($userName);
            if ($userID !== null) {
                $channelID = $this->getPrivateChannelID($userID);
            }
        }

        return $channelID;
    }

    /**
     * @return array|null
     */
    public function &getAllChannels()
    {
        if ($this->_allChannels === null) {
            $this->_allChannels = [];

            $this->_allChannels[$this->trimChannelName($this->getConfig('defaultChannelName'), $this->getConfig('contentEncoding'))] = $this->getConfig('defaultChannelID');
        }

        return $this->_allChannels;
    }

    /**
     * @param $channelName
     * @param $encoding
     *
     * @return bool|mixed|string
     */
    public function trimChannelName($channelName, $encoding)
    {
        return $this->trimString($channelName, $encoding, null, true, true);
    }

    /**
     * @param      $str
     * @param null $sourceEncoding
     * @param null $maxLength
     * @param bool $replaceWhitespace
     * @param bool $decodeEntities
     * @param null $htmlEntitiesMap
     *
     * @return bool|mixed|string
     */
    public function trimString($str, $sourceEncoding = null, $maxLength = null, $replaceWhitespace = false, $decodeEntities = false, $htmlEntitiesMap = null)
    {
        $str = $this->convertToUnicode($str, $sourceEncoding);

        $str = $this->removeUnsafeCharacters($str);

        $str = trim($str);

        if ($replaceWhitespace) {
            $str = preg_replace('/\s/u', '_', $str);
        }

        if ($decodeEntities) {
            $str = $this->decodeEntities($str, 'UTF-8', $htmlEntitiesMap);
        }

        if ($maxLength) {
            $str = $this->subString($str, 0, $maxLength);
        }

        return $str;
    }

    /**
     * @param      $str
     * @param null $sourceEncoding
     *
     * @return mixed|string
     */
    public function convertToUnicode($str, $sourceEncoding = null)
    {
        if ($sourceEncoding === null) {
            $sourceEncoding = $this->getConfig('sourceEncoding');
        }

        return $this->convertEncoding($str, $sourceEncoding, 'UTF-8');
    }

    /**
     * @param $str
     * @param $charsetFrom
     * @param $charsetTo
     *
     * @return mixed|string
     */
    public function convertEncoding($str, $charsetFrom, $charsetTo)
    {
        return AJAXChatEncoding::convertEncoding($str, $charsetFrom, $charsetTo);
    }

    /**
     * @param $str
     *
     * @return mixed
     */
    public function removeUnsafeCharacters($str)
    {
        return AJAXChatEncoding::removeUnsafeCharacters($str);
    }

    /**
     * @param        $str
     * @param string $encoding
     * @param null   $htmlEntitiesMap
     *
     * @return mixed|string
     */
    public function decodeEntities($str, $encoding = 'UTF-8', $htmlEntitiesMap = null)
    {
        return AJAXChatEncoding::decodeEntities($str, $encoding, $htmlEntitiesMap);
    }

    /**
     * @param        $str
     * @param int    $start
     * @param null   $length
     * @param string $encoding
     *
     * @return bool|string
     */
    public function subString($str, $start = 0, $length = null, $encoding = 'UTF-8')
    {
        return AJAXChatString::subString($str, $start, $length, $encoding);
    }

    /**
     * @param null $userName
     *
     * @return string
     */
    public function getPrivateChannelName($userName = null)
    {
        if ($userName === null) {
            $userName = $this->getUserName();
        }

        return $this->getConfig('privateChannelPrefix') . $userName . $this->getConfig('privateChannelSuffix');
    }

    /**
     * @param null $userID
     *
     * @return int|mixed|null
     */
    public function getPrivateChannelID($userID = null)
    {
        if ($userID === null) {
            $userID = $this->getUserID();
        }

        return $userID + $this->getConfig('privateChannelDiff');
    }

    /**
     * @param        $str
     * @param string $encoding
     *
     * @return int
     */
    public function stringLength($str, $encoding = 'UTF-8')
    {
        return AJAXChatString::stringLength($str, $encoding);
    }

    /**
     *
     * @param string $userName
     *
     * @throws \Envms\FluentPDO\Exception
     *
     * @return bool|int|mixed|null
     */
    public function getIDFromName(string $userName)
    {
        $userDataArray = $this->getOnlineUsersData(null, 'userName', $userName);
        if ($userDataArray && isset($userDataArray[0])) {
            return (int) $userDataArray[0]['userID'];
        }

        $userID = $this->_user->getUserIdFromName($userName);
        if ($userID) {
            return $userID;
        }

        return null;
    }

    /**
     * @param $channelID
     *
     * @throws \Envms\FluentPDO\Exception
     * @throws AuthError
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotLoggedInException
     *
     * @return bool
     */
    public function validateChannel($channelID)
    {
        if ($channelID === null) {
            return false;
        }
        if (in_array($channelID, $this->getChannels())) {
            return true;
        }
        if ($channelID == $this->getPrivateChannelID() && $this->isAllowedToCreatePrivateChannel()) {
            return true;
        }
        if (in_array($channelID, $this->getInvitations())) {
            return true;
        }

        return false;
    }

    /**
     * @return array|null
     */
    public function &getChannels()
    {
        if ($this->_channels === null) {
            $this->_channels = $this->getAllChannels();
        }

        return $this->_channels;
    }

    /**
     * @throws AuthError
     * @throws NotLoggedInException
     * @throws \Envms\FluentPDO\Exception
     *
     * @return bool
     */
    public function isAllowedToCreatePrivateChannel()
    {
        if ($this->getConfig('allowPrivateChannels') && $this->getUserRole() >= UC_MIN) {
            return true;
        }

        return false;
    }

    /**
     * @throws NotFoundException
     * @throws \Envms\FluentPDO\Exception
     * @throws DependencyException
     *
     * @return array|null
     */
    public function getInvitations()
    {
        if ($this->_invitations === null) {
            $this->_invitations = [];

            $sql = 'SELECT
                        channel
                    FROM
                        ajax_chat_invitations
                    WHERE
                        userID = ' . sqlesc($this->getUserID()) . '
                        AND
                        DATE_SUB(NOW(), INTERVAL 1 DAY) < dateTime;';

            $result = sql_query($sql) or sqlerr(__FILE__, __LINE__);

            while ($row = mysqli_fetch_array($result)) {
                array_push($this->_invitations, $row['channel']);
            }
        }

        return $this->_invitations;
    }

    /**
     * @param     $channelID
     * @param     $messageText
     * @param int $ttl
     *
     * @throws Exception
     */
    public function insertChatBotMessage($channelID, $messageText, $ttl = 300)
    {
        $this->insertCustomMessage($this->getConfig('chatBotID'), $this->getConfig('chatBotName'), AJAX_CHAT_CHATBOT, $channelID, $messageText, $ttl);
    }

    /**
     * @param     $userID
     * @param     $userName
     * @param     $userRole
     * @param     $channelID
     * @param     $text
     * @param int $ttl
     *
     * @throws Exception
     */
    public function insertCustomMessage($userID, $userName, $userRole, $channelID, $text, $ttl = 0)
    {
        $bot_only = [
            2,
            3,
            4,
        ]; // Announce, News, Git
        if (in_array($channelID, $bot_only) && $userRole != 100) {
            return;
        }

        $values = [
            'userID' => $userID,
            'userName' => $userName,
            'userRole' => $userRole,
            'channel' => $channelID,
            'dateTime' => gmdate('Y-m-d H:i:s', TIME_NOW),
            'text' => $text,
            'ttl' => $ttl,
        ];

        $this->_fluent->insertInto('ajax_chat_messages')
                      ->values($values)
                      ->execute();

        $set = [
            'dailyshouts' => new Literal('dailyshouts + 1'),
            'weeklyshouts' => new Literal('weeklyshouts + 1'),
            'monthlyshouts' => new Literal('monthlyshouts + 1'),
            'totalshouts' => new Literal('totalshouts + 1'),
        ];

        $this->_fluent->update('usersachiev')
                      ->set($set)
                      ->where('userid = ?', $userID)
                      ->execute();
    }

    /**
     * @param null $userID
     *
     * @return int|mixed|null
     */
    public function getPrivateMessageID($userID = null)
    {
        if ($userID === null) {
            $userID = $this->getUserID();
        }

        return $userID + $this->getConfig('privateMessageDiff');
    }

    /**
     * @param $channel
     */
    public function setChannel($channel)
    {
        $this->_session->set('Channel', $channel);

        $this->setChannelEnterTimeStamp(TIME_NOW);
    }

    /**
     * @param $time
     */
    public function setChannelEnterTimeStamp($time)
    {
        $this->_session->set('ChannelEnterTimeStamp', $time);
    }

    /**
     * @throws Exception
     */
    public function updateOnlineList()
    {
        $this->addToOnlineList();
    }

    /**
     * @param $channelID
     *
     * @throws \Envms\FluentPDO\Exception
     *
     * @return int|string|null
     */
    public function getChannelNameFromChannelID($channelID)
    {
        foreach ($this->getAllChannels() as $key => $value) {
            if ($value == $channelID) {
                return $key;
            }
        }

        if ($channelID == $this->getPrivateChannelID()) {
            return $this->getPrivateChannelName();
        }
        $userName = $this->getNameFromID($channelID - $this->getConfig('privateChannelDiff'));
        if ($userName === null) {
            return null;
        }

        return $this->getPrivateChannelName($userName);
    }

    /**
     * @param $userID
     *
     * @throws \Envms\FluentPDO\Exception
     *
     * @return mixed|null
     */
    public function getNameFromID($userID)
    {
        $userDataArray = $this->getOnlineUsersData(null, 'userID', $userID);
        if ($userDataArray && isset($userDataArray[0])) {
            return $userDataArray[0]['userName'];
        }

        return null;
    }

    /**
     * @throws Exception
     */
    public function updateOnlineStatus()
    {
        if (!$this->getStatusUpdateTimeStamp() || ((TIME_NOW - $this->getStatusUpdateTimeStamp()) > 60)) {
            $this->updateOnlineList();
            $this->setStatusUpdateTimeStamp(TIME_NOW);
        }
    }

    /**
     * @return mixed|null |null
     */
    public function getStatusUpdateTimeStamp()
    {
        return $this->_session->get('StatusUpdateTimeStamp');
    }

    /**
     * @param $time
     */
    public function setStatusUpdateTimeStamp($time)
    {
        $this->_session->set('StatusUpdateTimeStamp', $time);
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws \Envms\FluentPDO\Exception
     */
    public function checkAndRemoveInactive()
    {
        if (!$this->getInactiveCheckTimeStamp() || ((TIME_NOW - $this->getInactiveCheckTimeStamp()) > $this->getConfig('inactiveCheckInterval') * 60)) {
            $this->removeInactive();
            $this->setInactiveCheckTimeStamp(TIME_NOW);
        }
    }

    /**
     * @return mixed|null |null
     */
    public function getInactiveCheckTimeStamp()
    {
        return $this->_session->get('InactiveCheckTimeStamp');
    }

    /**
     * @param $time
     */
    public function setInactiveCheckTimeStamp($time)
    {
        $this->_session->set('InactiveCheckTimeStamp', $time);
    }

    /**
     * @throws Exception
     */
    public function chatViewLogin()
    {
        $this->setChannel($this->getValidRequestChannelID());
        $this->addToOnlineList();
        $this->addInfoMessage($this->getChannel(), 'channelID');
        $this->addInfoMessage($this->getChannelName(), 'channelName');
    }

    /**
     * @throws \Envms\FluentPDO\Exception
     * @throws AuthError
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotLoggedInException
     *
     * @return mixed|string|null
     */
    public function getValidRequestChannelID()
    {
        $channelID = $this->getRequestVar('channelID');
        $channelName = $this->getRequestVar('channelName');
        if (($channelID === null) && $channelName !== null) {
            $channelID = $this->getChannelIDFromChannelName($channelName);
            if ($channelID === null) {
                $channelID = $this->getChannelIDFromChannelName($this->trimChannelName($channelName, $this->getConfig('contentEncoding')));
            }
        }
        if (!$this->validateChannel($channelID)) {
            if ($this->getChannel() !== null) {
                return $this->getChannel();
            }

            return $this->getConfig('defaultChannelID');
        }

        return $channelID;
    }

    /**
     * @throws \Envms\FluentPDO\Exception
     *
     * @return int|string|null
     */
    public function getChannelName()
    {
        return $this->getChannelNameFromChannelID($this->getChannel());
    }

    /**
     * @throws Exception
     */
    public function handleRequest()
    {
        if ($this->getRequestVar('ajax')) {
            if ($this->isLoggedIn()) {
                $this->parseInfoRequests();

                $this->parseCommandRequests();

                $this->initMessageHandling();
            }
            $this->sendXMLMessages();
        } else {
            $this->sendXHTMLContent();
        }
    }

    /**
     * @throws AuthError
     * @throws NotLoggedInException
     * @throws \Envms\FluentPDO\Exception
     */
    public function parseInfoRequests()
    {
        if ($this->getRequestVar('getInfos')) {
            $infoRequests = explode(',', $this->getRequestVar('getInfos'));
            foreach ($infoRequests as $infoRequest) {
                $this->parseInfoRequest($infoRequest);
            }
        }
    }

    /**
     * @param $infoRequest
     *
     * @throws AuthError
     * @throws NotLoggedInException
     * @throws \Envms\FluentPDO\Exception
     */
    public function parseInfoRequest($infoRequest)
    {
        switch ($infoRequest) {
            case 'userID':
                $this->addInfoMessage($this->getUserID(), 'userID');
                break;
            case 'userName':
                $this->addInfoMessage($this->getUserName(), 'userName');
                break;
            case 'userRole':
                $this->addInfoMessage($this->getUserRole(), 'userRole');
                break;
            case 'channelID':
                $this->addInfoMessage($this->getChannel(), 'channelID');
                break;
            case 'channelName':
                $this->addInfoMessage($this->getChannelName(), 'channelName');
                break;
            default:
                $this->parseCustomInfoRequest($infoRequest);
        }
    }

    /**
     * @param $infoRequest
     */
    public function parseCustomInfoRequest($infoRequest)
    {
    }

    /**
     * @throws Exception
     */
    public function parseCommandRequests()
    {
        if ($this->getRequestVar('delete') !== null) {
            $this->deleteMessage($this->getRequestVar('delete'));
        }
    }

    /**
     * @param $messageID
     *
     * @throws Exception
     *
     * @return bool
     */
    public function deleteMessage($messageID)
    {
        $message = $this->_fluent->from('ajax_chat_messages')
                                 ->select(null)
                                 ->select('channel')
                                 ->select('userID')
                                 ->select('userRole')
                                 ->where('id = ?', $messageID)
                                 ->fetch();

        $delete = $result = false;
        if (!empty($message) && $message['channel'] >= 0) {
            if (has_access((int) $this->getUserRole(), UC_ADMINISTRATOR, 'coder')) {
                if ($message['userRole'] === AJAX_CHAT_CHATBOT || $message['userRole'] < $this->getUserRole() || $message['userID'] === $this->getUserID()) {
                    $delete = true;
                }
            } elseif ($this->getUserRole() >= UC_STAFF) {
                if ($message['userRole'] != AJAX_CHAT_CHATBOT || $message['userRole'] < UC_STAFF || $message['userID'] === $this->getUserID()) {
                    $delete = true;
                }
            } elseif ($this->getUserRole() < UC_STAFF && $this->getConfig('allowUserMessageDelete')) {
                if ($message['userID'] === $this->getUserID()) {
                    $delete = true;
                }
            } else {
                return false;
            }
            if ($delete) {
                $result = $this->_fluent->deleteFrom('ajax_chat_messages')
                                        ->where('id = ?', $messageID)
                                        ->execute();
            }

            if ($result) {
                $this->insertChatBotMessage($message['channel'], '/delete ' . $messageID, 240);

                return true;
            }
        }

        return false;
    }

    /**
     * @throws Exception
     */
    public function initMessageHandling()
    {
        if ($this->getView() != 'chat') {
            return;
        }

        if (!$this->validateChannel($this->getChannel())) {
            $this->switchChannel($this->getChannelNameFromChannelID($this->getConfig('defaultChannelID')));

            return;
        }

        if ($this->getRequestVar('text') !== null) {
            $this->insertMessage($this->getRequestVar('text'));
        }
    }

    /**
     * @param $text
     *
     * @throws Exception
     */
    public function insertMessage($text)
    {
        if (!$this->isAllowedToWriteMessage()) {
            return;
        }

        if (!$this->floodControl()) {
            return;
        }

        $text = $this->trimMessageText($text);
        if ($text == '') {
            return;
        }

        if (!$this->onNewMessage()) {
            return;
        }

        $text = $this->replaceCustomText($text);

        $this->insertParsedMessage($text);
    }

    /**
     * @throws AuthError
     * @throws NotLoggedInException
     * @throws \Envms\FluentPDO\Exception
     *
     * @return bool
     */
    public function isAllowedToWriteMessage()
    {
        if ($this->getUserRole() >= UC_MIN) {
            return true;
        }

        return false;
    }

    /**
     * @throws Exception
     *
     * @return bool
     */
    public function floodControl()
    {
        if ($this->getUserRole() >= UC_STAFF) {
            return true;
        }
        $time = TIME_NOW;
        if ($this->getInsertedMessagesRateTimeStamp() + 60 < $time) {
            $this->setInsertedMessagesRateTimeStamp($time);
            $this->setInsertedMessagesRate(1);
        } else {
            $rate = $this->getInsertedMessagesRate() + 1;
            $this->setInsertedMessagesRate($rate);
            if ($rate > $this->getConfig('maxMessageRate')) {
                $this->insertChatBotMessage($this->getPrivateMessageID(), '/error MaxMessageRate');

                return false;
            }
        }

        return true;
    }

    /**
     * @return mixed|null
     */
    public function getInsertedMessagesRateTimeStamp()
    {
        return $this->_session->get('InsertedMessagesRateTimeStamp');
    }

    /**
     * @param $time
     */
    public function setInsertedMessagesRateTimeStamp($time)
    {
        $this->_session->set('InsertedMessagesRateTimeStamp', $time);
    }

    /**
     * @param $rate
     */
    public function setInsertedMessagesRate($rate)
    {
        $this->_session->set('InsertedMessagesRate', $rate);
    }

    /**
     * @return mixed|null |null
     */
    public function getInsertedMessagesRate()
    {
        return $this->_session->get('InsertedMessagesRate');
    }

    /**
     * @param $text
     *
     * @return bool|mixed|string
     */
    public function trimMessageText($text)
    {
        return $this->trimString($text, 'UTF-8', $this->getConfig('messageTextMaxLength'));
    }

    /**
     * @return bool
     */
    public function onNewMessage()
    {
        return true;
    }

    /**
     * @param $text
     *
     * @return mixed
     */
    public function replaceCustomText(&$text)
    {
        return $text;
    }

    /**
     * @param $text
     *
     * @throws Exception
     * @throws UnbegunTransaction
     */
    public function insertParsedMessage($text)
    {
        if ($this->getQueryUserName() !== null && strpos($text, '/') !== 0) {
            $text = '/msg ' . $this->getQueryUserName() . ' ' . $text;
        }

        if (strpos($text, '/') === 0) {
            $textParts = explode(' ', $text);

            switch ($textParts[0]) {
                case '/join':
                    $this->insertParsedMessageJoin($textParts);
                    break;

                case '/quit':
                    $this->logout();
                    break;

                case '/msg':
                case '/describe':
                    $this->insertParsedMessagePrivMsg($textParts);
                    break;

                case '/invite':
                    $this->insertParsedMessageInvite($textParts);
                    break;

                case '/uninvite':
                    $this->insertParsedMessageUninvite($textParts);
                    break;

                case '/query':
                    $this->insertParsedMessageQuery($textParts);
                    break;

                case '/me':
                case '/action':
                    $this->insertParsedMessageAction($textParts);
                    break;

                case '/who':
                    $this->insertParsedMessageWho($textParts);
                    break;

                case '/list':
                    $this->insertParsedMessageList();
                    break;

                case '/whereis':
                    $this->insertParsedMessageWhereis($textParts);
                    break;

                case '/whois':
                    $this->insertParsedMessageWhois($textParts);
                    break;

                case '/roll':
                    $this->insertParsedMessageRoll($textParts);
                    break;

                case '/stats':
                    $this->insertParsedMessageStats($textParts);
                    break;

                case '/gift':
                    $this->insertParsedMessageGift($textParts);
                    break;

                case '/rep':
                    $this->insertParsedMessageRep($textParts);
                    break;

                case '/casino':
                    $this->insertParsedMessageCasino();
                    break;

                case '/seen':
                    $this->insertParsedMessageSeen($textParts);
                    break;

                case '/mentions':
                    $this->insertParsedMessageMentions();
                    break;

                default:
                    if (!$this->parseCustomCommands($text, $textParts)) {
                        $this->insertChatBotMessage($this->getPrivateMessageID(), '/error UnknownCommand ' . $textParts[0]);
                    }
            }
        } else {
            $this->insertCustomMessage($this->getUserID(), $this->getUserName(), $this->getUserRole(), $this->getChannel(), $text);
            if ($this->getUserRole() != AJAX_CHAT_CHATBOT) {
                asyncInclude(INCL_DIR . 'function_chatbot.php', [
                    $this->getUserID(),
                    $this->getChannel(),
                    urlencode($text),
                ]);
            }
        }
    }

    /**
     * @return mixed|null
     */
    public function getQueryUserName()
    {
        return $this->_session->get('QueryUserName');
    }

    /**
     * @param $textParts
     *
     * @throws Exception
     */
    public function insertParsedMessageJoin($textParts)
    {
        if (count($textParts) == 1) {
            if ($this->isAllowedToCreatePrivateChannel()) {
                $this->switchChannel($this->getChannelNameFromChannelID($this->getPrivateChannelID()));
            } else {
                $this->insertChatBotMessage($this->getPrivateMessageID(), '/error MissingChannelName');
            }
        } else {
            $this->switchChannel($textParts[1]);
        }
    }

    /**
     * @param $textParts
     *
     * @throws Exception
     */
    public function insertParsedMessagePrivMsg($textParts)
    {
        if ($this->isAllowedToSendPrivateMessage()) {
            if (count($textParts) < 3) {
                if (count($textParts) == 2) {
                    $this->insertChatBotMessage($this->getPrivateMessageID(), '/error MissingText');
                } else {
                    $this->insertChatBotMessage($this->getPrivateMessageID(), '/error MissingUserName');
                }
            } else {
                $toUserID = $this->getIDFromName($textParts[1]);
                if ($toUserID === null) {
                    if ($this->getQueryUserName() !== null) {
                        $this->insertMessage('/query');
                    } else {
                        $this->insertChatBotMessage($this->getPrivateMessageID(), '/error UserNameNotFound ' . $textParts[1]);
                    }
                } else {
                    $command = ($textParts[0] == '/describe') ? '/privaction' : '/privmsg';
                    $this->insertCustomMessage($this->getUserID(), $this->getUserName(), $this->getUserRole(), $this->getPrivateMessageID(), $command . 'to ' . $textParts[1] . ' ' . implode(' ', array_slice($textParts, 2)));
                    $this->insertCustomMessage($this->getUserID(), $this->getUserName(), $this->getUserRole(), $this->getPrivateMessageID($toUserID), $command . ' ' . implode(' ', array_slice($textParts, 2)));
                }
            }
        } else {
            $this->insertChatBotMessage($this->getPrivateMessageID(), '/error PrivateMessageNotAllowed');
        }
    }

    /**
     * @throws AuthError
     * @throws NotLoggedInException
     * @throws \Envms\FluentPDO\Exception
     *
     * @return bool
     */
    public function isAllowedToSendPrivateMessage()
    {
        if ($this->getConfig('allowPrivateMessages') || $this->getUserRole() >= UC_STAFF) {
            return true;
        }

        return false;
    }

    /**
     * @param $textParts
     *
     * @throws Exception
     */
    public function insertParsedMessageInvite($textParts)
    {
        if ($this->getChannel() == $this->getPrivateChannelID() || in_array($this->getChannel(), $this->getChannels())) {
            if (count($textParts) == 1) {
                $this->insertChatBotMessage($this->getPrivateMessageID(), '/error MissingUserName');
            } else {
                $toUserID = $this->getIDFromName($textParts[1]);
                if ($toUserID === null) {
                    $this->insertChatBotMessage($this->getPrivateMessageID(), '/error UserNameNotFound ' . $textParts[1]);
                } else {
                    $this->addInvitation($toUserID);
                    $invitationChannelName = $this->getChannelNameFromChannelID($this->getChannel());
                    $this->insertChatBotMessage($this->getPrivateMessageID(), '/inviteto ' . $textParts[1] . ' ' . $invitationChannelName);
                    $this->insertChatBotMessage($this->getPrivateMessageID($toUserID), '/invite ' . $this->getUserName() . ' ' . $invitationChannelName);
                }
            }
        } else {
            $this->insertChatBotMessage($this->getPrivateMessageID(), '/error InviteNotAllowed');
        }
    }

    /**
     * @param      $userID
     * @param null $channelID
     *
     * @throws DependencyException
     * @throws NotFoundException
     * @throws \Envms\FluentPDO\Exception
     */
    public function addInvitation($userID, $channelID = null)
    {
        $this->removeExpiredInvitations();

        $channelID = ($channelID === null) ? $this->getChannel() : $channelID;
        $sql = 'INSERT INTO ajax_chat_invitations (
                    userID,
                    channel,
                    dateTime
                )
                VALUES (
                    ' . sqlesc($userID) . ',
                    ' . sqlesc($channelID) . ',
                    NOW()
                );';

        sql_query($sql) or sqlerr(__FILE__, __LINE__);
    }

    /**
     * @throws \Envms\FluentPDO\Exception
     */
    public function removeExpiredInvitations()
    {
        $this->_fluent->deleteFrom('ajax_chat_invitations')
                      ->where('DATE_SUB(NOW(), INTERVAL 1 DAY)>dateTime')
                      ->execute();
    }

    /**
     * @param $textParts
     *
     * @throws Exception
     */
    public function insertParsedMessageUninvite($textParts)
    {
        if ($this->getChannel() == $this->getPrivateChannelID() || in_array($this->getChannel(), $this->getChannels())) {
            if (count($textParts) == 1) {
                $this->insertChatBotMessage($this->getPrivateMessageID(), '/error MissingUserName');
            } else {
                $toUserID = $this->getIDFromName($textParts[1]);
                if ($toUserID === null) {
                    $this->insertChatBotMessage($this->getPrivateMessageID(), '/error UserNameNotFound ' . $textParts[1]);
                } else {
                    $this->removeInvitation($toUserID);
                    $invitationChannelName = $this->getChannelNameFromChannelID($this->getChannel());
                    $this->insertChatBotMessage($this->getPrivateMessageID(), '/uninviteto ' . $textParts[1] . ' ' . $invitationChannelName);
                    $this->insertChatBotMessage($this->getPrivateMessageID($toUserID), '/uninvite ' . $this->getUserName() . ' ' . $invitationChannelName);
                }
            }
        } else {
            $this->insertChatBotMessage($this->getPrivateMessageID(), '/error UninviteNotAllowed');
        }
    }

    /**
     * @param      $userID
     * @param null $channelID
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function removeInvitation($userID, $channelID = null)
    {
        $channelID = ($channelID === null) ? $this->getChannel() : $channelID;

        $this->_fluent->deleteFrom('ajax_chat_invitations')
                      ->where('userID = ?', $userID)
                      ->where('channel = ?', $channelID)
                      ->execute();
    }

    /**
     * @param $textParts
     *
     * @throws Exception
     */
    public function insertParsedMessageQuery($textParts)
    {
        if ($this->isAllowedToSendPrivateMessage()) {
            if (count($textParts) == 1) {
                if ($this->getQueryUserName() !== null) {
                    $this->insertChatBotMessage($this->getPrivateMessageID(), '/queryClose ' . $this->getQueryUserName());
                    $this->setQueryUserName(null);
                } else {
                    $this->insertChatBotMessage($this->getPrivateMessageID(), '/error NoOpenQuery');
                }
            } elseif ($this->getIDFromName($textParts[1]) === null) {
                $this->insertChatBotMessage($this->getPrivateMessageID(), '/error UserNameNotFound ' . $textParts[1]);
            } else {
                if ($this->getQueryUserName() !== null) {
                    $this->insertMessage('/query');
                }
                $this->setQueryUserName($textParts[1]);
                $this->insertChatBotMessage($this->getPrivateMessageID(), '/queryOpen ' . $textParts[1]);
            }
        } else {
            $this->insertChatBotMessage($this->getPrivateMessageID(), '/error PrivateMessageNotAllowed');
        }
    }

    /**
     * @param $userName
     */
    public function setQueryUserName($userName)
    {
        $this->_session->set('QueryUserName', $userName);
    }

    /**
     * @param $textParts
     *
     * @throws Exception
     */
    public function insertParsedMessageAction($textParts)
    {
        if (count($textParts) == 1) {
            $this->insertChatBotMessage($this->getPrivateMessageID(), '/error MissingText');
        } elseif ($this->getQueryUserName() !== null) {
            $this->insertMessage('/describe ' . $this->getQueryUserName() . ' ' . implode(' ', array_slice($textParts, 1)));
        } else {
            $this->insertCustomMessage($this->getUserID(), $this->getUserName(), $this->getUserRole(), $this->getChannel(), implode(' ', $textParts));
        }
    }

    /**
     * @param $textParts
     *
     * @throws Exception
     */
    public function insertParsedMessageWho($textParts)
    {
        if (count($textParts) == 1) {
            if ($this->isAllowedToListHiddenUsers()) {
                $this->insertChatBotMessage($this->getPrivateMessageID(), '/who ' . implode(' ', $this->getOnlineUsers()));
            } else {
                $channels = $this->getChannels();
                if ($this->isAllowedToCreatePrivateChannel()) {
                    array_push($channels, $this->getPrivateChannelID());
                }
                foreach ($this->getInvitations() as $channelID) {
                    if (!in_array($channelID, $channels)) {
                        array_push($channels, $channelID);
                    }
                }
                $this->insertChatBotMessage($this->getPrivateMessageID(), '/who ' . implode(' ', $this->getOnlineUsers($channels)));
            }
        } else {
            $channelName = $textParts[1];
            $channelID = $this->getChannelIDFromChannelName($channelName);
            if (!$this->validateChannel($channelID)) {
                $this->insertChatBotMessage($this->getPrivateMessageID(), '/error InvalidChannelName ' . $channelName);
            } else {
                $onlineUsers = $this->getOnlineUsers([$channelID]);
                if (count($onlineUsers) > 0) {
                    $this->insertChatBotMessage($this->getPrivateMessageID(), '/whoChannel ' . $channelName . ' ' . implode(' ', $onlineUsers));
                } else {
                    $this->insertChatBotMessage($this->getPrivateMessageID(), '/whoEmpty -');
                }
            }
        }
    }

    /**
     * @throws AuthError
     * @throws NotLoggedInException
     * @throws \Envms\FluentPDO\Exception
     *
     * @return bool
     */
    public function isAllowedToListHiddenUsers()
    {
        if ($this->getUserRole() >= UC_STAFF) {
            return true;
        }

        return false;
    }

    /**
     * @param null $channelIDs
     *
     * @throws \Envms\FluentPDO\Exception
     *
     * @return array|null
     */
    public function getOnlineUsers($channelIDs = null)
    {
        return $this->getOnlineUsersData($channelIDs, 'userName');
    }

    /**
     * @throws Exception
     */
    public function insertParsedMessageList()
    {
        $channelNames = $this->getChannelNames();
        if ($this->isAllowedToCreatePrivateChannel()) {
            array_push($channelNames, $this->getPrivateChannelName());
        }
        foreach ($this->getInvitations() as $channelID) {
            $channelName = $this->getChannelNameFromChannelID($channelID);
            if ($channelName !== null && !in_array($channelName, $channelNames)) {
                array_push($channelNames, $channelName);
            }
        }
        $this->insertChatBotMessage($this->getPrivateMessageID(), '/list ' . implode(' ', $channelNames));
    }

    /**
     * @return array|null
     */
    public function getChannelNames()
    {
        return array_flip($this->getChannels());
    }

    /**
     * @param $textParts
     *
     * @throws Exception
     */
    public function insertParsedMessageWhereis($textParts)
    {
        if (count($textParts) == 1) {
            $this->insertChatBotMessage($this->getPrivateMessageID(), '/error MissingUserName');
        } else {
            $whereisUserID = $this->getIDFromName($textParts[1]);
            if ($whereisUserID === null) {
                $this->insertChatBotMessage($this->getPrivateMessageID(), '/error UserNameNotFound ' . $textParts[1]);
            } else {
                $channelID = $this->getChannelFromID($whereisUserID);
                if ($this->validateChannel($channelID)) {
                    $channelName = $this->getChannelNameFromChannelID($channelID);
                } else {
                    $channelName = null;
                }
                if ($channelName === null) {
                    $this->insertChatBotMessage($this->getPrivateMessageID(), '/error UserNameNotFound ' . $textParts[1]);
                } else {
                    $this->insertChatBotMessage($this->getPrivateMessageID(), '/whereis ' . $textParts[1] . ' ' . $channelName);
                }
            }
        }
    }

    /**
     * @param $userID
     *
     * @throws \Envms\FluentPDO\Exception
     *
     * @return mixed|null
     */
    public function getChannelFromID($userID)
    {
        $userDataArray = $this->getOnlineUsersData(null, 'userID', $userID);
        if ($userDataArray && isset($userDataArray[0])) {
            return $userDataArray[0]['channel'];
        }

        return null;
    }

    /**
     * @param $textParts
     *
     * @throws Exception
     */
    public function insertParsedMessageWhois($textParts)
    {
        if ($this->getUserRole() >= UC_STAFF) {
            if (count($textParts) == 1) {
                $this->insertChatBotMessage($this->getPrivateMessageID(), '/error MissingUserName');
            } else {
                $whoisUserID = $this->getIDFromName($textParts[1]);
                if ($whoisUserID === null) {
                    $this->insertChatBotMessage($this->getPrivateMessageID(), '/error UserNameNotFound ' . $textParts[1]);
                } else {
                    $this->insertChatBotMessage($this->getPrivateMessageID(), '/whois ' . $textParts[1] . ' ' . $this->getIPFromID($whoisUserID));
                }
            }
        } else {
            $this->insertChatBotMessage($this->getPrivateMessageID(), '/error CommandNotAllowed ' . $textParts[0]);
        }
    }

    /**
     * @param $userID
     *
     * @throws \Envms\FluentPDO\Exception
     *
     * @return mixed|null
     */
    public function getIPFromID($userID)
    {
        $userDataArray = $this->getOnlineUsersData(null, 'userID', $userID);
        if ($userDataArray && isset($userDataArray[0])) {
            return $userDataArray[0]['ip'];
        }

        return null;
    }

    /**
     * @param $textParts
     *
     * @throws Exception
     */
    public function insertParsedMessageRoll($textParts)
    {
        if (count($textParts) == 1) {
            $text = '/roll ' . $this->getUserName() . ' 1d6 ' . $this->rollDice(6);
        } else {
            $diceParts = explode('d', $textParts[1]);
            if (count($diceParts) == 2) {
                $number = (int) $diceParts[0];
                $sides = (int) $diceParts[1];
                $number = ($number > 0 && $number <= 100) ? $number : 1;
                $sides = ($sides > 0 && $sides <= 100) ? $sides : 6;
                $text = '/roll ' . $this->getUserName() . ' ' . $number . 'd' . $sides . ' ';
                for ($i = 0; $i < $number; ++$i) {
                    if ($i != 0) {
                        $text .= ',';
                    }
                    $text .= $this->rollDice($sides);
                }
            } else {
                $text = '/roll ' . $this->getUserName() . ' 1d6 ' . $this->rollDice(6);
            }
        }
        $this->insertChatBotMessage($this->getChannel(), $text);
    }

    /**
     * @param $sides
     *
     * @throws Exception
     *
     * @return int
     */
    public function rollDice($sides)
    {
        return random_int(1, $sides);
    }

    /**
     * @param $textParts
     *
     * @throws Exception
     */
    public function insertParsedMessageStats($textParts)
    {
        if (count($textParts) == 1) {
            $this->insertChatBotMessage($this->getPrivateMessageID(), '/error MissingUserName');
        } else {
            $whereisUserID = $this->getIDFromName($textParts[1]);
            if ($whereisUserID === null) {
                $this->insertChatBotMessage($this->getPrivateMessageID(), '/error UserNameNotFound ' . $textParts[1]);
            } else {
                $stats = $this->_user->getUserFromId((int) $whereisUserID);
                $offset = $stats['dst_in_use'] ? ($stats['time_offset'] + 1) * 3600 : $stats['time_offset'] * 3600;
                $stats['last_access'] = $stats['last_access'] + $offset;
                $stats['registered'] = $stats['registered'] + $offset;
                $stats['personal_freeleech'] = !empty($stats['personal_freeleech']) ? strtotime($stats['personal_freeleech']) + $offset : 0;
                $stats['personal_doubleseed'] = !empty($stats['personal_doubleseed']) ? strtotime($stats['personal_doubleseed']) + $offset : 0;
                $stats['bj'] = $stats['bj'] * 1024 * 1024 * 1024;
                $bj = $stats['bj'] > 0 ? '[color=#00FF00]' . mksize($stats['bj']) . '[/color]' : '[color=#CC0000]' . mksize($stats['bj']) . '[/color]';
                $uploaded = '[color=#00FF00]' . mksize($stats['uploaded']) . '[/color]';
                $downloaded = '[color=#00FF00]' . mksize($stats['downloaded']) . '[/color]';
                $userClass = get_user_class_name((int) $stats['class']);
                $enabled = $stats['status'] === 0 && $stats['downloadpos'] == 1 ? '[color=#00FF00](Enabled)[/color]' : '[color=#CC0000](Disabled)[/color]';
                $invites = $stats['invites'] > 0 && $stats['invite_rights'] === 'yes' ? '[color=#00FF00]' . number_format($stats['invites']) . '[/color]' : '[color=#CC0000]0[/color]';
                switch (true) {
                    case $stats['downloaded'] > 0 && $stats['uploaded'] > 0:
                        $ratio = '[color=#00FF00]' . number_format($stats['uploaded'] / $stats['downloaded'], 3) . '[/color]';
                        break;
                    case $stats['downloaded'] > 0 && $stats['uploaded'] == 0:
                        $ratio = '[color=#CC0000]' . number_format(1 / $stats['downloaded'], 3) . '[/color]';
                        break;
                    case $stats['downloaded'] == 0 && $stats['uploaded'] >= 0:
                        $ratio = '[color=#00FF00]INF[/color]';
                        break;
                    default:
                        $ratio = '---';
                }

                $casino = $stats['casino'] > 0 ? '[color=#00FF00]' . mksize($stats['casino']) . '[/color]' : '[color=#CC0000]' . mksize($stats['casino']) . '[/color]';
                $seedbonus = '[color=#00FF00]' . number_format((float) $stats['seedbonus']) . '[/color]';
                $freeslots = '[color=#00FF00]' . number_format($stats['freeslots']) . '[/color]';
                $ircidle = $stats['irctotal'] > 0 ? '[color=#00FF00]' . calc_time_difference((int) $stats['irctotal'], true) . '[/color]' : '[color=#CC0000]' . get_date((int) $stats['irctotal'], 'LONG', 0, 0, true) . '[/color]';
                $reputation = '[color=#00FF00]' . number_format($stats['reputation']) . '[/color]';
                $free = get_date((int) $stats['personal_freeleech'], 'LONG') > date('Y-m-d H:i:s') ? '[color=#00FF00]' . get_date((int) $stats['personal_freeleech'], 'LONG') . '[/color]' : '[color=#CC0000]Expired[/color]';
                $double = get_date((int) $stats['personal_doubleseed'], 'LONG') > date('Y-m-d H:i:s') ? '[color=#00FF00]' . get_date((int) $stats['personal_doubleseed'], 'LONG') . '[/color]' : '[color=#CC0000]Expired[/color]';
                $joined = '[color=#00FF00]' . get_date((int) $stats['registered'], 'LONG') . '[/color]';
                $seen = '[color=#00FF00]' . get_date((int) $stats['last_access'], 'LONG') . '[/color]';
                $seeder = $this->_fluent->from('peers')
                                        ->select(null)
                                        ->select('COUNT(id) AS count')
                                        ->where('seeder = "yes"')
                                        ->where('userid = ?', $whereisUserID)
                                        ->fetch('count');
                $seeding = '[color=#00FF00]' . number_format($seeder) . '[/color]';
                $leeching = $this->_fluent->from('peers')
                                          ->select(null)
                                          ->select('COUNT(id) AS count')
                                          ->where('seeder != "yes"')
                                          ->where('userid = ?', $whereisUserID)
                                          ->fetch('count');
                $leeching = '[color=#00FF00]' . number_format($leeching) . '[/color]';
                $uploads = $this->_fluent->from('torrents')
                                         ->select(null)
                                         ->select('COUNT(id) AS count')
                                         ->where('owner = ?', $whereisUserID)
                                         ->fetch('count');
                $uploads = '[color=#00FF00]' . number_format($uploads) . '[/color]';
                $snatched = $this->_fluent->from('snatched')
                                          ->select(null)
                                          ->select('COUNT(id) AS count')
                                          ->where('userid = ?', $whereisUserID)
                                          ->fetch('count');
                $snatched = '[color=#00FF00]' . number_format($snatched) . '[/color]';
                $hnrs = $this->_fluent->from('snatched')
                                      ->select(null)
                                      ->select('COUNT(id) AS count')
                                      ->where('mark_of_cain = "yes"')
                                      ->where('userid = ?', $whereisUserID)
                                      ->fetch('count');
                $hnrs = $hnrs == 0 ? '[color=#00FF00]' . '0[/color]' : '[color=#CC0000]' . number_format($hnrs) . '[/color]';
                $connectyes = $this->_fluent->from('peers')
                                            ->select(null)
                                            ->select('COUNT(id) AS count')
                                            ->where('seeder = "yes"')
                                            ->where('connectable = "yes"')
                                            ->where('userid = ?', $whereisUserID)
                                            ->fetch('count');
                $connectno = $this->_fluent->from('peers')
                                           ->select(null)
                                           ->select('COUNT(id) AS count')
                                           ->where('seeder = "yes"')
                                           ->where('connectable = "no"')
                                           ->where('userid = ?', $whereisUserID)
                                           ->fetch('count');
                if ($connectyes === 0 && $connectno === 0 || $connectno === $seeder) {
                    $connectable = '[color=#CC0000]no[/color]';
                } elseif ($connectyes != 0 && $connectno === 0) {
                    $connectable = '[color=#00FF00]yes[/color]';
                } else {
                    $connectable = '[color=#CC0000]' . number_format($connectyes / $seeder * 100) . '%[/color]';
                }
                $bpt = $this->_siteConfig['bonus']['per_duration'];
                $sql = 'SELECT COUNT(s.id)
                        FROM snatched AS s INNER JOIN users AS u ON u.id=s.userid
                        INNER JOIN torrents t ON s.torrentid=t.id
                        INNER JOIN categories c ON t.category = c.id
                        WHERE t.owner != ' . sqlesc($whereisUserID) . ' AND s.downloaded>0 AND s.seedtime < 259200 AND s.userid=' . sqlesc($whereisUserID);
                $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
                $row = mysqli_fetch_row($res);
                $count_incomplete = $row[0] > 0 ? "[color=#CC0000]{$row[0]}[/color]" : "[color=#00FF00]{$row[0]}[/color]";

                $ircbonus = $stats['onirc'] == 'yes' ? .45 : 0;
                $allbonus = number_format(($connectyes * $bpt * 2) + $ircbonus, 2);
                $earns = $connectyes > 0 ? '[color=#00FF00]' . $allbonus . 'bph[/color]' : '[color=#CC0000]' . $allbonus . 'bph[/color]';
                $seedsize = $this->_fluent->from('peers AS p')
                                          ->select(null)
                                          ->select('SUM(t.size) AS size')
                                          ->innerJoin('torrents AS t ON t.id=p.torrent')
                                          ->where('p.seeder = "yes"')
                                          ->where('p.connectable = "yes"')
                                          ->where('p.userid = ?', $whereisUserID)
                                          ->fetch('size');
                $volume = '[color=#00FF00]' . mksize($seedsize) . '[/color]';
                $whereisRoleClass = get_user_class_name((int) $stats['class'], true);
                $userNameClass = $whereisRoleClass != null ? '[' . $whereisRoleClass . '][url=' . $this->_siteConfig['paths']['baseurl'] . '/userdetails.php?id=' . $whereisUserID . '&hit=1]' . $stats['username'] . '[/url][/' . $whereisRoleClass . ']' : '@' . $textParts[1];
                $str = '';
                $str .= isset($stats['donor']) && $stats['donor'] === 'yes' ? '[img]' . $this->_siteConfig['paths']['chat_images_baseurl'] . 'star.png[/img]' : '';
                $str .= isset($stats['warned']) && $stats['warned'] >= 1 ? '[img]' . $this->_siteConfig['paths']['chat_images_baseurl'] . 'alertred.png[/img]' : '';
                $str .= isset($stats['leechwarn']) && $stats['leechwarn'] >= 1 ? '[img]' . $this->_siteConfig['paths']['chat_images_baseurl'] . 'alertblue.png[/img]' : '';
                $str .= isset($stats['enabled']) && $stats['status'] != 0 ? '[img]' . $this->_siteConfig['paths']['chat_images_baseurl'] . 'disabled.gif[/img]' : '';
                $str .= isset($stats['chatpost']) && $stats['chatpost'] == 0 ? '[img]' . $this->_siteConfig['paths']['chat_images_baseurl'] . 'warned.png[/img]' : '';
                $str .= isset($stats['pirate']) && $stats['pirate'] >= TIME_NOW ? '[img]' . $this->_siteConfig['paths']['chat_images_baseurl'] . 'pirate.png[/img]' : '';

                $text = "[center][size_6]$userNameClass{$str}[/size_6][/center]
[code]
[color=#fff]User Class:[/color]           [$whereisRoleClass]{$userClass}[/$whereisRoleClass]{$enabled}
[color=#fff]idling in irc for:[/color]    $ircidle
[color=#fff]Member Since:[/color]         $joined
[color=#fff]Last Seen:[/color]            $seen
[color=#fff]Downloaded:[/color]           $downloaded
[color=#fff]Uploaded:[/color]             $uploaded
[color=#fff]Ratio:[/color]                $ratio
[color=#fff]Seedbonus:[/color]            $seedbonus
[color=#fff]Invites:[/color]              $invites
[color=#fff]Reputation:[/color]           $reputation
[color=#fff]HnRs:[/color]                 $hnrs
[color=#fff]Snatched:[/color]             $snatched
[color=#fff]Seeding:[/color]              $seeding
[color=#fff]Seeding Size:[/color]         $volume
[color=#fff]Leeching:[/color]             $leeching
[color=#fff]Requirements Not Met:[/color] $count_incomplete
[color=#fff]Connectable:[/color]          $connectable
[color=#fff]Uploads:[/color]              $uploads
[color=#fff]Earning Bonus:[/color]        $earns
[color=#fff]Casino:[/color]               $casino
[color=#fff]Blackjack:[/color]            $bj
[color=#fff]Freeleech Until:[/color]      $free
[color=#fff]DoubleSeed Until:[/color]     $double
[color=#fff]Free/Double Slots:[/color]    $freeslots
[/code]";
                $this->insertChatBotMessage($this->getPrivateMessageID(), $text, 600);
            }
        }
    }

    /**
     * @param $textParts
     *
     * @throws NotFoundException
     * @throws UnbegunTransaction
     * @throws \Envms\FluentPDO\Exception
     * @throws DependencyException
     *
     * @return bool
     */
    public function insertParsedMessageGift($textParts)
    {
        if (count($textParts) == 1) {
            $this->insertChatBotMessage($this->getPrivateMessageID(), '/error MissingUserName');

            return false;
        }

        if (count($textParts) == 2 || !is_numeric($textParts[2]) || $textParts[2] <= 0) {
            $this->insertChatBotMessage($this->getPrivateMessageID(), '/error NotInteger');

            return false;
        }

        $toUserID = (int) $this->getIDFromName($textParts[1]);
        if (!$toUserID) {
            $this->insertChatBotMessage($this->getPrivateMessageID(), '/error UserNameNotFound' . $textParts[1]);

            return false;
        }

        $gift = (int) $textParts[2];
        $cur_user_data = $this->_user->getUserFromId($this->getUserID());

        $frombonus = $cur_user_data['seedbonus'];
        if ((int) $gift > (int) $frombonus) {
            $this->insertChatBotMessage($this->getPrivateMessageID(), '/error NotEnoughKarma');

            return false;
        }

        $toRoleClass = get_user_class_name((int) $this->getRoleFromID($toUserID), true);
        $user = '[' . $toRoleClass . ']' . $textParts[1] . '[/' . $toRoleClass . ']';
        $text = $user . ' has been given a Karma gift of ' . number_format($gift) . ' points from ' . $cur_user_data['username'] . '.';
        $user_data = $this->_user->getUserFromId($toUserID);
        $bonuscomment = get_date((int) TIME_NOW, 'DATE', 1) . " - given karma gift of $gift by " . $cur_user_data['username'] . ".\n" . $user_data['bonuscomment'];
        $recbonus = $user_data['seedbonus'];

        if ($this->_user->update(['seedbonus' => $frombonus - $gift], $cur_user_data['id'])) {
            $this->_user->update([
                'seedbonus' => $recbonus + $gift,
                'bonuscomment' => $bonuscomment,
            ], $toUserID);

            $this->insertChatBotMessage($this->getChannel(), $text, 1);
        } else {
            $this->insertChatBotMessage($this->getPrivateMessageID(), '/error Unknown');
        }

        return true;
    }

    /**
     * @param $userID
     *
     * @throws \Envms\FluentPDO\Exception
     *
     * @return mixed|null
     */
    public function getRoleFromID($userID)
    {
        $userDataArray = $this->getOnlineUsersData(null, 'userID', $userID);
        if ($userDataArray && isset($userDataArray[0])) {
            return $userDataArray[0]['userRole'];
        }

        return null;
    }

    /**
     * @param $textParts
     *
     * @throws NotFoundException
     * @throws UnbegunTransaction
     * @throws \Envms\FluentPDO\Exception
     * @throws DependencyException
     *
     * @return bool
     */
    public function insertParsedMessageRep($textParts)
    {
        if (count($textParts) == 1) {
            $this->insertChatBotMessage($this->getPrivateMessageID(), '/error MissingUserName');

            return false;
        }

        if (count($textParts) == 2 || !is_numeric($textParts[2]) || $textParts[2] <= 0) {
            $this->insertChatBotMessage($this->getPrivateMessageID(), '/error NotInteger');

            return false;
        }

        $toUserID = (int) $this->getIDFromName($textParts[1]);
        if (!$toUserID) {
            $this->insertChatBotMessage($this->getPrivateMessageID(), '/error UserNameNotFound' . $textParts[1]);

            return false;
        }

        $gift = (int) $textParts[2];
        $cur_user_data = $this->_user->getUserFromId($this->getUserID());
        $fromrep = $cur_user_data['reputation'];
        if ((int) $gift > (int) $fromrep) {
            $this->insertChatBotMessage($this->getPrivateMessageID(), '/error NotEnoughRep');

            return false;
        }

        $toRoleClass = get_user_class_name((int) $this->getRoleFromID($toUserID), true);
        $user = '[' . $toRoleClass . ']' . $textParts[1] . '[/' . $toRoleClass . ']';
        $text = $user . ' has been given ' . number_format($gift) . ' Reputation Points from ' . $cur_user_data['username'] . '.';
        $user_data = $this->_user->getUserFromId($toUserID);
        $bonuscomment = get_date((int) TIME_NOW, 'DATE', 1) . " - given reputation gift of $gift by " . $cur_user_data['username'] . ".\n" . $user_data['bonuscomment'];
        $recrep = $user_data['reputation'];

        if ($this->_user->update(['reputation' => $fromrep - $gift], $cur_user_data['id'])) {
            $this->_user->update([
                'reputation' => $recrep + $gift,
                'bonuscomment' => $bonuscomment,
            ], $toUserID);

            $this->insertChatBotMessage($this->getChannel(), $text, 1);
        } else {
            $this->insertChatBotMessage($this->getPrivateMessageID(), '/error Unknown');
        }

        return true;
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Exception
     */
    public function insertParsedMessageCasino()
    {
        $res = sql_query("SELECT COUNT(id) AS count FROM blackjack WHERE status = 'waiting'") or sqlerr(__FILE__, __LINE__);
        $row = mysqli_fetch_row($res);
        $msg = '[code][color=#00FF00]' . $row[0] . ' game' . plural((int) $row[0]) . ' of [url=' . $this->_siteConfig['paths']['baseurl'] . '/games.php]Blackjack[/url] waiting to be played.[/color] ';

        $res = sql_query("SELECT COUNT(id) AS count, SUM(amount) AS amount FROM casino_bets WHERE winner = ''") or sqlerr(__FILE__, __LINE__);
        $row = mysqli_fetch_row($res);

        if (!$row) {
            $msg .= '[color=#00FF00]There are no Casino bets in play. [/color]';
        } else {
            $count = !empty($row[0]) ? count($row[0]) : 0;
            $msg .= '[color=#00FF00]' . $row[0] . ' bet' . plural($count) . ' in the [url=' . $this->_siteConfig['paths']['baseurl'] . '/casino.php]Casino[/url] for ' . mksize($row[1]) . '. [/color]';
        }

        unset($row);
        $res = sql_query('SELECT u.username, c.win + (u.bjwins * 1024 * 1024 * 1024) AS wins, c.lost + (u.bjlosses * 1024 * 1024 * 1024) AS losses, (c.win + (u.bjwins * 1024 * 1024 * 1024)) - (c.lost + (u.bjlosses * 1024 * 1024 * 1024)) AS won FROM casino AS c INNER JOIN users AS u ON c.userid=u.id ORDER BY won DESC LIMIT 1') or sqlerr(__FILE__, __LINE__);
        $row = mysqli_fetch_row($res);
        if ($row) {
            $whereisRoleClass = get_user_class_name((int) $row[0], true);
            $userNameClass = $whereisRoleClass != null ? '[' . $whereisRoleClass . ']' . $row[0] . '[/' . $whereisRoleClass . ']' : $row[1];
            $msg .= $userNameClass . ' [color=#00FF00]is the biggest winner with ' . mksize($row[3]) . '. [/color]';
        }

        unset($row);
        $res = sql_query('SELECT u.username, c.win + (u.bjwins * 1024 * 1024 * 1024) AS wins, c.lost + (u.bjlosses * 1024 * 1024 * 1024) AS losses, (c.win + (u.bjwins * 1024 * 1024 * 1024)) - (c.lost + (u.bjlosses * 1024 * 1024 * 1024)) AS won FROM casino AS c INNER JOIN users AS u ON c.userid=u.id ORDER BY won LIMIT 1') or sqlerr(__FILE__, __LINE__);
        $row = mysqli_fetch_row($res);
        if ($row) {
            $whereisRoleClass = get_user_class_name((int) $row[0], true);
            $userNameClass = $whereisRoleClass != null ? '[' . $whereisRoleClass . ']' . $row[0] . '[/' . $whereisRoleClass . ']' : $row[1];
            $msg .= $userNameClass . ' [color=#00FF00]is the biggest loser with ' . mksize($row[3]) . '. [/color]';
        }

        unset($row);
        $res = sql_query('SELECT SUM(win) FROM casino') or sqlerr(__FILE__, __LINE__);
        $row = mysqli_fetch_row($res);
        if ($row) {
            $msg .= '[color=#00FF00]' . mksize($row[0]) . ' have been won (and lost) in the [url=' . $this->_siteConfig['paths']['baseurl'] . '/casino.php]Casino[/url].[/color] ';
        }

        $resbj = sql_query('SELECT SUM(bjwins) FROM users') or sqlerr(__FILE__, __LINE__);
        $bjsum = mysqli_fetch_row($resbj);
        if ($bjsum) {
            $msg .= '[color=#00FF00]' . mksize($bjsum[0] * 1024 * 1024 * 1024) . ' have been won (and lost) at the [url=' . $this->_siteConfig['paths']['baseurl'] . '/games.php]Blackjack[/url] tables.[/color]';
        }
        $msg .= '[/code]';
        $type = null;
        $text = $msg . $type;
        $this->insertChatBotMessage($this->getChannel(), $text, 600);
    }

    /**
     * @param $textParts
     *
     * @throws Exception
     *
     * @return bool
     */
    public function insertParsedMessageSeen($textParts)
    {
        if (count($textParts) == 1) {
            $this->insertChatBotMessage($this->getPrivateMessageID(), '/error MissingUserName');

            return false;
        } else {
            $userName = $textParts[1];
            $userID = $this->getIDFromName($userName);
            if (!$userID) {
                $this->insertChatBotMessage($this->getPrivateMessageID(), '/error UserNameNotFound' . $userName);

                return false;
            }

            $user_data = $this->_user->getUserFromId($userID);
            $isRoleClass = get_user_class_name((int) $user_data['class'], true);
            $user = '[' . $isRoleClass . ']' . $userName . '[/' . $isRoleClass . ']';
            $seen = $this->_fluent->from('ajax_chat_messages')
                                  ->select('UNIX_TIMESTAMP(dateTime) AS dateTime')
                                  ->where('userID = ?', $userID)
                                  ->where('channel = 0')
                                  ->orderBy('id DESC')
                                  ->limit(1)
                                  ->fetch();

            if ($seen) {
                $gender = $user_data['it'];
                $msg = "$user was last seen " . get_date((int) $seen['dateTime'], '') . ", where $gender said: [quote]" . $seen['text'] . '[/quote]';
            } else {
                $msg = "$user has not been seen in many days.";
            }
            $type = null;
            $text = $msg . $type;
            $this->insertChatBotMessage($this->getChannel(), $text, 300);
        }

        return true;
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws \Envms\FluentPDO\Exception
     * @throws Exception
     */
    public function insertParsedMessageMentions()
    {
        $userName = $this->getUserName();
        $whereisUserID = $this->getUserID();
        $whereisRoleClass = get_user_class_name((int) $this->getRoleFromID($whereisUserID), true);
        $user = '[' . $whereisRoleClass . ']' . $userName . '[/' . $whereisRoleClass . ']';

        $sql = "SELECT dateTime, userName, userID, text
                    FROM ajax_chat_messages
                    WHERE MATCH(text) AGAINST ('\"/privmsgto $userName\"  $userName' IN BOOLEAN MODE)
                        AND NOT MATCH(text) AGAINST ('/privmsg /announce /login /logout /roll /takeover /channelEnter /channelLeave /me' IN NATURAL LANGUAGE MODE)
                        AND userName != " . sqlesc($userName) . '
                        AND userID != ' . $this->getConfig('chatBotID') . '
                        AND channel = 0
                    ORDER BY id DESC LIMIT 25';
        $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $messages = [];
        while ($mentions = mysqli_fetch_array($res)) {
            $posterClass = get_user_class_name((int) $mentions['userID'], true);
            $posterName = '[' . $posterClass . ']' . $mentions['userName'] . '[/' . $posterClass . ']';
            $mention = str_replace('/privmsgto ', '[PM] ', $mentions['text']);
            $messages[] = "{$mentions['dateTime']}: " . $posterName . " => {$mention}";
        }

        if (count($messages) === 0) {
            $msg = "$user has not been mentioned.";
        } else {
            $msg = "\n[code]" . implode("\n", $messages) . '[/code]';
        }
        $type = null;
        $text = $msg . $type;
        $this->insertChatBotMessage($this->getPrivateMessageID(), $text, 600);
    }

    /**
     * @param $text
     * @param $textParts
     *
     * @throws NotLoggedInException
     * @throws \Envms\FluentPDO\Exception
     * @throws Exception
     * @throws AuthError
     * @throws DependencyException
     * @throws NotFoundException
     *
     * @return bool
     */
    public function parseCustomCommands($text, $textParts)
    {
        if ($this->getUserRole() >= UC_STAFF) {
            switch ($textParts[0]) {
                case '/takeover':
                    $this->insertChatBotMessage($this->getChannel(), $text);

                    return true;
                case '/announce':
                    $this->insertChatBotMessage(0, $text);
                    $this->insertChatBotMessage(5, $text);

                    $sql = 'SELECT id FROM users WHERE status = 0';
                    $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
                    while ($id = mysqli_fetch_assoc($res)) {
                        $ids[] = $id;
                    }
                    $msgs_buffer = [];
                    if (!empty($ids)) {
                        foreach ($ids as $rid) {
                            $msgs_buffer[] = [
                                'sender' => $this->getUserID(),
                                'receiver' => $rid['id'],
                                'added' => TIME_NOW,
                                'msg' => str_replace('/announce ', '', $text),
                                'subject' => 'Site News',
                                'poster' => $this->getUserID(),
                            ];
                        }
                    }
                    if (count($msgs_buffer) > 0) {
                        $this->_message->insert($msgs_buffer);
                    }

                    return true;
            }
        }

        return false;
    }

    /**
     * @throws AuthError
     * @throws DependencyException
     * @throws InvalidManipulation
     * @throws NotFoundException
     * @throws NotLoggedInException
     * @throws \Envms\FluentPDO\Exception
     */
    public function sendXMLMessages()
    {
        $httpHeader = new AJAXChatHTTPHeader('UTF-8', 'text/xml');
        $httpHeader->send();
        echo $this->getXMLMessages();
    }

    /**
     * @throws NotFoundException
     * @throws NotLoggedInException
     * @throws \Envms\FluentPDO\Exception
     * @throws AuthError
     * @throws DependencyException
     * @throws InvalidManipulation
     *
     * @return string
     */
    public function getXMLMessages()
    {
        switch ($this->getView()) {
            case 'chat':
                return $this->getChatViewXMLMessages();
            case 'teaser':
                return $this->getTeaserViewXMLMessages();
            case 'logs':
                return $this->getLogsViewXMLMessages();
            default:
                return $this->getLogoutXMLMessage();
        }
    }

    /**
     * @throws InvalidManipulation
     * @throws NotFoundException
     * @throws \Envms\FluentPDO\Exception
     * @throws DependencyException
     *
     * @return string
     */
    public function getChatViewXMLMessages()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<root>';
        $xml .= $this->getInfoMessagesXML();
        $xml .= $this->getChatViewOnlineUsersXML([$this->getChannel()]);
        $xml .= $this->getChatViewMessagesXML();
        $xml .= '</root>';

        return $xml;
    }

    /**
     * @return string
     */
    public function getInfoMessagesXML()
    {
        $xml = '<infos>';
        foreach ($this->getInfoMessages() as $type => $infoArray) {
            foreach ($infoArray as $info) {
                $xml .= '<info type="' . $type . '">';
                $xml .= '<![CDATA[' . $this->encodeSpecialChars((string) $info) . ']]>';
                $xml .= '</info>';
            }
        }
        $xml .= '</infos>';

        return $xml;
    }

    /**
     * @param null $type
     *
     * @return array|mixed
     */
    public function getInfoMessages($type = null)
    {
        if (!isset($this->_infoMessages)) {
            $this->_infoMessages = [];
        }
        if ($type) {
            if (!isset($this->_infoMessages[$type])) {
                $this->_infoMessages[$type] = [];
            }

            return $this->_infoMessages[$type];
        } else {
            return $this->_infoMessages;
        }
    }

    /**
     * @param string $str
     *
     * @return string
     */
    public function encodeSpecialChars(string $str)
    {
        return AJAXChatEncoding::encodeSpecialChars($str);
    }

    /**
     * @param $channelIDs
     *
     * @throws \Envms\FluentPDO\Exception
     *
     * @return string
     */
    public function getChatViewOnlineUsersXML($channelIDs)
    {
        // Get the online users for the given channels:
        $onlineUsersData = $this->getOnlineUsersData($channelIDs);

        $xml = '<users>';
        foreach ($onlineUsersData as $onlineUserData) {
            $xml .= '<user';
            $xml .= ' userID="' . $onlineUserData['userID'] . '"';
            $xml .= ' userRole="' . $onlineUserData['userRole'] . '"';
            $xml .= ' channelID="' . $onlineUserData['channel'] . '"';
            $xml .= ' pmCount="' . $onlineUserData['pmCount'] . '"';
            $xml .= '>';
            $xml .= '<![CDATA[' . $this->encodeSpecialChars($onlineUserData['userName']) . ']]>';
            $xml .= '</user>';
        }
        $xml .= '</users>';

        return $xml;
    }

    /**
     * @throws InvalidManipulation
     * @throws NotFoundException
     * @throws \Envms\FluentPDO\Exception
     * @throws DependencyException
     *
     * @return string
     */
    public function getChatViewMessagesXML()
    {
        $sql = 'SELECT
                    id,
                    userID,
                    userName,
                    userRole,
                    channel AS channelID,
                    UNIX_TIMESTAMP(dateTime) AS timeStamp,
                    text
                FROM
                    ajax_chat_messages
                WHERE
                    ' . $this->getMessageCondition() . '
                ORDER BY
                    id
                    DESC
                LIMIT ' . $this->getConfig('requestMessagesLimit') . ';';

        $result = sql_query($sql) or sqlerr(__FILE__, __LINE__);

        $messages = '';
        while ($row = mysqli_fetch_array($result)) {
            preg_match_all('/\[img\](.+?)\[\/img\]/s', $row['text'], $matches);
            foreach ($matches[1] as $match) {
                $row['text'] = str_replace($match, str_replace($this->_siteConfig['paths']['images_baseurl'], $this->_siteConfig['paths']['chat_images_baseurl'], url_proxy($match, true)), $row['text']);
            }
            $message = $this->getChatViewMessageXML((int) $row['id'], (int) $row['timeStamp'], (int) $row['userID'], html_entity_decode($row['userName']), (int) $row['userRole'], (int) $row['channelID'], html_entity_decode($row['text']));
            $messages = $message . $messages;
        }
        $messages = '<messages>' . $messages . '</messages>';

        return $messages;
    }

    /**
     * @throws NotFoundException
     * @throws DependencyException
     *
     * @return string
     */
    public function getMessageCondition()
    {
        $condition = 'id > ' . sqlesc($this->getRequestVar('lastID')) . '
                        AND (
                            channel = ' . sqlesc($this->getChannel()) . '
                            OR
                            channel = ' . sqlesc($this->getPrivateMessageID()) . '
                        )
                        AND
                        ';
        if ($this->getConfig('requestMessagesPriorChannelEnter') || ($this->getConfig('requestMessagesPriorChannelEnterList') && in_array($this->getChannel(), $this->getConfig('requestMessagesPriorChannelEnterList')))) {
            $condition .= 'NOW() < DATE_ADD(dateTime, interval ' . $this->getConfig('requestMessagesTimeDiff') . ' HOUR)';
        } else {
            $condition .= 'dateTime>= FROM_UNIXTIME(' . $this->getChannelEnterTimeStamp() . ')';
        }

        return $condition;
    }

    /**
     * @return mixed|null
     */
    public function getChannelEnterTimeStamp()
    {
        return $this->_session->get('ChannelEnterTimeStamp');
    }

    /**
     * @param int    $messageID
     * @param int    $timeStamp
     * @param int    $userID
     * @param string $userName
     * @param int    $userRole
     * @param int    $channelID
     * @param string $text
     *
     * @return string
     */
    public function getChatViewMessageXML(int $messageID, int $timeStamp, int $userID, string $userName, int $userRole, int $channelID, string $text)
    {
        $message = '<message';
        $message .= ' id="' . $messageID . '"';
        $message .= ' dateTime="' . date('r', $timeStamp) . '"';
        $message .= ' userID="' . $userID . '"';
        $message .= ' userRole="' . $userRole . '"';
        $message .= ' channelID="' . $channelID . '"';
        $message .= '>';
        $message .= '<username><![CDATA[' . $this->encodeSpecialChars($userName) . ']]></username>';
        $message .= '<text><![CDATA[' . $this->encodeSpecialChars($text) . ']]></text>';
        $message .= '</message>';

        return $message;
    }

    /**
     * @throws NotFoundException
     * @throws NotLoggedInException
     * @throws \Envms\FluentPDO\Exception
     * @throws AuthError
     * @throws DependencyException
     * @throws InvalidManipulation
     *
     * @return string
     */
    public function getTeaserViewXMLMessages()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<root>';
        $xml .= $this->getInfoMessagesXML();
        $xml .= $this->getTeaserViewMessagesXML();
        $xml .= '</root>';

        return $xml;
    }

    /**
     * @throws NotFoundException
     * @throws NotLoggedInException
     * @throws \Envms\FluentPDO\Exception
     * @throws AuthError
     * @throws DependencyException
     * @throws InvalidManipulation
     *
     * @return string
     */
    public function getTeaserViewMessagesXML()
    {
        $sql = 'SELECT
                    id,
                    userID,
                    userName,
                    userRole,
                    channel AS channelID,
                    UNIX_TIMESTAMP(dateTime) AS timeStamp,
                    text
                FROM
                    ajax_chat_messages
                WHERE
                    ' . $this->getTeaserMessageCondition() . '
                ORDER BY
                    id
                    DESC
                LIMIT ' . $this->getConfig('requestMessagesLimit') . ';';

        $result = sql_query($sql) or sqlerr(__FILE__, __LINE__);

        $messages = '';

        while ($row = mysqli_fetch_array($result)) {
            preg_match_all('/\[img\](.*)\[\/img\]/s', $row['text'], $matches);
            foreach ($matches[1] as $match) {
                $row['text'] = str_replace($match, url_proxy($match, true), $row['text']);
            }

            $message = '';
            $message .= '<message';
            $message .= ' id="' . $row['id'] . '"';
            $message .= ' dateTime="' . date('r', $row['timeStamp']) . '"';
            $message .= ' userID="' . $row['userID'] . '"';
            $message .= ' userRole="' . $row['userRole'] . '"';
            $message .= ' channelID="' . $row['channelID'] . '"';
            $message .= '>';
            $message .= '<username><![CDATA[' . $this->encodeSpecialChars($row['userName']) . ']]></username>';
            $message .= '<text><![CDATA[' . $this->encodeSpecialChars($row['text']) . ']]></text>';
            $message .= '</message>';
            $messages = $message . $messages;
        }
        $messages = '<messages>' . $messages . '</messages>';

        return $messages;
    }

    /**
     * @throws \Envms\FluentPDO\Exception
     * @throws AuthError
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotLoggedInException
     *
     * @return string
     */
    public function getTeaserMessageCondition()
    {
        $channelID = $this->getValidRequestChannelID();
        $condition = 'channel = ' . sqlesc($channelID) . '
                        AND
                        ';
        if ($this->getConfig('requestMessagesPriorChannelEnter') || ($this->getConfig('requestMessagesPriorChannelEnterList') && in_array($channelID, $this->getConfig('requestMessagesPriorChannelEnterList')))) {
            $condition .= 'NOW() < DATE_ADD(dateTime, interval ' . $this->getConfig('requestMessagesTimeDiff') . ' HOUR)';
        } else {
            $condition .= '0 = 1';
        }

        return $condition;
    }

    /**
     * @throws NotFoundException
     * @throws NotLoggedInException
     * @throws \Envms\FluentPDO\Exception
     * @throws AuthError
     * @throws DependencyException
     * @throws InvalidManipulation
     *
     * @return string
     */
    public function getLogsViewXMLMessages()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<root>';
        $xml .= $this->getInfoMessagesXML();
        $xml .= $this->getLogsViewMessagesXML();
        $xml .= '</root>';

        return $xml;
    }

    /**
     * @throws NotFoundException
     * @throws NotLoggedInException
     * @throws \Envms\FluentPDO\Exception
     * @throws AuthError
     * @throws DependencyException
     * @throws InvalidManipulation
     *
     * @return string
     */
    public function getLogsViewMessagesXML()
    {
        $sql = 'SELECT
                    id,
                    userID,
                    userName,
                    userRole,
                    channel AS channelID,
                    UNIX_TIMESTAMP(dateTime) AS timeStamp,
                    text
                FROM
                    ajax_chat_messages
                WHERE
                    ' . $this->getLogsViewCondition() . '
                ORDER BY
                    id
                LIMIT ' . $this->getConfig('logsRequestMessagesLimit') . ';';

        $result = sql_query($sql) or sqlerr(__FILE__, __LINE__);

        $xml = '<messages>';
        while ($row = mysqli_fetch_array($result)) {
            preg_match_all('/<img.*?src=["|\'](.*?)["|\'].*?>/s', $row['text'], $matches);
            foreach ($matches[1] as $match) {
                $row['text'] = str_replace($match, url_proxy($match, true), $row['text']);
            }
            $xml .= '<message';
            $xml .= ' id="' . (int) $row['id'] . '"';
            $xml .= ' dateTime="' . date('r', (int) $row['timeStamp']) . '"';
            $xml .= ' userID="' . (int) $row['userID'] . '"';
            $xml .= ' userRole="' . (int) $row['userRole'] . '"';
            $xml .= ' channelID="' . (int) $row['channelID'] . '"';
            $xml .= '>';
            $xml .= '<username><![CDATA[' . $this->encodeSpecialChars($row['userName']) . ']]></username>';
            $xml .= '<text><![CDATA[' . $this->encodeSpecialChars($row['text']) . ']]></text>';
            $xml .= '</message>';
        }

        $xml .= '</messages>';

        return $xml;
    }

    /**
     * @throws \Envms\FluentPDO\Exception
     * @throws AuthError
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotLoggedInException
     *
     * @return string
     */
    public function getLogsViewCondition()
    {
        $condition = 'id >' . sqlesc($this->getRequestVar('lastID'));

        switch ($this->getRequestVar('channelID')) {
            case '-3':
                if ($this->getUserRole() <= UC_STAFF) {
                    $condition .= ' AND (channel = ' . sqlesc($this->getPrivateMessageID());
                    $condition .= ' OR channel = ' . sqlesc($this->getPrivateChannelID());
                    foreach ($this->getChannels() as $channel) {
                        if ($this->getConfig('logsUserAccessChannelList') && !in_array($channel, $this->getConfig('logsUserAccessChannelList'))) {
                            continue;
                        }
                        $condition .= ' OR channel = ' . sqlesc($channel);
                    }
                    $condition .= ')';
                }
                break;
            case '-2':
                if ($this->getUserRole() <= UC_STAFF) {
                    $condition .= ' AND channel = ' . ($this->getPrivateMessageID());
                } else {
                    $condition .= ' AND channel>' . ($this->getConfig('privateMessageDiff') - 1);
                }
                break;
            case '-1':
                if ($this->getUserRole() <= UC_STAFF) {
                    $condition .= ' AND channel = ' . ($this->getPrivateChannelID());
                } else {
                    $condition .= ' AND (channel>' . ($this->getConfig('privateChannelDiff') - 1) . ' AND channel < ' . ($this->getConfig('privateMessageDiff')) . ')';
                }
                break;
            default:
                if ((has_access((int) $this->getUserRole(), UC_ADMINISTRATOR, 'coder') || !$this->getConfig('logsUserAccessChannelList') || in_array($this->getRequestVar('channelID'), $this->getConfig('logsUserAccessChannelList'))) && $this->validateChannel($this->getRequestVar('channelID'))) {
                    $condition .= ' AND channel = ' . sqlesc($this->getRequestVar('channelID'));
                } else {
                    $condition .= ' AND 0 = 1';
                }
        }

        $hour = ($this->getRequestVar('hour') === null || $this->getRequestVar('hour') > 23 || $this->getRequestVar('hour') < 0) ? null : $this->getRequestVar('hour');
        $day = ($this->getRequestVar('day') === null || $this->getRequestVar('day') > 31 || $this->getRequestVar('day') < 1) ? null : $this->getRequestVar('day');
        $month = ($this->getRequestVar('month') === null || $this->getRequestVar('month') > 12 || $this->getRequestVar('month') < 1) ? null : $this->getRequestVar('month');
        $year = ($this->getRequestVar('year') === null || $this->getRequestVar('year') > date('Y') || $this->getRequestVar('year') < $this->getConfig('logsFirstYear')) ? null : $this->getRequestVar('year');

        if ($hour !== null) {
            if ($day === null) {
                $day = (int) date('j');
            }
            if ($month === null) {
                $month = (int) date('n');
            }
            if ($year === null) {
                $year = (int) date('Y');
            }
        }

        $periodStart = TIME_NOW - 86400;
        $periodEnd = TIME_NOW;
        if ($year === null) {
            // No year given, so no period condition
        } elseif ($month === null) {
            // Define the given year as period:
            $periodStart = mktime(0, 0, 0, 1, 1, $year);
            // The last day in a month can be expressed by using 0 for the day of the next month:
            $periodEnd = mktime(23, 59, 59, 13, 0, $year);
        } elseif ($day === null) {
            // Define the given month as period:
            $periodStart = mktime(0, 0, 0, $month, 1, $year);
            // The last day in a month can be expressed by using 0 for the day of the next month:
            $periodEnd = mktime(23, 59, 59, $month + 1, 0, $year);
        } elseif ($hour === null) {
            // Define the given day as period:
            $periodStart = mktime(0, 0, 0, $month, $day, $year);
            $periodEnd = mktime(23, 59, 59, $month, $day, $year);
        } else {
            // Define the given hour as period:
            $periodStart = mktime($hour, 0, 0, $month, $day, $year);
            $periodEnd = mktime($hour, 59, 59, $month, $day, $year);
        }

        if (isset($periodStart)) {
            $condition .= ' AND dateTime > \'' . date('Y-m-d H:i:s', $periodStart) . '\' AND dateTime <= \'' . date('Y-m-d H:i:s', $periodEnd) . '\'';
        }

        // Check the search condition:
        if ($this->getRequestVar('search')) {
            if (($this->getUserRole() >= UC_STAFF) && strpos($this->getRequestVar('search'), 'ip=') === 0) {
                // Search for messages with the given IP:
                $ip = substr($this->getRequestVar('search'), 3);
                $condition .= ' AND (INET6_NTOA(ip) = ' . sqlesc($ip) . ')';
            } elseif (strpos($this->getRequestVar('search'), 'userID=') === 0) {
                // Search for messages with the given userID:
                $userID = substr($this->getRequestVar('search'), 7);
                $condition .= ' AND (userID = ' . sqlesc($userID) . ')';
            } else {
                // Use the search value as regular expression on message text and username:
                $condition .= ' AND (userName REGEXP ' . sqlesc($this->getRequestVar('search')) . ' OR text REGEXP ' . sqlesc($this->getRequestVar('search')) . ')';
            }
        }

        // If no period or search condition is given, just monitor the last messages on the given channel:
        if (!isset($periodStart) && !$this->getRequestVar('search')) {
            $condition .= ' AND NOW() < DATE_ADD(dateTime, interval ' . $this->getConfig('logsRequestMessagesTimeDiff') . ' HOUR)';
        }

        return $condition;
    }

    /**
     * @return string
     */
    public function getLogoutXMLMessage()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<root>';
        $xml .= '<infos>';
        $xml .= '<info type="logout">';
        $xml .= '<![CDATA[' . $this->encodeSpecialChars($this->getConfig('logoutData')) . ']]>';
        $xml .= '</info>';
        $xml .= '</infos>';
        $xml .= '</root>';

        return $xml;
    }

    public function sendXHTMLContent()
    {
        $httpHeader = new AJAXChatHTTPHeader($this->getConfig('contentEncoding'), $this->getConfig('contentType'));

        $template = new AJAXChatTemplate($this, $this->getTemplateFileName(), $httpHeader->getContentType());

        $httpHeader->send();

        echo $template->getParsedContent();
    }

    /**
     * @return string
     */
    public function getTemplateFileName()
    {
        switch ($this->getView()) {
            case 'chat':
                return AJAX_CHAT_PATH . 'lib' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'loggedIn.html';
            case 'logs':
                return AJAX_CHAT_PATH . 'lib' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'logs.html';
            default:
                return AJAX_CHAT_PATH . 'lib' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'loggedOut.html';
        }
    }

    /**
     * @param $name
     * @param $arguments
     */
    public static function __callStatic($name, $arguments)
    {
        // TODO: Implement __callStatic() method.
    }

    /**
     * @return mixed|null
     */
    public function getSessionIP()
    {
        return $this->_auth->getIpAddress();
    }

    /**
     * @return mixed
     */
    public function getRequestVars()
    {
        return $this->_requestVars;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setRequestVar($key, $value)
    {
        if (!$this->_requestVars) {
            $this->_requestVars = [];
        }
        $this->_requestVars[$key] = $value;
    }

    /**
     * @param null $channelIDs
     *
     * @throws \Envms\FluentPDO\Exception
     *
     * @return array|null
     */
    public function getOnlineUserIDs($channelIDs = null)
    {
        return $this->getOnlineUsersData($channelIDs, 'userID');
    }

    /**
     * @return mixed|null
     */
    public function getLoginTimeStamp()
    {
        return $this->_session->get('LoginTimeStamp');
    }

    /**
     * @param $userName
     *
     * @return bool|mixed|string
     */
    public function trimUserName($userName)
    {
        return $this->trimString($userName, null, $this->getConfig('userNameMaxLength'), true, true);
    }

    /**
     * @param      $str
     * @param null $contentEncoding
     *
     * @return mixed|string
     */
    public function convertFromUnicode($str, $contentEncoding = null)
    {
        if ($contentEncoding === null) {
            $contentEncoding = $this->getConfig('contentEncoding');
        }

        return $this->convertEncoding($str, 'UTF-8', $contentEncoding);
    }

    /**
     * @param        $str
     * @param string $encoding
     * @param null   $convmap
     *
     * @return string
     */
    public function encodeEntities($str, $encoding = 'UTF-8', $convmap = null)
    {
        return AJAXChatEncoding::encodeEntities($str, $encoding, $convmap);
    }

    /**
     * @param $str
     *
     * @return mixed|string
     */
    public function htmlEncode($str)
    {
        return AJAXChatEncoding::htmlEncode((string) $str, $this->getConfig('contentEncoding'));
    }

    /**
     * @param $str
     *
     * @return string
     */
    public function decodeSpecialChars($str)
    {
        return AJAXChatEncoding::decodeSpecialChars($str);
    }

    /**
     * @param null $key
     *
     * @return array|mixed|null
     */
    public function getLang($key = null)
    {
        if (!$this->_lang) {
            // Include the language file:
            $lang = null;
            $file = AJAX_CHAT_PATH . 'lib' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $this->getLangCode() . '.php';
            require_once $file;
            $this->_lang = &$lang;
        }
        if ($key === null) {
            return $this->_lang;
        }
        if (isset($this->_lang[$key])) {
            return $this->_lang[$key];
        }

        return null;
    }

    /**
     * @return mixed|null
     */
    public function getLangCode()
    {
        $language = new AJAXChatLanguage($this->getConfig('langAvailable'), $this->getConfig('langDefault'));
        $langCode = $language->getLangCode();

        return $langCode;
    }

    /**
     * @return string
     */
    public function getChatURL()
    {
        if (defined('AJAX_CHAT_URL')) {
            return AJAX_CHAT_URL;
        }

        return (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . (isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'] . '@' : '') . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'] . (isset($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] == 443 || $_SERVER['SERVER_PORT'] == 80 ? '' : ':' . $_SERVER['SERVER_PORT']))) . substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/') + 1);
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    public function getCustomVar($key)
    {
        if (!isset($this->_customVars)) {
            $this->_customVars = [];
        }
        if (!isset($this->_customVars[$key])) {
            return null;
        }

        return $this->_customVars[$key];
    }

    /**
     * @param $key
     * @param $value
     */
    public function setCustomVar($key, $value)
    {
        if (!isset($this->_customVars)) {
            $this->_customVars = [];
        }
        $this->_customVars[$key] = $value;
    }

    /**
     * @param $tag
     * @param $tagContent
     *
     * @return null
     */
    public function replaceCustomTemplateTags($tag, $tagContent)
    {
        return null;
    }
}
