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
 * Cohort enrolment instances.
 *
 * @package    core_cohort
 * @copyright  2019 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../config.php');
require_once($CFG->dirroot.'/cohort/locallib.php');

$id = required_param('id', PARAM_INT);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$page = optional_param('page', 0, PARAM_INT);

require_login();

$cohort = $DB->get_record('cohort', array('id' => $id), '*', MUST_EXIST);
$context = context::instance_by_id($cohort->contextid, MUST_EXIST);

require_capability('moodle/cohort:view', $context);

$params = array('id' => $id, 'page' => $page);

$baseurl = new moodle_url('/cohort/instances.php', $params);

$PAGE->set_context($context);
$PAGE->set_url($baseurl);
$PAGE->set_pagelayout('admin');

if ($returnurl) {
    $returnurl = new moodle_url($returnurl);
} else {
    $returnurl = new moodle_url('/cohort/index.php', array('id' => $cohort->contextid));
}

$instances = cohort_get_enrolment_instances($id, $page, 25);

if ($context->contextlevel == CONTEXT_COURSECAT) {
    $category = $DB->get_record('course_categories', array('id' => $context->instanceid), '*', MUST_EXIST);
    navigation_node::override_active_url(new moodle_url('/cohort/index.php', array('contextid' => $cohort->contextid)));
} else {
    navigation_node::override_active_url(new moodle_url('/cohort/index.php', array()));
}
$PAGE->navbar->add(get_string('instances', 'cohort', $cohort->name));

$PAGE->set_title(get_string('assignedinstances', 'cohort'));
$PAGE->set_heading($COURSE->fullname);

echo $OUTPUT->header();

// Output pagination bar.
echo $OUTPUT->paging_bar($instances['totalinstances'], $page, 25, $baseurl);

$data = array();
$editcolumnisempty = true;
foreach ($instances['instances'] as $instance) {
    $coursecontext = context_course::instance($instance->courseid);
    $tmpl = new \core_enrol\output\instancename($instance);
    $line = array();
    if (has_capability('enrol/cohort:config', $coursecontext)) {
        $line[] = $OUTPUT->render_from_template('core/inplace_editable', $tmpl->export_for_template($OUTPUT));
    } else {
        $line[] = $instance->name;
    }
    $line[] = $instance->fullname;
    $line[] = $instance->rolename;
    $line[] = $instance->groupname;

    $buttons = array();
    if (has_capability('enrol/cohort:config', $coursecontext)) {
        $urlparams = array('id' => $instance->id, 'courseid' => $instance->courseid, 'type' => $instance->enrol);
        $buttons[] = html_writer::link(new moodle_url('/enrol/editinstance.php', $urlparams),
            $OUTPUT->pix_icon('i/edit', get_string('edit')),
            array('title' => get_string('edit')));
        $editcolumnisempty = false;
    }
    $line[] = implode(' ', $buttons);

    $data[] = $row = new html_table_row($line);
    if ($instance->status) {
        $row->attributes['class'] = 'dimmed_text';
    }
}
$table = new html_table();
$table->head  = array(get_string('custominstancename', 'enrol'),
    get_string('course'),
    get_string('role'),
    get_string('group'));
$table->colclasses = array('leftalign name', 'leftalign course', 'leftalign role', 'leftalign group');
if (!$editcolumnisempty) {
    $table->head[] = get_string('edit');
    $table->colclasses[] = 'centeralign action';
} else {
    // Remove last column from $data.
    foreach ($data as $row) {
        array_pop($row->cells);
    }
}
$table->id = 'instances';
$table->attributes['class'] = 'admintable generaltable';
$table->data  = $data;

echo html_writer::table($table);
echo html_writer::link($returnurl, get_string('backtocohorts', 'cohort'));
echo $OUTPUT->paging_bar($instances['totalinstances'], $page, 25, $baseurl);
echo $OUTPUT->footer();
