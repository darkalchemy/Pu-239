<?php

declare(strict_types = 1);

use Pu239\User;

if (empty($_GET['wantusername'])) {
    die('<div class="margin10 has-text-info">You can\'t post nothing please enter a username!</div>');
}
require_once __DIR__ . '/../../include/bittorrent.php';
global $container;

$is_valid = valid_username($_GET['wantusername'], true);
if ($is_valid !== true) {
    echo $is_valid;
    die();
}

$user = $container->get(User::class);
if ($user->get_count_by_username(htmlsafechars($_GET['wantusername']))) {
    echo "<div class='has-text-danger tooltipper margin10' title='Username Not Available'><i class='icon-thumbs-down icon' aria-hidden='true'></i><b>Sorry... Username - " . htmlsafechars($_GET['wantusername']) . ' is already in use.</b></div>';
} else {
    echo "<div class='has-text-success tooltipper margin10' title='Username Available'><i class='icon-thumbs-up icon' aria-hidden='true'></i><b>Username Available</b></div>";
}
die();
