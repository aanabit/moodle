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
 * This is the external API for this component.
 *
 * @package    core_contentbank
 * @copyright  2020 Sara Arjona <sara@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_contentbank;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;

/**
 * This is the external API for this component.
 *
 * @copyright  2020 Sara Arjona <sara@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {
    /**
     * create_folder parameters.
     *
     * @since  Moodle 3.9
     * @return external_function_parameters
     */
    public static function create_folder_parameters(): \external_function_parameters {
        return new external_function_parameters(
            [
                'name' => new external_value(PARAM_TEXT, 'Name for the new folder', VALUE_REQUIRED),
                'parentid' => new external_value(PARAM_INT, 'The content id to delete', VALUE_DEFAULT, 0),
                'contextid' => new external_value(PARAM_INT, 'The context of the folder', VALUE_DEFAULT, 0)
            ]
        );
    }
    /**
     * Create a new folder in the content bank.
     *
     * @since  Moodle 3.9
     * @param string $name     Name of the new folder.
     * @param int $parentid    Id of the parent folder.
     * @param int $contextid     Context of the folder.
     * @return int Id of the new folder created
     */
    public static function create_folder(string $name, int $parentid = 0, int $contextid = 0): int {
        $params = external_api::validate_parameters(self::create_folder_parameters(), [
            'name' => $name,
            'parentid' => $parentid,
            'contextid' => $contextid
        ]);

        $content = new \stdClass();
        $content->name = $name;
        $content->parent = $parentid;
        $content->contextid = $contextid;
        $folder = folder::create_folder($content);
        if (empty($folder)) {
            return 0;
        }
        return $folder->get_id();
    }

    /**
     * create_folder return
     *
     * @since  Moodle 3.9
     * @return external_value
     */
    public static function create_folder_returns(): \external_value {
        return new external_value(PARAM_INT, 'The id of the created folder');
    }
}
