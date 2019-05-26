<?php declare(strict_types=1);

/** Adminer customization allowing usage of plugins
 *
 * @see     https://www.adminer.org/plugins/#use
 *
 * @author  Jakub Vrana, https://www.vrana.cz/
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 */
class AdminerPlugin extends Adminer
{
    public $plugins;

    /** Register plugins
     *
     * @param array object instances or null to register all classes starting by 'Adminer'
     */
    public function __construct($plugins)
    {
        if ($plugins === null) {
            $plugins = [];
            foreach (get_declared_classes() as $class) {
                if (preg_match('~^Adminer.~i', $class) && strcasecmp($this->_findRootClass($class), 'Adminer')) { //! can use interface
                    $plugins[$class] = new $class();
                }
            }
        }
        $this->plugins = $plugins;
        //! it is possible to use ReflectionObject to find out which plugins defines which methods at once
    }

    /**
     * @param $class
     *
     * @return mixed
     */
    public function _findRootClass($class)
    { // is_subclass_of(string, string) is available since PHP 5.0.3
        do {
            $return = $class;
        } while ($class = get_parent_class($class));

        return $return;
    }

    /**
     * @return array|mixed
     */
    public function dumpFormat()
    {
        $args = func_get_args();

        return $this->_appendPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $function
     * @param $args
     *
     * @return mixed
     */
    public function _appendPlugin($function, $args)
    {
        $return = $this->_callParent($function, $args);
        foreach ($this->plugins as $plugin) {
            if (method_exists($plugin, $function)) {
                $value = call_user_func_array([
                    $plugin,
                    $function,
                ], $args);
                if ($value) {
                    $return += $value;
                }
            }
        }

        return $return;
    }

    /**
     * @param $function
     * @param $args
     *
     * @return mixed
     */
    public function _callParent($function, $args)
    {
        return call_user_func_array([
            'parent',
            $function,
        ], $args);
    }

    // appendPlugin

    /**
     * @return array|mixed
     */
    public function dumpOutput()
    {
        $args = func_get_args();

        return $this->_appendPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $field
     *
     * @return array|mixed
     */
    public function editFunctions($field)
    {
        $args = func_get_args();

        return $this->_appendPlugin(__FUNCTION__, $args);
    }

    /**
     * @return mixed|string
     */
    public function name()
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    // applyPlugin

    /**
     * @param $function
     * @param $args
     *
     * @return mixed
     */
    public function _applyPlugin($function, $args)
    {
        foreach ($this->plugins as $plugin) {
            if (method_exists($plugin, $function)) {
                switch (count($args)) { // call_user_func_array() doesn't work well with references
                    case 0:
                        $return = $plugin->$function();
                        break;
                    case 1:
                        $return = $plugin->$function($args[0]);
                        break;
                    case 2:
                        $return = $plugin->$function($args[0], $args[1]);
                        break;
                    case 3:
                        $return = $plugin->$function($args[0], $args[1], $args[2]);
                        break;
                    case 4:
                        $return = $plugin->$function($args[0], $args[1], $args[2], $args[3]);
                        break;
                    case 5:
                        $return = $plugin->$function($args[0], $args[1], $args[2], $args[3], $args[4]);
                        break;
                    case 6:
                        $return = $plugin->$function($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);
                        break;
                    default:
                        trigger_error('Too many parameters.', E_USER_WARNING);
                }
                if ($return !== null) {
                    return $return;
                }
            }
        }

        return $this->_callParent($function, $args);
    }

    /**
     * @return array|mixed
     */
    public function credentials()
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @return mixed|void
     */
    public function connectSsl()
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param bool $create
     *
     * @return false|mixed|string
     */
    public function permanentLogin($create = false)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $server
     *
     * @return mixed
     */
    public function serverName($server)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @return mixed
     */
    public function database()
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @return array|mixed
     */
    public function schemas()
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param bool $flush
     *
     * @return array|mixed|null
     */
    public function databases($flush = true)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @return int|mixed
     */
    public function queryTimeout()
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @return mixed|void
     */
    public function headers()
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @return array|mixed
     */
    public function csp()
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @return bool|mixed
     */
    public function head()
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @return array|mixed
     */
    public function css()
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @return mixed|void
     */
    public function loginForm()
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $name
     * @param $heading
     * @param $value
     *
     * @return mixed|string
     */
    public function loginFormField($name, $heading, $value)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $login
     * @param $password
     *
     * @return bool|mixed|string
     */
    public function login($login, $password)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $tableStatus
     *
     * @return mixed
     */
    public function tableName($tableStatus)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param     $field
     * @param int $order
     *
     * @return mixed|string
     */
    public function fieldName($field, $order = 0)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param        $tableStatus
     * @param string $set
     *
     * @return mixed|void
     */
    public function selectLinks($tableStatus, $set = '')
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $table
     *
     * @return array|mixed
     */
    public function foreignKeys($table)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $table
     * @param $tableName
     *
     * @return array|mixed
     */
    public function backwardKeys($table, $tableName)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $backwardKeys
     * @param $row
     *
     * @return mixed|void
     */
    public function backwardKeysPrint($backwardKeys, $row)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param      $query
     * @param      $start
     * @param bool $failed
     *
     * @return mixed|string
     */
    public function selectQuery($query, $start, $failed = false)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $query
     *
     * @return mixed|string
     */
    public function sqlCommandQuery($query)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $table
     *
     * @return mixed|string
     */
    public function rowDescription($table)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $rows
     * @param $foreignKeys
     *
     * @return mixed
     */
    public function rowDescriptions($rows, $foreignKeys)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $val
     * @param $field
     *
     * @return mixed|void
     */
    public function selectLink($val, $field)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $val
     * @param $link
     * @param $field
     * @param $original
     *
     * @return mixed|string
     */
    public function selectVal($val, $link, $field, $original)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $val
     * @param $field
     *
     * @return mixed
     */
    public function editVal($val, $field)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $fields
     *
     * @return mixed|void
     */
    public function tableStructurePrint($fields)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $indexes
     *
     * @return mixed|void
     */
    public function tableIndexesPrint($indexes)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $select
     * @param $columns
     *
     * @return mixed|void
     */
    public function selectColumnsPrint($select, $columns)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $where
     * @param $columns
     * @param $indexes
     *
     * @return mixed|void
     */
    public function selectSearchPrint($where, $columns, $indexes)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $order
     * @param $columns
     * @param $indexes
     *
     * @return mixed|void
     */
    public function selectOrderPrint($order, $columns, $indexes)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $limit
     *
     * @return mixed|void
     */
    public function selectLimitPrint($limit)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $text_length
     *
     * @return mixed|void
     */
    public function selectLengthPrint($text_length)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $indexes
     *
     * @return mixed|void
     */
    public function selectActionPrint($indexes)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @return bool|mixed
     */
    public function selectCommandPrint()
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @return bool|mixed
     */
    public function selectImportPrint()
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $emailFields
     * @param $columns
     *
     * @return mixed|void
     */
    public function selectEmailPrint($emailFields, $columns)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $columns
     * @param $indexes
     *
     * @return array|mixed
     */
    public function selectColumnsProcess($columns, $indexes)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $fields
     * @param $indexes
     *
     * @return array|mixed
     */
    public function selectSearchProcess($fields, $indexes)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $fields
     * @param $indexes
     *
     * @return array|mixed
     */
    public function selectOrderProcess($fields, $indexes)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @return mixed|string
     */
    public function selectLimitProcess()
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @return mixed|string
     */
    public function selectLengthProcess()
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $where
     * @param $foreignKeys
     *
     * @return bool|mixed
     */
    public function selectEmailProcess($where, $foreignKeys)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $select
     * @param $where
     * @param $group
     * @param $order
     * @param $limit
     * @param $page
     *
     * @return mixed|string
     */
    public function selectQueryBuild($select, $where, $group, $order, $limit, $page)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param      $query
     * @param      $time
     * @param bool $failed
     *
     * @return mixed|string
     */
    public function messageQuery($query, $time, $failed = false)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $table
     * @param $field
     * @param $attrs
     * @param $value
     *
     * @return mixed|string
     */
    public function editInput($table, $field, $attrs, $value)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $table
     * @param $field
     * @param $value
     *
     * @return mixed|string
     */
    public function editHint($table, $field, $value)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param        $field
     * @param        $value
     * @param string $function
     *
     * @return mixed|string
     */
    public function processInput($field, $value, $function = '')
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $db
     *
     * @return mixed|void
     */
    public function dumpDatabase($db)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param     $table
     * @param     $style
     * @param int $is_view
     *
     * @return mixed|void
     */
    public function dumpTable($table, $style, $is_view = 0)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $table
     * @param $style
     * @param $query
     *
     * @return mixed|void
     */
    public function dumpData($table, $style, $query)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $identifier
     *
     * @return mixed|string|string[]|null
     */
    public function dumpFilename($identifier)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param      $identifier
     * @param bool $multi_table
     *
     * @return mixed|string
     */
    public function dumpHeaders($identifier, $multi_table = false)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @return mixed|string
     */
    public function importServerPath()
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @return bool|mixed
     */
    public function homepage()
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $missing
     *
     * @return mixed|void
     */
    public function navigation($missing)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $missing
     *
     * @return mixed|void
     */
    public function databasesPrint($missing)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }

    /**
     * @param $tables
     *
     * @return mixed|void
     */
    public function tablesPrint($tables)
    {
        $args = func_get_args();

        return $this->_applyPlugin(__FUNCTION__, $args);
    }
}
