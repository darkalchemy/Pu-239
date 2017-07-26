<?php
//############################################################################
// IMDBPHP.Movie                                         (c) Itzchak Rehberg #
// written by Itzchak Rehberg <izzysoft AT qumran DOT org>                   #
// http://www.izzysoft.de/                                                   #
// ------------------------------------------------------------------------- #
// This program is free software; you can redistribute and/or modify it      #
// under the terms of the GNU General Public License (see doc/LICENSE)       #
//############################################################################

/* $Id: movie_base.class.php 622 2013-10-23 08:36:34Z izzy $ */

require_once dirname(__FILE__) . '/browseremulator.class.php';
require_once dirname(__FILE__) . '/mdb_base.class.php';
require_once dirname(__FILE__) . '/mdb_request.class.php';

//===================================================[ The Movie Base class ]===

/** Accessing Movie information
 * @class movie_base
 * @extends mdb_base
 *
 * @author Izzy (izzysoft AT qumran DOT org)
 * @copyright (c) 2009 by Itzchak Rehberg and IzzySoft
 *
 * @version $Revision: 622 $ $Date: 2013-10-23 10:36:34 +0200 (Mi, 23. Okt 2013) $
 */
class movie_base extends mdb_base
{
    //------------------------------------------------------------[ Constructor ]---

    /** Initialize class
     * @constructor movie_base
     *
     * @param string id IMDBID to use for data retrieval
     */
    public function __construct($id)
    {
        parent::__construct($id);
        $this->reset_vars();
    }

    //--------------------------------------------------[ Start (over) / Reset ]---

    /** Reset page vars
     * @method protected reset_vars
     */
    protected function reset_vars()
    {
        $this->page['Title'] = '';
        $this->page['TitleFoot'] = ''; // IMDB only, as part of info was outsourced
        $this->page['Credits'] = '';
        $this->page['CrazyCredits'] = '';
        $this->page['Amazon'] = '';
        $this->page['Goofs'] = '';
        $this->page['Trivia'] = '';
        $this->page['Plot'] = '';
        $this->page['Synopsis'] = '';
        $this->page['Comments'] = '';
        $this->page['Quotes'] = '';
        $this->page['Taglines'] = '';
        $this->page['Plotoutline'] = '';
        $this->page['Trivia'] = '';
        $this->page['Directed'] = '';
        $this->page['Episodes'] = '';
        $this->page['Quotes'] = '';
        $this->page['Trailers'] = '';
        $this->page['MovieConnections'] = '';
        $this->page['ExtReviews'] = '';
        $this->page['ReleaseInfo'] = '';
        $this->page['CompanyCredits'] = '';
        $this->page['ParentalGuide'] = '';
        $this->page['OfficialSites'] = '';
        $this->page['Keywords'] = '';
        $this->page['Awards'] = '';
        $this->page['Locations'] = '';
        $this->page['VideoSites'] = '';

        $this->akas = [];
        $this->awards = [];
        $this->countries = [];
        $this->castlist = []; // pilot only
        $this->crazy_credits = [];
        $this->credits_cast = [];
        $this->credits_composer = [];
        $this->credits_director = [];
        $this->credits_producer = [];
        $this->credits_writing = [];
        $this->extreviews = [];
        $this->goofs = [];
        $this->langs = [];
        $this->langs_full = [];
        $this->aspectratio = '';
        $this->main_comment = '';
        $this->main_genre = '';
        $this->main_keywords = [];
        $this->all_keywords = [];
        $this->main_language = '';
        $this->main_photo = '';
        $this->main_thumb = '';
        $this->main_pictures = [];
        $this->main_plotoutline = '';
        $this->main_rating = -1;
        $this->main_runtime = '';
        $this->main_movietype = '';
        $this->main_title = '';
        $this->original_title = '';
        $this->main_votes = -1;
        $this->main_year = -1;
        $this->main_endyear = -1;
        $this->main_yearspan = [];
        $this->main_creator = [];
        $this->main_tagline = '';
        $this->main_storyline = '';
        $this->main_prodnotes = [];
        $this->main_movietypes = [];
        $this->main_top250 = -1;
        $this->moviecolors = [];
        $this->movieconnections = [];
        $this->moviegenres = [];
        $this->moviequotes = [];
        $this->movierecommendations = [];
        $this->movieruntimes = [];
        $this->mpaas = [];
        $this->mpaas_hist = [];
        $this->mpaa_justification = '';
        $this->plot_plot = [];
        $this->synopsis_wiki = '';
        $this->release_info = [];
        $this->seasoncount = -1;
        $this->season_episodes = [];
        $this->sound = [];
        $this->soundtracks = [];
        $this->split_comment = [];
        $this->split_plot = [];
        $this->taglines = [];
        $this->trailers = [];
        $this->video_sites = [];
        $this->soundclip_sites = [];
        $this->photo_sites = [];
        $this->misc_sites = [];
        $this->trivia = [];
        $this->compcred_prod = [];
        $this->compcred_dist = [];
        $this->compcred_special = [];
        $this->compcred_other = [];
        $this->parental_guide = [];
        $this->official_sites = [];
        $this->locations = [];
    }
} // end class movie_base
