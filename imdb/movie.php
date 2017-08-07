<?php
//############################################################################
// IMDBPHP                              (c) Giorgos Giagas & Itzchak Rehberg #
// written by Giorgos Giagas                                                 #
// extended & maintained by Itzchak Rehberg <izzysoft AT qumran DOT org>     #
// http://www.izzysoft.de/                                                   #
// ------------------------------------------------------------------------- #
// This program is free software; you can redistribute and/or modify it      #
// under the terms of the GNU General Public License (see doc/LICENSE)       #
//############################################################################

/* $Id: movie.php 618 2013-10-21 19:06:48Z izzy $ */

if (isset($_GET['mid']) && preg_match('/^[0-9]+$/', $_GET['mid'])) {
    $movieid = $_GET['mid'];
    $engine = $_GET['engine'];

    switch ($engine) {
        default:
            require 'imdb.class.php';
            $movie = new imdb($_GET['mid']);
            //$charset = "iso-8859-1";
            $charset = 'utf8';
            $source = "<b CLASS='active'>IMDB</b>";
            break;
    }

    $movie->setid($movieid);
    $rows = 2; // count for the rowspan; init with photo + year

    echo "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
    echo "<html><head>\n";
    echo ' <title>' . $movie->title() . ' (' . $movie->year() . ') [IMDBPHP2 v' . $movie->version . " Demo]</title>\n";
    echo " <style>body,td,th { font-size:12px; font-family:sans-serif; } b.active { color:#b00;background-color:#fff;text-decoration:underline;}</style>\n";
    echo " <meta http-equiv='Content-Type' content='text/html; charset=$charset'>\n";
    echo "</head>\n<body onload='fix_colspan()'>\n<table border='1' align='center' style='border-collapse:collapse'>";

    // Title & year
    echo '<tr><th colspan="3" style="background-color:#ffb000">';
    echo '[IMDBPHP2 v' . $movie->version . " Demo] Movie Details for '" . $movie->title() . "' (" . $movie->year() . ')';
    echo "<span style='float:right;text-align:right;display:inline !important;font-size:75%;'>Source: [$source]</span>";
    echo "</th></tr>\n";
    flush();

    // Photo
    echo '<tr><td id="photocol" rowspan="29" valign="top">';
    if (($photo_url = $movie->photo_localurl()) != false) {
        echo '<img src="' . $photo_url . '" alt="Cover">';
    } else {
        echo 'No photo available';
    }

    // AKAs
    $aka = $movie->alsoknow();
    $cc = count($aka);
    if (!empty($aka)) {
        echo '</td><td valign=top width=120><b>Also known as:</b> </td><td>';
        foreach ($aka as $ak) {
            echo $ak['title'];
            if (!empty($ak['year'])) {
                echo ' ' . $ak['year'];
            }
            echo ' =&gt; ' . $ak['country'];
            if (empty($ak['lang'])) {
                if (!empty($ak['comment'])) {
                    echo ' (' . $ak['comment'] . ')';
                }
            } else {
                if (!empty($ak['comment'])) {
                    echo ', ' . $ak['comment'];
                }
                echo ' [' . $ak['lang'] . ']';
            }
            echo '<bR>';
        }
        echo "</td></tr>\n";
        flush();
    }

    // Movie Type
    ++$rows;
    echo '<tr><td><b>Type:</b></td><td>' . $movie->movietype() . "</td></tr>\n";

    // Keywords
    $keywords = $movie->keywords();
    if (!empty($keywords)) {
        ++$rows;
        echo '<tr><td><b>Keywords:</b></td><td>' . implode(', ', $keywords) . "</td></tr>\n";
    }

    // Seasons
    if ($movie->seasons() != 0) {
        ++$rows;
        echo '<tr><td><b>Seasons:</b></td><td>' . $movie->seasons() . "</td></tr>\n";
        flush();
    }

    // Episode Details
    $ser = $movie->get_episode_details();
    if (!empty($ser)) {
        ++$rows;
        echo '<tr><td><b>Series Details:</b></td><td>' . $ser['seriestitle'] . ' Season ' . $ser['season'] . ', Episode ' . $ser['episode'] . ', Airdate ' . $ser['airdate'] . "</td></tr>\n";
    }

    // Year & runtime
    echo '<tr><td><b>Year:</b></td><td>' . $movie->year() . '</td></tr>';
    $runtime = $movie->runtime();
    if (!empty($runtime)) {
        ++$rows;
        echo "<tr><td valign=top><b>Runtime:</b></td><td>$runtime minutes</td></tr>\n";
    }
    flush();

    // MPAA
    $mpaa = $movie->mpaa();
    if (!empty($mpaa)) {
        ++$rows;
        $mpar = $movie->mpaa_reason();
        if (empty($mpar)) {
            echo '<tr><td><b>MPAA:</b></td><td>';
        } else {
            echo '<tr><td rowspan="2"><b>MPAA:</b></td><td>';
        }
        echo "<table align='left' border='1' style='border-collapse:collapse;background-color:#ddd;'><tr><th style='background-color:#07f;'>Country</th><th style='background-color:#07f;'>Rating</th></tr>";
        foreach ($mpaa as $key => $mpaa) {
            echo "<tr><td>$key</td><td>$mpaa</td></tr>";
        }
        echo "</table></td></tr>\n";
        if (!empty($mpar)) {
            ++$rows;
            echo "<tr><td>$mpar</td></tr>\n";
        }
    }

    // Ratings and votes
    $ratv = $movie->rating();
    if (!empty($ratv)) {
        echo "<tr><td><b>Rating:</b></td><td>$ratv</td></tr>\n";
        ++$rows;
    }
    $ratv = $movie->votes();
    if (!empty($ratv)) {
        echo "<tr><td><b>Votes:</b></td><td>$ratv</td></tr>\n";
        ++$rows;
    }
    flush();

    // Languages
    $languages = $movie->languages();
    if (!empty($languages)) {
        ++$rows;
        echo '<tr><td><b>Languages:</b></td><td>';
        for ($i = 0; $i + 1 < count($languages); ++$i) {
            echo $languages[$i] . ', ';
        }
        echo $languages[$i] . "</td></tr>\n";
    }
    flush();

    // Country
    $country = $movie->country();
    if (!empty($country)) {
        ++$rows;
        echo '<tr><td><b>Country:</b></td><td>';
        for ($i = 0; $i + 1 < count($country); ++$i) {
            echo $country[$i] . ', ';
        }
        echo $country[$i] . "</td></tr>\n";
    }

    // Genres
    $genre = $movie->genre();
    if (!empty($genre)) {
        echo "<tr><td><b>Genre:</b></td><td>$genre</td></tr>\n";
        ++$rows;
    }

    $gen = $movie->genres();
    if (!empty($gen)) {
        ++$rows;
        echo '<tr><td><b>All Genres:</b></td><td>';
        for ($i = 0; $i + 1 < count($gen); ++$i) {
            echo $gen[$i] . ', ';
        }
        echo $gen[$i] . "</td></tr>\n";
    }

    // Colors
    $col = $movie->colors();
    if (!empty($col)) {
        ++$rows;
        echo '<tr><td><b>Colors:</b></td><td>';
        for ($i = 0; $i + 1 < count($col); ++$i) {
            echo $col[$i] . ', ';
        }
        echo $col[$i] . "</td></tr>\n";
    }
    flush();

    // Sound
    $sound = $movie->sound();
    if (!empty($sound)) {
        ++$rows;
        echo '<tr><td><b>Sound:</b></td><td>';
        for ($i = 0; $i + 1 < count($sound); ++$i) {
            echo $sound[$i] . ', ';
        }
        echo $sound[$i] . "</td></tr>\n";
    }

    $tagline = $movie->tagline();
    if (!empty($tagline)) {
        ++$rows;
        echo "<tr><td valign='top'><b>Tagline:</b></td><td>$tagline</td></tr>\n";
    }

    //==[ Staff ]==
    // director(s)
    $director = $movie->director();
    if (!empty($director)) {
        ++$rows;
        echo '<tr><td valign=top><b>Director:</b></td><td>';
        echo "<table align='left' border='1' style='border-collapse:collapse;background-color:#ddd;'><tr><th style='background-color:#07f;'>Name</th><th style='background-color:#07f;'>Role</th></tr>";
        for ($i = 0; $i < count($director); ++$i) {
            echo '<tr><td width=200>';
            echo "<a href='person.php?engine=$engine&mid=" . $director[$i]['imdb'] . "'>";
            echo $director[$i]['name'] . '</a></td><td>';
            echo $director[$i]['role'] . '</td></tr>';
        }
        echo "</table></td></tr>\n";
    }

    // Story
    $write = $movie->writing();
    if (!empty($write)) {
        ++$rows;
        echo '<tr><td valign=top><b>Writing By:</b></td><td>';
        echo "<table align='left' border='1' style='border-collapse:collapse;background-color:#ddd;'><tr><th style='background-color:#07f;'>Name</th><th style='background-color:#07f;'>Role</th></tr>";
        for ($i = 0; $i < count($write); ++$i) {
            echo '<tr><td width=200>';
            echo "<a href='person.php?engine=$engine&mid=" . $write[$i]['imdb'] . "'>";
            echo $write[$i]['name'] . '</a></td><td>';
            echo $write[$i]['role'] . '</td></tr>';
        }
        echo "</table></td></tr>\n";
    }
    flush();

    // Producer
    $produce = $movie->producer();
    if (!empty($produce)) {
        ++$rows;
        echo '<tr><td valign=top><b>Produced By:</b></td><td>';
        echo "<table align='left' border='1' style='border-collapse:collapse;background-color:#ddd;'><tr><th style='background-color:#07f;'>Name</th><th style='background-color:#07f;'>Role</th></tr>";
        for ($i = 0; $i < count($produce); ++$i) {
            echo '<tr><td width=200>';
            echo "<a href='person.php?engine=$engine&mid=" . $produce[$i]['imdb'] . "'>";
            echo $produce[$i]['name'] . '</a></td><td>';
            echo $produce[$i]['role'] . '</td></tr>';
        }
        echo "</table></td></tr>\n";
    }

    // Music
    $compose = $movie->composer();
    if (!empty($compose)) {
        ++$rows;
        echo '<tr><td valign=top><b>Music:</b></td><td>';
        echo "<table align='left' border='1' style='border-collapse:collapse;background-color:#ddd;'><tr><th style='background-color:#07f;'>Name</th><th style='background-color:#07f;'>Role</th></tr>";
        for ($i = 0; $i < count($compose); ++$i) {
            echo '<tr><td width=200>';
            echo "<a href='person.php?engine=$engine&mid=" . $compose[$i]['imdb'] . "'>";
            echo $compose[$i]['name'] . '</a></td></tr>';
        }
        echo "</table></td></tr>\n";
    }
    flush();

    // Cast
    $cast = $movie->cast();
    if (!empty($cast)) {
        ++$rows;
        echo '<tr><td valign=top><b>Cast:</b></td><td>';
        echo "<table align='left' border='1' style='border-collapse:collapse;background-color:#ddd;'><tr><th style='background-color:#07f;'>Actor</th><th style='background-color:#07f;'>Role</th></tr>";
        for ($i = 0; $i < count($cast); ++$i) {
            echo '<tr><td width=200>';
            echo "<a href='person.php?engine=$engine&mid=" . $cast[$i]['imdb'] . "'>";
            echo $cast[$i]['name'] . '</a></td><td>';
            echo $cast[$i]['role'] . '</td></tr>';
        }
        echo "</table></td></tr>\n";
    }
    flush();

    // Plot outline & plot
    $plotoutline = $movie->plotoutline();
    if (!empty($plotoutline)) {
        ++$rows;
        echo "<tr><td valign='top'><b>Plot Outline:</b></td><td>$plotoutline</td></tr>\n";
    }

    $plot = $movie->plot();
    if (!empty($plot)) {
        ++$rows;
        echo '<tr><td valign=top><b>Plot:</b></td><td><ul>';
        for ($i = 0; $i < count($plot); ++$i) {
            echo '<li>' . $plot[$i] . "</li>\n";
        }
        echo "</ul></td></tr>\n";
    }
    flush();

    // Taglines
    $taglines = $movie->taglines();
    if (!empty($taglines)) {
        ++$rows;
        echo '<tr><td valign=top><b>Taglines:</b></td><td><ul>';
        for ($i = 0; $i < count($taglines); ++$i) {
            echo '<li>' . $taglines[$i] . "</li>\n";
        }
        echo "</ul></td></tr>\n";
    }

    // Seasons
    if ($movie->is_serial() || $movie->seasons()) {
        ++$rows;
        $episodes = $movie->episodes();
        echo '<tr><td valign=top><b>Episodes:</b></td><td>';
        foreach ($episodes as $season => $ep) {
            foreach ($ep as $episodedata) {
                echo '<b>Season ' . $episodedata['season'] . ', Episode ' . $episodedata['episode'] . ': <a href="' . $_SERVER['PHP_SELF'] . '?mid=' . $episodedata['imdbid'] . '">' . $episodedata['title'] . '</a></b> (<b>Original Air Date: ' . $episodedata['airdate'] . '</b>)<br>' . $episodedata['plot'] . '<br><br>';
            }
        }
        echo "</td></tr>\n";
    }

    // Locations
    $locs = $movie->locations();
    if (!empty($locs)) {
        ++$rows;
        echo '<tr><td valign="top"><b>Filming Locations:</b></td><td><ul>';
        foreach ($locs as $loc) {
            if (empty($loc['url'])) {
                echo '<li>' . $loc['name'] . '</li>';
            } else {
                echo '<li><a href="http://' . $movie->imdbsite . $loc['url'] . '">' . $loc['name'] . '</a></li>';
            }
        }
        echo "</ul></td></tr>\n";
    }

    // Selected User Comment
    $comment = $movie->comment();
    if (!empty($comment)) {
        ++$rows;
        echo "<tr><td valign='top'><b>User Comments:</b></td><td>$comment</td></tr>\n";
    }

    // Quotes
    $quotes = $movie->quotes();
    if (!empty($quotes)) {
        ++$rows;
        echo '<tr><td valign=top><b>Movie Quotes:</b></td><td>';
        echo preg_replace("/http\:\/\/" . str_replace('.', "\.", $movie->imdbsite) . "\/name\/nm(\d{7})\//", "person.php?engine=$engine&mid=\\1", $quotes[0]) . "</td></tr>\n";
    }

    // Trailer
    $trailers = $movie->trailers(true);
    if (!empty($trailers)) {
        ++$rows;
        echo '<tr><td valign=top><b>Trailers:</b></td><td>';
        for ($i = 0; $i < count($trailers); ++$i) {
            if (!empty($trailers[$i]['url'])) {
                echo "<a href='" . $trailers[$i]['url'] . "'>" . $trailers[$i]['title'] . "</a><br>\n";
            }
        }
        echo "</td></tr>\n";
    }

    // Crazy Credits
    $crazy = $movie->crazy_credits();
    $cc = count($crazy);
    if ($cc) {
        ++$rows;
        echo '<tr><td valign=top><b>Crazy Credits:</b></td><td>';
        echo "We know about $cc <i>Crazy Credits</i>. One of them reads:<br>$crazy[0]</td></tr>\n";
    }

    // Goofs
    $goofs = $movie->goofs();
    $gc = count($goofs);
    if ($gc > 0) {
        ++$rows;
        echo '<tr><td valign=top><b>Goofs:</b></td><td>';
        echo "We know about $gc goofs. Here comes one of them:<br>";
        echo '<b>' . $goofs[0]['type'] . '</b> ' . $goofs[0]['content'] . "</td></tr>\n";
    }

    // Trivia
    $trivia = $movie->trivia();
    $gc = count($trivia);
    if ($gc > 0) {
        ++$rows;
        echo '<tr><td valign=top><b>Trivia:</b></td><td>';
        echo "There are $gc entries in the trivia list - like these:<br><ul>";
        for ($i = 0; $i < 5; ++$i) {
            if (empty($trivia[$i])) {
                break;
            }
            echo '<li>' . preg_replace("/http\:\/\/" . str_replace('.', "\.", $movie->imdbsite) . "\/name\/nm(\d{7})\//", "person.php?engine=$engine&mid=\\1", $trivia[$i]) . '</li>';
        }
        echo "</ul></td></tr>\n";
    }

    // Soundtracks
    $soundtracks = $movie->soundtrack();
    $gc = count($soundtracks);
    if ($gc > 0) {
        ++$rows;
        echo '<tr><td valign=top><b>Soundtracks:</b></td><td>';
        echo "There are $gc soundtracks listed - like these:<br>";
        echo "<table align='center' border='1' style='border-collapse:collapse;background-color:#ddd;'><tr><th style='background-color:#07f;'>Soundtrack</th><th style='background-color:#07f;'>Credit 1</th><th style='background-color:#07f;'>Credit 2</th></tr>";
        for ($i = 0; $i < 5; ++$i) {
            if (isset($soundtracks[$i]['credits'][0])) {
                $credit1 = preg_replace("/http\:\/\/" . str_replace('.', "\.", $movie->imdbsite) . "\/name\/nm(\d{7})\//", "person.php?engine=$engine&mid=\\1", $soundtracks[$i]['credits'][0]['credit_to']) . ' (' . $soundtracks[$i]['credits'][0]['desc'] . ')';
            } else {
                $credit1 = '';
            }
            if (isset($soundtracks[$i]['credits'][1])) {
                $credit2 = preg_replace("/http\:\/\/" . str_replace('.', "\.", $movie->imdbsite) . "\/name\/nm(\d{7})\//", "person.php?engine=$engine&mid=\\1", $soundtracks[$i]['credits'][1]['credit_to']) . ' (' . $soundtracks[$i]['credits'][1]['desc'] . ')';
            } else {
                $credit2 = '';
            }
            echo '<tr><td>' . $soundtracks[$i]['soundtrack'] . "</td><td>$credit1</td><td>$credit2</td></tr>";
        }
        echo "</table></td></tr>\n";
    }

    echo "</table><br>\n";
    echo "<script>// <!--\n";
    echo "  function fix_colspan() {\n";
    echo "    document.getElementById('photocol').rowSpan = '$rows';\n";
    echo "  }\n//-->\n</script>\n";
    echo '</body></html>';
}
