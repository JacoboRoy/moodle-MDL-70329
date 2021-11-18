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
     * @var array Disabled columns in config_plugins table.
     */
    public $disabledcolumns;

    /**
     * Constructor for column_manager class.
     *
     */
    public function __construct() {
        $this->columnorder = array_flip(explode(',', get_config('qbank_columnsortorder', 'columnsortorderenabled')));
        $this->disabledcolumns = array_flip(explode(',', get_config('qbank_columnsortorder', 'columnsortorderdisabled')));
    }

    /**
     * Sets column order in the qbank_columnsortorder plugin config.
     *
     */
    public static function set_column_order(array $columns) : void {
        $columns = implode(',', $columns);
        set_config('columnsortorderenabled', $columns, 'qbank_columnsortorder');
    }

    /**
     * Gets qbank.
     *
     * @return array
     */
    protected function get_questionbank(): view {
        $course = (object) ['id' => 0];
        $context = context_system::instance();
        $contexts = new question_edit_contexts($context);
        // Dummy call to get the objects without error.
        $questionbank = new view($contexts, new moodle_url('/question/dummyurl.php'), $course, null);
        return $questionbank;
    }

    /**
     * Get enabled columns of the question list.
     *
     * @return array
     */
    public function get_columns(): array {
        $columns = [];
        foreach ($this->get_questionbank()->get_visiblecolumns() as $key => $column) {
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
        $disabledcolumns = $this->disabledcolumns;
        $disabled = [];
        foreach ($disabledcolumns as $class => $value) {
            if ($class !== 0) {
                if (strpos($class, 'qbank_customfields\custom_field_column') !== false) {
                    $class = explode('\\', $class);
                    $disabledname = array_pop($class);
                    $class = implode('\\', $class);
                    $disabled[] = (object) [
                        'disabledname' => $disabledname,
                    ];
                } else {
                    $columnobject = new $class($this->get_questionbank());
                    $disabled[] = (object) [
                        'disabledname' => $columnobject->get_title(),
                    ];
                }
            }
        }
        return $disabled;
    }

    protected function update_config($enabledcolumns, $disabledcolumns): void {
        if (!empty($enabledcolumns)) {
            $configenabled = implode(',', array_flip($enabledcolumns));
            set_config('columnsortorderenabled', $configenabled, 'qbank_columnsortorder');
        }
        if (!empty($disabledcolumns)) {
            $configdisabled = implode(',', array_flip($disabledcolumns));
            set_config('columnsortorderdisabled', $configdisabled, 'qbank_columnsortorder');
        }
    }

    /**
     * Removes any uninstalled or disabled plugin column in the config_plugins for 'qbank_columnsortorder' plugin.
     *
     * @param string $plugintoremove Plugin type and name ie: qbank_viewcreator.
     */
    public function remove_unused_column_from_db(string $plugintoremove): void {
        $enabledcolumns = $this->columnorder;
        $disabledcolumns = $this->disabledcolumns;
        foreach ($disabledcolumns as $key => $position) {
            if (strpos($key, $plugintoremove) !== false) {
                if (isset($enabledcolumns[$key])) {
                    unset($enabledcolumns[$key]);
                }
                if (isset($disabledcolumns[$key])) {
                    unset($disabledcolumns[$key]);
                }
            }
        }
        $this->update_config($enabledcolumns, $disabledcolumns);
    }

    /**
     * Enables columns in the config_plugins for 'qbank_columnsortorder' plugin.
     *
     * @param string $plugin Plugin type and name ie: qbank_viewcreator.
     */
    public function enablecolumns(string $plugin): void {
        $enabledcolumns = $this->columnorder;
        $disabledcolumns = $this->disabledcolumns;
        foreach ($disabledcolumns as $key => $position) {
            if (strpos($key, $plugin) !== false) {
                $enabledcolumns[$key] = $position;
                unset($disabledcolumns[$key]);
            }
        }
        $this->update_config($enabledcolumns, $disabledcolumns);
    }

    /**
     * Disables columns in the config_plugins for 'qbank_columnsortorder' plugin.
     *
     * @param string $plugin Plugin type and name ie: qbank_viewcreator.
     */
    public function disablecolumns(string $plugin): void {
        $enabledcolumns = $this->columnorder;
        $disabledcolumns = $this->disabledcolumns;
        foreach ($enabledcolumns as $key => $position) {
            if (strpos($key, $plugin) !== false) {
                $disabledcolumns[$key] = $position;
                unset($enabledcolumns[$key]);
            }
        }
        $this->update_config($enabledcolumns, $disabledcolumns);
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
