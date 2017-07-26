<?php
//############################################################################
// IMDBPHP.Person                                        (c) Itzchak Rehberg #
// written by Itzchak Rehberg <izzysoft AT qumran DOT org>                   #
// http://www.izzysoft.de/                                                   #
// ------------------------------------------------------------------------- #
// This program is free software; you can redistribute and/or modify it      #
// under the terms of the GNU General Public License (see doc/LICENSE)       #
//############################################################################

/* $Id: person_base.class.php 594 2013-09-19 22:23:55Z izzy $ */

require_once dirname(__FILE__) . '/browseremulator.class.php';
require_once dirname(__FILE__) . '/mdb_base.class.php';

//===================================================[ The Movie Base class ]===

/** Accessing Movie information
 * @class person_base
 * @extends mdb_base
 *
 * @author Izzy (izzysoft AT qumran DOT org)
 * @copyright (c) 2009 by Itzchak Rehberg and IzzySoft
 *
 * @version $Revision: 594 $ $Date: 2013-09-20 00:23:55 +0200 (Fr, 20. Sep 2013) $
 */
class person_base extends mdb_base
{
    //------------------------------------------------------------[ Constructor ]---

    /** Initialize class
     * @constructor person_base
     *
     * @param string id IMDBID to use for data retrieval
     */
    public function __construct($id)
    {
        parent::__construct($id);
    }

    //--------------------------------------------------[ Start (over) / Reset ]---

    /** Reset page vars
     * @method protected reset_vars
     */
    protected function reset_vars()
    {
        $this->page['Name'] = '';
        $this->page['Bio'] = '';

        // "Name" page:
        $this->main_photo = '';
        $this->fullname = '';
        $this->birthday = [];
        $this->deathday = [];
        $this->allfilms = [];
        $this->actressfilms = [];
        $this->actorsfilms = [];
        $this->producersfilms = [];
        $this->soundtrackfilms = [];
        $this->directorsfilms = [];
        $this->crewsfilms = [];
        $this->thanxfilms = [];
        $this->writerfilms = [];
        $this->selffilms = [];
        $this->archivefilms = [];

        // "Bio" page:
        $this->birth_name = '';
        $this->nick_name = [];
        $this->bodyheight = [];
        $this->spouses = [];
        $this->bio_bio = [];
        $this->bio_trivia = [];
        $this->bio_tm = [];
        $this->bio_salary = [];

        // "Publicity" page:
        $this->pub_prints = [];
        $this->pub_movies = [];
        $this->pub_interviews = [];
        $this->pub_articles = [];
        $this->pub_pictorial = [];
        $this->pub_magcovers = [];

        // SearchDetails
        $this->SearchDetails = [];
    }
} // end class person_base
