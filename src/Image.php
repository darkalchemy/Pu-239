<?php

namespace DarkAlchemy\Pu239;

/**
 * Class Image.
 */
class Image
{
    protected $fluent;
    protected $site_config;
    protected $limit;

    public function __construct()
    {
        global $fluent, $site_config;

        $this->fluent = $fluent;
        $this->site_config = $site_config;
        $this->limit = $this->site_config['query_limit'];
    }

    /**
     * @param array $values
     *
     * @throws \Exception
     */
    public function insert(array $values)
    {
        $this->fluent->insertInto('images')
            ->values($values)
            ->ignore()
            ->execute();
    }

    /**
     * @param array $values
     * @param array $update
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function update(array $values, array $update)
    {
        $count = floor($this->limit / max(array_map('count', $values)));
        foreach (array_chunk($values, $count) as $t) {
            $this->fluent->insertInto('images', $t)
                ->onDuplicateKeyUpdate($update)
                ->execute();
        }
    }
}
