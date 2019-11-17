<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Database;
use Pu239\Image;
use Pu239\ImageProxy;
use Pu239\Person;
use Spatie\Image\Exceptions\InvalidManipulation;

require_once __DIR__ . '/../include/bittorrent.php';
global $container;

set_time_limit(18000);
$image_proxy = $container->get(ImageProxy::class);
$path = IMAGES_DIR . 'proxy/';
$fluent = $container->get(Database::class);
$images = $fluent->from('images')
                 ->select(null)
                 ->select('url')
                 ->select('type')
                 ->where('fetched = "no"')
                 ->orderBy('added DESC')
                 ->fetchAll();
$count = process_images($images, 'images');

$photos = $fluent->from('person')
                 ->select(null)
                 ->select('photo AS url')
                 ->where('photo IS NOT NULL')
                 ->where('updated + 604800 < ?', TIME_NOW)
                 ->fetchAll();
$count += process_images($photos, 'person');

/**
 *
 * @param array  $images
 * @param string $table
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws InvalidManipulation
 *
 * @return int
 */
function process_images(array $images, string $table)
{
    global $container;

    if (empty($images)) {
        return 0;
    }
    $images_class = $container->get(Image::class);
    $person_class = $container->get(Person::class);
    $count = 0;
    foreach ($images as $image) {
        if (empty($image['type'])) {
            $image['type'] = 'person';
        }
        $exists = false;
        if (file_exists(PROXY_IMAGES_DIR . hash('sha256', $image['url']))) {
            $exists = true;
        }
        $start = microtime(true);
        $untouched = url_proxy($image['url'], true);
        $end = microtime(true);
        $run = $end - $start;
        if (!$exists) {
            $sleep = 1.5 - $run > 0 ? (1.5 - $run) * 1000000 : 0;
            usleep((int) $sleep);
            echo 'slept: ' . $sleep / 1000000 . "\n";
        }
        echo 'untouched: ' . $run . "\n\n";

        if (!empty($untouched)) {
            if ($image['type'] === 'poster') {
                $start1 = microtime(true);
                url_proxy($image['url'], true, 450);
                $end1 = microtime(true);
                $run1 = $end1 - $start1;
                echo 'w450q100: ' . $run1 . "\n\n";

                $start2 = microtime(true);
                url_proxy($image['url'], true, 250);
                $end2 = microtime(true);
                $run2 = $end2 - $start2;
                echo 'w250q100: ' . $run2 . "\n\n";

                $start4 = microtime(true);
                url_proxy($image['url'], true, 100);
                $end4 = microtime(true);
                $run4 = $end4 - $start4;
                echo 'w100q100: ' . $run4 . "\n\n";

                $start5 = microtime(true);
                url_proxy($image['url'], true, null, 300);
                $end5 = microtime(true);
                $run5 = $end5 - $start5;
                echo 'h300q100: ' . $run5 . "\n\n";

                $start3 = microtime(true);
                url_proxy($image['url'], true, 250, null, 20);
                $end3 = microtime(true);
                $run3 = $end3 - $start3;
                echo 'w250q20: ' . $run3 . "\n\n";
            } elseif ($image['type'] === 'poster' || $image['type'] === 'person') {
                $start2 = microtime(true);
                url_proxy($image['url'], true, 250);
                $end2 = microtime(true);
                $run2 = $end2 - $start2;
                echo 'w250q100: ' . $run2 . "\n\n";

                $start3 = microtime(true);
                url_proxy($image['url'], true, null, 110);
                $end3 = microtime(true);
                $run3 = $end3 - $start3;
                echo 'w110q100: ' . $run3 . "\n\n";
            } elseif ($image['type'] === 'banner') {
                $start4 = microtime(true);
                url_proxy($image['url'], true, 1000, 185);
                $end4 = microtime(true);
                $run4 = $end4 - $start4;
                echo '1000x185: ' . $run4 . "\n\n";
            }
            ++$count;
            if ($table === 'images') {
                $values[] = [
                    'url' => $image['url'],
                    'fetched' => 'yes',
                ];
                $update = [
                    'fetched' => 'yes',
                ];
                $images_class->update($values, $update);
            } else {
                $update = [
                    'updated' => TIME_NOW,
                ];
                $person_class->update_by_url($update, $image['url']);
            }
        }
    }

    return $count;
}

echo $count . " optimized and resized\n";
