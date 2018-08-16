<?php

namespace DarkAlchemy\Pu239;

use Spatie\Image\Image;
use Intervention\Image\ImageManager;

class ImageProxy
{
    public function get_image($url, $width, $height, $quality)
    {
        if (empty($url)) {
            return false;
        }

        $hash = hash('sha512', $url);
        $path = PROXY_IMAGES_DIR . $hash;

        if (file_exists($path)) {
            if (!@is_array(getimagesize($path))) {
                unlink($path);
            }
        }

        if (!file_exists($path)) {
            $this->store_image($url, $path);
        }

        if (!file_exists($path)) {
            return false;
        }

        if (!is_array(getimagesize($path))) {
            unlink($path);

            return false;
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
        $image = fetch($url);
        if (!$image) {
            return false;
        }
        file_put_contents($path, $image);
        if (!file_exists($path)) {
            return false;
        }
        if (!is_array(getimagesize($path))) {
            unlink($path);
            return false;
        }
        chmod($path, 0775);
        if ($this->optimize($path)) {
            return true;
        }

        return false;
    }

    protected function convert_image($url, $path, $quality)
    {
        $hash = hash('sha512', $url . '_converted');
        $new_path = PROXY_IMAGES_DIR . $hash;

        if (file_exists($new_path)) {
            return $hash;
        }
dd($path);
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
        if (mime_content_type($path) !== 'image/gif') {
            Image::load($path)
                ->optimize()
                ->save();
        }
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
            $image = $manager->make($path)
                ->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
        } catch (\Exception $e) {
            return false;
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
            $image = $manager->make($path)
                ->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            $image->save($path);
        }
        $this->optimize($path);

        return true;
    }
}
