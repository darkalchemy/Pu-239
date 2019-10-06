<?php

declare(strict_types = 1);

use Pu239\Database;

global $container;

$fluent = $container->get(Database::class);
$id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : 0);
if (!is_valid_id($id)) {
    stderr(_('Error'), _('Invalid ID.'));
}
$what = $fluent->from('attachments')
               ->where('id = ?', $id)
               ->fetch();

$update = [
    'times_downloaded' => $what['times_downloaded'] + 1,
];
$fluent->update('attachments')
       ->set($update)
       ->where('id = ?', $id)
       ->execute();
$download_as = "{$what['file_name']}.{$what['extension']}";
$stored_file = ATTACHMENT_DIR . $what['file'];
header('Content-type: application/' . $what['extension']);
header('Content-Disposition: attachment; filename="' . $download_as . '"');
header('Content-length: ' . filesize($stored_file));
flush();
readfile("$stored_file");
