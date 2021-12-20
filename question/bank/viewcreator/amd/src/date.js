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

import Templates from 'core/templates';
import {getCalendarMonthData as getCalendarData} from 'core_calendar/repository';

const addEventList = () => {
    const typeSelector = document.querySelector('[data-table-region=qbank-table]');
    typeSelector.addEventListener('change', (e) => {
        if (e.target.dataset.filterfield === 'type' && e.target.value === 'date') {
            Templates.renderForPromise('qbank_viewcreator/filter_row_date', {})
            .then(({html}) => {
                e.target.parentNode.querySelector('[data-filterfield=join]').options.length = 0;
                Templates.appendNodeContents('[data-filterfield=join]', html, '');
                return;
            }).catch(() => {
                return;
            });
        }
    });
};

export const init = (year, month, courseId, categoryId, includeNavigation, mini) => {
    getCalendarData(year, month, courseId, categoryId, includeNavigation, mini)
    .then((data) => {
        console.log(data);
        return;
    }).catch(() => {
        return;
    });

    addEventList();
};
