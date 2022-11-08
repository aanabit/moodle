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
 * Javascript module for deleting a database as a preset.
 *
 * @module      mod_data/importmappingdialogue
 * @copyright   2022 Amaia Anabitarte <amaia@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Notification from 'core/notification';
import Ajax from 'core/ajax';
import Url from 'core/url';
import Templates from 'core/templates';
import Modal from 'core/modal';

const selectors = {
    selectPresetButton: 'input[name="selectpreset"]',
    importPresetButton: 'button[data-action="save"]',
    cmInput: 'input[name="cmid"]',
    importFile: 'input[name="importfile"]'
};

/**
 * Initialize module
 */
export const init = () => {
    registerEventListeners();
};

/**
 * Register events for user preset button.
 */
const registerEventListeners = () => {
    document.addEventListener('click', (event) => {
        const usepreset = event.target.closest(selectors.selectPresetButton);
        if (usepreset) {
            event.preventDefault();
            mappingusepreset(usepreset);
        }
        const importpreset = event.target.closest(selectors.importPresetButton);
        if (importpreset) {
            event.preventDefault();
            mappingimportpreset();
        }
    });
};

/**
 * Show the confirmation modal for uploading a preset.
 *
 * @param {HTMLElement} usepreset the preset to import.
 */
const mappingusepreset = (usepreset) => {
    const presetName = usepreset.getAttribute('data-presetname');
    const dataId = usepreset.getAttribute('data-id');

    mappingdialogue(presetName, dataId);
};

/**
 * Show the confirmation modal for using a preset.
 *
 */
const mappingimportpreset = () => {
    let dataId, presetName;

    const cmInputElem = event.target.closest(selectors.cmInput);
    if (cmInputElem) {
        dataId = cmInputElem.getAttribute('value');
    }
    const fileElem = event.target.closest(selectors.importFile);
    if (fileElem) {
        presetName = fileElem.getAttribute('value');
    }

alert(dataId);
alert(presetName);
    mappingdialogue(presetName, dataId);
};

/**
 * Show the confirmation modal to map presets.
 *
 * @param {string} presetName The preset name to delete.
 * @param {int} dataId The id of the current database activity.
 */
const mappingdialogue = (presetName, dataId) => {
    showMappingDialogue(dataId, presetName).then((result) => {
        if (result.needsmapping) {
            const cancelButton = Url.relativeUrl(
                   'mod/data/preset.php',
                   {
                       d: dataId,
                   },
                   false
               );
            result['cancel'] = cancelButton;
            const mapButton = Url.relativeUrl(
                   'mod/data/field.php',
                   {
                       d: dataId,
                       fullname: presetName,
                       mode: 'usepreset',
                       action: 'select',
                   },
                   false
               );
            result['mapfieldsbutton'] = mapButton;
            const applyButton = Url.relativeUrl(
                   'mod/data/field.php',
                   {
                       d: dataId,
                       fullname: presetName,
                       mode: 'usepreset',
                       action: 'notmapping'
                   },
                   false
               );
            result['applybutton'] = applyButton;
            let modalPromise = Templates.render('mod_data/fields_mapping_modal', result);
            modalPromise.then(function(html) {
                return new Modal(html);
            }).fail(Notification.exception)
                .then((modal) => {
                    modal.show();
                    return modal;
                }).fail(Notification.exception);
                return result;
        } else {
            window.location.href = Url.relativeUrl(
                'mod/data/field.php',
                {
                    d: dataId,
                    mode: 'usepreset',
                    fullname: presetName,
                },
                false
            );
        }
    });
};

/**
 * Check whether we should show the mapping dialogue or not.
 *
 * @param {int} dataId The id of the current database activity.
 * @param {string} presetName The preset name to delete.
 * @return {promise} Resolved with the result and warnings of deleting a preset.
 */
async function showMappingDialogue(dataId, presetName) {
    var request = {
        methodname: 'mod_data_get_mapping_information',
        args: {
            dataid: dataId,
            import: presetName,
        }
    };
    return Ajax.call([request])[0];
}
