<?php

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'function_books.php';
global $session, $torrent_stuffs;

extract($_POST);

header('content-type: application/json');

if (empty($csrf) || !$session->validateToken($csrf)) {
    echo json_encode(['content' => 'csrf']);
    die();
}

$isbn = str_replace([
    ' ',
    '_',
    '-',
], '', $isbn);
$torrent = $torrent_stuffs->get($tid);
$poster = !empty($torrent['poster']) ? $torrent['poster'] : '';
$book_info = get_book_info((!empty($isbn) ? $isbn : '000000'), $name, $tid, $poster);
if (!empty($book_info)) {
    echo json_encode(['content' => $book_info[0]]);
    die();
}

echo json_encode(['content' => 'Lookup Failed']);
die();
