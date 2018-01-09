<?php

/**
 * Class Chmod
 */
class Chmod
{
    private $_dir;
    private $_modes = [
        'owner'  => 0,
        'group'  => 0,
        'public' => 0,
    ];

    /**
     * Chmod constructor.
     *
     * @param       $dir
     * @param array $OwnerModes
     * @param array $GroupModes
     * @param array $PublicModes
     */
    public function __construct($dir, $OwnerModes = [], $GroupModes = [], $PublicModes = [])
    {
        $this->_dir = $dir;
        $this->setOwnerModes($OwnerModes[0], $OwnerModes[1], $OwnerModes[2]);
        $this->setGroupModes($GroupModes[0], $GroupModes[1], $GroupModes[2]);
        $this->setPublicModes($PublicModes[0], $PublicModes[1], $PublicModes[2]);
    }

    /**
     * @param $read
     * @param $write
     * @param $execute
     */
    private function setOwnerModes($read, $write, $execute)
    {
        $this->_modes['owner'] = $this->setMode($read, $write, $execute);
    }

    /**
     * @param $read
     * @param $write
     * @param $execute
     *
     * @return int
     */
    private function setMode($read, $write, $execute)
    {
        $mode = 0;
        if ($read) {
            $mode += 4;
        }
        if ($write) {
            $mode += 2;
        }
        if ($execute) {
            $mode += 1;
        }

        return $mode;
    }

    /**
     * @param $read
     * @param $write
     * @param $execute
     */
    private function setGroupModes($read, $write, $execute)
    {
        $this->_modes['group'] = $this->setMode($read, $write, $execute);
    }

    /**
     * @param $read
     * @param $write
     * @param $execute
     */
    private function setPublicModes($read, $write, $execute)
    {
        $this->_modes['public'] = $this->setMode($read, $write, $execute);
    }

    /**
     * @return array
     */
    public function setChmod()
    {
        if (is_array($this->_dir)) {
            $return = [];
            foreach ($this->_dir as $dir) {
                $return[] = $this->returnValue($dir);
            }

            return $return;
        } else {
            return $this->returnValue($this->_dir);
        }
    }

    /**
     * @param $dir
     *
     * @return array
     */
    private function returnValue($dir)
    {
        return is_dir($dir) ? [
            'chmod',
            @chmod($dir, $this->getMode()),
            $this->getMode(),
            $dir,
        ] : [
            'mkdir',
            @mkdir($dir, $this->getMode()),
            $this->getMode(),
            $dir,
        ];
    }

    /**
     * @return string
     */
    private function getMode()
    {
        return $this->_modes['owner'] . $this->_modes['group'] . $this->_modes['public'];
    }
}
