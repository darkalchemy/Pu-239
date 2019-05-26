<?php

declare(strict_types = 1);
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */

// List containing the custom channels:
$channels = [];
$channels = [
    $this->_siteConfig['site']['name'],
    'Support',
    'Announce',
    'News',
    'Git',
    'Staff',
    'Sysop',
];
