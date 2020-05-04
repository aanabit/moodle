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
 * Define all of the selectors we will be using for the content bank.
 *
 * @module     core_contentbank/selectors
 * @package    core_contentbank
 * @copyright  2020 Bas Brands <bas@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


export default {
    elements: {
        contentbank: '[data-region="contentbank"]',
        filearea: '[data-region="filearea"]',
        searchinput: '[data-region="search-input"]',
        listitem: '.cb-listitem',
        searchicon: '.input-group-append .search-icon',
        clearsearch: '.input-group-append .clear',
        viewgrid: '[data-action="viewgrid"]',
        viewlist: '[data-action="viewlist"]',
        sortname: '[data-action="sortname"]'
    },
};
