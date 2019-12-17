<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;

/**
 * Class Radiance
 *
 * @package Pu239
 */
class Radiance
{
    protected $site_config;

    /**
     * Radiance constructor.
     *
     * @param Settings $settings
     *
     * @throws Exception
     */
    public function __construct(Settings $settings)
    {
        $this->site_config = $settings->get_settings();
    }

    /**
     * @return mixed
     */
    public function check_status()
    {
        exec("ps --no-headers -C radiance -o args,state", $result);

        return $result;
    }

    /**
     * @return mixed
     */
    public function start_radiance()
    {
        exec("radiance -d -c {$this->site_config['tracker']['config_path']}", $result);

        return $result;
    }

    /**
     * @param string $signal
     *
     * @return mixed
     */
    public function reload_radiance(string $signal)
    {
        exec("killall -s {$signal} radiance", $result);

        return $result;
    }
}
