<?php declare(strict_types=1);

/** Dump to Bzip2 format
 *
 * @see     https://www.adminer.org/plugins/#use
 *
 * @uses    bzopen(), tempnam("")
 *
 * @author  Jakub Vrana, https://www.vrana.cz/
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 */
class AdminerDumpBz2
{
    public $filename;

    public $fp;

    /**
     * @return array
     */
    public function dumpOutput()
    {
        if (!function_exists('bzopen')) {
            return [];
        }

        return ['bz2' => 'bzip2'];
    }

    /**
     * @param $string
     * @param $state
     *
     * @return false|string
     */
    public function _bz2($string, $state)
    {
        bzwrite($this->fp, $string);
        if ($state & PHP_OUTPUT_HANDLER_END) {
            bzclose($this->fp);
            $return = file_get_contents($this->filename);
            unlink($this->filename);

            return $return;
        }

        return '';
    }

    /**
     * @param      $identifier
     * @param bool $multi_table
     */
    public function dumpHeaders($identifier, $multi_table = false)
    {
        if ($_POST['output'] == 'bz2') {
            $this->filename = tempnam('', 'bz2');
            $this->fp = bzopen($this->filename, 'w');
            header('Content-Type: application/x-bzip');
            ob_start([
                $this,
                '_bz2',
            ], 1e6);
        }
    }
}
