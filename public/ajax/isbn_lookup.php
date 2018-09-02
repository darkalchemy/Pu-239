<?php

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'function_books.php';
check_user_status();
global $cache, $session;

extract($_POST);

header('content-type: application/json');

if (!$session->validateToken($csrf)) {
    echo json_encode(['content' => 'csrf']);
    die();
}

$isbn = str_replace([' ', '_', '-'], '', $isbn);
$torrent = $cache->get('torrent_details_' . $tid);
$poster = !empty($torrent['poster']) ? $torrent['poster'] : '';
$book_info = get_book_info($isbn, $name, $tid, $poster);
if (!empty($book_info)) {
    echo json_encode(['content' => $book_info]);
    die();
}

echo json_encode(['content' => 'Lookup Failed']);
die();
