<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace qbank_columnsortorder;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');

use core_question\local\bank\view;
use moodle_url;
use context_system;
use question_edit_contexts;

/**
 * Class column_manager responsible for loading and saving order to the config setting.
 *
 * @package    qbank_columnsortorder
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class column_manager {
    /**
     * @var array Column order as set in config_plugins 'class' => 'position', ie: question_type_column => 3.
     */
    public $columnorder;

    /**
     * Constructor for column_manager class.
     *
     */
    public function __construct() {
        $this->columnorder = array_flip(explode(',', get_config('qbank_columnsortorder', 'columnsortorder')));
    }

    /**
     * Method setting column order in the qbank_columnsortorder plugin config.
     *
     */
    public static function set_column_order(array $columns) : void {
        $columns = implode(',', $columns);
        set_config('columnsortorder', $columns, 'qbank_columnsortorder');
    }

    /**
     * Get enabled columns.
     *
     * @return array
     */
    protected function get_enabled_columns(): array {
        $course = (object) ['id' => 0];
        $context = context_system::instance();
        $contexts = new question_edit_contexts($context);
        // Dummy call to get the objects without error.
        $questionbank = new view($contexts, new moodle_url('/question/dummyurl.php'), $course, null);
        return $questionbank->get_visiblecolumns();
    }

    /**
     * Get the columns of the question list.
     *
     * @return array
     */
    public function get_columns(): array {
        $columns = [];
        foreach ($this->get_enabled_columns() as $key => $column) {
            if ($column->get_name() === 'checkbox') {
                continue;
            }
            $classelements = explode('\\', $key);
            $columns[] = (object) [
                'class' => get_class($column),
                'name' => $column->get_title(),
                'colname' => end($classelements),
            ];
        }
        return $columns;
    }

    /**
     * Get disabled columns.
     *
     * @return array
     */
    public function get_disabled_columns(): array {
        $columns = $this->get_enabled_columns();
        $classes = [];
        foreach ($columns as $key => $column) {
            $classes[get_class($column)] = $key;
        }
        $diffkey = array_diff_key($classes, $this->columnorder);
        foreach ($diffkey as $class => $value) {
            $disabled[] = (object) [
                'disabledclass' => $class,
                'disabledcolumn' => $value,
            ];
        }
        return $disabled;
    }

    /**
     * Removes any uninstalled or disabled plugin column in the config_plugins for 'qbank_columnsortorder' plugin.
     *
     * @param string $plugintoremove Plugin type and name ie: qbank_viewcreator.
     */
    public function remove_unused_column_from_db(string $plugintoremove): void {
        $qbankplugins = $this->get_columns();
        $config = $this->columnorder;
        foreach ($qbankplugins as $plugin) {
            if (strpos($plugin->class, $plugintoremove) !== false) {
                unset($config[$plugin->class]);
            }
        }
        $config = implode(',', array_flip($config));
        set_config('columnsortorder', $config, 'qbank_columnsortorder');
    }

    /**
     * Orders columns in the question bank view according to config_plugins table 'qbank_columnsortorder' config.
     *
     * @param array $ordertosort Unordered array of columns
     * @return array $properorder|$ordertosort Returns array ordered if 'qbank_columnsortorder' config exists.
     */
    public function sort_columns($ordertosort): array {
        // Check if db has order set.
        if (!empty($this->columnorder)) {
            // Merge new order with old one.
            $columnsortorder = $this->columnorder;
            asort($columnsortorder);
            $columnorder = [];
            foreach ($columnsortorder as $classname => $colposition) {
                $colname = explode('\\', $classname);
                if (strpos($classname, 'qbank_customfields\custom_field_column') !== false) {
                    unset($colname[0]);
                    $classname = implode('\\', $colname);
                    $columnorder[$classname] = $colposition;
                } else {
                    $columnorder[end($colname)] = $colposition;
                }
            }
            $properorder = array_merge($columnorder, $ordertosort);
            // Always have the checkbox at first column position.
            if (isset($properorder['checkbox_column'])) {
                $checkboxfirstelement = $properorder['checkbox_column'];
                unset($properorder['checkbox_column']);
                $properorder = array_merge(['checkbox_column' => $checkboxfirstelement], $properorder);
            }
            return $properorder;
        }
        return $ordertosort;
    }
}
