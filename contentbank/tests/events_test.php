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
 * Events tests.
 *
 * @package core_contentbank
 * @category test
 * @copyright 2020 Amaia Anabitarte <amaia@moodle.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/contentbank/tests/fixtures/testable_contenttype.php');
require_once($CFG->dirroot . '/contentbank/tests/fixtures/testable_content.php');

class core_contentbank_events_testcase extends advanced_testcase {

    /**
     * Test set up.
     *
     * This is executed before running any test in this file.
     */
    public function setUp() {
        $this->resetAfterTest();
    }

    /**
     * Test the content updated event.
     */
    public function test_content_updated() {

        $this->setAdminUser();

        // Save the system context.
        $systemcontext = context_system::instance();

        // Create a content bank content.
        $generator = $this->getDataGenerator()->get_plugin_generator('core_contentbank');
        $contents = $generator->generate_contentbank_data('contenttype_testable', 1);
        $content = array_shift($contents);

        // Store the name before we change it.
        $oldname = $content->get_name();

        // Trigger and capture the event when renaming a content.
        $sink = $this->redirectEvents();

        $newname = "New name";
        $content->set_name($newname);
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the event data is valid.
        $this->assertInstanceOf('\core_contentbank\event\content_updated', $event);
        $this->assertEquals($systemcontext, $event->get_context());
    }

    /**
     * Test the content deleted event
     */
    public function test_content_deleted() {
        global $DB;

        $this->setAdminUser();

        // Save the system context.
        $systemcontext = context_system::instance();

        // Create a content bank content.
        $generator = $this->getDataGenerator()->get_plugin_generator('core_contentbank');
        $contents = $generator->generate_contentbank_data('contenttype_testable', 3);
        $content = array_shift($contents);
        $this->assertEquals(3, $DB->count_records('contentbank_content'));

        $classname = '\\contenttype_testable\\contenttype';
        $contentype = new $classname($systemcontext);

        // Trigger and capture the event for deleting a content.
        $sink = $this->redirectEvents();
        $contentype->delete_content($content);
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the tag was deleted and the event data is valid.
        $this->assertEquals(2, $DB->count_records('contentbank_content'));
        $this->assertInstanceOf('\core_contentbank\event\content_deleted', $event);
        $this->assertEquals(context_system::instance(), $event->get_context());
    }
}
