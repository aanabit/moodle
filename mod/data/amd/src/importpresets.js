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
 * Javascript module for importing presets.
 *
 * @module     mod_data/importpreset
 * @copyright  2022 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalForm from 'core_form/modalform';
//import Notification from 'core/notification';
import Url from 'core/url';
import {get_string as getString} from 'core/str';
import {mappingdialogue} from 'mod_data/importmappingdialogue';

const selectors = {
    importPresetButton: '[data-action="importpresets"]',
};

/**
 * Initialize module
 */
export const init = () => {
    document.addEventListener('click', (event) => {
        const importPresetButton = event.target.closest(selectors.importPresetButton);
        const cmId = importPresetButton.dataset.cmid;

        if (!importPresetButton) {
            return;
        }

        event.preventDefault();
        const modalForm = new ModalForm({
            modalConfig: {
                title: getString('importpreset', 'mod_data'),
            },
            formClass: 'mod_data\\form\\import_presets',
            args: {cmid: cmId},
            saveButtonText: getString('importandapply', 'mod_data'),
        });

        modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, event => {
            if (event.detail && event.detail.data) {
                if (event.detail.data.needsmapping) {
                    mappingdialogue('', cmId);
                } else {
                    const redirection = Url.relativeUrl(
                        'mod/data/preset.php',
                       {
                           id: cmId,
                           fullname: '',
                           mode: 'finishimport',
                           action: 'notmapping'
                       },
                       false
                    );
                    window.location.assign(redirection);
                }
            }
        });
        modalForm.show();
    });
};
