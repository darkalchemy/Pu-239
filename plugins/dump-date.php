<?php declare(strict_types=1);

/** Include current date and time in export filename
 *
 * @see     https://www.adminer.org/plugins/#use
 *
 * @author  Jakub Vrana, https://www.vrana.cz/
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 */
class AdminerDumpDate
{
    /**
     * @param $identifier
     *
     * @return string|string[]|null
     */
    public function dumpFilename($identifier)
    {
        $connection = connection();

        return friendly_url(($identifier != '' ? $identifier : (SERVER != '' ? SERVER : 'localhost')) . '-' . $connection->result('SELECT NOW()'));
    }
}
