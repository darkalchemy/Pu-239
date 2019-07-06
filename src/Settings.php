<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use Psr\Container\ContainerInterface;

/**
 * Class Settings.
 * @package Pu239
 */
class Settings
{
    protected $cache;
    protected $fluent;
    protected $container;

    /**
     * Settings constructor.
     *
     * @param Cache              $cache
     * @param Database           $fluent
     * @param ContainerInterface $c
     */
    public function __construct(Cache $cache, Database $fluent, ContainerInterface $c)
    {
        $this->cache = $cache;
        $this->fluent = $fluent;
        $this->container = $c;
    }

    /**
     * @throws Exception
     *
     * @return array
     */
    public function get_settings()
    {
        $env = $this->container->get('env');
        $staff = $this->get_staff();
        $staff_forums = $this->get_staff_forums();
        $site_config = $this->get_site_config();
        $hnrs = $this->get_hnr();
        $forums = $this->get_forum_config();
        $badwords = $this->get_badwords();
        $this->class_config();
        $config = array_merge_recursive($env, $staff, $staff_forums, $site_config, $hnrs, $forums, $badwords);
        $config['site']['badwords'] = array_merge($config['badwords'], $config['site']['bad_words']);
        unset($config['badwords'], $config['site']['bad_words']);
        $this->recursive_ksort($config);

        return $config;
    }

    /**
     * @throws Exception
     *
     * @return bool|mixed
     */
    protected function get_staff()
    {
        $staff = $this->cache->get('is_staff_');
        if ($staff === false || is_null($staff)) {
            $sql = $this->fluent->from('users')
                                ->select(null)
                                ->select('id')
                                ->where('class >= ?', UC_STAFF)
                                ->where('class <= ?', UC_MAX)
                                ->orderBy('id ASC');
            foreach ($sql as $res) {
                $staff['is_staff'][] = $res['id'];
            }

            if (!empty($staff['is_staff'])) {
                $this->cache->set('is_staff_', $staff, 86400);
            } else {
                die("You don't have any users defined as STAFF");
            }
        }

        return $staff;
    }

    /**
     * @throws Exception
     *
     * @return bool|mixed
     */
    protected function get_staff_forums()
    {
        $staff_forums = $this->cache->get('staff_forums_');
        if ($staff_forums === false || is_null($staff_forums)) {
            $sql = $this->fluent->from('forums')
                                ->select(null)
                                ->select('id')
                                ->where('min_class_read >= ?', UC_STAFF)
                                ->orderBy('id')
                                ->fetchAll();

            if (empty($sql)) {
                $staff_forums['staff_forums'] = 0;
            } else {
                foreach ($sql as $res) {
                    $staff_forums['staff_forums'][] = $res['id'];
                }
            }

            $this->cache->set('staff_forums_', $staff_forums, 86400);
        }

        return $staff_forums;
    }

    /**
     * @throws Exception
     *
     * @return bool|mixed
     */
    protected function get_site_config()
    {
        $site_config_db = $this->cache->get('site_settings_');
        if ($site_config_db === false || is_null($site_config_db)) {
            $sql = $this->fluent->from('site_config')
                                ->orderBy('parent')
                                ->orderBy('name');

            foreach ($sql as $row) {
                switch ($row['type']) {
                    case 'int':
                        $value = (int) $row['value'];
                        break;
                    case 'float':
                        $value = (float) $row['value'];
                        break;
                    case 'bool':
                        $value = (bool) $row['value'];
                        break;
                    case 'array':
                        if (empty($row['value'])) {
                            $value = [];
                        } else {
                            $value = explode('|', $row['value']);
                            foreach ($value as $key => $item) {
                                if (is_numeric($item)) {
                                    $value[$key] = (int) $item;
                                }
                            }
                        }
                        break;
                    default:
                        $value = $row['value'];
                }
                if (!empty($row['parent'])) {
                    $site_config_db[$row['parent']][$row['name']] = $value;
                } else {
                    $site_config_db[$row['name']] = $value;
                }
            }

            $this->cache->set('site_settings_', $site_config_db, 86400);
        }

        return $site_config_db;
    }

    /**
     * @throws Exception
     *
     * @return bool|mixed
     */
    protected function get_hnr()
    {
        $hnr_config = $this->cache->get('hnr_config_');
        if ($hnr_config === false || is_null($hnr_config)) {
            $query = $this->fluent->from('hit_and_run_settings')
                                  ->orderBy('name');
            foreach ($query as $row) {
                if ($row['name'] === 'caindays') {
                    $value = (float) $row['value'];
                } else {
                    $value = is_numeric($row['value']) ? (int) $row['value'] : $row['value'];
                }
                $hnr_config['hnr_config'][$row['name']] = $value;
            }
            $this->cache->set('hnr_config_', $hnr_config, 86400);
        }

        return $hnr_config;
    }

    /**
     * @throws Exception
     *
     * @return bool|mixed
     */
    protected function get_forum_config()
    {
        $forum_config = $this->cache->get('forum_config_');
        if ($forum_config === false || is_null($forum_config)) {
            $query = $this->fluent->from('forum_config');
            foreach ($query as $row) {
                foreach ($row as $key => $value) {
                    if ($key === 'delete_for_real') {
                        $value = (bool) $value;
                    } elseif (is_numeric($value)) {
                        $value = (int) $value;
                    }
                    $forum_config['forum_config'][$key] = $value;
                }
            }
            $this->cache->set('forum_config_', $forum_config, 86400);
        }

        return $forum_config;
    }

    /**
     * @throws Exception
     *
     * @return bool|mixed
     */
    protected function get_badwords()
    {
        $badwords = $this->cache->get('badwords_');
        if ($badwords === false || is_null($badwords)) {
            $query = $this->fluent->from('class_config')
                                  ->select('name')
                                  ->select('classname')
                                  ->where('template = 1')
                                  ->where('classname != ""');
            $temp = [];
            foreach ($query as $classname) {
                $temp[] = $classname['name'];
                $temp[] = $classname['classname'];
                $temp[] = str_replace('_', '', $classname['name']);
                $temp[] = str_replace(' ', '', $classname['classname']);
            }
            $badwords['badwords'] = array_unique($temp);
            $this->cache->set('badwords_', $badwords, 86400);
        }

        return $badwords;
    }

    /**
     * @throws Exception
     */
    protected function class_config()
    {
        $styles = $this->get_styles();
        foreach ($styles as $style) {
            $class_config = $this->cache->get('class_config_' . $style);
            if ($class_config === false || is_null($class_config)) {
                $class_config = $this->fluent->from('class_config')
                                             ->orderBy('value')
                                             ->where('template = ?', $style)
                                             ->fetchAll();
                $this->cache->set('class_config_' . $style, $class_config, 86400);
            }
        }
    }

    /**
     * @throws Exception
     *
     * @return array
     */
    protected function get_styles()
    {
        $styles = $this->cache->get('styles_');
        if ($styles === false || is_null($styles)) {
            $query = $this->fluent->from('stylesheets')
                                  ->select(null)
                                  ->select('id');
            $styles = [];
            foreach ($query as $style) {
                $styles[] = $style['id'];
            }
            $this->cache->set('styles_', $styles, 86400);
        }

        return $styles;
    }

    /**
     * @param $array
     *
     * @return bool
     */
    protected function recursive_ksort(&$array)
    {
        foreach ($array as $k => &$v) {
            if (is_array($v)) {
                $this->recursive_ksort($v);
            }
        }

        return ksort($array);
    }
}
