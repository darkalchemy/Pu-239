<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */

// Class to handle HTML templates

/**
 * Class AJAXChatTemplate
 */
class AJAXChatTemplate
{
    public $ajaxChat;

    protected $_regExpTemplateTags;
    protected $_templateFile;
    protected $_contentType;
    protected $_content;
    protected $_parsedContent;

    /**
     * AJAXChatTemplate constructor.
     *
     * @param      $ajaxChat
     * @param      $templateFile
     * @param null $contentType
     */
    public function __construct(&$ajaxChat, $templateFile, $contentType = null)
    {
        $this->ajaxChat = $ajaxChat;
        $this->_regExpTemplateTags = '/\[(\w+?)(?:(?:\/)|(?:\](.+?)\[\/\1))\]/s';
        $this->_templateFile = $templateFile;
        $this->_contentType = $contentType;
    }

    /**
     * @return mixed
     */
    public function getParsedContent()
    {
        if (!$this->_parsedContent) {
            $this->parseContent();
        }

        return $this->_parsedContent;
    }

    /**
     *
     */
    public function parseContent()
    {
        $this->_parsedContent = $this->getContent();

        if ($this->_contentType && (strpos($this->_contentType, 'xml') === false)) {
            $doctypeStart = strpos($this->_parsedContent, '<!doctype ');
            if ($doctypeStart !== false) {
                $this->_parsedContent = substr($this->_parsedContent, $doctypeStart);
            }
        }

        $this->_parsedContent = preg_replace_callback($this->_regExpTemplateTags, [$this, 'replaceTemplateTags'], $this->_parsedContent);
    }

    /**
     * @return bool|string
     */
    public function getContent()
    {
        if (!$this->_content) {
            $this->_content = AJAXChatFileSystem::getFileContents($this->_templateFile);
        }

        return $this->_content;
    }

    /**
     * @param $tagData
     *
     * @return string
     */
    public function replaceTemplateTags($tagData)
    {
        switch ($tagData[1]) {
            case 'AJAX_CHAT_URL':
                return $this->ajaxChat->htmlEncode($this->ajaxChat->getChatURL());

            case 'JS':
                return $this->ajaxChat->getConfig('js');
            case 'JSLOG':
                return $this->ajaxChat->getConfig('jslog');

            case 'LANG':
                return $this->ajaxChat->htmlEncode($this->ajaxChat->getLang((isset($tagData[2]) ? $tagData[2] : null)));
            case 'LANG_CODE':
                return $this->ajaxChat->getLangCode();

            case 'BASE_DIRECTION':
                return $this->getBaseDirectionAttribute();

            case 'CONTENT_ENCODING':
                return $this->ajaxChat->getConfig('contentEncoding');

            case 'CONTENT_TYPE':
                return $this->_contentType;

            case 'LOGIN_URL':
                return ($this->ajaxChat->getRequestVar('view') == 'logs') ? './?view=logs' : './';

            case 'USER_NAME_MAX_LENGTH':
                return $this->ajaxChat->getConfig('userNameMaxLength');
            case 'MESSAGE_TEXT_MAX_LENGTH':
                return $this->ajaxChat->getConfig('messageTextMaxLength');

            case 'LOGIN_CHANNEL_ID':
                return $this->ajaxChat->getValidRequestChannelID();

            case 'SITE_NAME':
                return $this->ajaxChat->getConfig('siteName');
            case 'SESSION_NAME':
                return $this->ajaxChat->getConfig('sessionName');
            case 'SESSION_KEY_PREFIX':
                return $this->ajaxChat->getConfig('sessionKeyPrefix');
            case 'COOKIE_EXPIRATION':
                return $this->ajaxChat->getConfig('sessionCookieLifeTime');
            case 'COOKIE_PATH':
                return $this->ajaxChat->getConfig('sessionCookiePath');
            case 'COOKIE_DOMAIN':
                return $this->ajaxChat->getConfig('sessionCookieDomain');

            case 'CHAT_BOT_NAME':
                return rawurlencode($this->ajaxChat->getConfig('chatBotName'));
            case 'CHAT_BOT_ID':
                return $this->ajaxChat->getConfig('chatBotID');
            case 'CHAT_BOT_ROLE':
                return $this->ajaxChat->getConfig('chatBotRole');

            case 'ALLOW_USER_MESSAGE_DELETE':
                if ($this->ajaxChat->getConfig('allowUserMessageDelete')) {
                    return 1;
                } else {
                    return 0;
                }

            // no break
            case 'INACTIVE_TIMEOUT':
                return $this->ajaxChat->getConfig('inactiveTimeout');

            case 'PRIVATE_CHANNEL_DIFF':
                return $this->ajaxChat->getConfig('privateChannelDiff');
            case 'PRIVATE_MESSAGE_DIFF':
                return $this->ajaxChat->getConfig('privateMessageDiff');

            case 'SHOW_CHANNEL_MESSAGES':
                if ($this->ajaxChat->getConfig('showChannelMessages')) {
                    return 1;
                } else {
                    return 0;
                }

            // no break
            case 'SOCKET_SERVER_ENABLED':
                if ($this->ajaxChat->getConfig('socketServerEnabled')) {
                    return 1;
                } else {
                    return 0;
                }

            // no break
            case 'SOCKET_SERVER_HOST':
                if ($this->ajaxChat->getConfig('socketServerHost')) {
                    $socketServerHost = $this->ajaxChat->getConfig('socketServerHost');
                } else {
                    $socketServerHost = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);
                }

                return rawurlencode($socketServerHost);

            case 'SOCKET_SERVER_PORT':
                return $this->ajaxChat->getConfig('socketServerPort');

            case 'SOCKET_SERVER_CHAT_ID':
                return $this->ajaxChat->getConfig('socketServerChatID');

            case 'STYLE_SHEETS':
                return $this->getStyleSheetLinkTags();

            case 'CHANNEL_OPTIONS':
                return $this->getChannelOptionTags();
            case 'STYLE_OPTIONS':
                return $this->getStyleOptionTags();
            case 'LANGUAGE_OPTIONS':
                return $this->getLanguageOptionTags();

            case 'ERROR_MESSAGES':
                return $this->getErrorMessageTags();

            case 'LOGS_CHANNEL_OPTIONS':
                return $this->getLogsChannelOptionTags();
            case 'LOGS_YEAR_OPTIONS':
                return $this->getLogsYearOptionTags();
            case 'LOGS_MONTH_OPTIONS':
                return $this->getLogsMonthOptionTags();
            case 'LOGS_DAY_OPTIONS':
                return $this->getLogsDayOptionTags();
            case 'LOGS_HOUR_OPTIONS':
                return $this->getLogsHourOptionTags();
            case 'CLASS_WRITEABLE':
                return 'write_allowed';
            case 'TOKEN':
                return session_id();

            case 'ANON_LINK':
                return $this->ajaxChat->getConfig('anonymous_link');

            default:
                return $this->ajaxChat->replaceCustomTemplateTags($tagData[1], (isset($tagData[2]) ? $tagData[2] : null));
        }
    }

    /**
     * @return string
     */
    public function getBaseDirectionAttribute()
    {
        $langCodeParts = explode('-', $this->ajaxChat->getLangCode());
        switch ($langCodeParts[0]) {
            case 'ar':
            case 'fa':
            case 'he':
                return 'rtl';
            default:
                return 'ltr';
        }
    }

    /**
     * @return string
     */
    public function getStyleSheetLinkTags()
    {
        return '
        <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Acme|Baloo+Bhaijaan|Encode+Sans+Condensed|Lobster|Nova+Square|Open+Sans|Oswald|PT+Sans+Narrow" />
        <link rel="stylesheet" href="' . get_file_name('chat_css_trans') . '" title="transparent" />
        <link rel="alternate stylesheet" href="' . get_file_name('chat_css_uranium') . '" title="Uranium" />';
    }

    /**
     * @return string
     */
    public function getChannelOptionTags()
    {
        $channelOptions = '';
        $channelSelected = false;
        foreach ($this->ajaxChat->getChannels() as $name => $id) {
            if ($this->ajaxChat->isLoggedIn() && $this->ajaxChat->getChannel()) {
                $selected = ($id == $this->ajaxChat->getChannel()) ? ' selected="selected"' : '';
            } else {
                $selected = ($id == $this->ajaxChat->getConfig('defaultChannelID')) ? ' selected="selected"' : '';
            }
            if ($selected) {
                $channelSelected = true;
            }
            $channelOptions .= '<option value="' . $this->ajaxChat->htmlEncode($name) . '"' . $selected . '>' . $this->ajaxChat->htmlEncode($name) . '</option>';
        }
        if ($this->ajaxChat->isLoggedIn() && $this->ajaxChat->isAllowedToCreatePrivateChannel()) {
            if (!$channelSelected && $this->ajaxChat->getPrivateChannelID() == $this->ajaxChat->getChannel()) {
                $selected = ' selected="selected"';
                $channelSelected = true;
            } else {
                $selected = '';
            }
            $privateChannelName = $this->ajaxChat->getPrivateChannelName();
            $channelOptions .= '<option value="' . $this->ajaxChat->htmlEncode($privateChannelName) . '"' . $selected . '>' . $this->ajaxChat->htmlEncode($privateChannelName) . '</option>';
        }
        if (!$channelSelected) {
            $channelName = $this->ajaxChat->getChannelName();
            if ($channelName !== null) {
                $channelOptions .= '<option value="' . $this->ajaxChat->htmlEncode($channelName) . '" selected="selected">' . $this->ajaxChat->htmlEncode($channelName) . '</option>';
            } else {
                $channelOptions .= '<option value="" selected="selected">---</option>';
            }
        }

        return $channelOptions;
    }

    /**
     * @return string
     */
    public function getStyleOptionTags()
    {
        $styleOptions = '';
        foreach ($this->ajaxChat->getConfig('styleAvailable') as $style) {
            $selected = ($style == $this->ajaxChat->getConfig('styleDefault')) ? ' selected="selected"' : '';
            $styleOptions .= '<option value="' . $this->ajaxChat->htmlEncode($style) . '"' . $selected . '>' . $this->ajaxChat->htmlEncode($style) . '</option>';
        }

        return $styleOptions;
    }

    /**
     * @return string
     */
    public function getLanguageOptionTags()
    {
        $languageOptions = '';
        $languageNames = $this->ajaxChat->getConfig('langNames');
        foreach ($this->ajaxChat->getConfig('langAvailable') as $langCode) {
            $selected = ($langCode == $this->ajaxChat->getLangCode()) ? ' selected="selected"' : '';
            $languageOptions .= '<option value="' . $this->ajaxChat->htmlEncode($langCode) . '"' . $selected . '>' . $languageNames[$langCode] . '</option>';
        }

        return $languageOptions;
    }

    /**
     * @return string
     */
    public function getErrorMessageTags()
    {
        $errorMessages = '';
        foreach ($this->ajaxChat->getInfoMessages('error') as $error) {
            $errorMessages .= '<div class="has-text-centered">' . $this->ajaxChat->htmlEncode($this->ajaxChat->getLang($error)) . '</div>';
        }

        return $errorMessages;
    }

    /**
     * @return string
     */
    public function getLogsChannelOptionTags()
    {
        $channelOptions = '';
        $channelOptions .= '<option value="-3">------</option>';
        foreach ($this->ajaxChat->getChannels() as $key => $value) {
            if ($this->ajaxChat->getUserRole() <= UC_STAFF && $this->ajaxChat->getConfig('logsUserAccessChannelList') && !in_array($value, $this->ajaxChat->getConfig('logsUserAccessChannelList'))) {
                continue;
            }
            $channelOptions .= '<option value="' . $value . '">' . $this->ajaxChat->htmlEncode($key) . '</option>';
        }
        $channelOptions .= '<option value="-1">' . $this->ajaxChat->htmlEncode($this->ajaxChat->getLang('logsPrivateChannels')) . '</option>';
        $channelOptions .= '<option value="-2">' . $this->ajaxChat->htmlEncode($this->ajaxChat->getLang('logsPrivateMessages')) . '</option>';

        return $channelOptions;
    }

    /**
     * @return string
     */
    public function getLogsYearOptionTags()
    {
        $yearOptions = '';
        $yearOptions .= '<option value="-1">----</option>';
        for ($year = date('Y'); $year >= $this->ajaxChat->getConfig('logsFirstYear'); --$year) {
            $yearOptions .= '<option value="' . $year . '">' . $year . '</option>';
        }

        return $yearOptions;
    }

    /**
     * @return string
     */
    public function getLogsMonthOptionTags()
    {
        $monthOptions = '';
        $monthOptions .= '<option value="-1">--</option>';
        for ($month = 1; $month <= 12; ++$month) {
            $monthOptions .= '<option value="' . $month . '">' . sprintf('%02d', $month) . '</option>';
        }

        return $monthOptions;
    }

    /**
     * @return string
     */
    public function getLogsDayOptionTags()
    {
        $dayOptions = '';
        $dayOptions .= '<option value="-1">--</option>';
        for ($day = 1; $day <= 31; ++$day) {
            $dayOptions .= '<option value="' . $day . '">' . sprintf('%02d', $day) . '</option>';
        }

        return $dayOptions;
    }

    /**
     * @return string
     */
    public function getLogsHourOptionTags()
    {
        $hourOptions = '';
        $hourOptions .= '<option value="-1">-----</option>';
        for ($hour = 0; $hour <= 23; ++$hour) {
            $hourOptions .= '<option value="' . $hour . '">' . sprintf('%02d', $hour) . ':00</option>';
        }

        return $hourOptions;
    }

    /**
     * @param string $rowOdd
     * @param string $rowEven
     *
     * @return string
     */
    public function alternateRow($rowOdd = 'rowOdd', $rowEven = 'rowEven')
    {
        static $i;
        $i += 1;
        if ($i % 2 == 0) {
            return $rowEven;
        } else {
            return $rowOdd;
        }
    }
}
