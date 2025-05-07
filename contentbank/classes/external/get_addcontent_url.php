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

namespace core_contentbank\external;

use core_contentbank\contentbank;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use core_external\external_warnings;

/**
 * This is the external method for getting the url to add to course PHP page.
 *
 * @package    core_contentbank
 * @copyright  2025 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_addcontent_url extends external_api {
    /**
     * get_addcontent_url parameters.
     *
     * @since  Moodle 4.4
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'contentid' => new external_value(PARAM_INT, 'The content id to use', VALUE_REQUIRED),
            ]
        );
    }

    /**
     * Get the addtocourse URL from a content.
     *
     * @since  Moodle 5.1
     * @param  int $contentid The content id to use.
     * @return array True if the instance has been created; false and the warning, otherwise.
     */
    public static function execute(int $contentid): array {
        global $DB;

        $result = false;
        $warnings = [];

        $params = self::validate_parameters(self::execute_parameters(), [
            'contentid' => $contentid,
        ]);

        // If name is empty don't try to rename and return a more detailed message.
        try {
            $record = $DB->get_record('contentbank_content', ['id' => $params['contentid']], '*', MUST_EXIST);
            $cb = new contentbank();
            $content = $cb->get_content_from_id($record->id);
            $contenttype = $content->get_content_type_instance();

            $context = \context::instance_by_id($record->contextid, MUST_EXIST);
            self::validate_context($context);
            // Check capability.
            if ($contenttype->can_addtocourse($content)) {
                $result = $contenttype->get_addtocourse_url($content)->out_as_local_url();
            } else {
                // The user has no permission to manage this content.
                $warnings[] = [
                    'item' => $params['contentid'],
                    'warningcode' => 'nopermissiontomanage',
                    'message' => get_string('nopermissiontomanage', 'core_contentbank')
                ];
            }
        } catch (\moodle_exception $e) {
            // The content or the context don't exist.
            $warnings[] = [
                'item' => $params['contentid'],
                'warningcode' => 'exception',
                'message' => $e->getMessage()
            ];
        }

        return [
            'result' => $result,
            'warnings' => $warnings
        ];
    }

    /**
     * add_to_course URL return.
     *
     * @since  Moodle 5.1
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'result' => new external_value(PARAM_URL, 'The URL where to go to'),
            'warnings' => new external_warnings()
        ]);
    }
}
