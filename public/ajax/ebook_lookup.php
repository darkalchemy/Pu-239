<?php

declare(strict_types = 1);

use Pu239\Torrent;
use Rakit\Validation\Validator;

require_once __DIR__ . '/../../include/bittorrent.php';
require_once INCL_DIR . 'function_books.php';
check_user_status();
header('content-type: application/json');
global $container;

$validator = $container->get(Validator::class);
$validation = $validator->validate($_POST, [
    'isbn' => 'regex:/[0-9Xx]*/',
    'tid' => 'required|integer',
    'name' => 'required',
]);
if ($validation->fails()) {
    echo json_encode(['content' => 'Invalid or missing parameters']);
    die();
}
$tid = (int) $_POST['tid'];
$torrents_class = $container->get(Torrent::class);
$torrent = $torrents_class->get($tid);
$poster = !empty($torrent['poster']) ? $torrent['poster'] : '';
$isbn = !empty($_POST['isbn']) ? $_POST['isbn'] : '000000';
$title = htmlsafechars($_POST['name']);
$book_info = get_book_info($isbn, $title, $tid, $poster);
if (!empty($book_info)) {
    echo json_encode(['content' => $book_info['ebook']]);
    die();
}

echo json_encode(['content' => 'Lookup Failed']);
die();
