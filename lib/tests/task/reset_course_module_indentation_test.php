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

namespace core\task;

/**
 * Class containing unit tests for the task that fetch the latest version of H5P content types.
 *
 * @package   core
 * @copyright  2023 Amaia Anabitarte <amaia@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \core\task\reset_course_module_indentation
 */
class reset_course_module_indentation_test extends \advanced_testcase {

    /**
     * Test task execution
     *
     * @dataProvider    execute_provider
     * @param   int   $totalcoursemodules How many activities exist.
     * @param   int   $indented  How many activities are indented.
     *
     * @covers ::execute
     */
    public function test_task_execution(int $totalcoursemodules, int $indented): void {
        global $DB;

        if ($totalcoursemodules < $indented) {
            throw new \moodle_exception('error_wrongdataprovider', 'error');
        }
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course([]);
        $activities = [];
        for ($i = 0; $i < $totalcoursemodules; $i++) {
            $activities[] = $this->getDataGenerator()->create_module('page', ['course' => $course]);
        }
        for ($i = 0; $i < $indented; $i++) {
            $DB->set_field('course_modules', 'indent', 1, ['id' => $activities[$i]->cmid]);
        }

        $countindented = $DB->count_records('course_modules', ['indent' => 1]);
        $this->assertEquals($indented, $countindented);

        $task = new reset_course_module_indentation();
        $task->execute();
        $countindented = $DB->count_records('course_modules', ['indent' => 1]);
        $this->assertEquals(0, $countindented);
    }

    /**
     * Data provider for test_task_execution.
     *
     * @return  array
     */
    public function execute_provider(): array {
        return [
            'Empty course modules' => [0, 0],
            'Non indented one course module' => [1, 0],
            'Indented one course module' => [1, 1],
            'Many non-indented course modules' => [5, 0],
            'Many indented course modules' => [5, 2],
        ];
    }
}
