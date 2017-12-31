<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */

/**
 * Class AJAXChat
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
    protected $_sessionNew;
    protected $_onlineUsersData;
    protected $_bannedUsersData;

    /**
     * AJAXChat constructor.
     */
    public function __construct()
    {
        $this->initialize();
    }

    public function initialize()
    {
        // Initialize the messages direction
        $this->postDirection = false;

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

    public function initCustomConfig()
    {
        check_user_status();
    }

    public function initDataBaseConnection()
    {
        $this->db = new AJAXChatDataBase(
            $this->_config['dbConnection']
        );
        if (!$this->_config['dbConnection']['link']) {
            $this->db->connect($this->_config['dbConnection']);
            if ($this->db->error()) {
                echo $this->db->getError();
                die();
            }
            $this->db->select($this->_config['dbConnection']['name']);
            if ($this->db->error()) {
                echo $this->db->getError();
                die();
            }
        }
        unset($this->_config['dbConnection']);
    }

    public function initRequestVars()
    {
        $this->_requestVars = [];
        $this->_requestVars['ajax'] = isset($_REQUEST['ajax']) ? true : false;
        $this->_requestVars['userID'] = isset($_REQUEST['userID']) ? (int)$_REQUEST['userID'] : null;
        $this->_requestVars['userName'] = isset($_REQUEST['userName']) ? $_REQUEST['userName'] : null;
        $this->_requestVars['channelID'] = isset($_REQUEST['channelID']) ? (int)$_REQUEST['channelID'] : null;
        $this->_requestVars['channelName'] = isset($_REQUEST['channelName']) ? $_REQUEST['channelName'] : null;
        $this->_requestVars['text'] = isset($_POST['text']) ? $_POST['text'] : null;
        $this->_requestVars['lastID'] = isset($_REQUEST['lastID']) ? (int)$_REQUEST['lastID'] : 0;
        $this->_requestVars['login'] = isset($_REQUEST['login']) ? true : false;
        $this->_requestVars['logout'] = isset($_REQUEST['logout']) ? true : false;
        $this->_requestVars['password'] = isset($_REQUEST['password']) ? $_REQUEST['password'] : null;
        $this->_requestVars['view'] = isset($_REQUEST['view']) ? $_REQUEST['view'] : null;
        $this->_requestVars['year'] = isset($_REQUEST['year']) ? (int)$_REQUEST['year'] : null;
        $this->_requestVars['month'] = isset($_REQUEST['month']) ? (int)$_REQUEST['month'] : null;
        $this->_requestVars['day'] = isset($_REQUEST['day']) ? (int)$_REQUEST['day'] : null;
        $this->_requestVars['hour'] = isset($_REQUEST['hour']) ? (int)$_REQUEST['hour'] : null;
        $this->_requestVars['search'] = isset($_REQUEST['search']) ? $_REQUEST['search'] : null;
        $this->_requestVars['getInfos'] = isset($_REQUEST['getInfos']) ? $_REQUEST['getInfos'] : null;
        $this->_requestVars['lang'] = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : null;
        $this->_requestVars['delete'] = isset($_REQUEST['delete']) ? (int)$_REQUEST['delete'] : null;
        $this->_requestVars['token'] = isset($_REQUEST['token']) ? $_REQUEST['token'] : null;

        if (isset($_COOKIE[ $this->getConfig('sessionKeyPrefix') . 'settings' ])) {
            $cookies = explode('&', $_COOKIE[ $this->getConfig('sessionKeyPrefix') . 'settings' ]);
            foreach ($cookies as $cookie) {
                $split = explode('=', $cookie);
                if ($split[0] === 'postDirection') {
                    $this->postDirection = $split[1] == 'true' ? true : false;
                    break;
                }
            }
        }

        $this->initCustomRequestVars();
    }

    public function initSession()
    {
        $this->startSession();

        if ($this->isLoggedIn()) {
            // Logout if the Session IP is not the same when logged in and ipCheck is enabled:
            if ($this->getConfig('ipCheck') && ($this->getSessionIP() === null || $this->getSessionIP() != $_SERVER['REMOTE_ADDR'])) {
                $this->logout('IP');

                return;
            }

            if ($this->getRequestVar('logout') && $this->getRequestVar('token') == session_id() || !$this->isChatOpen() || !$this->revalidateUserID()) {
                $this->logout();

                return;
            }
        } elseif ($this->getConfig('forceAutoLogin') || $this->getRequestVar('login') || $this->getRequestVar('userName')) {
            $this->login();
        }

        $this->initView();

        if ($this->getView() == 'chat') {
            $this->initChatViewSession();
        } elseif ($this->getView() == 'logs') {
            $this->initLogsViewSession();
        }

        if (!$this->getRequestVar('ajax') && !headers_sent()) {
            $this->setStyle();
            $this->setLangCodeCookie();
        }

        $this->initCustomSession();
    }

    public function startSession()
    {
        if (!session_id()) {
            sessionStart();

            $this->_sessionNew = true;
        }
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
            return $this->_config[ $key ][ $subkey ];
        } else {
            return $this->_config[ $key ];
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
            if (!isset($this->_config[ $key ])) {
                $this->_config[ $key ] = [];
            }
            $this->_config[ $key ][ $subkey ] = $value;
        } else {
            $this->_config[ $key ] = $value;
        }
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return (bool)getSessionVar('LoggedIn');
    }

    /**
     * @return null
     */
    public function getSessionIP()
    {
        return getSessionVar('IP');
    }

    /**
     * @param null $type
     */
    public function logout($type = null)
    {
        if ($this->getConfig('socketServerEnabled')) {
            $this->updateSocketAuthentication($this->getUserID());
        }
        if ($this->isUserOnline()) {
            $this->chatViewLogout($type);
        }
        $this->setLoggedIn(false);

        $this->initView();
    }

    /**
     * @param      $userID
     * @param null $socketRegistrationID
     * @param null $channels
     */
    public function updateSocketAuthentication($userID, $socketRegistrationID = null, $channels = null)
    {
        $authentication = '<authenticate chatID="' . $this->getConfig('socketServerChatID') . '" userID="' . $userID . '" regID="' . $socketRegistrationID . '">';
        if ($channels) {
            foreach ($channels as $channelID) {
                $authentication .= '<channel id="' . $channelID . '"/>';
            }
        }
        $authentication .= '</authenticate>';
        $this->sendSocketMessage($authentication);
    }

    /**
     * @param $message
     */
    public function sendSocketMessage($message)
    {
        if ($socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) {
            if (@socket_connect($socket, $this->getConfig('socketServerIP'), $this->getConfig('socketServerPort'))) {
                $message .= "\0";
                @socket_write(
                    $socket,
                    $message,
                    strlen($message)
                );
            }
            @socket_close($socket);
        }
    }

    /**
     * @return null
     */
    public function getUserID()
    {
        return (int)getSessionVar('userID');
    }

    /**
     * @param null $userID
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
     * @return array|null
     */
    public function getOnlineUsersData($channelIDs = null, $key = null, $value = null)
    {
        global $fluent;
        if ($this->_onlineUsersData === null) {
            $this->_onlineUsersData = [];

            $sql = $fluent->from($this->getDataBaseTable('online'))
                ->select(null)
                ->select('userID')
                ->select('userName')
                ->select('userRole')
                ->select('channel')
                ->select('UNIX_TIMESTAMP(dateTime) AS timeStamp')
                ->select('INET6_NTOA(ip)')
                ->orderBy('userRole DESC')
                ->orderBy('LOWER(userName) ASC');

            foreach ($sql as $row) {
                $row['pmCount'] = getPMCount($row['userID']);
                array_push($this->_onlineUsersData, $row);
            }
        }

        if ($channelIDs || $key) {
            $onlineUsersData = [];
            foreach ($this->_onlineUsersData as $userData) {
                if ($channelIDs && !in_array($userData['channel'], $channelIDs)) {
                    continue;
                }
                if ($key) {
                    if (!isset($userData[ $key ])) {
                        return $onlineUsersData;
                    }
                    if ($value !== null) {
                        if ($userData[ $key ] == $value) {
                            array_push($onlineUsersData, $userData);
                        } else {
                            continue;
                        }
                    } else {
                        array_push($onlineUsersData, $userData[ $key ]);
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
     * @param $table
     *
     * @return string
     */
    public function getDataBaseTable($table)
    {
        if ($table === 'online' || $table === 'bans') {
            return $this->getConfig('dbTableNames', $table);
        }
        return $this->db->getName() ? '`' . $this->db->getName() . '`.' . $this->getConfig('dbTableNames', $table) : $this->getConfig('dbTableNames', $table);
    }

    /**
     * @param $type
     */
    public function chatViewLogout($type)
    {
        $this->removeFromOnlineList($this->getUserID());
    }

    /**
     * @param $userID
     */
    public function removeFromOnlineList($userID)
    {
        global $fluent;

        $fluent->deleteFrom($this->getDataBaseTable('online'))
            ->where('userID = ?', $this->getUserID())
            ->execute();

        if ($this->getConfig('socketServerEnabled')) {
            $this->updateSocketAuthentication($userID);
        }

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
        for ($i = 0; $i < count($this->_onlineUsersData); ++$i) {
            if ($this->_onlineUsersData[ $i ]['userID'] == $userID) {
                array_splice($this->_onlineUsersData, $i, 1);
                break;
            }
        }
    }

    /**
     * @return null
     */
    public function getUserName()
    {
        return getSessionVar('UserName');
    }

    /**
     * @param      $channelID
     * @param      $messageText
     * @param int  $mode
     * @param int  $ttl
     *
     * @throws Exception
     */
    public function insertChatBotMessage($channelID, $messageText, $mode = 0, $ttl = 300)
    {
        $this->insertCustomMessage(
            $this->getConfig('chatBotID'),
            $this->getConfig('chatBotName'),
            AJAX_CHAT_CHATBOT,
            $channelID,
            $messageText,
            $mode,
            $ttl
        );
    }

    /**
     * @param     $userID
     * @param     $userName
     * @param     $userRole
     * @param     $channelID
     * @param     $text
     * @param int $mode
     * @param int $ttl
     *
     * @throws Exception
     */
    public function insertCustomMessage($userID, $userName, $userRole, $channelID, $text, $mode = 0, $ttl = 0)
    {
        global $fluent, $pdo;

        // The $mode parameter is used for socket updates:
        // 0 = normal messages
        // 1 = channel messages (e.g. login/logout, channel enter/leave, kick)
        // 2 = messages with online user updates (nick)

        $bot_only = [2, 3, 4]; // Announce, News, Git
        if (in_array($channelID, $bot_only) && $userRole != 100) {
            return;
        }

        $values = [
            'userID'   => $userID,
            'userName' => $userName,
            'userRole' => $userRole,
            'channel'  => $channelID,
            'dateTime' => gmdate("Y-m-d H:i:s", TIME_NOW),
            'ip'       => getip(),
            'text'     => $text,
            'ttl'      => $ttl,
        ];

        $stmt = $pdo->prepare(
            'INSERT INTO ' . $this->getDataBaseTable('messages') . '
                        (userID, userName, userRole, channel, dateTime, ip, text, ttl)
                      VALUES
                        (:userID, :userName, :userRole, :channel, :dateTime, INET6_ATON(:ip), :text, :ttl)'
        );
        $stmt->execute($values);
        $lastInsertId = $pdo->lastInsertId();

        $set = [
            'dailyshouts'   => new Envms\FluentPDO\Literal('dailyshouts + 1'),
            'weeklyshouts'  => new Envms\FluentPDO\Literal('weeklyshouts + 1'),
            'monthlyshouts' => new Envms\FluentPDO\Literal('monthlyshouts + 1'),
            'totalshouts'   => new Envms\FluentPDO\Literal('totalshouts + 1'),
        ];

        $fluent->update('usersachiev')
            ->set($set)
            ->where('userid = ?', $userID)
            ->execute();

        if ($this->getConfig('socketServerEnabled')) {
            $this->sendSocketMessage(
                $this->getSocketBroadcastMessage(
                    $lastInsertId,
                    TIME_NOW,
                    $userID,
                    $userName,
                    $userRole,
                    $channelID,
                    $text,
                    $mode
                )
            );
        }
    }

    /**
     * @param $messageID
     * @param $timeStamp
     * @param $userID
     * @param $userName
     * @param $userRole
     * @param $channelID
     * @param $text
     * @param $mode
     *
     * @return string
     */
    public function getSocketBroadcastMessage(
        $messageID,
        $timeStamp,
        $userID,
        $userName,
        $userRole,
        $channelID,
        $text,
        $mode
    )
    {
        // The $mode parameter:
        // 0 = normal messages
        // 1 = channel messages (e.g. login/logout, channel enter/leave, kick)
        // 2 = messages with online user updates (nick)

        // Get the message XML content:
        $xml = '<root chatID="' . $this->getConfig('socketServerChatID') . '" channelID="' . $channelID . '" mode="' . $mode . '">';
        if ($mode) {
            // Add the list of online users if the user list has been updated ($mode > 0):
            $xml .= $this->getChatViewOnlineUsersXML([$channelID]);
        }
        if ($mode != 1 || $this->getConfig('showChannelMessages')) {
            $xml .= '<messages>';
            $xml .= $this->getChatViewMessageXML(
                $messageID,
                $timeStamp,
                $userID,
                $userName,
                $userRole,
                $channelID,
                $text
            );
            $xml .= '</messages>';
        }
        $xml .= '</root>';

        return $xml;
    }

    /**
     * @param $channelIDs
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
     * @param $str
     *
     * @return string
     */
    public function encodeSpecialChars($str)
    {
        return AJAXChatEncoding::encodeSpecialChars($str);
    }

    /**
     * @param $messageID
     * @param $timeStamp
     * @param $userID
     * @param $userName
     * @param $userRole
     * @param $channelID
     * @param $text
     *
     * @return string
     */
    public function getChatViewMessageXML(
        $messageID,
        $timeStamp,
        $userID,
        $userName,
        $userRole,
        $channelID,
        $text
    )
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
     * @return null
     */
    public function getChannel()
    {
        return getSessionVar('Channel');
    }

    /**
     * @param $bool
     */
    public function setLoggedIn($bool)
    {
        setSessionVar('LoggedIn', $bool);
    }

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
     * @param $key
     *
     * @return null
     */
    public function getRequestVar($key)
    {
        if ($this->_requestVars && isset($this->_requestVars[ $key ])) {
            return $this->_requestVars[ $key ];
        }

        return null;
    }

    /**
     * @param $view
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
                if ($this->isLoggedIn() && (
                        $this->getUserRole() >= UC_ADMINISTRATOR ||
                        ($this->getConfig('logsUserAccess') && ($this->getUserRole() >= UC_USER))
                    )) {
                    return true;
                }

                return false;
            default:
                return false;
        }
    }

    /**
     * @return null
     */
    public function getUserRole()
    {
        $userRole = getSessionVar('UserRole');
        if ($userRole === null) {
            userlogin();
        }

        return $userRole;
    }

    /**
     * @return bool
     */
    public function isChatOpen()
    {
        if ($this->getUserRole() >= UC_ADMINISTRATOR) {
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
        } else {
            if (($this->getConfig('openingHour') > date('G', $time)) && ($this->getConfig('closingHour') <= date('G', $time))) {
                return false;
            }
        }

        if (!in_array(date('w', $time), $this->getConfig('openingWeekDays'))) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function revalidateUserID()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function login()
    {
        $userData = $this->getValidLoginUserData();

        if (!$userData) {
            unsetSessionVar('Channel');
            $this->addInfoMessage('errorInvalidUser');

            return false;
        }

        if (!$this->isChatOpen() && $userData['userRole'] <= UC_ADMINISTRATOR) {
            unsetSessionVar('Channel');
            $this->addInfoMessage('errorChatClosed');

            return false;
        }

        if ($this->isUserOnline($userData['userID']) || $this->isUserNameInUse($userData['userName'])) {
            if ($userData['userRole'] >= UC_USER) {
                $this->setInactive($userData['userID'], $userData['userName']);
                $this->removeInactive();
            } else {
                unsetSessionVar('Channel');
                $this->addInfoMessage('errorUserInUse');

                return false;
            }
        }

        if ($userData['userRole'] < UC_MAX && $this->isUserBanned($userData['userName'], $userData['userID'], $_SERVER['REMOTE_ADDR'])) {
            unsetSessionVar('Channel');
            $this->addInfoMessage('errorBanned');

            return false;
        }

        if (($userData['userRole'] < UC_STAFF) && $this->isMaxUsersLoggedIn()) {
            unsetSessionVar('Channel');
            $this->addInfoMessage('errorMaxUsersLoggedIn');

            return false;
        }

        $this->setUserID($userData['userID']);
        $this->setUserName($userData['userName']);
        $this->setLoginUserName($userData['userName']);
        $this->setUserRole($userData['userRole']);
        $this->setLoggedIn(true);
        $this->setLoginTimeStamp(TIME_NOW);

        $this->setSessionIP($_SERVER['REMOTE_ADDR']);

        if ($this->getConfig('socketServerEnabled')) {
            $this->setSocketRegistrationID(
                md5(uniqid(random_int(), true))
            );
        }

        $this->addInfoMessage($this->getUserID(), 'userID');
        $this->addInfoMessage($this->getUserName(), 'userName');
        $this->addInfoMessage($this->getUserRole(), 'userRole');

        // Purge logs:
        if ($this->getConfig('logsPurgeLogs')) {
            $this->purgeLogs();
        }

        return true;
    }

    public function getValidLoginUserData()
    {
        userlogin();
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
     * @param        $info
     * @param string $type
     */
    public function addInfoMessage($info, $type = 'error')
    {
        if (!isset($this->_infoMessages)) {
            $this->_infoMessages = [];
        }
        if (!isset($this->_infoMessages[ $type ])) {
            $this->_infoMessages[ $type ] = [];
        }
        if (!in_array($info, $this->_infoMessages[ $type ])) {
            array_push($this->_infoMessages[ $type ], $info);
        }
    }

    /**
     * @param null $userName
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
     * @param $userID
     * @param $userName
     *
     * @throws Exception
     */
    public function setInactive($userID, $userName)
    {
        global $fluent;

        $set = [
            'dateTime' => gmdate("Y-m-d H:i:s", TIME_NOW - ($this->getConfig('inactiveTimeout') * 60) - 60),
        ];

        $fluent->update($this->getDataBaseTable('online'))
            ->set($set)
            ->where('userId = ?', $userID)
            ->where('userName = ?', $userName)
            ->execute();

        $this->resetOnlineUsersData();
    }

    public function resetOnlineUsersData()
    {
        $this->_onlineUsersData = null;
    }

    public function removeInactive()
    {
        $sql = 'SELECT
                    userID,
                    userName,
                    channel
                FROM
                    ' . $this->getDataBaseTable('online') . '
                WHERE
                    NOW() > DATE_ADD(dateTime, INTERVAL ' . $this->getConfig('inactiveTimeout') . ' MINUTE);';

        // Create a new SQL query:
        $result = sql_query($sql) or sqlerr(__FILE__, __LINE__);

        if (mysqli_num_rows($result) > 0) {
            $condition = '';

            while ($row = mysqli_fetch_array($result)) {
                if (!empty($condition)) {
                    $condition .= ' OR ';
                }
                // Add userID to condition for removal:
                $condition .= 'userID=' . sqlesc($row['userID']);

                // Update the socket server authentication for the kicked user:
                if ($this->getConfig('socketServerEnabled')) {
                    $this->updateSocketAuthentication($row['userID']);
                }

                $this->removeUserFromOnlineUsersData($row['userID']);
            }

            $sql = 'DELETE FROM
                        ' . $this->getDataBaseTable('online') . '
                    WHERE
                        ' . $condition . ';';

            // Create a new SQL query:
            sql_query($sql) or sqlerr(__FILE__, __LINE__);
        }
    }

    /**
     * @param      $userName
     * @param null $userID
     * @param null $ip
     *
     * @return bool
     */
    public function isUserBanned($userName, $userID = null, $ip = null)
    {
        if ($userID !== null) {
            $bannedUserDataArray = $this->getBannedUsersData('userID', $userID);
            if ($bannedUserDataArray && isset($bannedUserDataArray[0])) {
                return true;
            }
        }
        if ($ip !== null) {
            $bannedUserDataArray = $this->getBannedUsersData('ip', $ip);
            if ($bannedUserDataArray && isset($bannedUserDataArray[0])) {
                return true;
            }
        }
        $bannedUserDataArray = $this->getBannedUsersData('userName', $userName);
        if ($bannedUserDataArray && isset($bannedUserDataArray[0])) {
            return true;
        }

        return false;
    }

    /**
     * @param null $key
     * @param null $value
     *
     * @return array|null
     */
    public function getBannedUsersData($key = null, $value = null)
    {
        global $fluent;

        if ($this->_bannedUsersData === null) {
            $this->_bannedUsersData = [];

            $res = $fluent->from($this->getDataBaseTable('bans'))
                ->select(null)
                ->select('userID')
                ->select('userName')
                ->select('INET6_NTOA(ip)')
                ->where('dateTime > NOW()');

            foreach ($res as $row) {
                array_push($this->_bannedUsersData, $row);
            }
        }

        if ($key) {
            $bannedUsersData = [];
            foreach ($this->_bannedUsersData as $bannedUserData) {
                if (!isset($bannedUserData[ $key ])) {
                    return $bannedUsersData;
                }
                if ($value) {
                    if ($bannedUserData[ $key ] == $value) {
                        array_push($bannedUsersData, $bannedUserData);
                    } else {
                        continue;
                    }
                } else {
                    array_push($bannedUsersData, $bannedUserData[ $key ]);
                }
            }

            return $bannedUsersData;
        }

        return $this->_bannedUsersData;
    }

    /**
     * @return bool
     */
    public function isMaxUsersLoggedIn()
    {
        if (count($this->getOnlineUsersData()) >= $this->getConfig('maxUsersLoggedIn')) {
            return true;
        }

        return false;
    }

    /**
     * @param $id
     */
    public function setUserID($id)
    {
        setSessionVar('UserID', $id);
    }

    /**
     * @param $name
     */
    public function setUserName($name)
    {
        setSessionVar('UserName', $name);
    }

    /**
     * @param $name
     */
    public function setLoginUserName($name)
    {
        setSessionVar('LoginUserName', $name);
    }

    /**
     * @param $role
     */
    public function setUserRole($role)
    {
        setSessionVar('UserRole', $role);
    }

    /**
     * @param $time
     */
    public function setLoginTimeStamp($time)
    {
        setSessionVar('LoginTimeStamp', $time);
    }

    /**
     * @param $ip
     */
    public function setSessionIP($ip)
    {
        setSessionVar('IP', $ip);
    }

    /**
     * @param $value
     */
    public function setSocketRegistrationID($value)
    {
        setSessionVar('SocketRegistrationID', $value);
    }

    public function purgeLogs()
    {
        global $fluent;

        $fluent->deleteFrom($this->getDataBaseTable('messages'))
            ->where('dateTime < ?', gmdate("Y-m-d H:i:s", TIME_NOW - ($this->getConfig('logsPurgeTimeDiff') * 86400)))
            ->execute();
    }

    public function getView()
    {
        return $this->_view;
    }

    public function initChatViewSession()
    {
        if ($this->getChannel() !== null) {
            if (!$this->isUserOnline()) {
                $this->logout();

                return;
            }
            if ($this->getRequestVar('ajax')) {
                $this->initChannel();
                $this->updateOnlineStatus();
                $this->checkAndRemoveInactive();
            }
        } else {
            if ($this->getRequestVar('ajax')) {
                $this->chatViewLogin();
            }
        }
    }

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
     */
    public function switchChannel($channelName)
    {
        $channelID = $this->getChannelIDFromChannelName($channelName);

        if ($channelID !== null && $this->getChannel() == $channelID) {
            return;
        }

        if (!$this->validateChannel($channelID)) {
            $text = '/error InvalidChannelName ' . $channelName;
            $this->insertChatBotMessage(
                $this->getPrivateMessageID(),
                $text
            );

            return;
        }

        $oldChannel = $this->getChannel();

        $this->setChannel($channelID);
        $this->updateOnlineList();

        $this->addInfoMessage($channelName, 'channelSwitch');
        $this->addInfoMessage($channelID, 'channelID');
        $this->_requestVars['lastID'] = 0;
    }

    /**
     * @param $channelName
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
            return $channels[ $channelName ];
        }
        $channelID = null;
        if ($channelName == $this->getPrivateChannelName()) {
            return $this->getPrivateChannelID();
        }
        $strlenChannelName = $this->stringLength($channelName);
        $strlenPrefix = $this->stringLength($this->getConfig('privateChannelPrefix'));
        $strlenSuffix = $this->stringLength($this->getConfig('privateChannelSuffix'));
        if ($this->subString($channelName, 0, $strlenPrefix) == $this->getConfig('privateChannelPrefix')
            && $this->subString($channelName, $strlenChannelName - $strlenSuffix) == $this->getConfig('privateChannelSuffix')) {
            $userName = $this->subString(
                $channelName,
                $strlenPrefix,
                $strlenChannelName - ($strlenPrefix + $strlenSuffix)
            );
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

            $this->_allChannels[ $this->trimChannelName($this->getConfig('defaultChannelName')) ] = $this->getConfig('defaultChannelID');
        }

        return $this->_allChannels;
    }

    /**
     * @param $channelName
     *
     * @return bool|mixed|string
     */
    public function trimChannelName($channelName)
    {
        return $this->trimString($channelName, null, null, true, true);
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
     * @return null
     */
    public function getPrivateChannelID($userID = null)
    {
        if ($userID === null) {
            $userID = $this->getUserID();
        }

        return $userID + $this->getConfig('privateChannelDiff');
    }

    /**
     * @param $userName
     *
     * @return null
     */
    public function getIDFromName($userName)
    {
        $userDataArray = $this->getOnlineUsersData(null, 'userName', $userName);
        if ($userDataArray && isset($userDataArray[0])) {
            return $userDataArray[0]['userID'];
        }

        $userDataArray = $this->getBannedUsersData('userName', $userName);
        if ($userDataArray && isset($userDataArray[0])) {
            return $userDataArray[0]['userID'];
        }

        return null;
    }

    /**
     * @param $channelID
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
     * @return bool
     */
    public function isAllowedToCreatePrivateChannel()
    {
        if ($this->getConfig('allowPrivateChannels') && $this->getUserRole() >= UC_USER) {
            return true;
        }

        return false;
    }

    /**
     * @return array|null
     */
    public function getInvitations()
    {
        if ($this->_invitations === null) {
            $this->_invitations = [];

            $sql = 'SELECT
                        channel
                    FROM
                        ' . $this->getDataBaseTable('invitations') . '
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
     * @param null $userID
     *
     * @return null
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
        setSessionVar('Channel', $channel);

        $this->setChannelEnterTimeStamp(TIME_NOW);

        if ($this->getConfig('socketServerEnabled')) {
            $this->updateSocketAuthentication(
                $this->getUserID(),
                $this->getSocketRegistrationID(),
                [$channel, $this->getPrivateMessageID()]
            );
        }

        if (getSessionVar('logsViewSocketAuthenticated')) {
            setSessionVar('logsViewSocketAuthenticated', false);
        }
    }

    /**
     * @param $time
     */
    public function setChannelEnterTimeStamp($time)
    {
        setSessionVar('ChannelEnterTimeStamp', $time);
    }

    /**
     * @return null
     */
    public function getSocketRegistrationID()
    {
        return getSessionVar('SocketRegistrationID');
    }

    public function updateOnlineList()
    {
        $this->addToOnlineList();
    }

    /**
     * @param $channelID
     *
     * @return int|null|string
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
     * @return null
     */
    public function getNameFromID($userID)
    {
        $userDataArray = $this->getOnlineUsersData(null, 'userID', $userID);
        if ($userDataArray && isset($userDataArray[0])) {
            return $userDataArray[0]['userName'];
        }

        return null;
    }

    public function updateOnlineStatus()
    {
        if (!$this->getStatusUpdateTimeStamp() || ((TIME_NOW - $this->getStatusUpdateTimeStamp()) > 50)) {
            $this->updateOnlineList();
            $this->setStatusUpdateTimeStamp(TIME_NOW);
        }
    }

    /**
     * @return null
     */
    public function getStatusUpdateTimeStamp()
    {
        return getSessionVar('StatusUpdateTimeStamp');
    }

    /**
     * @param $time
     */
    public function setStatusUpdateTimeStamp($time)
    {
        setSessionVar('StatusUpdateTimeStamp', $time);
    }

    public function checkAndRemoveInactive()
    {
        if (!$this->getInactiveCheckTimeStamp() || ((TIME_NOW - $this->getInactiveCheckTimeStamp()) > $this->getConfig('inactiveCheckInterval') * 60)) {
            $this->removeInactive();
            $this->setInactiveCheckTimeStamp(TIME_NOW);
        }
    }

    /**
     * @return null
     */
    public function getInactiveCheckTimeStamp()
    {
        return getSessionVar('InactiveCheckTimeStamp');
    }

    /**
     * @param $time
     */
    public function setInactiveCheckTimeStamp($time)
    {
        setSessionVar('InactiveCheckTimeStamp', $time);
    }

    public function chatViewLogin()
    {
        $this->setChannel($this->getValidRequestChannelID());
        $this->addToOnlineList();

        $this->addInfoMessage($this->getChannel(), 'channelID');
        $this->addInfoMessage($this->getChannelName(), 'channelName');
    }

    /**
     * @return mixed|null
     */
    public function getValidRequestChannelID()
    {
        $channelID = $this->getRequestVar('channelID');
        $channelName = $this->getRequestVar('channelName');
        if ($channelID === null) {
            if ($channelName !== null) {
                $channelID = $this->getChannelIDFromChannelName($channelName);
                if ($channelID === null) {
                    $channelID = $this->getChannelIDFromChannelName(
                        $this->trimChannelName($channelName, $this->getConfig('contentEncoding'))
                    );
                }
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

    public function addToOnlineList()
    {
        global $pdo;
        $values = [
            'userID'   => $this->getUserID(),
            'userName' => $this->getUserName(),
            'userRole' => $this->getUserRole(),
            'channel'  => $this->getChannel(),
            'dateTime' => gmdate("Y-m-d H:i:s", TIME_NOW),
            'ip'       => getip(),
        ];

        $sql = 'INSERT INTO ' . $this->getDataBaseTable('online') . '
                (userID, userName, userRole, channel, dateTime, ip)
            VALUES
                (:userID, :userName, :userRole, :channel, :dateTime, INET6_ATON(:ip))
            ON DUPLICATE KEY UPDATE
                userName = VALUES(userName), userRole = VALUES(userRole), channel = VALUES(channel), dateTime = VALUES(dateTime), ip = VALUES(ip)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);

        $this->resetOnlineUsersData();
    }

    /**
     * @return int|null|string
     */
    public function getChannelName()
    {
        return $this->getChannelNameFromChannelID($this->getChannel());
    }

    public function initLogsViewSession()
    {
        if ($this->getConfig('socketServerEnabled')) {
            if (!getSessionVar('logsViewSocketAuthenticated')) {
                $this->updateLogsViewSocketAuthentication();
                setSessionVar('logsViewSocketAuthenticated', true);
            }
        }
    }

    public function updateLogsViewSocketAuthentication()
    {
        if ($this->getUserRole() < UC_ADMINISTRATOR) {
            $channels = [];
            foreach ($this->getChannels() as $channel) {
                if ($this->getConfig('logsUserAccessChannelList') && !in_array($channel, $this->getConfig('logsUserAccessChannelList'))) {
                    continue;
                }
                array_push($channels, $channel);
            }
            array_push($channels, $this->getPrivateMessageID());
            array_push($channels, $this->getPrivateChannelID());
        } else {
            // The channelID "ALL" authenticates for all channels:
            $channels = ['ALL'];
        }
        $this->updateSocketAuthentication(
            $this->getUserID(),
            $this->getSocketRegistrationID(),
            $channels
        );
    }

    public function setStyle()
    {
    }

    public function setLangCodeCookie()
    {
        setcookie(
            $this->getConfig('sessionKeyPrefix') . 'lang',
            $this->getLangCode(),
            TIME_NOW + 60 * 60 * 24 * $this->getConfig('sessionCookieLifeTime'),
            $this->getConfig('sessionCookiePath'),
            $this->getConfig('sessionCookieDomain'),
            $this->getConfig('sessionCookieSecure')
        );
    }

    /**
     * @return null
     */
    public function getLangCode()
    {
        $langCodeCookie = isset($_COOKIE[ $this->getConfig('sessionKeyPrefix') . 'lang' ]) ? $_COOKIE[ $this->getConfig('sessionKeyPrefix') . 'lang' ] : null;
        $langCode = $this->getRequestVar('lang') ? $this->getRequestVar('lang') : $langCodeCookie;
        if (!in_array($langCode, $this->getConfig('langAvailable'))) {
            $language = new AJAXChatLanguage($this->getConfig('langAvailable'), $this->getConfig('langDefault'));
            $langCode = $language->getLangCode();
        }

        return $langCode;
    }

    public function initCustomSession()
    {
    }

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
            case 'socketRegistrationID':
                $this->addInfoMessage($this->getSocketRegistrationID(), 'socketRegistrationID');
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

    public function parseCommandRequests()
    {
        if ($this->getRequestVar('delete') !== null) {
            $this->deleteMessage($this->getRequestVar('delete'));
        }
    }

    /**
     * @param $messageID
     *
     * @return bool
     */
    public function deleteMessage($messageID)
    {
        global $fluent;

        $channel = $fluent->from($this->getDataBaseTable('messages'))
            ->select(null)
            ->select('channel')
            ->where('id = ?', $messageID)
            ->fetch('channel');

        if ($channel !== null) {
            if ($this->getUserRole() >= UC_ADMINISTRATOR) {
                $result = $fluent->deleteFrom($this->getDataBaseTable('messages'))
                    ->where('id = ?', $messageID)
                    ->where('(userRole < ? OR userID = ?)', $this->getUserRole(), $this->getUserID())
                    ->execute();
            } elseif ($this->getUserRole() >= UC_STAFF) {
                $result = $fluent->deleteFrom($this->getDataBaseTable('messages'))
                    ->where('id = ?', $messageID)
                    ->where('(userRole < ? OR userID = ?)', UC_STAFF, $this->getUserID())
                    ->where('userRole != ?', AJAX_CHAT_CHATBOT)
                    ->execute();
            } elseif ($this->getUserRole() < UC_STAFF && $this->getConfig('allowUserMessageDelete')) {
                $result = $fluent->deleteFrom($this->getDataBaseTable('messages'))
                    ->where('id = ?', $messageID)
                    ->where('userID = ?', $this->getUserID())
                    ->execute();
            } else {
                return false;
            }

            if ($result) {
                $this->insertChatBotMessage($channel, '/delete ' . $messageID, 0, 240);

                return true;
            }
        }

        return false;
    }

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

        if (!$this->onNewMessage($text)) {
            return;
        }

        $text = $this->replaceCustomText($text);

        $this->insertParsedMessage($text);
    }

    /**
     * @return bool
     */
    public function isAllowedToWriteMessage()
    {
        if ($this->getUserRole() >= UC_USER) {
            return true;
        }

        return false;
    }

    /**
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
                $this->insertChatBotMessage(
                    $this->getPrivateMessageID(),
                    '/error MaxMessageRate'
                );

                return false;
            }
        }

        return true;
    }

    /**
     * @return null
     */
    public function getInsertedMessagesRateTimeStamp()
    {
        return getSessionVar('InsertedMessagesRateTimeStamp');
    }

    /**
     * @param $time
     */
    public function setInsertedMessagesRateTimeStamp($time)
    {
        setSessionVar('InsertedMessagesRateTimeStamp', $time);
    }

    /**
     * @param $rate
     */
    public function setInsertedMessagesRate($rate)
    {
        setSessionVar('InsertedMessagesRate', $rate);
    }

    /**
     * @return null
     */
    public function getInsertedMessagesRate()
    {
        return getSessionVar('InsertedMessagesRate');
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
     * @param $text
     *
     * @return bool
     */
    public function onNewMessage($text)
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

                case '/kick':
                    $this->insertParsedMessageKick($textParts);
                    break;

                case '/bans':
                    $this->insertParsedMessageBans($textParts);
                    break;

                case '/unban':
                    $this->insertParsedMessageUnban($textParts);
                    break;

                case '/me':
                case '/action':
                    $this->insertParsedMessageAction($textParts);
                    break;

                case '/who':
                    $this->insertParsedMessageWho($textParts);
                    break;

                case '/list':
                    $this->insertParsedMessageList($textParts);
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

                case '/nick':
                    $this->insertParsedMessageNick($textParts);
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
                    $this->insertParsedMessageCasino($textParts);
                    break;

                case '/seen':
                    $this->insertParsedMessageSeen($textParts);
                    break;

                case '/mentions':
                    $this->insertParsedMessageMentions($textParts);
                    break;

                default:
                    if (!$this->parseCustomCommands($text, $textParts)) {
                        $this->insertChatBotMessage(
                            $this->getPrivateMessageID(),
                            '/error UnknownCommand ' . $textParts[0]
                        );
                    }
            }
        } else {
            $this->insertCustomMessage(
                $this->getUserID(),
                $this->getUserName(),
                $this->getUserRole(),
                $this->getChannel(),
                $text
            );
        }
    }

    /**
     * @return null
     */
    public function getQueryUserName()
    {
        return getSessionVar('QueryUserName');
    }

    /**
     * @param $textParts
     */
    function insertParsedMessageJoin($textParts)
    {
        if (count($textParts) == 1) {
            if ($this->isAllowedToCreatePrivateChannel()) {
                $this->switchChannel($this->getChannelNameFromChannelID($this->getPrivateChannelID()));
            } else {
                $this->insertChatBotMessage(
                    $this->getPrivateMessageID(),
                    '/error MissingChannelName'
                );
            }
        } else {
            $this->switchChannel($textParts[1]);
        }
    }

    /**
     * @param $textParts
     */
    public function insertParsedMessagePrivMsg($textParts)
    {
        if ($this->isAllowedToSendPrivateMessage()) {
            if (count($textParts) < 3) {
                if (count($textParts) == 2) {
                    $this->insertChatBotMessage(
                        $this->getPrivateMessageID(),
                        '/error MissingText'
                    );
                } else {
                    $this->insertChatBotMessage(
                        $this->getPrivateMessageID(),
                        '/error MissingUserName'
                    );
                }
            } else {
                $toUserID = $this->getIDFromName($textParts[1]);
                if ($toUserID === null) {
                    if ($this->getQueryUserName() !== null) {
                        $this->insertMessage('/query');
                    } else {
                        $this->insertChatBotMessage(
                            $this->getPrivateMessageID(),
                            '/error UserNameNotFound ' . $textParts[1]
                        );
                    }
                } else {
                    $command = ($textParts[0] == '/describe') ? '/privaction' : '/privmsg';
                    $this->insertCustomMessage(
                        $this->getUserID(),
                        $this->getUserName(),
                        $this->getUserRole(),
                        $this->getPrivateMessageID(),
                        $command . 'to ' . $textParts[1] . ' ' . implode(' ', array_slice($textParts, 2))
                    );
                    $this->insertCustomMessage(
                        $this->getUserID(),
                        $this->getUserName(),
                        $this->getUserRole(),
                        $this->getPrivateMessageID($toUserID),
                        $command . ' ' . implode(' ', array_slice($textParts, 2))
                    );
                }
            }
        } else {
            $this->insertChatBotMessage(
                $this->getPrivateMessageID(),
                '/error PrivateMessageNotAllowed'
            );
        }
    }

    /**
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
     */
    public function insertParsedMessageInvite($textParts)
    {
        if ($this->getChannel() == $this->getPrivateChannelID() || in_array($this->getChannel(), $this->getChannels())) {
            if (count($textParts) == 1) {
                $this->insertChatBotMessage(
                    $this->getPrivateMessageID(),
                    '/error MissingUserName'
                );
            } else {
                $toUserID = $this->getIDFromName($textParts[1]);
                if ($toUserID === null) {
                    $this->insertChatBotMessage(
                        $this->getPrivateMessageID(),
                        '/error UserNameNotFound ' . $textParts[1]
                    );
                } else {
                    $this->addInvitation($toUserID);
                    $invitationChannelName = $this->getChannelNameFromChannelID($this->getChannel());
                    $this->insertChatBotMessage(
                        $this->getPrivateMessageID(),
                        '/inviteto ' . $textParts[1] . ' ' . $invitationChannelName
                    );
                    $this->insertChatBotMessage(
                        $this->getPrivateMessageID($toUserID),
                        '/invite ' . $this->getUserName() . ' ' . $invitationChannelName
                    );
                }
            }
        } else {
            $this->insertChatBotMessage(
                $this->getPrivateMessageID(),
                '/error InviteNotAllowed'
            );
        }
    }

    /**
     * @param      $userID
     * @param null $channelID
     */
    public function addInvitation($userID, $channelID = null)
    {
        $this->removeExpiredInvitations();

        $channelID = ($channelID === null) ? $this->getChannel() : $channelID;
        $sql = 'INSERT INTO ' . $this->getDataBaseTable('invitations') . '(
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

    public function removeExpiredInvitations()
    {
        global $fluent;

        $fluent->deleteFrom($this->getDataBaseTable('invitations'))
            ->where('DATE_SUB(NOW(), INTERVAL 1 DAY) > dateTime')
            ->execute();
    }

    /**
     * @param $textParts
     */
    public function insertParsedMessageUninvite($textParts)
    {
        if ($this->getChannel() == $this->getPrivateChannelID() || in_array($this->getChannel(), $this->getChannels())) {
            if (count($textParts) == 1) {
                $this->insertChatBotMessage(
                    $this->getPrivateMessageID(),
                    '/error MissingUserName'
                );
            } else {
                $toUserID = $this->getIDFromName($textParts[1]);
                if ($toUserID === null) {
                    $this->insertChatBotMessage(
                        $this->getPrivateMessageID(),
                        '/error UserNameNotFound ' . $textParts[1]
                    );
                } else {
                    $this->removeInvitation($toUserID);
                    $invitationChannelName = $this->getChannelNameFromChannelID($this->getChannel());
                    $this->insertChatBotMessage(
                        $this->getPrivateMessageID(),
                        '/uninviteto ' . $textParts[1] . ' ' . $invitationChannelName
                    );
                    $this->insertChatBotMessage(
                        $this->getPrivateMessageID($toUserID),
                        '/uninvite ' . $this->getUserName() . ' ' . $invitationChannelName
                    );
                }
            }
        } else {
            $this->insertChatBotMessage(
                $this->getPrivateMessageID(),
                '/error UninviteNotAllowed'
            );
        }
    }

    /**
     * @param      $userID
     * @param null $channelID
     */
    public function removeInvitation($userID, $channelID = null)
    {
        global $fluent;

        $channelID = ($channelID === null) ? $this->getChannel() : $channelID;

        $fluent->deleteFrom($this->getDataBaseTable('invitations'))
            ->where('userID = ?', $userID)
            ->where('channel = ?', $channelID)
            ->execute();
    }

    /**
     * @param $textParts
     */
    public function insertParsedMessageQuery($textParts)
    {
        if ($this->isAllowedToSendPrivateMessage()) {
            if (count($textParts) == 1) {
                if ($this->getQueryUserName() !== null) {
                    $this->insertChatBotMessage(
                        $this->getPrivateMessageID(),
                        '/queryClose ' . $this->getQueryUserName()
                    );
                    $this->setQueryUserName(null);
                } else {
                    $this->insertChatBotMessage(
                        $this->getPrivateMessageID(),
                        '/error NoOpenQuery'
                    );
                }
            } else {
                if ($this->getIDFromName($textParts[1]) === null) {
                    $this->insertChatBotMessage(
                        $this->getPrivateMessageID(),
                        '/error UserNameNotFound ' . $textParts[1]
                    );
                } else {
                    if ($this->getQueryUserName() !== null) {
                        $this->insertMessage('/query');
                    }
                    $this->setQueryUserName($textParts[1]);
                    $this->insertChatBotMessage(
                        $this->getPrivateMessageID(),
                        '/queryOpen ' . $textParts[1]
                    );
                }
            }
        } else {
            $this->insertChatBotMessage(
                $this->getPrivateMessageID(),
                '/error PrivateMessageNotAllowed'
            );
        }
    }

    /**
     * @param $userName
     */
    public function setQueryUserName($userName)
    {
        setSessionVar('QueryUserName', $userName);
    }

    /**
     * @param $textParts
     */
    public function insertParsedMessageKick($textParts)
    {
        if ($this->getUserRole() >= UC_STAFF) {
            if (count($textParts) == 1) {
                $this->insertChatBotMessage(
                    $this->getPrivateMessageID(),
                    '/error MissingUserName'
                );
            } else {
                $kickUserID = $this->getIDFromName($textParts[1]);
                if ($kickUserID === null) {
                    $this->insertChatBotMessage(
                        $this->getPrivateMessageID(),
                        '/error UserNameNotFound ' . $textParts[1]
                    );
                } else {
                    $kickUserRole = $this->getRoleFromID($kickUserID);
                    if ($this->getUserRole() <= $kickUserRole) {
                        $this->insertChatBotMessage(
                            $this->getPrivateMessageID(),
                            '/error KickNotAllowed ' . $textParts[1]
                        );
                    } else {
                        $channel = $this->getChannelFromID($kickUserID);
                        $banMinutes = (count($textParts) > 2) ? $textParts[2] : null;
                        $this->kickUser($textParts[1], $banMinutes, $kickUserID);
                        if ($channel !== null) {
                            $this->insertChatBotMessage(
                                $channel,
                                '/kick ' . $textParts[1],
                                null,
                                1
                            );
                            if ($channel != $this->getChannel()) {
                                $this->insertChatBotMessage(
                                    $this->getPrivateMessageID(),
                                    '/kick ' . $textParts[1],
                                    null,
                                    1
                                );
                            }
                        }
                    }
                }
            }
        } else {
            $this->insertChatBotMessage(
                $this->getPrivateMessageID(),
                '/error CommandNotAllowed ' . $textParts[0]
            );
        }
    }

    /**
     * @param $userID
     *
     * @return null
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
     * @param $userID
     *
     * @return null
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
     * @param      $userName
     * @param null $banMinutes
     * @param null $userID
     */
    public function kickUser($userName, $banMinutes = null, $userID = null)
    {
        if ($userID === null) {
            $userID = $this->getIDFromName($userName);
        }
        if ($userID === null) {
            return;
        }

        $banMinutes = ($banMinutes !== null) ? $banMinutes : $this->getConfig('defaultBanTime');

        if ($banMinutes) {
            $this->banUser($userName, $banMinutes, $userID);
        }

        $this->removeFromOnlineList($userID);
    }

    /**
     * @param      $userName
     * @param null $banMinutes
     * @param null $userID
     */
    public function banUser($userName, $banMinutes = null, $userID = null)
    {
        if ($userID === null) {
            $userID = $this->getIDFromName($userName);
        }
        $ip = $this->getIPFromID($userID);
        if (!$ip || $userID === null) {
            return;
        }

        $this->removeExpiredBans();

        $banMinutes = (int)$banMinutes;
        if (!$banMinutes) {
            $banMinutes = $this->getConfig('defaultBanTime');
        }

        $sql = 'INSERT INTO ' . $this->getDataBaseTable('bans') . '(
                    userID,
                    userName,
                    dateTime,
                    ip
                )
                VALUES (
                    ' . sqlesc($userID) . ',
                    ' . sqlesc($userName) . ',
                    DATE_ADD(NOW(), INTERVAL ' . sqlesc($banMinutes) . ' MINUTE),
                    ' . ipToStorageFormat($ip) . '
                );';

        // Create a new SQL query:
        sql_query($sql) or sqlerr(__FILE__, __LINE__);
    }

    /**
     * @param $userID
     *
     * @return null
     */
    public function getIPFromID($userID)
    {
        $userDataArray = $this->getOnlineUsersData(null, 'userID', $userID);
        if ($userDataArray && isset($userDataArray[0])) {
            return $userDataArray[0]['ip'];
        }

        return null;
    }

    public function removeExpiredBans()
    {
        global $fluent;

        $fluent->deleteFrom($this->getDataBaseTable('bans'))
            ->where('dateTime < NOW()')
            ->execute();
    }

    /**
     * @param $textParts
     */
    public function insertParsedMessageBans($textParts)
    {
        if ($this->getUserRole() >= UC_STAFF) {
            $this->removeExpiredBans();
            $bannedUsers = $this->getBannedUsers();
            if (count($bannedUsers) > 0) {
                $this->insertChatBotMessage(
                    $this->getPrivateMessageID(),
                    '/bans ' . implode(' ', $bannedUsers)
                );
            } else {
                $this->insertChatBotMessage(
                    $this->getPrivateMessageID(),
                    '/bansEmpty -'
                );
            }
        } else {
            $this->insertChatBotMessage(
                $this->getPrivateMessageID(),
                '/error CommandNotAllowed ' . $textParts[0]
            );
        }
    }

    /**
     * @return array|null
     */
    public function getBannedUsers()
    {
        return $this->getBannedUsersData('userName');
    }

    /**
     * @param $textParts
     */
    public function insertParsedMessageUnban($textParts)
    {
        if ($this->getUserRole() >= UC_STAFF) {
            $this->removeExpiredBans();
            if (count($textParts) == 1) {
                $this->insertChatBotMessage(
                    $this->getPrivateMessageID(),
                    '/error MissingUserName'
                );
            } else {
                if (!in_array($textParts[1], $this->getBannedUsers())) {
                    $this->insertChatBotMessage(
                        $this->getPrivateMessageID(),
                        '/error UserNameNotFound ' . $textParts[1]
                    );
                } else {
                    $this->unbanUser($textParts[1]);
                    $this->insertChatBotMessage(
                        $this->getPrivateMessageID(),
                        '/unban ' . $textParts[1]
                    );
                }
            }
        } else {
            $this->insertChatBotMessage(
                $this->getPrivateMessageID(),
                '/error CommandNotAllowed ' . $textParts[0]
            );
        }
    }

    /**
     * @param $userName
     */
    public function unbanUser($userName)
    {
        global $fluent;

        $fluent->deleteFrom($this->getDataBaseTable('bans'))
            ->where('userName = ?', $userName)
            ->execute();
    }

    /**
     * @param $textParts
     */
    public function insertParsedMessageAction($textParts)
    {
        if (count($textParts) == 1) {
            $this->insertChatBotMessage(
                $this->getPrivateMessageID(),
                '/error MissingText'
            );
        } else {
            if ($this->getQueryUserName() !== null) {
                $this->insertMessage('/describe ' . $this->getQueryUserName() . ' ' . implode(' ', array_slice($textParts, 1)));
            } else {
                $this->insertCustomMessage(
                    $this->getUserID(),
                    $this->getUserName(),
                    $this->getUserRole(),
                    $this->getChannel(),
                    implode(' ', $textParts)
                );
            }
        }
    }

    /**
     * @param $textParts
     */
    public function insertParsedMessageWho($textParts)
    {
        if (count($textParts) == 1) {
            if ($this->isAllowedToListHiddenUsers()) {
                $this->insertChatBotMessage(
                    $this->getPrivateMessageID(),
                    '/who ' . implode(' ', $this->getOnlineUsers())
                );
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
                $this->insertChatBotMessage(
                    $this->getPrivateMessageID(),
                    '/who ' . implode(' ', $this->getOnlineUsers($channels))
                );
            }
        } else {
            $channelName = $textParts[1];
            $channelID = $this->getChannelIDFromChannelName($channelName);
            if (!$this->validateChannel($channelID)) {
                $this->insertChatBotMessage(
                    $this->getPrivateMessageID(),
                    '/error InvalidChannelName ' . $channelName
                );
            } else {
                $onlineUsers = $this->getOnlineUsers([$channelID]);
                if (count($onlineUsers) > 0) {
                    $this->insertChatBotMessage(
                        $this->getPrivateMessageID(),
                        '/whoChannel ' . $channelName . ' ' . implode(' ', $onlineUsers)
                    );
                } else {
                    $this->insertChatBotMessage(
                        $this->getPrivateMessageID(),
                        '/whoEmpty -'
                    );
                }
            }
        }
    }

    /**
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
     * @return array|null
     */
    public function getOnlineUsers($channelIDs = null)
    {
        return $this->getOnlineUsersData($channelIDs, 'userName');
    }

    /**
     * @param $textParts
     */
    public function insertParsedMessageList($textParts)
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
        $this->insertChatBotMessage(
            $this->getPrivateMessageID(),
            '/list ' . implode(' ', $channelNames)
        );
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
     */
    public function insertParsedMessageWhereis($textParts)
    {
        if (count($textParts) == 1) {
            $this->insertChatBotMessage(
                $this->getPrivateMessageID(),
                '/error MissingUserName'
            );
        } else {
            $whereisUserID = $this->getIDFromName($textParts[1]);
            if ($whereisUserID === null) {
                $this->insertChatBotMessage(
                    $this->getPrivateMessageID(),
                    '/error UserNameNotFound ' . $textParts[1]
                );
            } else {
                $channelID = $this->getChannelFromID($whereisUserID);
                if ($this->validateChannel($channelID)) {
                    $channelName = $this->getChannelNameFromChannelID($channelID);
                } else {
                    $channelName = null;
                }
                if ($channelName === null) {
                    $this->insertChatBotMessage(
                        $this->getPrivateMessageID(),
                        '/error UserNameNotFound ' . $textParts[1]
                    );
                } else {
                    $this->insertChatBotMessage(
                        $this->getPrivateMessageID(),
                        '/whereis ' . $textParts[1] . ' ' . $channelName
                    );
                }
            }
        }
    }

    /**
     * @param $textParts
     */
    public function insertParsedMessageWhois($textParts)
    {
        if ($this->getUserRole() >= UC_STAFF) {
            if (count($textParts) == 1) {
                $this->insertChatBotMessage(
                    $this->getPrivateMessageID(),
                    '/error MissingUserName'
                );
            } else {
                $whoisUserID = $this->getIDFromName($textParts[1]);
                if ($whoisUserID === null) {
                    $this->insertChatBotMessage(
                        $this->getPrivateMessageID(),
                        '/error UserNameNotFound ' . $textParts[1]
                    );
                } else {
                    $this->insertChatBotMessage(
                        $this->getPrivateMessageID(),
                        '/whois ' . $textParts[1] . ' ' . $this->getIPFromID($whoisUserID)
                    );
                }
            }
        } else {
            $this->insertChatBotMessage(
                $this->getPrivateMessageID(),
                '/error CommandNotAllowed ' . $textParts[0]
            );
        }
    }

    /**
     * @param $textParts
     */
    public function insertParsedMessageRoll($textParts)
    {
        if (count($textParts) == 1) {
            $text = '/roll ' . $this->getUserName() . ' 1d6 ' . $this->rollDice(6);
        } else {
            $diceParts = explode('d', $textParts[1]);
            if (count($diceParts) == 2) {
                $number = (int)$diceParts[0];
                $sides = (int)$diceParts[1];
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
        $this->insertChatBotMessage(
            $this->getChannel(),
            $text
        );
    }

    /**
     * @param $sides
     *
     * @return int
     */
    public function rollDice($sides)
    {
        return random_int(1, $sides);
    }

    /**
     * @param $textParts
     */
    public function insertParsedMessageNick($textParts)
    {
        if (!$this->getConfig('allowNickChange') || $this->getUserRole() <= UC_USER) {
            $this->insertChatBotMessage(
                $this->getPrivateMessageID(),
                '/error CommandNotAllowed ' . $textParts[0]
            );
        } elseif (count($textParts) == 1) {
            $this->insertChatBotMessage(
                $this->getPrivateMessageID(),
                '/error MissingUserName'
            );
        } else {
            $newUserName = implode(' ', array_slice($textParts, 1));
            if ($newUserName == $this->getLoginUserName()) {
                $prefix = '';
                $suffix = '';
            } else {
                $prefix = $this->getConfig('changedNickPrefix');
                $suffix = $this->getConfig('changedNickSuffix');
            }
            $maxLength = $this->getConfig('userNameMaxLength')
                - $this->stringLength($prefix)
                - $this->stringLength($suffix);
            $newUserName = $this->trimString($newUserName, 'UTF-8', $maxLength, true);
            if (!$newUserName) {
                $this->insertChatBotMessage(
                    $this->getPrivateMessageID(),
                    '/error InvalidUserName'
                );
            } else {
                $newUserName = $prefix . $newUserName . $suffix;
                if ($this->isUserNameInUse($newUserName)) {
                    $this->insertChatBotMessage(
                        $this->getPrivateMessageID(),
                        '/error UserNameInUse'
                    );
                } else {
                    $oldUserName = $this->getUserName();
                    $this->setUserName($newUserName);
                    $this->updateOnlineList();
                    $this->addInfoMessage($this->getUserName(), 'userName');
                    $this->insertChatBotMessage(
                        $this->getChannel(),
                        '/nick ' . $oldUserName . ' ' . $newUserName,
                        2
                    );
                }
            }
        }
    }

    public function insertParsedMessageCasino($textParts)
    {
        global $site_config;
        $res = sql_query("SELECT COUNT(*) AS count FROM blackjack WHERE status = 'waiting'") or sqlerr(__FILE__, __LINE__);
        $row = mysqli_fetch_row($res);
        $msg = '[color=#00FF00]' . $row[0] . ' game' . plural($row[0]) . ' of [url=' . $site_config['baseurl'] . '/games.php]BlackJack[/url] waiting to be played.[/color] ';

        $res = sql_query("SELECT COUNT(*) AS count, SUM(amount) AS amount FROM casino_bets WHERE winner = ''") or sqlerr(__FILE__, __LINE__);
        $row = mysqli_fetch_row($res);

        if (!$row) {
            $msg .= '[color=#00FF00]There are no Casino bets in play. [/color]';
        } else {
            $msg .= '[color=#00FF00]' . $row[0] . ' bet' . plural(count($row[0])) . ' in the [url=' . $site_config['baseurl'] . '/casino.php]Casino[/url] for ' . mksize($row[1]) . '. [/color]';
        }

        unset($row);
        $res = sql_query('SELECT u.username, c.win + (u.bjwins * 1024 * 1024 * 1024) AS wins, c.lost + (u.bjlosses * 1024 * 1024 * 1024) AS losses, (c.win + (u.bjwins * 1024 * 1024 * 1024)) - (c.lost + (u.bjlosses * 1024 * 1024 * 1024)) AS won FROM casino AS c INNER JOIN users AS u ON c.userid = u.id ORDER BY won DESC LIMIT 1') or sqlerr(__FILE__, __LINE__);
        $row = mysqli_fetch_row($res);
        if ($row) {
            $whereisRoleClass = get_user_class_name($row[0], true);
            $userNameClass = $whereisRoleClass != null ? '[' . $whereisRoleClass . ']' . $row[0] . '[/' . $whereisRoleClass . ']' : $row[1];
            $msg .= $userNameClass . ' [color=#00FF00]is the biggest winner with ' . mksize($row[3]) . '. [/color]';
        }

        unset($row);
        $res = sql_query('SELECT u.username, c.win + (u.bjwins * 1024 * 1024 * 1024) AS wins, c.lost + (u.bjlosses * 1024 * 1024 * 1024) AS losses, (c.win + (u.bjwins * 1024 * 1024 * 1024)) - (c.lost + (u.bjlosses * 1024 * 1024 * 1024)) AS won FROM casino AS c INNER JOIN users AS u ON c.userid = u.id ORDER BY won ASC LIMIT 1') or sqlerr(__FILE__, __LINE__);
        $row = mysqli_fetch_row($res);
        if ($row) {
            $whereisRoleClass = get_user_class_name($row[0], true);
            $userNameClass = $whereisRoleClass != null ? '[' . $whereisRoleClass . ']' . $row[0] . '[/' . $whereisRoleClass . ']' : $row[1];
            $msg .= $userNameClass . ' [color=#00FF00]is the biggest loser with ' . mksize($row[3]) . '. [/color]';
        }

        unset($row);
        $res = sql_query('SELECT SUM(win) FROM casino') or sqlerr(__FILE__, __LINE__);
        $row = mysqli_fetch_row($res);
        if ($row) {
            $msg .= '[color=#00FF00]' . mksize($row[0]) . ' have been won (and lost) in the [url=' . $site_config['baseurl'] . '/casino.php]Casino[/url].[/color] ';
        }

        $resbj = sql_query('SELECT SUM(bjwins) FROM users') or sqlerr(__FILE__, __LINE__);
        $bjsum = mysqli_fetch_row($resbj);
        if ($bjsum) {
            $msg .= '[color=#00FF00]' . mksize($bjsum[0] * 1024 * 1024 * 1024) . ' have been won (and lost) at the [url=' . $site_config['baseurl'] . '/games.php]BlackJack[/url] tables.[/color]';
        }

        $type = null;
        $text = $msg . $type;
        $this->insertChatBotMessage(
            $this->getChannel(),
            $text,
            1,
            600
        );
    }

    public function insertParsedMessageStats($textParts)
    {
        global $site_config;
        if (count($textParts) == 1) {
            $this->insertChatBotMessage(
                $this->getPrivateMessageID(),
                '/error MissingUserName'
            );
        } else {
            $whereisUserID = $this->getIDFromName($textParts[1]);
            if ($whereisUserID === null) {
                $this->insertChatBotMessage(
                    $this->getPrivateMessageID(),
                    '/error UserNameNotFound ' . $textParts[1]
                );
            } else {
                $sql = 'SELECT u.onirc, u.irctotal, u.donor, u.warned, u.leechwarn, u.pirate, u.king, u.enabled, 
                            u.downloadpos, u.last_access, u.username, u.reputation, u.class, u.bjwins - u.bjlosses AS bj,
                            u.uploaded, u.downloaded, u.seedbonus, u.freeslots, u.free_switch, u.added, u.invite_rights,
                            u.invites, c.win - c.lost AS casino
						FROM users AS u 
						LEFT JOIN casino AS c ON c.userid = u.id
						WHERE id = ' . $whereisUserID;

                $result = sql_query($sql) or sqlerr(__FILE__, __LINE__);
                $stats = mysqli_fetch_assoc($result);
                $stats['bj'] = $stats['bj'] * 1024 * 1024 * 1024;
                $bj = $stats['bj'] > 0 ? '[color=#00FF00]' . mksize($stats['bj']) . '[/color]' : '[color=#CC0000]' . mksize($stats['bj']) . '[/color]';
                $uploaded = '[color=#00FF00]' . human_filesize($stats['uploaded']) . '[/color]';
                $downloaded = '[color=#00FF00]' . human_filesize($stats['downloaded']) . '[/color]';
                $userClass = get_user_class_name($stats['class']);
                $enabled = $stats['enabled'] === 'yes' && $stats['downloadpos'] == 1 ? '[color=#00FF00](Enabled)[/color]' : '[color=#CC0000](Disabled)[/color]';
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
                $seedbonus = '[color=#00FF00]' . number_format($stats['seedbonus']) . '[/color]';
                $freeslots = '[color=#00FF00]' . number_format($stats['freeslots']) . '[/color]';
                $ircidle = $stats['irctotal'] > 0 ? '[color=#00FF00]' . get_date($stats['irctotal'], 'LONG', false, false, true) . '[/color]' : '[color=#CC0000]' . get_date($stats['irctotal'], 'LONG', false, false, true) . '[/color]';
                $reputation = '[color=#00FF00]' . number_format($stats['reputation']) . '[/color]';
                $free = get_date($stats['free_switch'], 'LONG') > date('Y-m-d H:i:s') ? '[color=#00FF00]' . get_date($stats['free_switch'], 'LONG') . '[/color]' : '[color=#CC0000]Expired[/color]';
                $double = get_date($stats['double_switch'], 'LONG') > date('Y-m-d H:i:s') ? '[color=#00FF00]' . get_date($stats['double_switch'], 'LONG') . '[/color]' : '[color=#CC0000]Expired[/color]';
                $joined = '[color=#00FF00]' . get_date($stats['added'], 'LONG') . '[/color]';
                $seen = '[color=#00FF00]' . get_date($stats['last_access'], 'LONG') . '[/color]';
                $seeder = (int)get_row_count('peers', 'WHERE seeder = "yes" and userid = ' . sqlesc($whereisUserID));
                $seeding = '[color=#00FF00]' . number_format($seeder) . '[/color]';
                $leeching = '[color=#00FF00]' . number_format((int)get_row_count('peers', 'WHERE seeder != "yes" and userid = ' . sqlesc($whereisUserID))) . '[/color]';
                $uploads = '[color=#00FF00]' . number_format((int)get_row_count('torrents', 'WHERE owner = ' . sqlesc($whereisUserID))) . '[/color]';
                $snatched = '[color=#00FF00]' . number_format((int)get_row_count('snatched', 'WHERE userid = ' . sqlesc($whereisUserID))) . '[/color]';
                $hnrs = (int)get_row_count('snatched', 'WHERE mark_of_cain = "yes" AND userid = ' . sqlesc($whereisUserID));
                $hnrs = $hnrs == 0 ? '[color=#00FF00]' . '0[/color]' : '[color=#CC0000]' . number_format($hnrs) . '[/color]';
                $connectyes = (int)get_row_count('peers', 'WHERE seeder = "yes" and connectable = "yes" and userid = ' . sqlesc($whereisUserID));
                $connectno = (int)get_row_count('peers', 'WHERE seeder = "yes" and connectable = "no" and userid = ' . sqlesc($whereisUserID));
                if ($connectyes === 0 && $connectno === 0 || $connectno === $seeder) {
                    $connectable = '[color=#CC0000]no[/color]';
                } elseif ($connectyes != 0 && $connectno === 0) {
                    $connectable = '[color=#00FF00]yes[/color]';
                } else {
                    $connectable = '[color=#CC0000]' . number_format($connectyes / $seeder * 100) . '%[/color]';
                }
                $bpt = $site_config['bonus_per_duration'];
                $sql = 'SELECT COUNT(*)
                        FROM snatched AS s INNER JOIN users AS u ON u.id = s.userid
                        INNER JOIN torrents t ON s.torrentid = t.id
                        INNER JOIN categories c ON t.category = c.id
                        WHERE t.owner != ' . sqlesc($whereisUserID) . " AND s.downloaded > 0 AND s.seedtime < 259200 AND s.userid = " . sqlesc($whereisUserID);
                $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
                $row = mysqli_fetch_row($res);
                $count_incomplete = $row[0] > 0 ? "[color=#CC0000]{$row[0]}[/color]" : "[color=#00FF00]{$row[0]}[/color]";

                $ircbonus = $stats['onirc'] == 'yes' ? .45 : 0;
                $allbonus = number_format(($connectyes * $bpt * 2) + $ircbonus, 2);
                $earns = $connectyes > 0 ? '[color=#00FF00]' . $allbonus . 'bph[/color]' : '[color=#CC0000]' . $allbonus . 'bph[/color]';
                $seedsize = get_one_row('peers AS p INNER JOIN torrents AS t ON t.id = p.torrent', 'SUM(t.size)', "WHERE p.seeder = 'yes' AND p.connectable = 'yes' AND p.userid = " . $whereisUserID);
                $volume = '[color=#00FF00]' . human_filesize($seedsize) . '[/color]';
                $whereisRoleClass = get_user_class_name($stats['class'], true);
                $userNameClass = $whereisRoleClass != null ? '[' . $whereisRoleClass . '][url=' . $site_config['baseurl'] . '/userdetails.php?id=' . $whereisUserID . '&hit=1]' . $stats['username'] . '[/url][/' . $whereisRoleClass . ']' : '@' . $textParts[1];
                $str = '';
                $str .= (isset($stats['donor']) && $stats['donor'] === 'yes' && isset($stats['show_donor']) && $stats['show_donor'] === 'yes' ? '[img]' . $site_config['baseurl'] . '/pic/star.png[/img]' : '');
                $str .= (isset($stats['warned']) && $stats['warned'] >= 1 ? '[img]' . $site_config['baseurl'] . '/pic/alertred.png[/img]' : '');
                $str .= (isset($stats['leechwarn']) && $stats['leechwarn'] >= 1 ? '[img]' . $site_config['baseurl'] . '/pic/alertblue.png[/img]' : '');
                $str .= (isset($stats['enabled']) && $stats['enabled'] != 'yes' ? '[img]' . $site_config['baseurl'] . '/pic/disabled.gif[/img]' : '');
                $str .= (isset($stats['chatpost']) && $stats['chatpost'] == 0 ? '[img]' . $site_config['baseurl'] . '/pic/warned.png[/img]' : '');
                $str .= (isset($stats['pirate']) && $stats['pirate'] >= TIME_NOW ? '[img]' . $site_config['baseurl'] . '/pic/pirate.png[/img]' : '');

                $text = "$userNameClass{$str}: [color=#fff]User Class:[/color] [$whereisRoleClass]{$userClass}[/$whereisRoleClass]$enabled
[color=#fff]idling in irc for:[/color]  {$ircidle}, [color=#fff]Member Since:[/color]  $joined, [color=#fff]Last Seen:[/color]  $seen, [color=#fff]Downloaded:[/color]  $downloaded, [color=#fff]Uploaded:[/color]  $uploaded, [color=#fff]Ratio:[/color]  $ratio, [color=#fff]Seedbonus:[/color]  $seedbonus, [color=#fff]Invites:[/color]  $invites, [color=#fff]Reputation:[/color]  $reputation, [color=#fff]HnRs:[/color]  $hnrs, [color=#fff]Snatched:[/color]  $snatched, [color=#fff]Seeding:[/color]  $seeding, [color=#fff]Seeding Size:[/color]  $volume, [color=#fff]Leeching:[/color]  $leeching, [color=#fff]Requirements Not Met:[/color]  $count_incomplete, [color=#fff]Connectable:[/color]  $connectable, [color=#fff]Uploads:[/color]  $uploads, [color=#fff]Earning Bonus:[/color]  $earns, [color=#fff]Casino:[/color]  $casino, [color=#fff]Blackjack:[/color]  $bj, [color=#fff]Freeleech Until:[/color]  $free, [color=#fff]DoubleUp Until:[/color]  $double, [color=#fff]Free/Double Slots:[/color]  $freeslots";
                $this->insertChatBotMessage(
                    $this->getPrivateMessageID(),
                    $text,
                    1,
                    600
                );
            }
        }
    }

    public function insertParsedMessageSeen($textParts)
    {
        if (count($textParts) == 1) {
            $this->insertChatBotMessage(
                $this->getPrivateMessageID(),
                '/error MissingUserName'
            );

            return false;
        } else {
            $userName = $textParts[1];
            $res = sql_query('SELECT username FROM users WHERE username = ' . sqlesc($userName)) or sqlerr(__FILE__, __LINE__);
            $quick = mysqli_fetch_array($res);
            if (empty($quick['username'])) {
                $this->insertChatBotMessage(
                    $this->getPrivateMessageID(),
                    '/error InvalidUserName'
                );

                return false;
            }
            $userName = $quick['username'];
            $whereisUserID = $this->getIDFromName($textParts[1]);
            $whereisRoleClass = get_user_class_name($this->getRoleFromID($whereisUserID), true);
            $user = '[' . $whereisRoleClass . ']' . $userName . '[/' . $whereisRoleClass . ']';
            $res = sql_query('SELECT dateTime, text FROM ajax_chat_messages WHERE userName = ' . sqlesc($userName) . ' AND channel = 0 ORDER BY id DESC LIMIT 1') or sqlerr(__FILE__, __LINE__);
            $seen = mysqli_fetch_array($res);
            if ($seen) {
                $gender = get_one_row('users', 'gender', 'WHERE id = ' . sqlesc($whereisUserID));
                if ($gender === 'Male') {
                    $it = 'he';
                } elseif ($gender === 'Female') {
                    $it = 'she';
                } else {
                    $it = 'it';
                }
                $msg = "$user was last seen " . $seen['dateTime'] . ", where $it said: [quote]" . $seen['text'] . '[/quote]';
            } else {
                $msg = "$user has not been seen in many days.";
            }
            $type = null;
            $text = $msg . $type;
            $this->insertChatBotMessage(
                $this->getChannel(),
                $text,
                1,
                300
            );
        }
    }

    public function insertParsedMessageMentions($textParts)
    {
        global $CURUSER;
        $userName = $CURUSER['username'];
        $whereisUserID = $CURUSER['id'];
        $whereisRoleClass = get_user_class_name($this->getRoleFromID($whereisUserID), true);
        $user = '[' . $whereisRoleClass . ']' . $userName . '[/' . $whereisRoleClass . ']';

        $sql = "SELECT dateTime, userName, userID, text
					FROM ajax_chat_messages
					WHERE MATCH(text) AGAINST ('\"/privmsgto $userName\"  $userName' IN BOOLEAN MODE)
						AND NOT MATCH(text) AGAINST ('/privmsg /announce /login /logout /roll /takeover /channelEnter /channelLeave /kick /me /nick' IN NATURAL LANGUAGE MODE)
						AND userName != " . sqlesc($userName) . "
						AND userID != " . $this->getConfig('chatBotID') . "
					ORDER BY id DESC LIMIT 25";
        $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $messages = [];
        while ($mentions = mysqli_fetch_array($res)) {
            $posterClass = get_user_class_name($mentions['userID'], true);
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
        $this->insertChatBotMessage(
            $this->getPrivateMessageID(),
            $text,
            1,
            600
        );
    }

    public function insertParsedMessageRep($textParts)
    {
        global $CURUSER, $site_config, $cache;
        if (count($textParts) == 1) {
            $this->insertChatBotMessage(
                $this->getPrivateMessageID(),
                '/error MissingUserName'
            );

            return false;
        } elseif (count($textParts) == 2 || !is_numeric($textParts[2]) || $textParts[2] <= 0) {
            $this->insertChatBotMessage(
                $this->getPrivateMessageID(),
                '/error NotInteger'
            );

            return false;
        }
        $gift = number_format($textParts[2]);
        $sql = sql_query('SELECT reputation FROM users WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        $res = mysqli_fetch_row($sql);
        $fromrep = $res[0];
        if ((int)$textParts[2] > (int)$fromrep) {
            $this->insertChatBotMessage(
                $this->getPrivateMessageID(),
                '/error NotEnoughRep'
            );

            return false;
        }
        $userName = $textParts[1];
        $whereisUserID = $this->getIDFromName($textParts[1]);
        $whereisRoleClass = get_user_class_name($this->getRoleFromID($whereisUserID), true);
        $user = '[' . $whereisRoleClass . ']' . $userName . '[/' . $whereisRoleClass . ']';
        $gift = $textParts[2];
        $text = $user . ' has been given ' . number_format($gift) . ' Reputation Points from ' . $CURUSER['username'] . '.';
        $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . " - given reputation gift of $gift by " . $CURUSER['username'] . '.';
        $recbonus = get_one_row('users', 'reputation', 'WHERE username = ' . sqlesc($userName));
        if (sql_query("UPDATE users SET reputation = reputation - $gift WHERE id = " . sqlesc($CURUSER['id']))) {
            sql_query("UPDATE users SET reputation = reputation + $gift, bonuscomment = CONCAT(" . sqlesc($bonuscomment) . ",'\n',IFNULL(bonuscomment,'')) WHERE username = " . sqlesc($userName)) or sqlerr(__FILE__, __LINE__);
            // receiver
            $cache->update_row('user' . $whereisUserID, [
                'reputation' => $recbonus + $gift,
            ], $site_config['expires']['user_cache']);
            // giver
            $cache->update_row('user' . $CURUSER['id'], [
                'reputation' => $fromrep - $gift,
            ], $site_config['expires']['user_cache']);

            $cache->delete('user_rep_' . $whereisUserID);
            $save = [
                'reputation' => sqlesc($gift),
                'whoadded'   => sqlesc($CURUSER['id']),
                'reason'     => sqlesc('AJAX Chat'),
                'dateadd'    => time(),
                'locale'     => sqlesc('torrents'),
                'postid'     => 0,
                'userid'     => sqlesc($whereisUserID),
            ];
            $sql = 'INSERT INTO reputation (' . implode(', ', array_keys($save)) . ') VALUES (' . implode(', ', $save) . ')';
            sql_query($sql) or sqlerr(__FILE__, __LINE__);
            $this->insertChatBotMessage(
                $this->getChannel(),
                $text,
                1,
                600
            );
        } else {
            $this->insertChatBotMessage(
                $this->getPrivateMessageID(),
                '/error Unknown'
            );
        }
    }

    public function insertParsedMessageGift($textParts)
    {
        global $CURUSER, $cache;
        if (count($textParts) == 1) {
            $this->insertChatBotMessage(
                $this->getPrivateMessageID(),
                '/error MissingUserName'
            );

            return false;
        } elseif (count($textParts) == 2 || !is_numeric($textParts[2]) || $textParts[2] <= 0) {
            $this->insertChatBotMessage(
                $this->getPrivateMessageID(),
                '/error NotInteger'
            );

            return false;
        }
        $gift = number_format($textParts[2]);
        $sql = sql_query('SELECT seedbonus FROM users WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        $res = mysqli_fetch_row($sql);
        $frombonus = $res[0];
        if ((int)$textParts[2] > (int)$frombonus) {
            $this->insertChatBotMessage(
                $this->getPrivateMessageID(),
                '/error NotEnoughKarma'
            );

            return false;
        }
        $userName = $textParts[1];
        $whereisUserID = $this->getIDFromName($textParts[1]);
        $whereisRoleClass = get_user_class_name($this->getRoleFromID($whereisUserID), true);
        $user = '[' . $whereisRoleClass . ']' . $userName . '[/' . $whereisRoleClass . ']';
        $text = $user . ' has been given a Karma gift of ' . number_format($gift) . ' points from ' . $CURUSER['username'] . '.';
        $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . " - given karma gift of $gift by " . $CURUSER['username'] . '.';
        $recbonus = get_one_row('users', 'seedbonus', 'WHERE username = ' . sqlesc($userName));
        if (sql_query("UPDATE users SET seedbonus = seedbonus - $gift WHERE id = " . sqlesc($CURUSER['id']))) {
            sql_query("UPDATE users SET seedbonus = seedbonus + $gift, bonuscomment = CONCAT(" . sqlesc($bonuscomment) . ",'\n',IFNULL(bonuscomment,'')) WHERE username = " . sqlesc($userName)) or sqlerr(__FILE__, __LINE__);
            $cache->update_row('userstats_' . $whereisUserID, [
                'seedbonus' => $recbonus + $gift,
            ], 0);
            $cache->update_row('user_stats_' . $whereisUserID, [
                'seedbonus' => $recbonus + $gift,
            ], 0);
            $cache->update_row('userstats_' . $CURUSER['id'], [
                'seedbonus' => $frombonus - $gift,
            ], 0);
            $cache->update_row('user_stats_' . $CURUSER['id'], [
                'seedbonus' => $frombonus - $gift,
            ], 0);

            $this->insertChatBotMessage(
                $this->getChannel(),
                $text,
                1
            );
        } else {
            $this->insertChatBotMessage(
                $this->getPrivateMessageID(),
                '/error Unknown'
            );
        }
    }

    /**
     * @return null
     */
    public function getLoginUserName()
    {
        return getSessionVar('LoginUserName');
    }

    /**
     * @param $text
     * @param $textParts
     *
     * @return bool
     */
    public function parseCustomCommands($text, $textParts)
    {
        return false;
    }

    public function sendXMLMessages()
    {
        $httpHeader = new AJAXChatHTTPHeader('UTF-8', 'text/xml');
        $httpHeader->send();
        echo $this->getXMLMessages();
    }

    /**
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
                $xml .= '<![CDATA[' . $this->encodeSpecialChars($info) . ']]>';
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
            if (!isset($this->_infoMessages[ $type ])) {
                $this->_infoMessages[ $type ] = [];
            }

            return $this->_infoMessages[ $type ];
        } else {
            return $this->_infoMessages;
        }
    }

    /**
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
                    ' . $this->getDataBaseTable('messages') . '
                WHERE
                    ' . $this->getMessageCondition() . '
                    ' . $this->getMessageFilter() . '
                ORDER BY
                    id
                    DESC
                LIMIT ' . $this->getConfig('requestMessagesLimit') . ';';

        $result = sql_query($sql) or sqlerr(__FILE__, __LINE__);

        $messages = '';

        while ($row = mysqli_fetch_array($result)) {
            $message = $this->getChatViewMessageXML(
                $row['id'],
                $row['timeStamp'],
                $row['userID'],
                $row['userName'],
                $row['userRole'],
                $row['channelID'],
                $row['text']
            );
            if ($this->postDirection) {
                $messages = $messages . $message;
            } else {
                $messages = $message . $messages;
            }
        }
        $messages = '<messages>' . $messages . '</messages>';

        return $messages;
    }

    /**
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
        if ($this->getConfig('requestMessagesPriorChannelEnter') ||
            ($this->getConfig('requestMessagesPriorChannelEnterList') && in_array($this->getChannel(), $this->getConfig('requestMessagesPriorChannelEnterList')))) {
            $condition .= 'NOW() < DATE_ADD(dateTime, interval ' . $this->getConfig('requestMessagesTimeDiff') . ' HOUR)';
        } else {
            $condition .= 'dateTime >= FROM_UNIXTIME(' . $this->getChannelEnterTimeStamp() . ')';
        }

        return $condition;
    }

    /**
     * @return null
     */
    public function getChannelEnterTimeStamp()
    {
        return getSessionVar('ChannelEnterTimeStamp');
    }

    /**
     * @return string
     */
    public function getMessageFilter()
    {
        $filterChannelMessages = '';
        if (!$this->getConfig('showChannelMessages')) {
            $filterChannelMessages = '  AND NOT (
                                            text LIKE (\'/kick%\')
                                        )';
        }

        return $filterChannelMessages;
    }

    /**
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
                    ' . $this->getDataBaseTable('messages') . '
                WHERE
                    ' . $this->getTeaserMessageCondition() . '
                    ' . $this->getMessageFilter() . '
                ORDER BY
                    id
                    DESC
                LIMIT ' . $this->getConfig('requestMessagesLimit') . ';';

        $result = sql_query($sql) or sqlerr(__FILE__, __LINE__);

        $messages = '';

        while ($row = mysqli_fetch_array($result)) {
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
            if ($this->postDirection) {
                $messages = $messages . $message;
            } else {
                $messages = $message . $messages;
            }
        }
        $messages = '<messages>' . $messages . '</messages>';

        return $messages;
    }

    /**
     * @return string
     */
    public function getTeaserMessageCondition()
    {
        $channelID = $this->getValidRequestChannelID();
        $condition = 'channel = ' . sqlesc($channelID) . '
                        AND
                        ';
        if ($this->getConfig('requestMessagesPriorChannelEnter') ||
            ($this->getConfig('requestMessagesPriorChannelEnterList') && in_array($channelID, $this->getConfig('requestMessagesPriorChannelEnterList')))) {
            $condition .= 'NOW() < DATE_ADD(dateTime, interval ' . $this->getConfig('requestMessagesTimeDiff') . ' HOUR)';
        } else {
            $condition .= '0 = 1';
        }

        return $condition;
    }

    /**
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
                    INET6_NTOA(ip),
                    text
                FROM
                    ' . $this->getDataBaseTable('messages') . '
                WHERE
                    ' . $this->getLogsViewCondition() . '
                ORDER BY
                    id
                LIMIT ' . $this->getConfig('logsRequestMessagesLimit') . ';';

        $result = sql_query($sql) or sqlerr(__FILE__, __LINE__);

        $xml = '<messages>';
        while ($row = mysqli_fetch_array($result)) {
            $xml .= '<message';
            $xml .= ' id="' . $row['id'] . '"';
            $xml .= ' dateTime="' . date('r', $row['timeStamp']) . '"';
            $xml .= ' userID="' . $row['userID'] . '"';
            $xml .= ' userRole="' . $row['userRole'] . '"';
            $xml .= ' channelID="' . $row['channelID'] . '"';
            if ($this->getUserRole() >= UC_STAFF) {
                $xml .= ' ip="' . $row['ip'] . '"';
            }
            $xml .= '>';
            $xml .= '<username><![CDATA[' . $this->encodeSpecialChars($row['userName']) . ']]></username>';
            $xml .= '<text><![CDATA[' . $this->encodeSpecialChars($row['text']) . ']]></text>';
            $xml .= '</message>';
        }

        $xml .= '</messages>';

        return $xml;
    }

    /**
     * @return string
     */
    public function getLogsViewCondition()
    {
        $condition = 'id > ' . sqlesc($this->getRequestVar('lastID'));

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
                    $condition .= ' AND channel > ' . ($this->getConfig('privateMessageDiff') - 1);
                }
                break;
            case '-1':
                if ($this->getUserRole() <= UC_STAFF) {
                    $condition .= ' AND channel = ' . ($this->getPrivateChannelID());
                } else {
                    $condition .= ' AND (channel > ' . ($this->getConfig('privateChannelDiff') - 1) . ' AND channel < ' . ($this->getConfig('privateMessageDiff')) . ')';
                }
                break;
            default:
                if (($this->getUserRole() >= UC_ADMINISTRATOR || !$this->getConfig('logsUserAccessChannelList') || in_array($this->getRequestVar('channelID'), $this->getConfig('logsUserAccessChannelList')))
                    && $this->validateChannel($this->getRequestVar('channelID'))) {
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
                $day = date('j');
            }
            if ($month === null) {
                $month = date('n');
            }
            if ($year === null) {
                $year = date('Y');
            }
        }

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
                $condition .= ' AND (ip = ' . ipToStorageFormat($ip) . ')';
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
        $this->_requestVars[ $key ] = $value;
    }

    /**
     * @param null $channelIDs
     *
     * @return array|null
     */
    public function getOnlineUserIDs($channelIDs = null)
    {
        return $this->getOnlineUsersData($channelIDs, 'userID');
    }

    /**
     * @return null
     */
    public function getLoginTimeStamp()
    {
        return getSessionVar('LoginTimeStamp');
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
        return AJAXChatEncoding::htmlEncode($str, $this->getConfig('contentEncoding'));
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
     * @return null
     */
    public function getLang($key = null)
    {
        if (!$this->_lang) {
            // Include the language file:
            $lang = null;
            require_once AJAX_CHAT_PATH . 'lib' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $this->getLangCode() . '.php';
            $this->_lang = &$lang;
        }
        if ($key === null) {
            return $this->_lang;
        }
        if (isset($this->_lang[ $key ])) {
            return $this->_lang[ $key ];
        }

        return null;
    }

    /**
     * @return string
     */
    public function getChatURL()
    {
        if (defined('AJAX_CHAT_URL')) {
            return AJAX_CHAT_URL;
        }

        return
            (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') .
            (isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'] . '@' : '') .
            (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'] .
                (isset($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] == 443 || $_SERVER['SERVER_PORT'] == 80 ? '' : ':' . $_SERVER['SERVER_PORT']))) .
            substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/') + 1);
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
        if (!isset($this->_customVars[ $key ])) {
            return null;
        }

        return $this->_customVars[ $key ];
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
        $this->_customVars[ $key ] = $value;
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
