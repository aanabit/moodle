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

/*
 * @package    atto_h5p
 * @copyright  2019 Bas Brands  <bas@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module moodle-atto_h5p-button
 */

/**
 * Atto h5p content tool.
 *
 * @namespace M.atto_h5p
 * @class Button
 * @extends M.editor_atto.EditorPlugin
 */

var CSS = {
        INPUTALT: 'atto_h5p_altentry',
        INPUTSUBMIT: 'atto_h5p_urlentrysubmit',
        INPUTHVPFILE: 'atto_h5p_file',
        URLWARNING: 'atto_h5p_warning'
    },
    SELECTORS = {
        INPUTHVPFILE: '.' + CSS.INPUTHVPFILE
    },

    COMPONENTNAME = 'atto_h5p',

    TEMPLATE = '' +
            '<form class="atto_form">' +

                // Add the repository browser button.
                '<div class="mb-1">' +
                    '<label for="{{elementid}}_{{CSS.INPUTHVPFILE}}">{{get_string "enterurl" component}}</label>' +
                    '<div style="display:none" role="alert" class="alert alert-warning mb-1 {{CSS.URLWARNING}}">' +
                        '{{get_string "invalidh5purl" component}}' +
                    '</div>' +
                    '<input class="form-control fullwidth {{CSS.INPUTHVPFILE}}" type="url" ' +
                    'id="{{elementid}}_{{CSS.INPUTHVPFILE}}" size="32"/>' +
                '</div>' +

                // Add the submit button and close the form.
                '<button class="btn btn-secondary {{CSS.INPUTSUBMIT}}" type="submit">' + '' +
                    '{{get_string "saveh5p" component}}</button>' +
                '</div>' +
            '</form>',

        H5PTEMPLATE = '' +
            '<iframe src="{{url}}/embed" width="1088" height="637" frameborder="0"' +
                'allowfullscreen="{{allowfullscreen}}" allowmedia="{{allowmedia}}"></iframe>' +
            '<script src="' + M.cfg.wwwroot + '/lib/h5p/js/h5p-resizer.js" charset="UTF-8"></script>';

Y.namespace('M.atto_h5p').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {
    /**
     * A reference to the current selection at the time that the dialogue
     * was opened.
     *
     * @property _currentSelection
     * @type Range
     * @private
     */
    _currentSelection: null,

    /**
     * A reference to the currently open form.
     *
     * @param _form
     * @type Node
     * @private
     */
    _form: null,

    initializer: function() {
        this.addButton({
            icon: 'e/insert_h5p',
            callback: this._displayDialogue,
            tags: 'h5p',
            tagMatchRequiresAll: false
        });
    },

    /**
     * Display the h5p editing tool.
     *
     * @method _displayDialogue
     * @private
     */
    _displayDialogue: function() {
        // Store the current selection.
        this._currentSelection = this.get('host').getSelection();
        if (this._currentSelection === false) {
            return;
        }
        var dialogue = this.getDialogue({
            headerContent: M.util.get_string('h5pproperties', COMPONENTNAME),
            width: 'auto',
            focusAfterHide: true,
            focusOnShowSelector: SELECTORS.INPUTHVPFILE
        });

        // Set the dialogue content, and then show the dialogue.
        dialogue.set('bodyContent', this._getDialogueContent())
                .show();
    },

    /**
     * Return the dialogue content for the tool, attaching any required
     * events.
     *
     * @method _getDialogueContent
     * @return {Node} The content to place in the dialogue.
     * @private
     */
    _getDialogueContent: function() {
        var template = Y.Handlebars.compile(TEMPLATE),
            content = Y.Node.create(template({
                elementid: this.get('host').get('elementid'),
                CSS: CSS,
                component: COMPONENTNAME
            }));

        this._form = content;

        // Configure the view of the current h5p.
        this._form.one('.' + CSS.INPUTSUBMIT).on('click', this._setHvp, this);

        return content;
    },

    /**
     * Update the h5p in the contenteditable.
     *
     * @method _setHvp
     * @param {EventFacade} e
     * @private
     */
    _setHvp: function(e) {
        var form = this._form,
            url = form.one('.' + CSS.INPUTHVPFILE).get('value'),
            h5phtml,
            host = this.get('host');

        e.preventDefault();

        // Check if there are any issues.
        if (this._updateWarning()) {
            return;
        }

        // Focus on the editor in preparation for inserting the h5p.
        host.focus();
        if (url !== '') {

            host.setSelection(this._currentSelection);

            var template = Y.Handlebars.compile(H5PTEMPLATE);
            h5phtml = template({
                url: url,
                allowfullscreen: 'allowfullscreen',
                allowmedia: 'geolocation *; microphone *; camera *; midi *; encrypted-media *'
            });

            this.get('host').insertContentAtFocusPoint(h5phtml);

            this.markUpdated();
        }

        this.getDialogue({
            focusAfterHide: null
        }).hide();

    },

    /**
     * Check if this could be a h5p URL.
     *
     * @method _updateWarning
     * @return {boolean} whether a warning should be displayed.
     * @private
     */
    _validURL: function (str) {
        var pattern = new RegExp('^https:\\/\\/([^.\\s]+\\.+h5p\\.com\\/content|h5p\\.org\\/h5p\\/embed)\\/\\d+');
        return !!pattern.test(str);
    },

    /**
     * Update the url warning.
     *
     * @method _updateWarning
     * @return {boolean} whether a warning should be displayed.
     * @private
     */
    _updateWarning: function() {
        Y.log('update warning');
        var form = this._form,
            state = true,
            url = form.one('.' + CSS.INPUTHVPFILE).get('value');
        if (this._validURL(url)) {
            form.one('.' + CSS.URLWARNING).setStyle('display', 'none');
            state = false;
        } else {
            form.one('.' + CSS.URLWARNING).setStyle('display', 'block');
            state = true;
        }
        return state;
    }
});
