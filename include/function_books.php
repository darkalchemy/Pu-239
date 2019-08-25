<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_html.php';

use Biblys\Isbn\Isbn;
use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Cache;
use Pu239\Image;
use Pu239\Torrent;
use Scriptotek\GoogleBooks\GoogleBooks;
use Spatie\Image\Exceptions\InvalidManipulation;

/**
 * @param string|null $isbn
 * @param string|null $name
 * @param int|null    $tid
 * @param string|null $poster
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 * @throws InvalidManipulation
 *
 * @return array|bool
 */
function get_book_info(?string $isbn, ?string $name, ?int $tid, ?string $poster)
{
    global $container, $BLOCKS;

    if (!$BLOCKS['google_books_api_on']) {
        return false;
    }
    $name = htmlspecialchars_decode($name);
    $data = fetch_book_info($isbn, $name);
    if (!empty($data)) {
        $ebook = format_ebook_html($data['ebook'], $data['api_hits']);
        if (!empty($tid) && empty($poster)) {
            $torrents_class = $container->get(Torrent::class);
            $torrent = $torrents_class->get($tid);
            if (!empty($torrent)) {
                if (empty($torrent['poster']) && !empty($ebook['poster'])) {
                    $set = [
                        'poster' => $ebook['poster'],
                    ];
                }
                if (!empty($ebook)) {
                    if (!empty($ebook['categories'])) {
                        $temp = implode(', ', array_map('strtolower', $ebook['categories']));
                        $temp = explode(', ', $temp);
                        $set['newgenre'] = implode(', ', array_map('ucwords', $temp));
                    }
                    if (!empty($ebook['publishedDate'])) {
                        preg_match('/(\d{4})/', $ebook['publishedDate'], $match);
                        if (!empty($match[1])) {
                            $set['year'] = $match[1];
                        }
                    }
                    if (!empty($ebook['isbn13'])) {
                        $set['isbn'] = $ebook['isbn13'];
                    } elseif (!empty($ebook['isbn10'])) {
                        $set['isbn'] = $ebook['isbn10'];
                    }
                    if (!empty($ebook['rating'])) {
                        $set['rating'] = $ebook['rating'];
                    }
                }
                if (!empty($set)) {
                    $torrents_class->update($set, $tid);
                }
            }
        }

        return $ebook;
    }

    return false;
}

/**
 * @param array $ebook
 * @param int   $api_hits
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws Exception
 * @throws DependencyException
 * @throws InvalidManipulation
 * @throws NotFoundException
 *
 * @return array|bool
 */
function format_ebook_html(array $ebook, int $api_hits)
{
    global $container, $user;

    if (empty($ebook)) {
        return false;
    }
    $ebook_info = "<div class='columns'>
                        <span class='has-text-danger column is-2 size_5 padding5'>Title: </span>
                        <span class='column padding5'>{$ebook['title']}</span>
                    </div>";
    if (!empty($ebook['subtitle'])) {
        $ebook_info .= "<div class='columns'>
                        <span class='has-text-danger column is-2 size_5 padding5'>Subtitle: </span>
                        <span class='column padding5'>{$ebook['subtitle']}</span>
                    </div>";
    }
    $ebook_info .= "
                    <div class='columns'>
                        <span class='has-text-danger column is-2 size_5 padding5'>Author: </span>
                        <span class='column padding5'>" . implode(', ', $ebook['authors']) . " </span>
                    </div>
                    <div class='columns'>
                        <span class='has-text-danger column is-2 size_5 padding5'>Published: </span>
                        <span class='column padding5'>{$ebook['publisher']}" . (!empty($ebook['publisher']) ? '<br>' : '') . "{$ebook['publishedDate']}</span>
                    </div>";

    if (!empty($ebook['description'])) {
        $ebook_info .= "<div class='columns'>
                        <span class='has-text-danger column is-2 size_5 padding5'>Description: </span>
                        <span class='column padding5'>{$ebook['description']}</span>
                    </div>";
    }

    if (!empty($ebook['isbn10'])) {
        $ebook_info .= "<div class='columns'>
                        <span class='has-text-danger column is-2 size_5 padding5'>ISBN 10: </span>
                        <span class='column padding5'>
                            <a href='" . url_proxy("https://www.amazon.com/gp/search/field-isbn={$ebook['isbn10']}") . "' target='_blank'>{$ebook['isbn10']}</a>
                        </span>
                    </div>";
    }

    if (!empty($ebook['isbn13'])) {
        $ebook_info .= "<div class='columns'>
                        <span class='has-text-danger column is-2 size_5 padding5'>ISBN 13: </span>
                        <span class='column padding5'>
                            <a href='" . url_proxy("https://www.amazon.com/gp/search/field-isbn={$ebook['isbn13']}") . "' target='_blank'>{$ebook['isbn13']}</a>
                        </span>
                    </div>";
    }

    if (!empty($ebook['categories'])) {
        $ebook_info .= "<div class='columns'>
                        <span class='has-text-danger column is-2 size_5 padding5'>Genre: </span>
                        <span class='column padding5'>" . implode(', ', $ebook['categories']) . '</span>
                    </div>';
    }

    $ebook_info .= "<div class='columns'>
                        <span class='has-text-danger column is-2 size_5 padding5'>Pages: </span>
                        <span class='column padding5'>{$ebook['pageCount']}</span>
                    </div>";

    if (!empty($ebook['rating'])) {
        $ebook_info .= "<div class='columns'>
                        <span class='has-text-danger column is-2 size_5 padding5'>Rating: </span>
                        <span class='column padding5'>{$ebook['rating']}</span>
                    </div>";
    }

    if (!empty($user) && has_access($user['class'], UC_STAFF, 'coder')) {
        $ebook_info .= "<div class='columns'>
                        <span class='has-text-danger column is-2 size_5 padding5'>API Hits: </span>
                        <span class='column padding5'>$api_hits</span>
                    </div>";
    }

    $poster = '';
    if (!empty($ebook['poster'])) {
        $poster = $ebook['poster'];
        $values = [
            'url' => $poster,
            'type' => 'poster',
        ];
        if (!empty($ebook['isbn13'])) {
            $values['isbn'] = $ebook['isbn13'];
        } elseif (!empty($ebook['isbn10'])) {
            $values['isbn'] = $ebook['isbn10'];
        }
        $images_class = $container->get(Image::class);
        $images_class->insert($values);
        $ebook_info = "<div class='padding10'>
            <div class='columns'>
                <div class='column is-3'>
                    <img src='" . url_proxy($poster, true, 250) . "' class='round10 img-polaroid' alt='{$ebook['title']} Image'>
                </div>
                <div class='column'>
                    <div class='padding10'>$ebook_info</div>
                </div>
            </div>
        </div>";
    } else {
        $ebook_info = "<div class='padding10'>$ebook_info</div>";
    }

    return [
        'ebook' => $ebook_info,
        'poster' => $poster,
    ];
}

/**
 * @param string|null $isbn
 * @param string|null $name
 *
 * @throws NotFoundException
 * @throws DependencyException
 *
 * @return array|bool
 */
function fetch_book_info(?string $isbn, ?string $name)
{
    global $container, $user, $site_config;

    if (empty($isbn) && empty($name)) {
        return false;
    }
    $cache = $container->get(Cache::class);
    $api_hits = (int) $cache->get('google_api_hits_');
    $lookup = !empty($isbn) && $isbn != '00000' ? $isbn : $name;
    $hash = hash('sha256', $lookup);
    $cache->delete('book_info_' . $hash);
    $ebook = $cache->get('book_info_' . $hash);
    if ($ebook === false || is_null($ebook)) {
        $api_limit = 100;
        if (!empty($site_config['api']['google'])) {
            $api_limit = 1000;
        }
        if ($api_hits >= $api_limit) {
            if (!empty($user) && has_access($user['class'], UC_STAFF, 'coder')) {
                return [
                    "
                <div class='padding10'>
                    <div class='columns'>
                        <span class='has-text-danger column is-2 size_5 padding5'>Google API: </span>
                        <span class='column padding5'>API Limit exceeded: $api_hits / $api_limit</span>
                    </div>
                </div>",
                    '',
                ];
            }

            return false;
        } else {
            date_default_timezone_set('America/Los_Angeles');
            $secs = strtotime('tomorrow 00:00:00') - TIME_NOW;
            $cache->increment('google_api_hits_', 1, 0, $secs);
        }
        if (!empty($isbn) && $isbn != '00000') {
            $isbn_class = new Isbn($isbn);
            try {
                $isbn_class->validate();
            } catch (Exception $e) {
                return false;
            }
        }
        $books = $container->get(GoogleBooks::class);
        $cache->increment('google_api_hits_');
        try {
            $book = $books->volumes->byIsbn($isbn);
        } catch (Exception $e) {
            return false;
        }
        if (empty($book) && !empty($name)) {
            $cache->increment('google_api_hits_');
            try {
                $book = $books->volumes->firstOrNull($name);
            } catch (Exception $e) {
                return false;
            }
        }
        if (!empty($book) && !empty($book->title)) {
            $ebook['authors'] = $categories = [];
            $ebook['title'] = $book->title;
            $ebook['subtitle'] = !empty($book->subtitle) ? $book->subtitle : null;
            if (!empty($book->authors)) {
                foreach ($book->authors as $author) {
                    $ebook['authors'][] = $author;
                }
            }

            $ebook['rating'] = !empty($book->averageRating) ? $book->averageRating : null;
            $ebook['publisher'] = !empty($book->publisher) ? $book->publisher : null;
            $ebook['publishedDate'] = !empty($book->publishedDate) ? $book->publishedDate : null;
            $ebook['description'] = !empty($book->description) ? $book->description : null;
            if (!empty($book->industryIdentifiers)) {
                foreach ($book->industryIdentifiers as $industryIdentifier) {
                    foreach ($industryIdentifier as $key => $value) {
                        if (strlen($value) === 10) {
                            $ebook['isbn10'] = $value;
                        } elseif (strlen($value) === 13) {
                            $ebook['isbn13'] = $value;
                        }
                    }
                }
            }

            if (!empty($book->categories)) {
                foreach ($book->categories as $category) {
                    $ebook['categories'][] = $category;
                }
            }
            $ebook['pageCount'] = !empty($book->pageCount) ? $book->pageCount : null;
            $ebook['poster'] = null;
            if (!empty($book->imageLinks->large)) {
                $ebook['poster'] = $book->imageLinks->large;
            } elseif (!empty($book->imageLinks->medium)) {
                $ebook['poster'] = $book->imageLinks->medium;
            } elseif (!empty($book->imageLinks->small)) {
                $ebook['poster'] = $book->imageLinks->small;
            } elseif (!empty($book->imageLinks->thumbnail)) {
                $ebook['poster'] = $book->imageLinks->thumbnail;
            } elseif (!empty($book->imageLinks->smallThumbnail)) {
                $ebook['poster'] = $book->imageLinks->smallThumbnail;
            }
            $ebook['poster'] = str_replace([
                'zoom=5',
                'zoom=4',
                'zoom=3',
                'zoom=2',
                'zoom=1',
            ], 'zoom=0', $ebook['poster']);
            $cache->set('book_info_' . $hash, $ebook, $site_config['expires']['book_info']);

            return [
                'ebook' => $ebook,
                'api_hits' => $api_hits,
            ];
        } else {
            $cache->set('book_info_' . $hash, 'failed', 86400);

            return false;
        }
    }

    return [
        'ebook' => $ebook,
        'api_hits' => $api_hits,
    ];
}
