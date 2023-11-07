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

namespace core_course\external;

use core\context\course;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use core_external\external_warnings;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/course/format/lib.php');

/**
 * This is the external method for getting information about courses and sections.
 *
 * @package    core_course
 * @since      Moodle 4.4
 * @copyright  2023 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_courses_and_sections extends external_api {
    /**
     * Parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'capability' => new external_value(PARAM_TEXT, 'Preset to be imported', VALUE_OPTIONAL),
        ]);
    }

    /**
     * Get courses and sections information.
     *
     * @param string $capability the capability to check for in the course context.
     * @return array Course and sections information.
     */
    public static function execute(string $capability = ''): array {
        global $DB;

        $params = self::validate_parameters(
            self::execute_parameters(),
            ['capability' => $capability]
        );

        $fields = 's.id as sectionid, c.id as courseid, c.fullname, c.shortname, s.section';
        $courses = $DB->get_records_sql("SELECT ". $fields . "
                                   FROM {course} c LEFT JOIN {course_sections} s ON c.id = s.course
                                   ORDER BY c.id, s.section");

        $data = [];
        $formats = [];
        $contexts = [];
        foreach ($courses as $course) {
            if (!array_key_exists($course->courseid, $data)) {
                if (empty($formats[$course->courseid])) {
                    $formats[$course->courseid] = course_get_format($course->courseid);
                }
                if (empty($contexts[$course->courseid])) {
                    $contexts[$course->courseid] = \context_course::instance($course->courseid);
                }
                if (empty($capability) || has_capability($params['capability'], $contexts[$course->courseid])) {
                    $courseinfo = [
                        'id' => $course->courseid,
                        'fullname' => $course->fullname,
                        'shortname' => $course->shortname,
                        'sections' => [],
                        ];
                    $data[$course->courseid] = $courseinfo;
                }
            }
            if (!is_null($course->sectionid)) {
                $courseformat = $formats[$course->courseid];
                $section = [
                    'sectionid' => $course->sectionid,
                    'name' => $courseformat->get_section_name($course->section),
                    'section' => $course->section,
                ];
                $data[$course->courseid]['sections'][] = $section;
            }
        }
        return ['result' => $data, 'warnings' => []];
    }

    /**
     * Return.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'result' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Id of the course'),
                    'fullname' => new external_value(PARAM_TEXT, 'Course full name'),
                    'shortname' => new external_value(PARAM_TEXT, 'Course short name'),
                    'sections' => new external_multiple_structure(
                            new external_single_structure([
                                'sectionid' => new external_value(PARAM_INT, 'Id of the section'),
                                'name' => new external_value(PARAM_TEXT, 'Section name'),
                                'section' => new external_value(PARAM_INT, 'Section numeric identification'),
                            ], 'Course sections information', VALUE_OPTIONAL, []),
                        ),
                ], 'Courses and sections information', VALUE_OPTIONAL, []),
            ),
            'warnings' => new external_warnings(),
        ]);
    }
}
