<?php

declare(strict_types = 1);

namespace Pu239;

use ZipArchive;

/**
 * Class Phpzip.
 * @package Pu239
 */
class Phpzip extends ZipArchive
{
    /**
     * @param $zipfile
     *
     * @return bool
     */
    public function force_download($zipfile)
    {
        if (file_exists($zipfile)) {
            header('Content-type: application/zip');
            header('Content-Disposition: attachment; filename="' . basename($zipfile) . '"');
            header('Content-Transfer-Encoding: Binary');
            header('Content-length: ' . filesize($zipfile));
            header('Pragma: no-cache');
            header('Expires: 0');

            ob_clean();
            flush();
            readfile($zipfile);

            return true;
        }

        return false;
    }
}
