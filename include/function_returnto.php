<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Rakit\Validation\Validator;

/**
 * @param string $partial_url
 *
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return bool|mixed
 */
function get_return_to(string $partial_url)
{
    global $container, $site_config;

    $validator = $container->get(Validator::class);
    $real_url = (strpos(urldecode($partial_url), $site_config['paths']['baseurl']) !== false) ? urldecode($partial_url) : $site_config['paths']['baseurl'] . urldecode($partial_url);
    $url = [
        'http_url' => $real_url,
    ];
    $validation = $validator->validate($url, [
        'http_url' => 'url',
    ]);
    if (!$validation->fails()) {
        $returnto = explode('?', urldecode($partial_url));
        if (file_exists(ROOT_DIR . trim('/', $returnto[0]))) {
            return $url['http_url'];
        }
    }

    return false;
}
