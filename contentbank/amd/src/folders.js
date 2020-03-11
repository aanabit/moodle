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
 * Module to manage content bank folder actions, such as create, delete or rename.
 *
 * @module     core_contentbank/folders
 * @package    core_contentbank
 * @copyright  2020 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
        'jquery',
        'core/ajax',
        'core/notification',
        'core/str',
        'core/templates',
        'core/url',
        'core/modal_factory',
        'core/modal_events'],
    function($,
             Ajax,
             Notification,
             Str,
             Templates,
             Url,
             ModalFactory,
             ModalEvents
    ) {

        /**
         * List of action selectors.
         *
         * @type {{CREATE_FOLDER: string}, {EDIT_FOLDER: string}}
         */
        var ACTIONS = {
            CREATE_FOLDER: '[data-action="createfolder"]',
            EDIT_FOLDER: '[data-action="editfolder"]',
        };

        /**
         * Folders class.
         */
        var Folders = function() {
            this.registerEvents();
        };

        /**
         * Register event listeners.
         */
        Folders.prototype.registerEvents = function() {
            $(ACTIONS.CREATE_FOLDER).click(function(e) {
                e.preventDefault();

                var parentid = $(this).data('parentid');
                var strings = [
                    {
                        key: 'addnewfolder',
                        component: 'core_contentbank'
                    },
                    {
                        key: 'add',
                        component: 'core_contentbank'
                    },
                ];
                var addButtonText = '';
                Str.get_strings(strings).then(function(langStrings) {
                    var modalTitle = langStrings[0];
                    addButtonText = langStrings[1];
                    return ModalFactory.create({
                        title: modalTitle,
                        body: Templates.render('core_contentbank/newfolder', {'parentid': parentid}),
                        type: ModalFactory.types.SAVE_CANCEL
                    });
                }).then(function(modal) {
                    modal.setSaveButtonText(addButtonText);
                    modal.getRoot().on(ModalEvents.save, function(e) {
                        // The action is now confirmed, sending an action for it.
                        var name = $(e.currentTarget).find('#newname').val();
                        return createFolder(name, parentid);
                    });

                    // Handle hidden event.
                    modal.getRoot().on(ModalEvents.hidden, function() {
                        // Destroy when hidden.
                        modal.destroy();
                    });

                    // Show the modal.
                    modal.show();
                    return;
                }).catch(Notification.exception);
            });

            $(ACTIONS.EDIT_FOLDER).click(function(e) {
                e.preventDefault();

                var parentid = $(this).data('parentid');
                var folderid = $(this).data('folderid');
                var foldername = $(this).data('foldername');
                var timecreated = $(this).data('timecreated');
                var timemodified = $(this).data('timemodified');

                parentid = 0;
                folderid = 58;
                timecreated = 10000;
                timemodified = 20000;

                var strings = [
                    {
                        key: 'editfolder',
                        component: 'core_contentbank',
                        param: {
                            foldername: foldername
                        }
                    }
                ];
                Str.get_strings(strings).then(function(langStrings) {
                    var modalTitle = langStrings[0];
                    return ModalFactory.create({
                        title: modalTitle,
                        body: Templates.render('core_contentbank/editfolder', {
                            'parentid': parentid,
                            'folderid': folderid,
                            'foldername': foldername,
                            'timecreated': timecreated,
                            'timemodified': timemodified
                        }),
                        type: ModalFactory.types.SAVE_CANCEL
                    });
                }).then(function(modal) {
                    modal.getRoot().on(ModalEvents.save, function(e) {
                        // The action is now confirmed, sending an action for it.
                        var newname = $(e.currentTarget).find('#foldername').val();
                        return renameFolder(folderid, newname, parentid);
                    });

                    // Handle hidden event.
                    modal.getRoot().on(ModalEvents.hidden, function() {
                        // Destroy when hidden.
                        modal.destroy();
                    });

                    // Show the modal.
                    modal.show();
                    return;
                }).catch(Notification.exception);
            });
        };

        /**
         * Create folder in the content bank.
         *
         * @param {string} name The name for the new folder.
         * @param {int} parentid The id of the parent folder.
         */
        function createFolder(name, parentid) {
            var request = {
                methodname: 'core_contentbank_create_folder',
                args: {
                    name: name,
                    parentid: parentid
                }
            };

            var requestType = 'success';
            Ajax.call([request])[0].then(function(data) {
                if (data) {
                    return Str.get_string('foldercreated', 'core_contentbank');
                }
                requestType = 'error';
                return Str.get_string('duplicatedfoldername', 'core_contentbank');

            }).then(function(message) {
                var params = null;
                if (requestType == 'success') {
                    params = {
                        parent: parentid
                    };
                } else {
                    params = {
                        parent: parentid,
                        errormsg: message
                    };
                }
                // Redirect to the main content bank page and display error message if exists.
                window.location.href = Url.relativeUrl('contentbank/index.php', params, false);
                return;
            }).catch(Notification.exception);
        }

        /**
         * Rename folder in the content bank.
         *
         * @param {int} folderid The id of the folder to be renamed.
         * @param {string} name The new name for the folder.
         * @param {int} parentid The id of the parent folder for return URL.
         */
        function renameFolder(folderid, name, parentid) {
            var request = {
                methodname: 'core_contentbank_rename_folder',
                args: {
                    folderid: folderid,
                    name: name
                }
            };

            var requestType = 'success';
            Ajax.call([request])[0].then(function(data) {
                if (data) {
                    return Str.get_string('folderrenamed', 'core_contentbank');
                }
                requestType = 'error';
                return Str.get_string('duplicatedfoldername', 'core_contentbank');

            }).then(function(message) {
                var params = null;
                if (requestType == 'success') {
                    params = {
                        parent: parentid
                    };
                } else {
                    params = {
                        parent: parentid,
                        errormsg: message
                    };
                }
                // Redirect to the main content bank page and display error message if exists.
                window.location.href = Url.relativeUrl('contentbank/index.php', params, false);
                return;
            }).catch(Notification.exception);
        }

        return /** @alias module:core_contentbank/folders */ {
            /**
             * Initialise the unified user filter.
             *
             * @method init
             * @return {Folders}
             */
            'init': function() {
                return new Folders();
            }
        };
    });