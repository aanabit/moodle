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
 * Reset course indentation
 *
 * @copyright 2023 Amaia Anabitarte <amaia@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');


$PAGE->set_url('/admin/course/resetindentation.php');
$PAGE->set_context(context_system::instance());

//require_admin();

$strtitle = get_string('resetindentation', 'admin');

$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);

navigation_node::override_active_url(new moodle_url('/admin/settings.php', ['section' => 'coursecontact']));

echo $OUTPUT->header();

echo html_writer::div(get_string('confirmtoresetcourse', 'admin'));
echo $OUTPUT->single_button(new moodle_url('/admin/course/resetindentation.php', ['confirm' => 1, 'sesskey' => sesskey()]),
    get_string('resetindentation', 'admin'));
echo $OUTPUT->single_button(new moodle_url('/admin/settings', ['section' => 'coursecontact']),
    get_string('cancel'));

echo $OUTPUT->footer();
