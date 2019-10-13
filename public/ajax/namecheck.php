<?php

declare(strict_types = 1);

if (empty($_GET['wantusername'])) {
    die('<div class="margin10 has-text-info">' . _('You must enter a username!') . '</div>');
}
require_once __DIR__ . '/../../include/bittorrent.php';
global $container;

valid_username($_GET['wantusername'], true, true);
