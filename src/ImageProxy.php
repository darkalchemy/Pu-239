<?php

namespace DarkAlchemy\Pu239;

use Spatie\Image\Image;
use Intervention\Image\ImageManager;
//use Spatie\ImageOptimizer\OptimizerChainFactory AS Image;

class ImageProxy
{
    public function get_image($url, $image, $width, $height, $quality)
    {
        if (empty($url)) {
            //dd('url is empty');
            return null;
        }

        $hash = hash('sha512', $url);
        $path = PROXY_IMAGES_DIR . $hash;

        if (!file_exists($path)) {
            $this->store_image($url, $path);
        }
        if (!file_exists($path)) {
            //dd('could not save image ' . $url);
            return null;
        }

        if (!empty($quality)) {
            $hash = $this->convert_image($url, $path, $quality);
            //dd($hash);
        } elseif ($width || $height) {
            $hash = $this->resize_image($url, $path, $width, $height);
            //dd($hash);
        }
        //dd($hash);
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
        echo 'convert';
        if (mime_content_type($path) != "image/jpeg") {
            Image::load($new_path)
                ->format(Manipulations::FORMAT_JPG)
                //->quality(50)
                //->blur(75)
                ->quality($quality)
                ->optimize()
                ->save($new_path);
        } else {
            Image::load($path)
                //->quality(50)
                //->blur(75)
                ->quality($quality)
                ->optimize()
                ->save($new_path);
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
        echo 'resize';
        $image = $manager->make($path)->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $image->save($new_path);
        $this->optimize($new_path);

        return $hash;
    }
}
