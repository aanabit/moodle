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
 * Generic content bank to create new instances.
 *
 * @package   core_contentbank
 * @copyright  2023 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->dirroot .'/course/modlib.php');

require_login();

$id = required_param('id', PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$record = $DB->get_record('contentbank_content', ['id' => $id], '*', MUST_EXIST);
$context = context::instance_by_id($record->contextid, MUST_EXIST);
if (!$courseid) {
    $courseid = $context->get_course_context()->instanceid;
}
require_capability('moodle/contentbank:access', $context);

$returnurl = new \moodle_url('/contentbank/index.php', ['contextid' => $context->id]);

$plugin = core_plugin_manager::instance()->get_plugin_info($record->contenttype);
if (!$plugin || !$plugin->is_enabled()) {
    throw new \moodle_exception('unsupported', 'core_contentbank', $returnurl);
}

$cb = new \core_contentbank\contentbank();
$content = $cb->get_content_from_id($record->id);
$contenttype = $content->get_content_type_instance();

if (!$contenttype->can_useincourse()) {
    $cburl = new \moodle_url('/contentbank/view.php', ['id' => $id, 'errormsg' => 'notavailable']);
    redirect($cburl);
}

if ($context->contextlevel != CONTEXT_COURSE) {
    $cburl = new \moodle_url('/contentbank/view.php', ['id' => $id, 'errormsg' => 'availableforcoursesonly']);
    redirect($cburl);
}

// Grab the course context.
$coursecontext = \context_course::instance($courseid);

// Get the files..
$fs = get_file_storage();
$files = $fs->get_area_files($context->id, 'contentbank', 'public', $id,
    'itemid, filepath, filename', false);

if (empty($files)) {
    throw new \moodle_exception('Invalid content bank item!');
}

if (count($files) > 1) {
    throw new \moodle_exception('Too many files found!');
}

$file = reset($files);

// Everything seems fine. Create the course_module.
$course = get_course($courseid);
//$courseformat = course_get_format($course);
//$section = $courseformat->get_section(0);
list($module, $context, $cw, $cm, $data) = prepare_new_moduleinfo_data($course, 'h5pactivity', 447);
$moduleid = add_course_module($data);

// Create a default h5pactivity object to pass to h5pactivity_add_instance()!
$h5p = get_config('h5pactivity');
$h5p->intro = '';
$h5p->introformat = FORMAT_HTML;
$h5p->course = $courseid;
$h5p->coursemodule = $moduleid;
$h5p->grade = $CFG->gradepointdefault;

// Add some special handling for the H5P options checkboxes.
$factory = new \core_h5p\factory();
$core = $factory->get_core();
$config = \core_h5p\helper::decode_display_options($core);
$h5p->displayoptions = \core_h5p\helper::get_display_options($core, $config);

$h5p->cmidnumber = '';
$h5p->name = $content->get_name();
$h5p->reference = $file->get_filename();

h5pactivity_add_instance($h5p, null);

$courseurl = new moodle_url('/course/view.php', ['id' => $courseid]);
redirect($courseurl);