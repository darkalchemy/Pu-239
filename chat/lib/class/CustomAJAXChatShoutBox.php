<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */

class CustomAJAXChatShoutBox extends CustomAJAXChat
{
    public function initialize()
    {
        // Initialize configuration settings:
        $this->initConfig();
    }

    public function getShoutBoxContent()
    {
        $template = new AJAXChatTemplate($this, AJAX_CHAT_PATH.'lib/template/shoutbox.html');

        // Return parsed template content:
        return $template->getParsedContent();
    }
}
