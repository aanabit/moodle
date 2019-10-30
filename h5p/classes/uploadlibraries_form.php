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
 * Upload H5P content-types form
 *
 * @package    core_h5p
 * @copyright  2019 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir.'/formslib.php';

/**
 * Upload a zip or h5p content to update libraries.
 *
 * @copyright  2019 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class h5p_uploadlibraries_form extends moodleform {

    function definition () {
        $mform = $this->_form;

        $mform->addElement('header', 'settingsheader', get_string('uploadlibraries', 'core_h5p'));

        $filemanageroptions = array();
        $filemanageroptions['accepted_types'] = array('.h5p', '.zip');
        $filemanageroptions['maxbytes'] = 0;
        $filemanageroptions['maxfiles'] = 1;
        $filemanageroptions['subdirs'] = 0;

        $mform->addElement('filemanager', 'h5ppackage', get_string('h5ppackage', 'core_h5p'),  null, $filemanageroptions);
        $mform->addHelpButton('h5ppackage', 'h5ppackage', 'core_h5p');
        $mform->addRule('h5ppackage', null, 'required');

        $this->add_action_buttons(false, get_string('uploadlibraries', 'core_h5p'));
    }

    public function validation($data, $files) {
        global $CFG, $USER;

        $errors = parent::validation($data, $files);

        if (empty($data['h5ppackage'])) {
            $errors['h5ppackage'] = get_string('required');
        } else {
            $draftitemid = file_get_submitted_draft_itemid('h5ppackage');

            file_prepare_draft_area($draftitemid, context_system::instance(), 'core_h5p', 'h5ppackagefilecheck', null,
                array('subdirs' => 0, 'maxfiles' => 1));

            // Get file from users draft area.
            $usercontext = context_user::instance($USER->id);
            $fs = get_file_storage();
            $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);

            if (count($files) < 1) {
                $errors['h5ppackage'] = get_string('required');
                return $errors;
            }
            $file = reset($files);

            // Validate this H5P package.
            $h5pfactory = new \core_h5p\factory();
            if (!\core_h5p\helper::save_h5p($h5pfactory, $file, new stdClass(), false, true)) {
                $errors['h5ppackage'] = get_string('invalidpackage', 'core_h5p');
            }
        }

        return $errors;
    }
}
