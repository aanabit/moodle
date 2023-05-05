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

require_login();

$id = required_param('id', PARAM_INT);
$course = optional_param('course', 0, PARAM_INT);
$record = $DB->get_record('contentbank_content', ['id' => $id], '*', MUST_EXIST);
$context = context::instance_by_id($record->contextid, MUST_EXIST);
if (!$course) {
    $course = $context->get_course_context()->instanceid;
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

if (!$contenttype->can_useincourse($content)) {
    $cburl = new \moodle_url('/contentbank/view.php', ['id' => $id, 'errormsg' => 'notavailable']);
    redirect($cburl);
}

if ($context->contextlevel != CONTEXT_COURSE) {
    $cburl = new \moodle_url('/contentbank/view.php', ['id' => $id, 'errormsg' => 'availableforcoursesonly']);
    redirect($cburl);
}

// Hemen
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

// Grab the course context.
$coursecontext = \context_course::instance($course);

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

// Get the backup file.
$file = reset($files);

// Get a backup temp directory name and create it.
$tempdir = \restore_controller::get_tempdir_name($context->id, $USER->id);
$fulltempdir = make_backup_temp_directory($tempdir);

// Extract the backup to tempdir.
$fb = get_file_packer('application/vnd.moodle.backup');
$fb->extract_to_pathname($file, $fulltempdir);

// As far as recycle bin is using MODE_AUTOMATED, it observes the General restore settings.
// For recycle bin we want to ensure that backup files are always restore the users and groups information.
// In order to achieve that, we hack the setting here via $CFG->forced_plugin_settings,
// so it won't interfere other operations.
// See MDL-65218 and MDL-35773 for more information.
// This hack will be removed once recycle bin switches to use its own backup mode, with
// own preferences and 100% separate from MOODLE_AUTOMATED.
// TODO: Remove this as part of MDL-65228.
$CFG->forced_plugin_settings['restore'] = ['restore_general_users' => 1, 'restore_general_groups' => 1];

// Define the import.
$controller = new \restore_controller(
    $tempdir,
    $course,
    \backup::INTERACTIVE_NO,
    \backup::MODE_GENERAL,
    $USER->id,
    \backup::TARGET_EXISTING_ADDING
);

// Prechecks.
if (!$controller->execute_precheck()) {
    $results = $controller->get_precheck_results();

    // If errors are found then delete the file we created.
    if (!empty($results['errors'])) {
        fulldelete($fulltempdir);

        $backurl = new moodle_url('/contentbank/view.php', ['id' => $id, 'errormsg' => 'notvalidpackage']);
        redirect($backurl);
    }
}

// Run the import.
$controller->execute_plan();

// We don't need the forced setting anymore, hence unsetting it.
// TODO: Remove this as part of MDL-65228.
unset($CFG->forced_plugin_settings['restore']);

// Have finished with the controller, let's destroy it, freeing mem and resources.
$controller->destroy();

// Fire event.
//$event = \tool_recyclebin\event\course_bin_item_restored::create(array(
//    'objectid' => $item->id,
//    'context' => $context
//));
//$event->add_record_snapshot('tool_recyclebin_course', $item);
//$event->trigger();

$courseurl = new moodle_url('/course/view.php', ['id' => $course]);
redirect($courseurl);