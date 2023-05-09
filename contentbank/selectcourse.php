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
 * Generic content bank visualizer.
 *
 * @package   core_contentbank
 * @copyright  2023 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_contentbank\content;

require('../config.php');

require_login();

$id = required_param('id', PARAM_INT);

$record = $DB->get_record('contentbank_content', ['id' => $id], '*', MUST_EXIST);
$context = context::instance_by_id($record->contextid, MUST_EXIST);

$returnurl = new \moodle_url('/contentbank/index.php', ['contextid' => $context->id]);
$plugin = core_plugin_manager::instance()->get_plugin_info($record->contenttype);
if (!$plugin || !$plugin->is_enabled()) {
    throw new \moodle_exception('unsupported', 'core_contentbank', $returnurl);
}

$title = get_string('contentbank');
\core_contentbank\helper::get_page_ready($context, $title, true);
if ($PAGE->course) {
    require_login($PAGE->course->id);
}

$cb = new \core_contentbank\contentbank();
$content = $cb->get_content_from_id($record->id);
$contenttype = $content->get_content_type_instance();

if (!$contenttype->can_useincourse($content)) {
    $cburl = new \moodle_url('/contentbank/index.php', ['contextid' => $context->id, 'errormsg' => 'notavailable']);
    redirect($cburl);
}

if ($context->contextlevel == CONTEXT_COURSECAT) {
    $PAGE->set_primary_active_tab('home');
}

$PAGE->set_url(new \moodle_url('/contentbank/view.php', ['id' => $id]));
if ($context->id == \context_system::instance()->id) {
    $PAGE->set_context(context_course::instance($context->id));
} else {
    $PAGE->set_context($context);
}
$PAGE->navbar->add($record->name);
$title .= ": ".$record->name;
$PAGE->set_title($title);
$PAGE->set_pagetype('contentbank');
$PAGE->set_pagelayout('incourse');
$PAGE->set_secondary_active_tab('contentbank');

echo $OUTPUT->header();

//$selectcourses = new core_contentbank\output\selectcourse($contenttype, $content);
//echo $OUTPUT->render($selectcourses);

$form = new core_contentbank\form\selectcourse(
    $contenttype->get_useincourse_url($content),
    enrol_get_my_courses(['id', 'fullname', 'shortname'], null, 0, [], true)
);
$form->display();

echo $OUTPUT->footer();
