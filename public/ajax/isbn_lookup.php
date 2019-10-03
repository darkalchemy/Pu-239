<?php

declare(strict_types = 1);

use Rakit\Validation\Validator;

require_once __DIR__ . '/../../include/bittorrent.php';
require_once INCL_DIR . 'function_books.php';
check_user_status();
header('content-type: application/json');
global $container;

$validator = $container->get(Validator::class);
$validation = $validator->validate($_POST, [
    'isbn' => 'regex:/[0-9Xx]*/',
]);
if ($validation->fails()) {
    echo json_encode(['content' => 'invalid']);
    die();
}
$book_info = get_book_info($_POST['isbn'], '', 0, '');
if (!empty($book_info)) {
    echo json_encode(['content' => $book_info['ebook']]);
    die();
}

echo json_encode(['content' => 'Lookup Failed']);
die();
