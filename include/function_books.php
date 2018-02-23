<?php

use Scriptotek\GoogleBooks\GoogleBooks;

/**
 * @param $torrent
 *
 * @return bool|mixed|string
 */
function get_book_info($torrent)
{
    global $site_config, $CURUSER, $cache;

    $search = $torrent['name'];
    if (!empty($torrent['isbn'])) {
        $search = $torrent['isbn'];
    }
    $api_hits = $cache->get('google_api_limits_');
    $hash     = hash('sha256', $search);
    $ebook    = $cache->get('book_info_' . $hash);
    if (false === $ebook || is_null($ebook)) {
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
                        <div class='has-text-red column is-2 size_5 padding5'>Google API: </div>
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
        if (!empty($torrent['isbn'])) {
            $book = $books->volumes->byIsbn($torrent['isbn']);
        } else {
            $book = $books->volumes->firstOrNull($torrent['name']);
        }
        $keys           = $ebook['authors']           = $categories           = [];
        $ebook['title'] = $book->title;
        foreach ($book->authors as $author) {
            $ebook['authors'][] = $author;
        }
        $ebook['publisher']     = $book->publisher;
        $ebook['publishedDate'] = $book->publishedDate;
        $ebook['description']   = $book->description;
        foreach ($book->industryIdentifiers as $industryIdentifier) {
            foreach ($industryIdentifier as $key => $value) {
                $keys[] = $value;
            }
        }
        $ebook['isbn10'] = !empty($keys[1]) && 10 === strlen($keys[1]) ? $keys[1] : !empty($keys[3]) ? $keys[3] : '';
        $ebook['isbn13'] = !empty($keys[3]) && 13 === strlen($keys[3]) ? $keys[3] : !empty($keys[1]) ? $keys[1] : '';
        foreach ($book->categories as $category) {
            $ebook['categories'][] = $category;
        }
        $ebook['pageCount'] = $book->pageCount;
        $ebook['poster']    = $book->imageLinks->thumbnail;

        if (!empty($book)) {
            $cache->set('book_info_' . $hash, $ebook, $site_config['expires']['book_info']);
        }
    }

    if (empty($ebook)) {
        return false;
    }

    $ebook_info = "
                    <div class='columns'>
                        <div class='has-text-red column is-2 size_5 padding5'>Title: </div>
                        <span class='column padding5'>{$ebook['title']}</span>
                    </div>
                    <div class='columns'>
                        <div class='has-text-red column is-2 size_5 padding5'>Author: </div>
                        <span class='column padding5'>" . implode(', ', $ebook['authors']) . "</span>
                    </div>
                    <div class='columns'>
                        <div class='has-text-red column is-2 size_5 padding5'>Published: </div>
                        <span class='column padding5'>{$ebook['publisher']}<br>{$ebook['publishedDate']}</span>
                    </div>
                    <div class='columns'>
                        <div class='has-text-red column is-2 size_5 padding5'>Description: </div>
                        <span class='column padding5'>{$ebook['description']}</span>
                    </div>";
    if (!empty($keys)) {
        $ebook_info .= "
                    <div class='columns'>
                        <div class='has-text-red column is-2 size_5 padding5'>ISBN 10: </div>
                        <span class='column padding5'>
                            <a href='{$site_config['anonymizer_url']}https://www.amazon.com/gp/search/field-isbn={$ebook['isbn10']}' target='_blank'>{$ebook['isbn10']}</a>
                        </span>
                    </div>
                    <div class='columns'>
                        <div class='has-text-red column is-2 size_5 padding5'>ISBN 13: </div>
                        <span class='column padding5'>
                            <a href='{$site_config['anonymizer_url']}https://www.amazon.com/gp/search/field-isbn={$ebook['isbn13']}' target='_blank'>{$ebook['isbn13']}</a>
                        </span>
                    </div>";
    }

    if (!empty($ebook['categories'])) {
        $ebook_info .= "
                    <div class='columns'>
                        <div class='has-text-red column is-2 size_5 padding5'>Genre: </div>
                        <span class='column padding5'>" . implode(', ', $ebook['categories']) . '</span>
                    </div>';
    }
    $ebook_info .= "
                    <div class='columns'>
                        <div class='has-text-red column is-2 size_5 padding5'>Pages: </div>
                        <span class='column padding5'>{$ebook['pageCount']}</span>
                    </div>";

    if ($CURUSER['class'] >= UC_STAFF) {
        $ebook_info .= "
                    <div class='columns'>
                        <div class='has-text-red column is-2 size_5 padding5'>API Hits: </div>
                        <span class='column padding5'>$api_hits</span>
                    </div>";
    }

    $poster = '';
    if (empty($torrent['poster']) && !empty($ebook['poster'])) {
        $poster = $ebook['poster'];
    }

    return [
        "<div class='padding10'>$ebook_info</div>",
        $poster,
    ];
}
