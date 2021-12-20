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

namespace qbank_viewcreator;

use calendar_information;
use core_question\local\bank\condition;

/**
 * This class controls from which date to which date questions are listed.
 * 
 * @package    qbank_viewcreator
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class date_condition extends condition {

    /** @var int The default filter type (BETWEEN) */
    const JOINTYPE_DEFAULT = 2;

    /** @var int Before of the following match */
    const JOINTYPE_BEFORE = 0;

    /** @var int After of the following match */
    const JOINTYPE_AFTER = 1;

    /** @var int Between of the following match */
    const JOINTYPE_BETWEEN = 2;

    /**
     * Constructor to initialize the date filter condition.
     */
    public function __construct($qbank) {
        global $PAGE;
        // This must be displayed instead of type or select
        $renderer = $PAGE->get_renderer('core_calendar');
        $calendar = calendar_information::create(time(), SITEID, null);
        list($data, $template) = calendar_get_view($calendar, 'minithree');
        $calendarhtml = $renderer->render_from_template($template, $data);
        $PAGE->requires->js_call_amd('qbank_viewcreator/date','init');
    }

    public function where() {
        return $this->where;
    }

    public function get_condition_key() {
        return 'date';
    }

    /**
     * Return parameters to be bound to the above WHERE clause fragment.
     * @return array parameter name => value.
     */
    public function params() {
        return [];
    }

    /**
     * Display GUI for selecting criteria for this condition. Displayed when Show More is open.
     *
     * Compare display_options(), which displays always, whether Show More is open or not.
     * @return bool|string HTML form fragment
     */
    public function display_options_adv() {
        return false;
    }

    /**
     * Display GUI for selecting criteria for this condition. Displayed always, whether Show More is open or not.
     *
     * Compare display_options_adv(), which displays when Show More is open.
     * @return bool|string HTML form fragment
     */
    public function display_options() {
        return false;
    }

    /**
     * Get options for filter.
     *
     * @return array
     */
    public function get_filter_options(): array {
        // TODO: Replace strings in lang files.
        $filteroptions = [
            'name' => 'date',
            'title' => 'Date',
            'custom' => false,
            'multiple' => true,
            'filterclass' => null,
            'values' => [],
            'allowempty' => true,
        ];
        return $filteroptions;
    }

    /**
     * Get the list of available joins for the filter.
     *
     * @return array
     */
    public function get_join_list(): array {
        return [
            self::JOINTYPE_BETWEEN => 'Between',
            self::JOINTYPE_BEFORE => 'Before',
            self::JOINTYPE_AFTER => 'After',
        ];
    }
}