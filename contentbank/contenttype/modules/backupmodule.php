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
//
///**
// * This script is used to configure and execute the backup proccess.
// *
// * @package    core
// * @subpackage backup
// * @copyright  Moodle
// * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
// */
//
//define('NO_OUTPUT_BUFFERING', true);

require_once('../../../config.php');

require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/moodle2/backup_plan_builder.class.php');

$courseid = required_param('courseid', PARAM_INT);
$sectionid = optional_param('section', null, PARAM_INT);
$cmid = required_param('cmid', PARAM_INT);

$modinfo = get_fast_modinfo($courseid);
if (!isset($modinfo->cms[$cmid])) {
    return; // Can't continue without the module information.
}
$cminfo = $modinfo->cms[$cmid];
$context = context_course::instance($courseid);

// Check backup/restore support.
if (!plugin_supports('mod', $cminfo->modname , FEATURE_BACKUP_MOODLE2)) {
    return;
}
$CFG->forced_plugin_settings['backup'] = ['backup_auto_storage' => 0, 'backup_auto_files' => 1];

// Backup the activity.
$controller = new \backup_controller(
    \backup::TYPE_1ACTIVITY,
    $cmid,
    \backup::FORMAT_MOODLE,
    \backup::INTERACTIVE_NO,
    \backup::MODE_GENERAL,
    $USER->id
);

// When "backup_auto_activities" setting is disabled, activities can't be restored from recycle bin.
$plan = $controller->get_plan();
$activitiessettings = $plan->get_setting('activities');
$settingsvalue = $activitiessettings->get_value();
if (empty($settingsvalue)) {
    $controller->destroy();
    return;
}

$controller->execute_plan();

// We don't need the forced setting anymore, hence unsetting it.
unset($CFG->forced_plugin_settings['backup']);

// Grab the result.
$result = $controller->get_results();
if (!isset($result['backup_destination'])) {
    throw new \moodle_exception('Failed to backup activity prior to deletion.');
}

// Have finished with the controller, let's destroy it, freeing mem and resources.
$controller->destroy();

// Grab the filename.
$file = $result['backup_destination'];
if (!$file->get_contenthash()) {
    throw new \moodle_exception('Failed to backup activity prior to deletion (invalid file).');
}

// Record the activity, get an ID.
$content = new \stdClass();
$content->name = $cminfo->name;
$content->contenttype= 'contenttype_modules';
$content->contextid = $context->id;
$content->timecreated = time();
$content->usercreated = $USER->id;
$configdata = [
    'modulename' => $cminfo->modname,
    'moduleid' => $cminfo->instance,
    'title' => $cminfo->name,
];
$content->configdata = json_encode($configdata);
$cbcontent = $DB->insert_record('contentbank_content', $content);

// Create the location we want to copy this file to.
$filerecord = array(
    'contextid' => $content->contextid,
    'component' => 'contentbank',
    'filearea' => 'public',
    'itemid' => $cbcontent,
    'timemodified' => time()
);

// Move the file to content bank.
$fs = get_file_storage();
if (!$fs->create_file_from_storedfile($filerecord, $file)) {
    // Failed, cleanup first.
    $DB->delete_records('contentbank_content', ['id' => $cbcontent]);

    throw new \moodle_exception("Failed to copy backup file to content bank.");
}
$file->delete();

$url = new moodle_url('/course/view.php', ['id' => $courseid]);

redirect($url, get_string('activitysaved', 'contenttype_modules'), null, \core\output\notification::NOTIFY_SUCCESS);
