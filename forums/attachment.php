<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Database;

/**
 * @param int $post_id
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return array
 */
function upload_attachments(int $post_id)
{
    global $container, $site_config, $CURUSER;

    $max_file_size = (int) $site_config['forum_config']['max_file_size'];
    $upload_folder = ROOT_DIR . $site_config['forum_config']['upload_folder'];
    $extension_error = $size_error = 0;
    if ($CURUSER['class'] >= $site_config['forum_config']['min_upload_class']) {
        foreach ($_FILES['attachment']['name'] as $key => $name) {
            if (!empty($name)) {
                $size = (int) $_FILES['attachment']['size'][$key];
                $type = $_FILES['attachment']['type'][$key];
                $accepted_file_types = explode('|', $site_config['forum_config']['accepted_file_types']);
                $accepted_file_extension = explode('|', $site_config['forum_config']['accepted_file_extension']);
                $file_name = preg_replace('#[^\s\[\]\.a-zA-Z0-9_-]#', '', pathinfo($name, PATHINFO_FILENAME));
                $file_extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                switch (true) {
                    case $size > $max_file_size:
                        $size_error++;
                        break;

                    case !in_array($file_extension, $accepted_file_extension):
                        $extension_error++;
                        break;

                    case !in_array($type, $accepted_file_types):
                        $extension_error++;
                        break;

                    default:
                        $upload_name = $file_name . '(pid-' . $post_id . ').' . $file_extension;
                        $upload_to = $upload_folder . $upload_name;
                        $values = [
                            'post_id' => $post_id,
                            'user_id' => $CURUSER['id'],
                            'file' => $upload_name,
                            'file_name' => $file_name,
                            'added' => TIME_NOW,
                            'extension' => $file_extension,
                            'size' => $size,
                        ];
                        $fluent = $container->get(Database::class);
                        $fluent->insertInto('attachments')
                               ->values($values)
                               ->execute();
                        copy($_FILES['attachment']['tmp_name'][$key], $upload_to);
                        chmod($upload_to, 0777);
                }
            }
        }

        return [
            $size_error,
            $extension_error,
        ];
    }
}
