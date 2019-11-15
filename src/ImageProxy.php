<?php

declare(strict_types = 1);

namespace Pu239;

use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use Intervention\Image\ImageManager;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\Image\Image;
use Spatie\Image\Manipulations;
use Spatie\ImageOptimizer\OptimizerChainFactory;

/**
 * Class ImageProxy.
 */
class ImageProxy
{
    /**
     *
     * @param string $url
     * @param ?int   $width
     * @param ?int   $height
     * @param ?int   $quality
     *
     * @throws DependencyException
     * @throws InvalidManipulation
     * @throws NotFoundException
     * @throws \Envms\FluentPDO\Exception
     *
     * @return bool|string
     */
    public function get_image(string $url, ?int $width, ?int $height, ?int $quality)
    {
        if (empty($url) || $url == 'N/A') {
            return false;
        }

        $hash = hash('sha256', $url);
        $path = PROXY_IMAGES_DIR . $hash;

        if (file_exists($path) && !exif_imagetype($path)) {
            unlink($path);
        }

        if (!file_exists($path) && !$this->store_image($url, $path)) {
            return false;
        }
        if (!empty($quality)) {
            $hash = $this->convert_image($url, $path, $quality);
        } elseif (!empty($width) || !empty($height)) {
            $hash = $this->resize_image($url, false, $width, $height);
        }

        $this->set_permissions($path);
        $this->set_permissions(PROXY_IMAGES_DIR . $hash);

        return $hash;
    }

    /**
     *
     * @param string $url
     * @param string $path
     *
     * @throws NotFoundException
     * @throws \Envms\FluentPDO\Exception
     * @throws DependencyException
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
        try {
            if (!exif_imagetype($path)) {
                unlink($path);

                return false;
            }
        } catch (Exception $e) {
            // TODO
        }
        if ($this->optimize($path, false, false)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $path
     * @param bool   $failed
     * @param bool   $debug
     *
     * @return bool
     */
    protected function optimize(string $path, bool $failed, bool $debug)
    {
        if (mime_content_type($path) !== 'image/gif') {
            $temp = tempnam('/dev/shm', 'optimize');
            $optimizerChain = OptimizerChainFactory::create();
            $before = $after = 0;
            try {
                if ($debug) {
                    $before = filesize($path);
                }
                $optimizerChain->setTimeout(5)
                               ->optimize($path, $temp);
                rename($temp, $path);
                $this->set_permissions($path);
                if ($debug) {
                    $after = filesize($path);
                    $result = ($after - $before) / $before;
                    $bytes = mksize($before - $after);
                    echo sprintf("Optimize Results: %.2f%% (%s)\n", $result * 100, $bytes);
                }
            } catch (Exception $e) {
                unlink($temp);
                if (!$failed) {
                    if ($debug) {
                        echo 'Message: ' . $e->getMessage() . "\n";
                    }
                    $this->optimize($path, true, $debug);
                }
                if ($debug) {
                    echo 'Message: ' . $e->getMessage() . "\n";
                }

                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @param $path
     */
    protected function set_permissions(string $path)
    {
        if (file_exists($path) && is_writable(dirname($path)) && is_writable($path)) {
            chmod($path, 0775);
        }
    }

    /**
     * @param $url
     * @param $path
     * @param $quality
     *
     * @throws InvalidManipulation
     *
     * @return string
     */
    protected function convert_image(string $url, string $path, int $quality)
    {
        $hash = hash('sha256', $url . '_converted_' . $quality);
        $new_path = PROXY_IMAGES_DIR . $hash;

        if (!file_exists($path)) {
            return false;
        } elseif (file_exists($new_path)) {
            return $hash;
        }

        if (mime_content_type($path) !== 'image/gif') {
            if (mime_content_type($path) !== 'image/jpeg') {
                Image::load($path)
                     ->format(Manipulations::FORMAT_JPG)
                     ->quality($quality)
                     ->save($new_path);
            } else {
                Image::load($path)
                     ->quality($quality)
                     ->save($new_path);
            }
            $this->optimize($new_path, false, false);
        }

        return $hash;
    }

    /**
     *
     * @param string   $url
     * @param bool     $debug
     * @param null|int $width
     * @param null|int $height
     *
     * @throws DependencyException
     * @throws NotFoundException
     * @throws \Envms\FluentPDO\Exception
     *
     * @return bool|string
     */
    protected function resize_image(string $url, bool $debug, int $width = null, int $height = null)
    {
        $hash = hash('sha256', $url . (!empty($width) ? "_$width" : '') . (!empty($height) ? "_$height" : ''));
        $new_path = PROXY_IMAGES_DIR . $hash;
        if (file_exists($new_path)) {
            return $hash;
        }
        $this->store_image($url, $new_path);
        if (!empty($width) || !empty($height)) {
            try {
                $image = Image::load($new_path);
                if (!empty($width)) {
                    $image->width($width);
                }
                if (!empty($height)) {
                    $image->height($height);
                }
                $image->save($new_path);
            } catch (Exception $e) {
                echo 'Message: ' . $e->getMessage() . "\n";

                return false;
            }
        }
        $this->optimize($new_path, false, $debug);

        return $hash;
    }

    /**
     *
     * @param string   $path
     * @param string   $url
     * @param bool     $debug
     * @param null|int $width
     * @param null|int $height
     *
     * @throws DependencyException
     * @throws NotFoundException
     * @throws \Envms\FluentPDO\Exception
     *
     * @return bool
     */
    public function optimize_image(string $path, string $url, bool $debug, int $width = null, int $height = null)
    {
        if (!empty($width) || !empty($height)) {
            $hash = $this->resize_image($url, $debug, $width, $height);
            $path = PROXY_IMAGES_DIR . $hash;
        } else {
            $this->optimize($path, false, $debug);
        }
        $this->set_permissions($path);

        return true;
    }

    /**
     * @param int    $width
     * @param int    $height
     * @param string $color
     *
     * @return \Intervention\Image\Image
     */
    public function create_image(int $width, int $height, string $color)
    {
        $manager = new ImageManager(['driver' => 'imagick']);
        $img = $manager->canvas($width, $height, $color)
                       ->encode('jpg', 50);

        return $img;
    }
}
