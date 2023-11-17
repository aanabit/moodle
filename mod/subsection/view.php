<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Prints an instance of mod_subsection.
 *
 * @package     mod_subsection
 * @copyright   2023 Amaia Anabitarte <amaia@moodle.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_subsection\manager;

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$s = optional_param('s', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('subsection', $id, 0, false, MUST_EXIST);
    $manager = manager::create_from_coursemodule($cm);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $moduleinstance = $manager->get_instance();
} else {
    $moduleinstance = $DB->get_record('subsection', ['id' => $s], '*', MUST_EXIST);
    $manager = manager::create_from_data_record($moduleinstance);
    $course = $DB->get_record('course', ['id' => $moduleinstance->course], '*', MUST_EXIST);
    $cm = $manager->get_coursemodule();
}

require_login($course, true, $cm);

$modulecontext = $manager->get_context();
$manager->set_module_viewed($course);

$PAGE->set_url('/mod/subsection/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$renderer = $manager->get_renderer();

echo $OUTPUT->header();
echo $OUTPUT->box($moduleinstance->name, "generalbox center clearfix");
echo $OUTPUT->footer();
