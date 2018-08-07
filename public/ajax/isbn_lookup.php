<?php

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'function_books.php';
check_user_status();
global $CURUSER, $site_config, $fluent, $cache;

extract($_POST);

header('content-type: application/json');

if (!$session->validateToken($csrf)) {
    echo json_encode(['content' => 'csrf']);
    die();
}

if (!empty($isbn)) {
    $isbn = str_replace([' ', '_', '-'], '', $isbn);
    if (!empty($isbn) && (strlen($isbn) === 10 || strlen($isbn) === 13)) {
        $torrent = [
            'isbn' => $isbn,
            'name' => '',
        ];
        $book_info = get_book_info($torrent);
        if (!empty($book_info)) {
            echo json_encode([
                'content' => $book_info,
            ]);
            die();
        }
    }
}
