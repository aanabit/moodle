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
 * H5P content-type libraries settings page
 *
 * @package    core_h5p
 * @copyright  2019 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/h5p/classes/uploadlibraries_form.php');

require_login(null, false);
$context = context_system::instance();
require_capability('moodle/h5p:updatelibraries', $context);

$pagetitle = get_string('h5pmanage', 'core_h5p');
$url = new moodle_url("/h5p/libraries.php");
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title($pagetitle);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($pagetitle);

echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle);
echo $OUTPUT->box(get_string('librariesmanagerdescription', 'core_h5p'));

$form = new h5p_uploadlibraries_form();
if ($data = $form->get_data()) {
    require_sesskey();

    if (empty($data->h5ppackage)) {
        $error = get_string('required');
    } else {
        $draftitemid = file_get_submitted_draft_itemid('h5ppackage');

        file_prepare_draft_area($draftitemid, context_system::instance(), 'core_h5p', 'h5ppackagefilecheck', null,
            array('subdirs' => 0, 'maxfiles' => 1));

        // Get file from users draft area.
        $usercontext = context_user::instance($USER->id);
        $fs = get_file_storage();
        $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);

        if (count($files) < 1) {
            $error = get_string('required');
        } else {
            $file = reset($files);

            // Validate this H5P package.
            $h5pfactory = new \core_h5p\factory();
            // Because we are passing skipcontent = false to save_h5p function, the returning value is false for error,
            // null for saving package without creating the content.
            if (\core_h5p\helper::save_h5p($h5pfactory, $file, new stdClass(), false, true) === false) {
                $error = get_string('invalidpackage', 'core_h5p');
            }
        }
    }
    if (empty($error)) {
        echo $OUTPUT->notification(get_string('uploadsuccess', 'core_h5p'), 'success');
    } else {
        echo $OUTPUT->notification($error, 'error');
    }
}
$form->display();
echo $OUTPUT->footer();
