<?php

use Scriptotek\GoogleBooks\GoogleBooks;

/**
 * @param $torrent
 *
 * @return bool|mixed|string
 */
function get_book_info($torrent)
{
    global $cache, $site_config;

    $poster = '';
    $search = $torrent['name'];
    if (!empty($torrent['isbn'])) {
        $search = $torrent['isbn'];
    }
    $hash = hash('sha256', $search);
    $ebook_info = $cache->get('book_info_' . $hash);
    if ($ebook_info === false || is_null($ebook_info)) {
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

        if (empty($book)) {
            return false;
        }

        $ebook_info = "
                    <div class='columns'>
                        <div class='has-text-red column is-2 size_5 padding5'>Title: </div>
                        <span class='column padding5'>{$book->title}</span>
                    </div>";
        $authors = [];
        foreach ($book->authors as $author) {
            $authors[] = $author;
        }
        $ebook_info .= "
                    <div class='columns'>
                        <div class='has-text-red column is-2 size_5 padding5'>Author: </div>
                        <span class='column padding5'>" . implode(', ', $authors) . "</span>
                    </div>";
        $ebook_info .= "
                    <div class='columns'>
                        <div class='has-text-red column is-2 size_5 padding5'>Published: </div>
                        <span class='column padding5'>{$book->publishedDate}</span>
                    </div>
                    <div class='columns'>
                        <div class='has-text-red column is-2 size_5 padding5'>Description: </div>
                        <span class='column padding5'>{$book->description}</span>
                    </div>";
        $keys = [];
        foreach ($book->industryIdentifiers as $industryIdentifier) {
            foreach ($industryIdentifier as $key => $value) {
                $keys[] = $value;
            }
        }
        if (!empty($keys)) {
            $ebook_info .= "
                    <div class='columns'>
                        <div class='has-text-red column is-2 size_5 padding5'>ISBN 10: </div>
                        <span class='column padding5'>
                            <a href='{$site_config['anonymizer_url']}https://www.amazon.com/gp/search/field-isbn={$keys[1]}' target='_blank'>{$keys[1]}</a>
                        </span>
                    </div>
                    <div class='columns'>
                        <div class='has-text-red column is-2 size_5 padding5'>ISBN 13: </div>
                        <span class='column padding5'>
                            <a href='{$site_config['anonymizer_url']}https://www.amazon.com/gp/search/field-isbn={$keys[3]}' target='_blank'>{$keys[3]}</a>
                        </span>
                    </div>";
        }

        $categories = [];
        if (!empty($books->categories)) {
            foreach ($book->categories as $category) {
                $categories[] = $category;
            }
        }

        if (!empty($categories)) {
            $ebook_info .= "
                    <div class='columns'>
                        <div class='has-text-red column is-2 size_5 padding5'>Genre: </div>
                        <span class='column padding5'>" . implode(', ', $categories) . "</span>
                    </div>";
        }
        if (empty($torrent['poster']) && !empty($book->imageLinks->thumbnail)) {
            $poster = $book->imageLinks->thumbnail;
        }
        $cache->set('book_info_' . $hash, $ebook_info, $site_config['expires']['book_info']);
    }
    return [
        $ebook_info,
        $poster
    ];
}
