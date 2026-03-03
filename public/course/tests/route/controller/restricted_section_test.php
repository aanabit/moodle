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

namespace core_course\route\controller;

use core\router\route_loader_interface;
use core\tests\router\route_testcase;
use core\url;
use core_courseformat\formatactions;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Restricted section controller tests.
 *
 * @package     core_course
 * @copyright   2026 Amaia Anabitarte <amaia@moodle.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
#[CoversClass(\core_course\route\controller\restricted_section::class)]
final class restricted_section_test extends route_testcase {
    /**
     * Test the restricted section URL redirections for none restricted sections.
     *
     * The reason why the section is not restricted for the user
     * (whether the user has enough permission to see the section page or
     * whether the restrictions don't apply to the user) is not important.
     * So we are testing only one of the reasons: the user has enough permission.
     * But for non-applied restrictions will work exactly the same because
     * the returned value for $sectioninfo->uservisible = true,
     * and that is the only value we are checking to redirect to section page or not.
     */
    public function test_restricted_section(): void {

        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['numsections' => 3]);
        $modinfo = get_fast_modinfo($course);

        $visibleavailability = json_encode(\core_availability\tree::get_root_json(
            [
                \availability_date\condition::get_json(
                    \availability_date\condition::DIRECTION_FROM,
                    time() + 3600,
                ),
            ],
            '&',
            true,
        ));
        $hiddenavailability = json_encode(\core_availability\tree::get_root_json(
            [
                \availability_date\condition::get_json(
                    \availability_date\condition::DIRECTION_FROM,
                    time() + 3600,
                ),
            ],
            '&',
            false,
        ));

        // Restrict Section 1 with visible restrictions.
        formatactions::section($course)->update(
            $modinfo->get_section_info(1),
            ['availability' => $visibleavailability],
        );

        // Restrict Section 2 with hidden restrictions.
        formatactions::section($course)->update(
            $modinfo->get_section_info(2),
            ['availability' => $hiddenavailability],
        );

        $teacher = $generator->create_and_enrol($course, 'editingteacher');
        $this->setUser($teacher);
        $restrictedsection = $modinfo->get_section_info(1);
        $response = $this->process_request(
            'GET',
            'course/sections/' . $restrictedsection->id . '/restricted',
            route_loader_interface::ROUTE_GROUP_PAGE
        );

        $this->assert_valid_response($response, 302);
        $location = $response->getHeader('Location'); // Just to consume the header if any.
        $this->assertNotEmpty($location, 'The redirection header should be present.');
        $this->assertEquals(
            new url('/course/section.php', ['id' => $restrictedsection->id]),
            new url($location[0])
        );

        $hiddensection = $modinfo->get_section_info(2);
        $response = $this->process_request(
            'GET',
            'course/sections/' . $hiddensection->id . '/restricted',
            route_loader_interface::ROUTE_GROUP_PAGE
        );

        $this->assert_valid_response($response, 302);
        $location = $response->getHeader('Location'); // Just to consume the header if any.
        $this->assertNotEmpty($location, 'The redirection header should be present.');
        $this->assertEquals(
            new url('/course/section.php', ['id' => $hiddensection->id]),
            new url($location[0])
        );
    }
}
