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
 * Created events tests.
 *
 * @package core_contentbank
 * @category test
 * @copyright 2020 Amaia Anabitarte <amaia@moodle.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_contentbank;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/contentbank/tests/fixtures/testable_contenttype.php');
require_once($CFG->dirroot . '/contentbank/tests/fixtures/testable_content.php');

/**
 * Test for content bank created event.
 *
 * @package    core_contentbank
 * @category   test
 * @copyright  2020 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \core\event\contentbank_content_created
 */
class core_contentbank_created_event_testcase extends \advanced_testcase {

    /**
     * Test the content created event.
     *
     * @covers ::create_from_record
     */
    public function test_content_created() {
        global $USER;

        $this->resetAfterTest();
        $this->setAdminUser();

        $contenttypeclass = "\\contenttype_testable\\contenttype";
        $systemcontext = \context_system::instance();
        $type = new $contenttypeclass($systemcontext);

        // Trigger and capture the event when renaming a content.
        $sink = $this->redirectEvents();

        // Create content.
        $record = new \stdClass();
        $record->name = 'Test content';
        $record->configdata = '';
        $record->usercreated = $USER->id;
        $content = $type->create_content($record);

        $events = $sink->get_events();
        $event = reset($events);

        // Check that the event data is valid.
        $this->assertInstanceOf('\core\event\contentbank_content_created', $event);
        $this->assertEquals($systemcontext, $event->get_context());
    }
}
