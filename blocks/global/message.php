<?php

declare(strict_types = 1);

use Pu239\Message;

$user = check_user_status();
global $container, $site_config;

if ($site_config['alerts']['message'] && !empty($user)) {
    $messages_class = $container->get(Message::class);
    $unread = $messages_class->get_count($user['id'], $site_config['pm']['inbox'], true);

    if (!empty($unread)) {
        $htmlout .= "
        <li>
            <a href='{$site_config['paths']['baseurl']}/messages.php'>
                <span class='button tag is-info has-text-black dt-tooltipper-small' data-tooltip-content='#message_tooltip'>
                   " . _pfe('{0} Unread PM', "{0} Unread PM's", $unread) . "
                </span>
                <div class='tooltip_templates'>
                    <div id='message_tooltip' class='margin20'>
                        <div class='size_6 has-text-centered has-text-success has-text-weight-bold bottom10'>
                            " . _pfe('{0} Unread PM', "{0} Unread PM's", $unread) . "
                        </div>
                        <div class='has-text-centered'>
                            " . _pfe('You have {0} new message', 'You have {0} new messages', $unread) . '
                        </div>
                    </div>
                </div>
            </a>
        </li>';
    }
}
