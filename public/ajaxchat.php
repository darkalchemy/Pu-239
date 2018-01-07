<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @author Philip Nicolcev
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */

// Path to the chat directory:
/**
 *
 */
define('AJAX_CHAT_PATH', dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'chat' . DIRECTORY_SEPARATOR);

// Include custom libraries and initialization code:
require_once AJAX_CHAT_PATH . 'lib' . DIRECTORY_SEPARATOR . 'custom.php';

// Include Class libraries:
require_once AJAX_CHAT_PATH . 'lib' . DIRECTORY_SEPARATOR . 'classes.php';

// Initialize the chat:
$ajaxChat = new CustomAJAXChat();
