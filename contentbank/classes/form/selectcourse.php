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


namespace core_contentbank\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Upload files to content bank form
 *
 * @package    core_contentbank
 * @copyright  2023 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class selectcourse extends \moodleform {

    /**
     * Add elements to this form.
     */
    public function definition() {
        global $OUTPUT;

        $mform = $this->_form;

        $mform->addElement('html', $OUTPUT->heading(get_string('selectacourse', 'core_contentbank'), 2));

        foreach ($this->_customdata as $course) {
            $context = \context_course::instance($course->id);
            if (has_capability('moodle/course:manageactivities', $context)) {
                $mform->addElement('radio', 'courseid', null, $course->fullname.' ('.$course->shortname.')', $course->id);
            }
        }
        $this->_form->addElement('submit', 'useincourse', get_string('useincourse', 'core_contentbank'));
    }
}
