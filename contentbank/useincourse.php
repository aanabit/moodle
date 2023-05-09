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
 * Select the content bank to reuse in the course.
 *
 * @package    core_contentbank
 * @copyright  2023 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../config.php');

require_login();

$contextid = optional_param('contextid', \context_system::instance()->id, PARAM_INT);
$search = optional_param('search', '', PARAM_CLEAN);
$context = context::instance_by_id($contextid, MUST_EXIST);
$courseid = required_param('courseid', PARAM_INT);

$cb = new \core_contentbank\contentbank();
if (!$cb->is_context_allowed($context)) {
    throw new \moodle_exception('contextnotallowed', 'core_contentbank');
}

require_capability('moodle/contentbank:access', $context);

$statusmsg = optional_param('statusmsg', '', PARAM_ALPHANUMEXT);
$errormsg = optional_param('errormsg', '', PARAM_ALPHANUMEXT);

$title = get_string('contentbank');
\core_contentbank\helper::get_page_ready($context, $title);
if ($PAGE->course) {
    require_login($PAGE->course->id);
}
$PAGE->set_url('/contentbank/useincourse.php', ['contextid' => $contextid, 'courseid' => $courseid]);
if ($contextid == \context_system::instance()->id) {
    $PAGE->set_context(context_course::instance($contextid));
} else {
    $PAGE->set_context($context);
}

if ($context->contextlevel == CONTEXT_COURSECAT) {
    $PAGE->set_primary_active_tab('home');
}

$PAGE->set_title($title);
$PAGE->add_body_class('limitedwidth');
$PAGE->set_pagetype('contentbank');
$PAGE->set_secondary_active_tab('contentbank');

// Get all contents managed by active plugins where the user has permission to render them.
$contenttypes = [];
$enabledcontenttypes = $cb->get_contenttypes_with_capability_feature(core_contentbank\contenttype::CAN_USEINCOURSE);
foreach ($enabledcontenttypes as $contenttypename) {
    $contenttypeclass = "\\contenttype_$contenttypename\\contenttype";
    $contenttype = new $contenttypeclass($context);
    if ($contenttype->can_access()) {
        $contenttypes[] = $contenttypename;
    }
}

$foldercontents = $cb->search_contents($search, $contextid, $contenttypes);

echo $OUTPUT->header();
echo $OUTPUT->heading($title, 2);
echo $OUTPUT->box_start('generalbox');

// Render the contentbank contents.
$folder = new \core_contentbank\output\bankcontent($foldercontents, [], $context, $cb, $courseid);
echo $OUTPUT->render($folder);

echo $OUTPUT->box_end();
echo $OUTPUT->footer();
