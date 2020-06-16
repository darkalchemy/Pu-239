<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_fanart.php';
require_once INCL_DIR . 'function_tmdb.php';
require_once INCL_DIR . 'function_imdb.php';
require_once INCL_DIR . 'function_details.php';
require_once INCL_DIR . 'function_html.php';

use DI\DependencyException;
use DI\NotFoundException;
use Imdb\Config;
use Imdb\Person;
use Imdb\Title;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Image;
use Pu239\Torrent;
use Spatie\Image\Exceptions\InvalidManipulation;

/**
 *
 * @param string      $imdb_id
 * @param bool        $title
 * @param bool        $data_only
 * @param int|null    $tid
 * @param string|null $poster
 *
 * @throws DependencyException
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws UnbegunTransaction
 * @throws \Envms\FluentPDO\Exception
 *
 * @return array|bool|mixed
 */
function get_imdb_info(string $imdb_id, bool $title, bool $data_only, ?int $tid, ?string $poster)
{
    global $container, $site_config, $BLOCKS;

    $cache = $container->get(Cache::class);
    if (!$BLOCKS['imdb_api_on']) {
        return false;
    }

    $imdbid = $imdb_id;
    $imdb_id = str_replace('tt', '', $imdb_id);
    $imdb_data = $cache->get('imdb_' . $imdb_id);
    if ($imdb_data === false || is_null($imdb_data)) {
        $config = $container->get(Config::class);
        $movie = new Title($imdb_id, $config);

        if (empty($movie->title())) {
            $cache->set('imdb_' . $imdb_id, 'failed', 86400);

            return false;
        }
        $imdb_data = [
            'title' => $movie->title(),
            'director' => $movie->director(),
            'writing' => $movie->writing(),
            'producer' => $movie->producer(),
            'composer' => $movie->composer(),
            'cast' => $movie->cast(),
            'genres' => $movie->genres(),
            'plotoutline' => $movie->plotoutline(true),
            'trailers' => $movie->trailers(true),
            'language' => $movie->language(),
            'rating' => is_numeric($movie->rating()) ? $movie->rating() : 0,
            'year' => $movie->year(),
            'runtime' => $movie->runtime(),
            'votes' => $movie->votes(),
            'critics' => $movie->metacriticRating(),
            'poster' => $movie->photo(false),
            'country' => $movie->country(),
            'vote_count' => $movie->votes(),
            'mpaa' => $movie->mpaa(),
            'mpaa_reason' => $movie->mpaa_reason(),
            'id' => $imdbid,
            'aspect_ratio' => $movie->aspect_ratio(),
            'plot' => $movie->plot(),
            'top250' => $movie->top250(),
            'movietype' => $movie->movietype(),
            'storyline' => $movie->storyline(),
            'updated' => get_date((int) TIME_NOW, 'LONG', 1, 0),
        ];

        if (count($imdb_data['genres']) > 0) {
            $temp = implode(', ', array_map('strtolower', $imdb_data['genres']));
            $imdb_data['genres'] = explode(', ', $temp);
            $imdb_data['newgenre'] = implode(', ', array_map('ucwords', $imdb_data['genres']));
        }

        $members = [
            'director',
            'writing',
            'producer',
            'composer',
            'cast',
        ];

        $cast = $persons = $roles = [];
        foreach ($members as $member) {
            if (count($imdb_data[$member]) > 0) {
                foreach ($imdb_data[$member] as $person) {
                    if (!empty($person['imdb'])) {
                        $persons[] = [
                            'name' => $person['name'],
                            'imdb_id' => $person['imdb'],
                        ];
                        $cast[] = [
                            'imdb_id' => $imdb_id,
                            'person_id' => $person['imdb'],
                            'type' => $member,
                        ];
                        if ($member === 'cast' && !empty($person['role'])) {
                            $roles[] = [
                                'imdb_id' => $imdb_id,
                                'name' => $person['role'],
                            ];
                        }
                    }
                }
            }
        }

        $fluent = $container->get(Database::class);
        if (!empty($persons)) {
            $fluent->insertInto('person')
                   ->values($persons)
                   ->ignore()
                   ->execute();
        }

        if (!empty($cast)) {
            $fluent->insertInto('imdb_person')
                   ->values($cast)
                   ->ignore()
                   ->execute();
        }

        if (!empty($roles)) {
            $fluent->insertInto('imdb_role')
                   ->values($roles)
                   ->ignore()
                   ->execute();
        }

        unset($cast, $persons, $roles);

        if (!empty($imdb_data['plotoutline'])) {
            $values = [
                'imdb_id' => $imdb_id,
                'plot' => $imdb_data['plotoutline'],
                'top250' => $imdb_data['top250'],
                'rating' => $imdb_data['rating'],
            ];
            $update = [
                'plot' => $imdb_data['plotoutline'],
                'top250' => $imdb_data['top250'],
                'rating' => $imdb_data['rating'],
            ];
            $fluent->insertInto('imdb_info', $values)
                   ->onDuplicateKeyUpdate($update)
                   ->execute();
        }

        $cache->delete('cast_' . $imdb_id);
        $cache->set('imdb_' . $imdb_id, $imdb_data, 604800);
    }

    if ($tid) {
        $set = [];
        if (!empty($imdb_data['newgenre'])) {
            $set = [
                'newgenre' => $imdb_data['newgenre'],
            ];
        }
        $set = array_merge($set, [
            'year' => $imdb_data['year'],
            'rating' => $imdb_data['rating'],
        ]);
        $torrents_class = $container->get(Torrent::class);
        $torrents_class->update($set, $tid);
    }

    if (empty($imdb_data)) {
        $cache->set('imdb_' . $imdb_id, 'failed', 86400);

        return false;
    }
    if ($data_only) {
        return $imdb_data;
    }
    if (!empty($imdb_data['poster']) && empty($poster)) {
        $poster = $imdb_data['poster'];
        $values = [
            'imdb_id' => $imdbid,
            'url' => $poster,
            'type' => 'poster',
        ];
        $images_class = $container->get(Image::class);
        $images_class->insert($values);
    }
    if (empty($poster)) {
        $poster = get_poster($imdbid);
    }
    $imdb = [
        'title' => 'Title',
        'mpaa_reason' => 'MPAA',
        'country' => 'Country',
        'language' => 'Language',
        'director' => 'Directors',
        'writing' => 'Writers',
        'producer' => 'Producer',
        'plot' => 'Description',
        'composer' => 'Music',
        'plotoutline' => 'Plot Outline',
        'storyline' => 'Storyline',
        'trailers' => 'Trailers',
        'genres' => 'All genres',
        'rating' => 'Rating',
        'top250' => 'Top 250',
        'aspect_ratio' => 'Aspect Ratio',
        'year' => 'Year',
        'runtime' => 'Runtime',
        'votes' => 'Votes',
        'critics' => 'Critic Rating',
        'movietype' => 'Type',
        'updated' => 'Last Updated',
        'cast' => 'Cast',
    ];
    $imdb_data['cast'] = !empty($imdb_data['cast']) ? array_slice($imdb_data['cast'], 0, 25) : '';
    foreach ($imdb_data['cast'] as $pp) {
        if (!empty($pp['name']) && !empty($pp['photo'])) {
            $realname = $birthday = $died = $birthplace = $history = '';
            $bio = get_imdb_person($pp['imdb']);
            if (!empty($bio['realname'])) {
                $realname = "
                                                        <div class='column padding5 is-4'>
                                                            <span class='size_4 has-text-primary'>" . _('Real Name') . ":</span>
                                                        </div>
                                                        <div class='column padding5 is-8'>
                                                            <span class='size_4'>{$bio['realname']}</span>
                                                        </div>";
            }
            if (!empty($bio['birthday'])) {
                $birthdate = date('F j, Y', strtotime($bio['birthday']));
                $birthday = "
                                                        <div class='column padding5 is-4'>
                                                            <span class='size_4 has-text-primary'>" . _('Birthdate') . ":</span>
                                                        </div>
                                                        <div class='column padding5 is-8'>
                                                            <span class='size_4'>{$birthdate}</span>
                                                        </div>";
            }
            if (!empty($bio['died'])) {
                $died = date('F j, Y', strtotime($bio['died']));
                $died = "
                                                        <div class='column padding5 is-4'>
                                                            <span class='size_4 has-text-primary'>" . _('Deceased') . ":</span>
                                                        </div>
                                                        <div class='column padding5 is-8'>
                                                            <span class='size_4'>{$died}</span>
                                                        </div>";
            }
            if (!empty($bio['birthplace'])) {
                $birthplace = "
                                                        <div class='column padding5 is-4'>
                                                            <span class='size_4 has-text-primary'>" . _('Birth Place') . ":</span>
                                                        </div>
                                                        <div class='column padding5 is-8'>
                                                            <span class='size_4'>{$bio['birth_place']}</span>
                                                        </div>";
            }
            if (!empty($bio['bio'])) {
                $stripped = strip_tags($bio['bio']);
                $text = strlen($stripped) > 500 ? substr($stripped, 0, 500) . '...' : $stripped;
                $history = "
                                                        <div class='column padding5 is-4'>
                                                            <span class='size_4 has-text-primary'>" . _('Biography') . ":</span>
                                                        </div>
                                                        <div class='column padding5 is-8'>
                                                            <span class='size_4'>{$text}</span>
                                                        </div>";
            }

            $cast[] = "
                        <ul class='right10'>
                            <li>
                                <a href='" . url_proxy("https://www.imdb.com/name/nm{$pp['imdb']}") . "' target='_blank'>
                                    <div class='dt-tooltipper-large' data-tooltip-content='#cast_{$pp['imdb']}_tooltip'>
                                        <span class='cast'>
                                            <img src='" . url_proxy(strip_tags($pp['photo']), true, null, 110) . "' alt='' class='round5'>
                                        </span>
                                        <div class='tooltip_templates'>
                                            <div id='cast_{$pp['imdb']}_tooltip'>
                                                <div class='tooltip-torrent padding10'>
													<div class='columns is-marginless is-paddingless'>
														<div class='column padding10 is-4'>
                                                            <span>
                                                                <img src='" . url_proxy(strip_tags($pp['photo']), true, 250) . "' class='tooltip-poster' alt=''>
                                                            </span>
														</div>
														<div class='column paddin10 is-8'>
                                                            <div>
                                                                <div class='columns is-multiline'>
                                                                    <div class='column padding5 is-4'>
                                                                        <span class='size_4 has-text-primary'>" . _('Name') . ":</span>
                                                                    </div>
                                                                    <div class='column padding5 is-8'>
                                                                        <span class='size_4'>{$pp['name']}</span>
                                                                    </div>
                                                                    <div class='column padding5 is-4'>
                                                                        <span class='size_4 has-text-primary'>" . _('Role') . ":</span>
                                                                    </div>
                                                                    <div class='column padding5 is-8'>
                                                                        <span class='size_4'>{$pp['role']}</span>
                                                                    </div>{$realname}{$birthday}{$died}{$birthplace}{$history}
                                                                </div>
                                                            </div>
														</div>
													</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        </ul>";
        }
    }

    $imdb_info = '';
    foreach ($imdb as $foo => $boo) {
        if (!empty($imdb_data[$foo])) {
            if (!is_array($imdb_data[$foo])) {
                $imdb_data[$foo] = $boo === 'Title' ? "<a href='{$site_config['paths']['baseurl']}/browse.php?si={$imdbid}' class='tooltipper' title='" . _('Browse by IMDb') . "'>{$imdb_data[$foo]}</a>" : $imdb_data[$foo];
                if ($boo === 'Rating') {
                    $percent = $imdb_data['rating'] * 10;
                    $imdb_data[$foo] = "
                        <div class='level-left'>
                            <div class='right5'>{$imdb_data['rating']}</div>
                            <div class='star-ratings-css tooltipper' title='" . _fe('{0}% out of {1} votes!', $percent, $imdb_data['votes']) . "'>
                                <div class='star-ratings-css-top' style='width: {$percent}%'><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></div>
                                <div class='star-ratings-css-bottom'><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></div>
                            </div>
                        </div>";
                } elseif ($boo === 'Year') {
                    $year = _('Search by year') . ': ' . $imdb_data['year'];
                    $imdb_data[$foo] = "<a href='{$site_config['paths']['baseurl']}/browse.php?sys={$imdb_data['year']}&amp;sye={$imdb_data['year']}' target='_blank' class='tooltipper' title='$year'>{$imdb_data['year']}</a>";
                } elseif ($boo === 'MPAA') {
                    if (empty($imdb_data['mpaa_reason']) && !empty($imdb_data['mpaa']['United States'])) {
                        $imdb_data['mpaa_reason'] = $imdb_data['mpaa']['United States'];
                    }
                } elseif ($boo === 'Runtime') {
                    $imdb_data['runtime'] = date('G:i', mktime(0, (int) $imdb_data['runtime']));
                }
                $imdb_info .= "
                    <div class='columns'>
                        <div class='has-text-primary column is-2 size_5 padding5'>$boo: </div>
                        <div class='column padding5'>{$imdb_data[$foo]}</div>
                    </div>";
            } elseif (is_array($imdb_data[$foo]) && in_array($foo, [
                'director',
                'writing',
                'producer',
                'composer',
                'cast',
                'trailers',
            ])
            ) {
                foreach ($imdb_data[$foo] as $pp) {
                    if ($foo === 'cast' && !empty($cast)) {
                        $imdb_tmp[] = "<div class='level-left is-wrapped'>" . implode(' ', $cast) . '</div>';
                        unset($cast);
                    }
                    if ($foo === 'trailers') {
                        $imdb_tmp[] = "<a href='" . url_proxy($pp['url']) . "' target='_blank' class='tooltipper' title='IMDb: {$pp['title']}'>{$pp['title']}</a>";
                    } elseif ($foo != 'cast') {
                        $role = !empty($pp['role']) ? ucwords($pp['role']) : 'unidentified';
                        $imdb_tmp[] = "<a href='" . url_proxy("https://www.imdb.com/name/nm{$pp['imdb']}") . "' target='_blank' class='tooltipper' title='$role'>{$pp['name']}</a>";
                    }
                }
            } elseif ($foo === 'genres') {
                foreach ($imdb_data[$foo] as $genre) {
                    $genre_title = _('Search by genre') . ': ' . ucwords($genre);
                    $imdb_tmp[] = "<a href='{$site_config['paths']['baseurl']}/browse.php?sg=" . urlencode(strtolower($genre)) . "' target='_blank' class='tooltipper' title='$genre_title'>" . ucwords($genre) . '</a>';
                }
            }
            if (!empty($imdb_tmp)) {
                $imdb_info .= "
                    <div class='columns'>
                        <div class='has-text-primary column is-2 size_5 padding5'>$boo: </div>
                        <div class='column padding5'>" . implode(', ', $imdb_tmp) . '</div>
                    </div>';
                unset($imdb_tmp);
            }
        }
    }

    $imdb_info = preg_replace('/&(?![A-Za-z0-9#]{1,7};)/', '&amp;', $imdb_info);
    if ($title) {
        $imdb_info = "
        <div class='padding20'>
            <div class='columns bottom20'>
                <div class='column is-one-third is-paddingless'>
                    <img src='" . url_proxy($poster, true, 450) . "' alt='' class='round10 img-polaroid'>
                </div>
                <div class='column'>
                    <div class='left20'>
                        $imdb_info
                    </div>
                </div>
            </div>
        </div>";
        $cache->set('imdb_fullset_title_' . $imdbid, $imdb_info, 604800);
    } else {
        $imdb_info = "<div class='padding20'>$imdb_info</div>";
    }

    return [
        $imdb_info,
        $poster,
    ];
}

/**
 * @param $imdb_id
 *
 * @throws Exception
 *
 * @return bool|mixed
 */
function get_imdb_title($imdb_id)
{
    global $BLOCKS;

    if (!$BLOCKS['imdb_api_on']) {
        return false;
    }

    $imdb_id = str_replace('tt', '', $imdb_id);
    $imdb_data = get_imdb_info($imdb_id, true, true, null, null);
    if (empty($imdb_data['title'])) {
        return false;
    }

    return $imdb_data['title'];
}

/**
 * @param $imdb_id
 *
 * @throws Exception
 *
 * @return bool|string|string[]|null
 */
function get_imdb_info_short($imdb_id)
{
    global $container, $site_config, $BLOCKS;

    if (empty($imdb_id)) {
        return false;
    }
    $images_class = $container->get(Image::class);
    $cache = $container->get(Cache::class);
    if (!$BLOCKS['imdb_api_on']) {
        return false;
    }

    $imdbid = $imdb_id;
    $imdb_id = str_replace('tt', '', $imdb_id);
    $imdb_data = get_imdb_info($imdb_id, true, true, null, null);
    if (empty($imdb_data)) {
        return false;
    }
    $poster = !empty($imdb_data['poster']) ? $imdb_data['poster'] : '';
    if (empty($poster)) {
        $poster = getMovieImagesByID($imdbid, true, 'movieposter');
    }
    if (empty($poster)) {
        $tmdbid = get_movie_id($imdbid, 'tmdb_id');
        if (!empty($tmdbid)) {
            $poster = getMovieImagesByID((string) $tmdbid, true, 'movieposter');
        }
    }
    if (!empty($poster) && !is_bool($poster)) {
        $image = url_proxy((string) $poster, true, 250);
        if (!empty($image)) {
            $imdb_data['poster'] = $image;
        }
        $values = [];
        if (!empty($tmdbid)) {
            $values = [
                'tmdb_id' => $tmdbid,
            ];
        }
        $values = array_merge($values, [
            'imdb_id' => $imdbid,
            'url' => $poster,
            'type' => 'poster',
        ]);
        $imdb_short = $cache->get('imdb_short_' . $imdbid);
        if ($imdb_short === false || is_null($imdb_short)) {
            $images_class->insert($values);
            $cache->set('imdb_short_' . $imdbid, 'inserted', 86400);
        }
    }

    if (empty($imdb_data['poster']) || $imdb_data['poster'] === $site_config['paths']['images_baseurl'] . 'proxy/') {
        $poster = $site_config['paths']['images_baseurl'] . 'noposter.png';
        $imdb_data['poster'] = $poster;
    }
    $imdb_data['placeholder'] = url_proxy($poster, true, 250, null, 20);
    if (!empty($imdb_data['critics'])) {
        $imdb_data['critics'] .= '%';
    } else {
        $imdb_data['critics'] = '?';
    }
    if (empty($imdb_data['vote_count'])) {
        $imdb_data['vote_count'] = '?';
    } else {
        $imdb_data['vote_count'] = number_format($imdb_data['vote_count']);
    }
    if (empty($imdb_data['rating'])) {
        $imdb_data['rating'] = '?';
    }
    if (empty($imdb_data['mpaa_reason']) && !empty($imdb_data['mpaa']['United States'])) {
        $imdb_data['mpaa_reason'] = $imdb_data['mpaa']['United States'];
    }

    $imdb_data['mpaa_reason'] = !empty($imdb_data['mpaa_reason']) ? $imdb_data['mpaa_reason'] : '?';
    $background = $images_class->find_images($imdbid, $type = 'background');
    $imdb_info = "
            <div class='masonry-item-clean padding10 bg-04 round10'>
                <div class='dt-tooltipper-large has-text-centered' data-tooltip-content='#movie_{$imdb_data['id']}_tooltip'>
                    <a href='{$site_config['paths']['baseurl']}/browse.php?si=tt{$imdb_id}'>
                        <img src='{$imdb_data['placeholder']}' data-src='{$imdb_data['poster']}' alt='Poster' class='lazy tooltip-poster'>
                    </a>
                    <div class='has-text-centered top10'>{$imdb_data['title']}</div>
                    <div class='tooltip_templates'>
                        <div id='movie_{$imdb_data['id']}_tooltip' class='round10 tooltip-background'" . (!empty($background) ? " style='background-image: url(" . url_proxy($background) . ");'" : '') . ">
                            <div class='columns is-marginless is-paddingless'>
                                <div class='column padding10 is-4'>
                                    <span>
                                        <img src='{$imdb_data['placeholder']}' data-src='{$imdb_data['poster']}' alt='Poster' class='lazy tooltip-poster'>
                                    </span>
                                </div>
                                <div class='column padding10 is-8'>
                                    <div class='padding20 is-8 bg-09 round10 h-100'>
                                        <div class='columns is-multiline'>
                                            <div class='column padding5 is-4'>
                                                <span class='size_4 right10 has-text-primary has-text-wight-bold'>" . _('Title') . ": </span>
                                            </div>
                                            <div class='column padding5 is-8'>
                                                <span>" . htmlsafechars($imdb_data['title']) . "</span>
                                            </div>
                                            <div class='column padding5 is-4'>
                                                <span class='size_4 right10 has-text-primary has-text-wight-bold'>" . _('MPAA') . ": </span>
                                            </div>
                                            <div class='column padding5 is-8'>
                                                <span class='size_4'>" . htmlsafechars($imdb_data['mpaa_reason']) . "</span>
                                            </div>
                                            <div class='column padding5 is-4'>
                                                <span class='size_4 right10 has-text-primary has-text-wight-bold'>" . _('Critics') . ": </span>
                                            </div>
                                            <div class='column padding5 is-8'>
                                                <span class='size_4'>" . htmlsafechars($imdb_data['critics']) . "</span>
                                            </div>
                                            <div class='column padding5 is-4'>
                                                <span class='size_4 right10 has-text-primary has-text-wight-bold'>" . _('Rating') . ": </span>
                                            </div>
                                            <div class='column padding5 is-8'>
                                                <span class='size_4'>" . htmlsafechars($imdb_data['rating']) . "</span>
                                            </div>
                                            <div class='column padding5 is-4'>
                                                <span class='size_4 right10 has-text-primary has-text-wight-bold'>" . _('Votes') . ": </span>
                                            </div>
                                            <div class='column padding5 is-8'>
                                                <span class='size_4'>" . $imdb_data['vote_count'] . "</span>
                                            </div>
                                            <div class='column padding5 is-4'>
                                                <span class='size_4 right10 has-text-primary has-text-wight-bold'>" . _('Overview') . ": </span>
                                            </div>
                                            <div class='column padding5 is-8'>
                                                <span class='size_4'>" . htmlsafechars(strip_tags($imdb_data['plotoutline'])) . '</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';

    $imdb_info = preg_replace('/&(?![A-Za-z0-9#]{1,7};)/', '&amp;', $imdb_info);

    return $imdb_info;
}

/**
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws UnbegunTransaction
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 *
 * @return array|bool
 */
function get_upcoming()
{
    global $container, $BLOCKS;

    $cache = $container->get(Cache::class);
    if (!$BLOCKS['imdb_api_on']) {
        return false;
    }
    $imdb_data = $cache->get('imdb_upcoming_');
    if ($imdb_data === false || is_null($imdb_data)) {
        $url = 'https://www.imdb.com/movies-coming-soon/';
        $imdb_data = fetch($url);
        if ($imdb_data) {
            $cache->set('imdb_upcoming_', $imdb_data, 86400);
        } else {
            $cache->set('imdb_upcoming_', 'failed', 3600);
        }
    }
    if (empty($imdb_data)) {
        return false;
    }
    preg_match_all('/<h4.*<a (name|id)=.*>(.*)&nbsp;/i', $imdb_data, $timestamp);
    $dates = $timestamp[2];
    $regex = '';
    foreach ($dates as $date) {
        $regex .= '<a name(.*)';
    }
    $regex .= 'see-more';
    preg_match("/$regex/isU", $imdb_data, $datemovies);
    $temp = [];
    foreach ($datemovies as $key => $value) {
        preg_match_all('/<table(.*)<\/table/isU', $value, $out);
        if ($key != 0) {
            $temp[$dates[$key - 1]] = $out[1];
        }
    }
    $imdbs = [];
    foreach ($dates as $date) {
        foreach ($temp[$date] as $code) {
            preg_match('#<a href=\"/title/(tt\d{7,8})/\?ref_=cs_ov_i\"\s*>#', $code, $imdb);
            if (!empty($imdb[1])) {
                $imdbs[$date][] = $imdb[1];
            }
        }
    }
    if (!empty($imdbs)) {
        foreach ($imdbs as $day) {
            foreach ($day as $imdb) {
                get_imdb_info($imdb, true, true, null, null);
            }
        }

        return $imdbs;
    }

    return false;
}

/**
 *
 * @param string $imdb_id
 *
 * @throws NotFoundException
 * @throws UnbegunTransaction
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws InvalidManipulation
 *
 * @return bool
 */
function update_torrent_data(string $imdb_id)
{
    global $container, $site_config, $BLOCKS;

    $cache = $container->get(Cache::class);
    if (!$BLOCKS['imdb_api_on']) {
        return false;
    }

    $imdb_id = str_replace('tt', '', $imdb_id);
    $imdb_data = get_imdb_info($imdb_id, true, true, null, null);
    $set = [];
    if (!empty($imdb_data['newgenre'])) {
        $set = [
            'newgenre' => $imdb_data['newgenre'],
        ];
    }
    $set = array_merge($set, [
        'year' => $imdb_data['year'],
        'rating' => $imdb_data['rating'],
    ]);
    $fluent = $container->get(Database::class);
    $result = $fluent->update('torrents')
                     ->set($set)
                     ->where('imdb_id = ?', 'tt' . $imdb_id)
                     ->execute();

    if ($result) {
        $torrents = $fluent->from('torrents')
                           ->select(null)
                           ->select('id')
                           ->where('imdb_id = ?', 'tt' . $imdb_id)
                           ->fetchAll();

        foreach ($torrents as $torrent) {
            $cache->update_row('torrent_details_' . $torrent['id'], $set, $site_config['expires']['torrent_details']);
        }
    }

    return true;
}

/**
 * @param $person_id
 *
 * @throws DependencyException
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return array|bool|mixed
 */
function get_imdb_person($person_id)
{
    global $container, $site_config, $BLOCKS;

    $cache = $container->get(Cache::class);
    if (!$BLOCKS['imdb_api_on']) {
        return false;
    }
    $imdb_person = $cache->get('imdb_person_' . $person_id);
    $fluent = $container->get(Database::class);
    if ($imdb_person === false || is_null($imdb_person)) {
        $imdb_person = $fluent->from('person')
                              ->where('imdb_id = ?', $person_id)
                              ->where('updated + 2592000 > ?', TIME_NOW)
                              ->fetch();

        if (!empty($imdb_person)) {
            $cache->set('imdb_person_' . $person_id, $imdb_person, 604800);

            return $imdb_person;
        } else {
            $cache->set('imdb_person_' . $person_id, 'failed', 86400);
        }
        $config = $container->get(Config::class);
        $person = new Person($person_id, $config);
        $imdb_person = [];
        if (!empty($person->name())) {
            $imdb_person['name'] = $person->name();
        } else {
            $set = [
                'updated' => TIME_NOW,
            ];
            $fluent->update('person')
                   ->set($set)
                   ->where('imdb_id = ?', $person_id)
                   ->execute();

            return false;
        }
        if (!empty($person->birthname())) {
            $imdb_person['realname'] = $person->birthname();
        }

        if (!empty($person->born())) {
            $data = $person->born();
            if (!empty($data['year']) && !empty($data['mon']) && !empty($data['day'])) {
                $imdb_person['birthday'] = $data['year'] . '-' . str_pad($data['mon'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($data['day'], 2, '0', STR_PAD_LEFT);
            }
            if (!empty($data['place'])) {
                $imdb_person['birth_place'] = $data['place'];
            }
        }

        if (!empty($person->bio())) {
            $data = $person->bio();
            $imdb_person['bio'] = str_replace([
                '<br />',
                'href="',
            ], [
                '<br>',
                'href="' . $site_config['site']['anonymizer_url'],
            ], $data[0]['desc']);
        }

        if (!empty($person->died())) {
            $data = $person->died();
            if (!empty($data['year']) && !empty($data['mon']) && !empty($data['day'])) {
                $imdb_person['died'] = $data['year'] . '-' . str_pad($data['mon'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($data['day'], 2, '0', STR_PAD_LEFT);
            }
        }

        if (!empty($person->photo(false))) {
            $data = $person->photo(false);
            if (strpos($data, 'nopicture') === false) {
                $imdb_person['photo'] = $data;
                url_proxy($data, true, null, 110);
                url_proxy($data, true, 250);
            }
        }

        $imdb_person['imdb_id'] = $person_id;
        $imdb_person['updated'] = TIME_NOW;
        $update = $imdb_person;
        unset($update['name']);
        $fluent->insertInto('person', $imdb_person)
               ->onDuplicateKeyUpdate($update)
               ->execute();

        $cache->set('imdb_person_' . $person_id, $imdb_person, 604800);
    } else {
        $set = [
            'updated' => TIME_NOW,
        ];
        $fluent->update('person')
               ->set($set)
               ->where('imdb_id = ?', $person_id)
               ->execute();
    }

    return $imdb_person;
}

/**
 *
 * @param int $count
 *
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws UnbegunTransaction
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 *
 * @return array|bool|mixed
 */
function get_top_movies(int $count)
{
    global $container;

    $cache = $container->get(Cache::class);
    $top = $cache->get('imdb_top_movies_' . $count);
    if ($top === false || is_null($top)) {
        $top = [];
        for ($i = 1; $i <= $count; $i += 50) {
            $url = 'https://www.imdb.com/search/title?groups=top_1000&sort=user_rating,desc&view=simple&start=' . $i;
            $html = fetch($url);
            if (!empty($html)) {
                preg_match_all('#<a href=\"/title/(tt\d{7,8})/\?ref_=adv_li_i\"\s*>#', $html, $matches);
                foreach ($matches[1] as $match) {
                    if (!in_array($match, $top)) {
                        $top[] = $match;
                    }
                }
            }
        }
        if (!empty($top)) {
            foreach ($top as $imdb) {
                get_imdb_info($imdb, true, true, null, null);
            }
            $cache->set('imdb_top_movies_' . $count, $top, 604800);
        }
    }

    return $top;
}

/**
 * @throws UnbegunTransaction
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws InvalidManipulation
 * @throws NotFoundException
 *
 * @return array|bool
 */
function get_in_theaters()
{
    global $container, $BLOCKS;

    $cache = $container->get(Cache::class);
    if (!$BLOCKS['imdb_api_on']) {
        return false;
    }
    $imdb_data = $cache->get('imdb_in_theaters_');
    if ($imdb_data === false || is_null($imdb_data)) {
        $url = 'https://www.imdb.com/movies-in-theaters';
        $imdb_data = fetch($url);
        if ($imdb_data) {
            $cache->set('imdb_in_theaters_', $imdb_data, 86400);
        } else {
            $cache->set('imdb_in_theaters_', 'failed', 3600);
        }
    }
    if (empty($imdb_data)) {
        return false;
    }
    preg_match_all('#<a href=\"/title/(tt\d{7,8})/\?ref_=[a-z_]*\"\s*>#', $imdb_data, $imdb);
    foreach ($imdb[1] as $match) {
        $imdbs[] = $match;
    }
    if (!empty($imdbs)) {
        foreach ($imdbs as $imdb) {
            get_imdb_info($imdb, true, true, null, null);
        }

        return $imdbs;
    }

    return false;
}

/**
 *
 * @param int $count
 *
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws UnbegunTransaction
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 *
 * @return array|bool|mixed
 */
function movies_by_release_date(int $count)
{
    global $container, $BLOCKS;

    $cache = $container->get(Cache::class);
    if (!$BLOCKS['imdb_api_on']) {
        return false;
    }
    $top = $cache->get('movies_by_release_date_' . $count);
    if ($top === false || is_null($top)) {
        $top = [];
        for ($i = 1; $i <= $count; $i += 50) {
            $url = 'https://www.imdb.com/search/title/?title_type=feature&sort=release_date,desc&view=simple&start=' . $i;
            $html = fetch($url);
            preg_match_all('#<a href=\"/title/(tt\d{7,8})/\?ref_=adv_li_i\"\s*>#', $html, $matches);
            foreach ($matches[1] as $match) {
                if (!in_array($match, $top)) {
                    $top[] = $match;
                }
            }
        }
        if (!empty($top)) {
            foreach ($top as $imdb) {
                get_imdb_info($imdb, true, true, null, null);
            }
            $cache->set('movies_by_release_date_' . $count, $top, 604800);
        }
    }

    return $top;
}

/**
 *
 * @param int $count
 *
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws UnbegunTransaction
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 *
 * @return array|bool|mixed
 */
function get_top_tvshows(int $count)
{
    global $container;

    $cache = $container->get(Cache::class);
    $top = $cache->get('imdb_top_tvshows_' . $count);
    if ($top === false || is_null($top)) {
        $top = [];
        for ($i = 1; $i <= $count; $i += 50) {
            $url = 'https://www.imdb.com/search/title?title_type=tv_series&num_votes=30000,&countries=us&sort=user_rating,desc&view=simple&start=' . $i;
            $html = fetch($url);
            if (!empty($html)) {
                preg_match_all('#<a href=\"/title/(tt\d{7,8})/\?ref_=adv_li_i\"\s*>#', $html, $matches);
                foreach ($matches[1] as $match) {
                    if (!in_array($match, $top)) {
                        $top[] = $match;
                    }
                }
            }
        }
        if (!empty($top)) {
            foreach ($top as $imdb) {
                get_imdb_info($imdb, true, true, null, null);
            }
            $cache->set('imdb_top_tvshows_' . $count, $top, 604800);
        }
    }

    return $top;
}

/**
 *
 * @param int $count
 *
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 *
 * @return array|bool|mixed
 */
function get_top_anime(int $count)
{
    global $container;

    $cache = $container->get(Cache::class);
    $top = $cache->get('imdb_top_anime_' . $count);
    if ($top === false || is_null($top)) {
        $top = [];
        for ($i = 1; $i <= $count; $i += 50) {
            $url = 'https://www.imdb.com/search/title?genres=drama&keywords=anime&num_votes=2000,sort=user_rating,desc&view=simple&start=' . $i;
            $html = fetch($url);
            preg_match_all('#<a href=\"/title/(tt\d{7,8})/\?ref_=adv_li_i\"\s*>#', $html, $matches);
            foreach ($matches[1] as $match) {
                if (!in_array($match, $top)) {
                    $top[] = $match;
                }
            }
        }
        if (!empty($top)) {
            $cache->set('imdb_top_anime_' . $count, $top, 604800);
        }
    }

    return $top;
}

/**
 *
 * @param int $count
 *
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 *
 * @return array|bool|mixed
 */
function get_oscar_winners(int $count)
{
    global $container;

    $cache = $container->get(Cache::class);
    $top = $cache->get('imdb_oscar_winners_' . $count);
    if ($top === false || is_null($top)) {
        $top = [];
        for ($i = 1; $i <= $count; $i += 50) {
            $url = 'https://www.imdb.com/search/title?groups=oscar_winner&sort=user_rating,desc&view=simple&start=' . $i;
            $html = fetch($url);
            preg_match_all('#<a href=\"/title/(tt\d{7,8})/\?ref_=adv_li_i\"\s*>#', $html, $matches);
            foreach ($matches[1] as $match) {
                if (!in_array($match, $top)) {
                    $top[] = $match;
                }
            }
        }
        if (!empty($top)) {
            $cache->set('imdb_oscar_winners_' . $count, $top, 604800);
        }
    }

    return $top;
}
