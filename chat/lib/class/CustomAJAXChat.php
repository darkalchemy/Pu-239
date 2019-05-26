<?php

declare(strict_types = 1);
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */

/**
 * Class CustomAJAXChat.
 */
class CustomAJAXChat extends AJAXChat
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws \Envms\FluentPDO\Exception
     *
     * @return bool
     */
    public function getValidLoginUserData()
    {
        $user = $this->_user->getUserFromId($this->getUserID());

        if (!empty($user) && $user['enabled'] === 'yes' && $user['chatpost'] === 1) {
            $userData['userID'] = $user['id'];
            $userData['userName'] = $this->trimUserName($user['username']);
            $userData['userClass'] = get_user_class_name($user['class']);
            $userData['userRole'] = $user['class'];
            $userData['channels'] = [
                0,
                1,
                2,
                3,
                4,
            ];
            if ($user['class'] >= UC_ADMINISTRATOR) {
                $userData['channels'] = [
                    0,
                    1,
                    2,
                    3,
                    4,
                    5,
                    6,
                ];
            } elseif ($user['class'] >= UC_STAFF) {
                $userData['channels'] = [
                    0,
                    1,
                    2,
                    3,
                    4,
                    5,
                ];
            }

            return $userData;
        }

        if ($user['chatpost'] !== 1) {
            $this->_session->unset('Channel');
            $this->addInfoMessage('errorBanned');
        }

        if ($user['enabled'] !== 'yes') {
            $this->_session->destroy();
        }

        return false;
    }

    /**
     * @return array|null
     */
    public function &getChannels()
    {
        $validChannels = [];
        if ($this->_channels === null) {
            $this->_channels = [];

            $customUsers = $this->getCustomUsers();

            if (!empty($this->getUserID())) {
                // Get the channels, the user has access to:
                $validChannels = $customUsers[$this->getUserID()]['channels'];
            }

            // Add the valid channels to the channel list (the defaultChannelID is always valid):
            foreach ($this->getAllChannels() as $key => $value) {
                if ($value == $this->getConfig('defaultChannelID')) {
                    $this->_channels[$key] = $value;
                    continue;
                }
                // Check if we have to limit the available channels:
                if ($this->getConfig('limitChannelList') && !in_array($value, $this->getConfig('limitChannelList'))) {
                    continue;
                }
                if (in_array($value, $validChannels)) {
                    $this->_channels[$key] = $value;
                }
            }
        }

        return $this->_channels;
    }

    /**
     * @return array
     */
    public function &getCustomUsers()
    {
        // List containing the registered chat users:
        $users = [];
        require_once AJAX_CHAT_PATH . 'lib' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'users.php';

        return $users;
    }

    /**
     * @return array|null
     */
    public function &getAllChannels()
    {
        if ($this->_allChannels === null) {
            // Get all existing channels:
            $customChannels = $this->getCustomChannels();

            $defaultChannelFound = false;

            foreach ($customChannels as $name => $id) {
                $this->_allChannels[$this->trimChannelName($name, $this->getConfig('contentEncoding'))] = $id;
                if ($id == $this->getConfig('defaultChannelID')) {
                    $defaultChannelFound = true;
                }
            }

            if (!$defaultChannelFound) {
                // Add the default channel as first array element to the channel list
                // First remove it in case it appeard under a different ID
                unset($this->_allChannels[$this->getConfig('defaultChannelName')]);
                $this->_allChannels = array_merge([
                    $this->trimChannelName($this->getConfig('defaultChannelName'), $this->getConfig('contentEncoding')) => $this->getConfig('defaultChannelID'),
                ], $this->_allChannels);
            }
        }

        return $this->_allChannels;
    }

    /**
     * @return array|null
     */
    public function getCustomChannels()
    {
        // List containing the custom channels:
        $channels = null;
        require_once AJAX_CHAT_PATH . 'lib' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'channels.php';
        // Channel array structure should be:
        // ChannelName => ChannelID
        return array_flip($channels);
    }
}
