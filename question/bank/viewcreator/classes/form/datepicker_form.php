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

namespace qbank_viewcreator\form;

use context;
use context_system;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * Defines the form for editing question categories.
 *
 * Form for date picking.
 *
 * @package    qbank_viewcreator
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class datepicker_form extends \core_form\dynamic_form {

    /**
     * Build the form definition.
     *
     * This adds all the form fields that the date picker feature needs.
     * @throws \coding_exception
     */
    protected function definition() {
        $mform = $this->_form;
        $mform->addElement('date_time_selector', 'assesstimestart', get_string('from'));
        $mform->addElement('date_selector', 'assesstimefinish', get_string('to'));
    }

    protected function get_context_for_dynamic_submission(): context {
        return context_system::instance();
    }

    protected function check_access_for_dynamic_submission():void {
        
    }

    public function process_dynamic_submission() {
        $v = 0;
    }

    public function set_data_for_dynamic_submission(): void {

    }

    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $url = $this->optional_param('url', null, PARAM_URL);
        return new moodle_url($url);
    }
}