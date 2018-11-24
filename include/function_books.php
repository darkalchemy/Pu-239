<?php

require_once INCL_DIR . 'html_functions.php';

use Scriptotek\GoogleBooks\GoogleBooks;

function get_book_info($isbn, $name, $tid, $poster)
{
    global $site_config, $CURUSER, $cache, $BLOCKS, $torrent_stuffs, $image_stuffs;

    if (!$BLOCKS['google_books_api_on']) {
        return false;
    }

    $search = !empty($isbn) ? $isbn : $name;
    $api_hits = $cache->get('google_api_limits_');
    $hash = hash('sha256', $search);
    $ebook = $cache->get('book_info_' . $hash);
    if ($ebook === false || is_null($ebook)) {
        $api_limit = 100;
        if (!empty($_ENV['GOOGLE_API_KEY'])) {
            $api_limit = 1000;
        }
        if ($api_hits >= $api_limit) {
            if ($CURUSER['class'] >= UC_STAFF) {
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
            $cache->increment('google_api_limits_', 1, 0, $secs);
        }

        if (!empty($_ENV['GOOGLE_API_KEY'])) {
            $books = new GoogleBooks(['key' => $_ENV['GOOGLE_API_KEY']]);
        } else {
            $books = new GoogleBooks();
        }
        if (!empty($isbn)) {
            $book = $books->volumes->byIsbn($isbn);
        } else {
            $book = $books->volumes->firstOrNull($name);
        }
        $keys = $ebook['authors'] = $categories = [];
        if (empty($book->title)) {
            $cache->set('book_info_' . $hash, 'failed', 86400);

            return false;
        }
        $ebook['title'] = $book->title;
        if (!empty($book->authors)) {
            foreach ($book->authors as $author) {
                $ebook['authors'][] = $author;
            }
        }
        $ebook['rating'] = get_or_null($book->averageRating);
        $ebook['publisher'] = get_or_null($book->publisher);
        $ebook['publishedDate'] = get_or_null($book->publishedDate);
        $ebook['description'] = get_or_null($book->description);
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
        $ebook['pageCount'] = get_or_null($book->pageCount);
        $ebook['poster'] = get_or_null($book->imageLinks->thumbnail);

        if (!empty($ebook)) {
            if (!empty($ebook['categories'])) {
                $temp = implode(', ', array_map('strtolower', $ebook['categories']));
                $temp = explode(', ', $temp);
                $ebook['newgenre'] = implode(', ', array_map('ucwords', $temp));
            }
            preg_match('/(\d{4})/', $ebook['publishedDate'], $match);
            $ebook['year'] = !empty($match[1]) ? $match[1] : null;
            $set = [
                'year' => $ebook['year'],
                'rating' => $ebook['rating'],
                'newgenre' => $ebook['newgenre'],
                'isbn' => !empty($ebook['isbn13']) ? $ebook['isbn13'] : $ebook['isbn10'],
            ];
            $torrent_stuffs->set($set, $tid);
            $cache->set('book_info_' . $hash, $ebook, $site_config['expires']['book_info']);
        }
    }

    if (empty($ebook)) {
        $cache->set('book_info_' . $hash, 'failed', 86400);

        return false;
    }

    $ebook_info = "
                    <div class='columns'>
                        <span class='has-text-danger column is-2 size_5 padding5'>Title: </span>
                        <span class='column padding5'>{$ebook['title']}</span>
                    </div>
                    <div class='columns'>
                        <span class='has-text-danger column is-2 size_5 padding5'>Author: </span>
                        <span class='column padding5'>" . implode(', ', $ebook['authors']) . "</span>
                    </div>
                    <div class='columns'>
                        <span class='has-text-danger column is-2 size_5 padding5'>Published: </span>
                        <span class='column padding5'>{$ebook['publisher']}" . (!empty($ebook['publisher']) ? '<br>' : '') . "{$ebook['publishedDate']}</span>
                    </div>";
    if (!empty($ebook['description'])) {
        $ebook_info .= "
                    <div class='columns'>
                        <span class='has-text-danger column is-2 size_5 padding5'>Description: </span>
                        <span class='column padding5'>{$ebook['description']}</span>
                    </div>";
    }
    if (!empty($ebook['isbn10'])) {
        $ebook_info .= "
                    <div class='columns'>
                        <span class='has-text-danger column is-2 size_5 padding5'>ISBN 10: </span>
                        <span class='column padding5'>
                            <a href='" . url_proxy("https://www.amazon.com/gp/search/field-isbn={$ebook['isbn10']}") . "' target='_blank'>{$ebook['isbn10']}</a>
                        </span>
                    </div>";
    }
    if (!empty($ebook['isbn13'])) {
        $ebook_info .= "
                    <div class='columns'>
                        <span class='has-text-danger column is-2 size_5 padding5'>ISBN 13: </span>
                        <span class='column padding5'>
                            <a href='" . url_proxy("https://www.amazon.com/gp/search/field-isbn={$ebook['isbn13']}") . "' target='_blank'>{$ebook['isbn13']}</a>
                        </span>
                    </div>";
    }

    if (!empty($ebook['categories'])) {
        $ebook_info .= "
                    <div class='columns'>
                        <span class='has-text-danger column is-2 size_5 padding5'>Genre: </span>
                        <span class='column padding5'>" . implode(', ', $ebook['categories']) . '</span>
                    </div>';
    }

    $ebook_info .= "
                    <div class='columns'>
                        <span class='has-text-danger column is-2 size_5 padding5'>Pages: </span>
                        <span class='column padding5'>{$ebook['pageCount']}</span>
                    </div>";

    if (!empty($ebook['rating'])) {
        $ebook_info .= "
                    <div class='columns'>
                        <span class='has-text-danger column is-2 size_5 padding5'>Rating: </span>
                        <span class='column padding5'>{$ebook['rating']}</span>
                    </div>";
    }

    if ($CURUSER['class'] >= UC_STAFF) {
        $ebook_info .= "
                    <div class='columns'>
                        <span class='has-text-danger column is-2 size_5 padding5'>API Hits: </span>
                        <span class='column padding5'>$api_hits</span>
                    </div>";
    }

    if (empty($poster) && !empty($ebook['poster'])) {
        $poster = $ebook['poster'];
        $set = [
            'poster' => $poster,
        ];
        $torrent_stuffs->set($set, $tid);
        $values = [
            'isbn' => $ebook['isbn13'],
            'url' => $poster,
            'type' => 'poster',
        ];
        $image_stuffs->insert($values);
    }

    if (!empty($poster)) {
        $ebook_info = "
        <div class='padding10'>
            <div class='columns'>
                <div class='column is-3'>
                    <img src='" . placeholder_image('225') . "' data-src='" . url_proxy($poster, true, 225) . "' class='lazy round10 img-polaroid'>
                </div>
                <div class='column'>
                    $ebook_info
                </div>
            </div>
        </div>";
    } else {
        $ebook_info = "<div class='padding10'>$ebook_info</div>";
    }

    $cache->set('book_fullset_' . $hash, $ebook_info, $site_config['expires']['book_info']);

    return [
        $ebook_info,
        $poster,
    ];
}

function get_or_null($content)
{
    if (empty($content)) {
        return null;
    } else {
        return $content;
    }
}
