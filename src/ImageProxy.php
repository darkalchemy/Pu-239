<?php

namespace DarkAlchemy\Pu239;

use Spatie\Image\Image;
use Intervention\Image\ImageManager;

class ImageProxy
{
    public function get_image($url, $image, $width, $height, $quality)
    {
        if (empty($url)) {
            return null;
        }

        $hash = hash('sha512', $url);
        $path = PROXY_IMAGES_DIR . $hash;

        if (!file_exists($path)) {
            $this->store_image($url, $path);
        }

        if (!file_exists($path)) {
            return null;
        }

        if (!empty($quality)) {
            $hash = $this->convert_image($url, $path, $quality);
        } elseif ($width || $height) {
            $hash = $this->resize_image($url, $path, $width, $height);
        }

        return $hash;
    }

    protected function store_image($url, $path)
    {
        $client = new \GuzzleHttp\Client([
            'synchronous' => true,
            'http_errors' => false,
            'sink' => $path,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36',
            ],
        ]);

        $response = $client->request('GET', $url);
        if ($response->getStatusCode() == 200) {
            $this->optimize($path);
        }
    }

    protected function convert_image($url, $path, $quality)
    {
        $hash = hash('sha512', $url . '_converted');
        $new_path = PROXY_IMAGES_DIR . $hash;

        if (file_exists($new_path)) {
            return $hash;
        }
        if (mime_content_type($path) !== 'image/gif') {
            if (mime_content_type($path) !== 'image/jpeg') {
                Image::load($new_path)
                    ->format(Manipulations::FORMAT_JPG)
                    ->quality($quality)
                    ->blur(50)
                    ->optimize()
                    ->save($new_path);
            } else {
                Image::load($path)
                    ->quality($quality)
                    ->blur(50)
                    ->optimize()
                    ->save($new_path);
            }
        }

        return $hash;
    }

    protected function optimize($path)
    {
        Image::load($path)
            ->optimize()
            ->save();
    }

    protected function resize_image($url, $path, $width = null, $height = null)
    {
        $manager = new ImageManager();
        $hash = hash('sha512', $url . (!empty($width) ? "_$width" : "_$height"));
        $new_path = PROXY_IMAGES_DIR . $hash;

        if (file_exists($new_path)) {
            return $hash;
        }
        try {
            $image = $manager->make($path)->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        } catch (\Exception $e) {
            return null;
        }
        $image->save($new_path);
        $this->optimize($new_path);

        return $hash;
    }

    public function optimize_image($path, $width = null, $height = null)
    {
        $manager = new ImageManager();

        if (!file_exists($path)) {
            return false;
        }

        if ($width || $height) {
            $image = $manager->make($path)->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $image->save($path);
        }
        $this->optimize($path);

        return true;
    }
}
