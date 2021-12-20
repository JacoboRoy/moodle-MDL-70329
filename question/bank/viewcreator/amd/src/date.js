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

/**
 * Question bank filter managemnet.
 *
 * @module     qbank_viewcreator
 * @copyright  2021 Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalForm from 'core_form/modalform';
import Templates from 'core/templates';
//import {getCalendarMonthData as getCalendarData} from 'core_calendar/repository';

const addEventList = () => {
    const typeSelector = document.querySelector('[data-table-region=qbank-table]');
    typeSelector.addEventListener('change', (e) => {
        if (e.target.dataset.filterfield === 'type' && e.target.value === 'date') {
            Templates.renderForPromise('qbank_viewcreator/filter_row_date', {})
            .then(({html}) => {
                e.target.parentNode.querySelector('[data-filterfield=join]').options.length = 0;
                Templates.appendNodeContents('[data-filterfield=join]', html, '');
                datePickerSetup();
                // Get calendar data for calendar renderer.
                // getCalendarData(year, month, courseId, categoryId, includeNavigation, mini)
                // .then((context) => {
                //     Templates.render('qbank_viewcreator/calendar', context)
                //     .done((html) => {
                //         Templates.replaceNodeContents('[data-filterregion=value]', html, '');
                //     });
                //     return;
                // }).catch(() => {
                //     return;
                // });
                return;
            }).catch(() => {
                return;
            });
        }
    });
};

const datePickerSetup = () => {
    const typeorselec = document.querySelectorAll('[data-filterregion=value]')[0];
    const jointype = document.querySelector('[data-filterfield=join]');
    typeorselec.addEventListener('click', e => {
        e.preventDefault();
        console.log('JOINTYPe: ', jointype);
        const element = e.target;
        const modalForm = new ModalForm({
            // Name of the class where form is defined (must extend \core_form\dynamic_form):
            formClass: 'qbank_viewcreator\\form\\datepicker_form',
            // Add as many arguments as you need, they will be passed to the form:
            args: {
                url: location.href,
                jointype: jointype
            },
            // Pass any configuration settings to the modal dialogue, for example, the title:
            modalConfig: {
                title: 'simple'
            },
            // DOM element that should get the focus after the modal dialogue is closed:
            returnFocus: element,
        });
        // Listen to events if you want to execute something on form submit.
        // Event detail will contain everything the process() function returned:
        modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e) => window.console.log(e.detail));
        // Show the form.
        modalForm.show();
    });
};

export const init = () => {
    addEventList();
};
