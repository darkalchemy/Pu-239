<?php

use Scriptotek\GoogleBooks\GoogleBooks;

/**
 * @param $title
 *
 * @return bool|mixed|string
 */
function get_book_info($title)
{
    global $cache, $site_config;

    $hash = hash('sha256', $title);
    $ebook_info = $cache->get('book_info_' . $hash);
    if ($ebook_info === false || is_null($ebook_info)) {
        if (!empty($_ENV['GOOGLE_API_KEY'])) {
            $books = new GoogleBooks(['key' => $_ENV['GOOGLE_API_KEY']]);
        } else {
            $books = new GoogleBooks();
        }

        $book = $books->volumes->firstOrNull($title);
        if (empty($book)) {
            return false;
        }

        $ebook_info = "
        <tr>
            <td class='rowhead'>Title</td><td>{$book->title}</td>
        </tr>";
        $authors = [];
        foreach ($book->authors as $author) {
            $authors[] = $author;
        }
        $ebook_info .= "
        <tr>
            <td class='rowhead'>Author</td><td>" . implode(', ', $authors) . "</td>
        </tr>";
        $ebook_info .= "
        <tr>
            <td class='rowhead'>Published</td><td>{$book->publishedDate}</td>
        </tr>
        <tr>
            <td class='rowhead'>Description</td><td>{$book->description}</td>
        </tr>";
        $keys = [];
        foreach ($book->industryIdentifiers as $industryIdentifier) {
            foreach ($industryIdentifier as $key => $value) {
                $keys[] = $value;
            }
        }
        if (!empty($keys)) {
            $ebook_info .= "
        <tr>
            <td class='rowhead'>ISBN 13</td><td>{$keys[1]}</td>
        </tr>
        <tr>
            <td class='rowhead'>ISBN 10</td><td>{$keys[3]}</td>
        </tr>";
        }

        $categories = [];
        foreach ($book->categories as $category) {
            $categories[] = $category;
        }
        $ebook_info .= "
        <tr>
            <td class='rowhead'>Genre</td><td>" . implode(', ', $categories) . "</td>
        </tr>";
        $cache->set('book_info_' . $hash, $ebook_info, $site_config['expires']['book_info']);
    }
    return $ebook_info;
}
