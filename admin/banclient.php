<?php

declare(strict_types = 1);

require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);

$HTMLOUT = "
    <h1 class='has-text-centered'>Not Implemented Yet</h1>";

echo stdhead('Ban Torrent Clients') . wrapper($HTMLOUT) . stdfoot();
