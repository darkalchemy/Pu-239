<?php

namespace DarkAlchemy\Pu239;

use Spatie\Image\Image;
use Intervention\Image\ImageManager;
use Spatie\Image\Manipulations;
use Spatie\ImageOptimizer\OptimizerChainFactory;

/**
 * Class ImageProxy.
 */
class ImageProxy
{
    /**
     * @param string   $url
     * @param int|null $width
     * @param int|null $height
     * @param int|null $quality
     *
     * @return bool|string
     *
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function get_image(string $url, ?int $width, ?int $height, ?int $quality)
    {
        if (empty($url) || $url == 'N/A') {
            return false;
        }

        $hash = hash('sha512', $url);
        $path = PROXY_IMAGES_DIR . $hash;

        if (file_exists($path)) {
            if (!exif_imagetype($path)) {
                unlink($path);
            }
        }

        if (!file_exists($path)) {
            if (!$this->store_image($url, $path)) {
                return false;
            }
        }

        if (!file_exists($path)) {
            return false;
        }

        if (!empty($quality)) {
            $hash = $this->convert_image($url, $path, $quality);
        } elseif ($width || $height) {
            $hash = $this->resize_image($url, $path, $width, $height);
        }

        return $hash;
    }

    /**
     * @param $url
     * @param $path
     *
     * @return bool
     */
    protected function store_image(string $url, string $path)
    {
        $image = fetch($url);
        if (!$image) {
            return false;
        }
        if (!file_put_contents($path, $image)) {
            return false;
        }
        if (!exif_imagetype($path)) {
            unlink($path);

            return false;
        }
        chmod($path, 0775);
        if ($this->optimize($path, false)) {
            return true;
        }

        return false;
    }

    /**
     * @param $url
     * @param $path
     * @param $quality
     *
     * @return string
     *
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    protected function convert_image(string $url, string $path, ?int $quality)
    {
        $hash = hash('sha512', $url . '_converted' . (!empty($quality) ? '_' . $quality : ''));
        $new_path = PROXY_IMAGES_DIR . $hash;

        if (!file_exists($path)) {
            return false;
        }

        if (file_exists($new_path)) {
            return $hash;
        }

        if (mime_content_type($path) !== 'image/gif') {
            if (mime_content_type($path) !== 'image/jpeg') {
                Image::load($path)
                    ->format(Manipulations::FORMAT_JPG)
                    ->save($new_path, $quality);
            } else {
                Image::load($path)
                    ->save($new_path, $quality);
            }
            $this->optimize($new_path, false);
        }

        return $hash;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    protected function optimize(string $path, bool $failed)
    {
        if (mime_content_type($path) !== 'image/gif') {
            $temp = '/dev/shm/temp.jpg';
            echo 'Filesize before: ' .  filesize($path) . "\n";
            $optimizerChain = OptimizerChainFactory::create();
            try {
                $optimizerChain->setTimeout(5)->optimize($path, $temp);
                rename($temp, $path);
                echo 'Filesize after: ' .  filesize($path) . "\n";
            } catch (\Exception $e) {
                unlink($temp);
                if (!$failed) {
                    echo 'Message: ' . $e->getMessage() . "\n";
                    $this->optimize($path, true);
                }
                echo 'Message: ' . $e->getMessage() . "\n";

                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @param string   $url
     * @param string   $path
     * @param int|null $width
     * @param int|null $height
     *
     * @return bool|string
     */
    protected function resize_image(string $url, string $path, int $width = null, int $height = null)
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
            echo 'Message: ' . $e->getMessage() . "\n";

            return false;
        }
        $image->save($new_path);
        $this->optimize($new_path, false);

        return $hash;
    }

    /**
     * @param string   $path
     * @param int|null $width
     * @param int|null $height
     *
     * @return bool
     */
    public function optimize_image(string $path, int $width = null, int $height = null)
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
        $this->optimize($path, false);

        return true;
    }

    /**
     * @param int         $width
     * @param int         $height
     * @param string|null $color
     *
     * @return \Intervention\Image\Image
     */
    public function create_image(int $width = 1000, int $height = 1000, string $color = null)
    {
        $manager = new ImageManager();
        $img = $manager->canvas($width, $height, $color)
            ->encode('jpg', 50);

        return $img;
    }
}
