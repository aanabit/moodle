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
 * Content bank UI actions.
 *
 * @module     core_contentbank/views
 * @package    core_contentbank
 * @copyright  2020 Bas Brands <bas@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {debounce} from 'core/utils';
import selectors from 'core_contentbank/selectors';

/**
 * Set up the contentbank views.
 *
 * @method init
 */
export const init = () => {
    const contentBank = document.querySelector(selectors.elements.contentbank);

    registerListenerEvents(contentBank);
};


/**
 * Register contentbank related event listeners.
 *
 * @method registerListenerEvents
 * @param {HTMLElement} contentBank The DOM node of the content bank
 */
const registerListenerEvents = (contentBank) => {

    // The search.
    const fileArea = document.querySelector(selectors.elements.filearea);
    const searchInput = contentBank.querySelector(selectors.elements.searchinput);
    const clearSearchButton = contentBank.querySelector(selectors.elements.clearsearch);
    const searchIcon = contentBank.querySelector(selectors.elements.searchicon);
    const shownItems = fileArea.querySelectorAll(selectors.elements.listitem);

    // The search input is triggered.
    searchInput.addEventListener('input', debounce(() => {
        // Display the search results.
        if (searchInput.value !== '') {
            clearSearchButton.classList.remove('d-none');
            searchIcon.classList.add('d-none');
        } else {
            clearSearchButton.classList.add('d-none');
            searchIcon.classList.remove('d-none');
        }
        shownItems.forEach((listItem) => {
            if (listItem.getAttribute('data-file').toLowerCase().includes(searchInput.value.toLowerCase())) {
                listItem.classList.remove('d-none');
            } else {
                listItem.classList.add('d-none');
            }
        });
    }, 300));

    // Clear search results and the search input.
    clearSearchButton.addEventListener('click', () => {
        shownItems.forEach((listItem) => {
            listItem.classList.remove('d-none');
        });
        searchInput.value = "";
        searchInput.focus();
    });

    // The view buttons.
    const viewGrid = contentBank.querySelector(selectors.elements.viewgrid);
    const viewList = contentBank.querySelector(selectors.elements.viewlist);

    viewGrid.addEventListener('click', () => {
        contentBank.classList.remove('view-list');
        contentBank.classList.add('view-grid');
        viewGrid.classList.add('active');
        viewList.classList.remove('active');
    });

    viewList.addEventListener('click', () => {
        contentBank.classList.remove('view-grid');
        contentBank.classList.add('view-list');
        viewList.classList.add('active');
        viewGrid.classList.remove('active');
    });

    // Sort by name alphabetical
    const sortByName = contentBank.querySelector(selectors.elements.sortname);
    sortByName.addEventListener('click', () => {
        let ascending = true;
        if (sortByName.classList.contains('dir-none')) {
            sortByName.classList.remove('dir-none');
            sortByName.classList.add('dir-asc');
        } else if (sortByName.classList.contains('dir-asc')) {
            sortByName.classList.remove('dir-asc');
            sortByName.classList.add('dir-desc');
            ascending = false;
        } else if (sortByName.classList.contains('dir-desc')) {
            sortByName.classList.remove('dir-desc');
            sortByName.classList.add('dir-asc');
            ascending = true;
        }

        let sortList = [].slice.call(shownItems).sort(function (a, b) {
            if (ascending) {
                return a.getAttribute('data-file') > b.getAttribute('data-file') ? 1 : -1;
            } else {
                return a.getAttribute('data-file') < b.getAttribute('data-file') ? 1 : -1;
            }
        });
        sortList.forEach(function (listItem) {
            fileArea.appendChild(listItem);
        });
    });
};