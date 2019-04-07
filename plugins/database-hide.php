<?php

/** Hide some databases from the interface - just to improve design, not a security plugin
 *
 * @see     https://www.adminer.org/plugins/#use
 *
 * @author  Jakub Vrana, https://www.vrana.cz/
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 */
class AdminerDatabaseHide
{
    protected $disabled;

    /**
     * @param array case insensitive database names in values
     */
    public function __construct($disabled)
    {
        $this->disabled = array_map('strtolower', $disabled);
    }

    public function databases($flush = true)
    {
        $return = [];
        foreach (get_databases($flush) as $db) {
            if (!in_array(strtolower($db), $this->disabled)) {
                $return[] = $db;
            }
        }

        return $return;
    }
}
