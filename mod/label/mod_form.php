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

/**
 * Add label form
 *
 * @package mod_label
 * @copyright  2006 Jamie Pratt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_label_mod_form extends moodleform_mod {

    function definition() {
        global $PAGE;

        $PAGE->force_settings_menu();

        $mform = $this->_form;

        $mform->addElement('header', 'generalhdr', get_string('general'));

        $mform->addElement('hidden', 'hastitle', 0);
        $mform->setType('hastitle', PARAM_BOOL);

        // Adding the standard "name" field.
        $mform->addElement('text', 'notrequiredname', get_string('name'), ['size' => '64']);
        $mform->setType('notrequiredname', PARAM_TEXT);
        if (!empty($CFG->formatstringstriptags)) {
        } else {
            $mform->setType('notrequiredname', PARAM_CLEANHTML);
        }
        $mform->addRule('notrequiredname', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements(get_string('labeltext', 'label'));

        // Label does not add "Show description" checkbox meaning that 'intro' is always shown on the course page.
        $mform->addElement('hidden', 'showdescription', 1);
        $mform->setType('showdescription', PARAM_INT);

        $this->standard_coursemodule_elements();

//-------------------------------------------------------------------------------
// buttons
        $this->add_action_buttons(true, false, null);

    }

    /**
     * Move name to notrequiredname when hastitle is true.
     *
     * @param array $default_values passed by reference
     */
    function data_preprocessing(&$default_values){
        if ($default_values['hastitle'] && array_key_exists('name', $default_values)) {
            $default_values['notrequiredname'] = $default_values['name'];
        }
    }

    /**
     * Allows modules to modify the data returned by form get_data().
     * This method is also called in the bulk activity completion form.
     *
     * @param stdClass $data passed by reference
     */
    public function data_postprocessing($data) {
        parent::data_postprocessing($data);

        $data->hastitle = !empty($data->notrequiredname);
        if ($data->hastitle) {
            $data->name = $data->notrequiredname;
        }
    }
}
