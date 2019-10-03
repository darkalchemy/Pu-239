<?php

declare(strict_types = 1);

use Pu239\User;

if (empty($_GET['wantemail'])) {
    die('<div class="margin10 has-text-info">You can\'t post nothing please enter a email!</div>');
}
require_once __DIR__ . '/../../include/bittorrent.php';
global $container;

if (is_array($_GET['wantemail']) || !validemail($_GET['wantemail'])) {
    echo "<span class='has-text-danger'>" . _('Invalid Email Address') . '</span>';
    die();
}

$user = $container->get(User::class);
if ($user->get_count_by_email(htmlsafechars($_GET['wantemail']))) {
    echo "<div class='has-text-danger tooltipper margin10' title='" . _('Email Not Available') . "'><i class='icon-thumbs-down icon' aria-hidden='true'></i>" . _fe('Email: {0} is unavailable.', format_comment($_GET['wantemail'])) . '</div>';
} else {
    echo "<div class='has-text-success tooltipper margin10' title='" . _('Email Available') . "'><i class='icon-thumbs-up icon' aria-hidden='true'></i><b>" . _('Email Available') . '</b></div>';
}
die();
