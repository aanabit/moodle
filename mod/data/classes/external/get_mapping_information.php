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

namespace mod_data\external;

use core\notification;
use mod_data\local\importer\preset_existing_importer;
use mod_data\local\importer\preset_importer;
use mod_data\manager;
use mod_data\preset;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * This is the external method for deleting a saved preset.
 *
 * @package    mod_data
 * @since      Moodle 4.1
 * @copyright  2022 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_mapping_information extends \external_api {
    /**
     * Parameters.
     *
     * @return \external_function_parameters
     */
    public static function execute_parameters(): \external_function_parameters {
        return new \external_function_parameters([
            'dataid' => new \external_value(PARAM_INT, 'Id of the data activity', VALUE_REQUIRED),
            'import' => new \external_value(PARAM_TEXT, 'Preset to be imported'),
        ]);
    }

    /**
     * Get importing information for the given database activity.
     *
     * @param  int $dataid Id of the data activity where to import the preset.
     * @param  string $import Plugin or zip file to be imported.
     * @return array Information needed to decide whether to show the dialogue or not.
     */
    public static function execute(int $dataid, string $import): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), ['dataid' => $dataid, 'import' => $import]);

        // Let's get the manager.
        $instance = $DB->get_record('data', ['id' => $params['dataid']], '*', MUST_EXIST);
        $manager = manager::create_from_instance($instance);

        $result = [
            'needsmapping' => false,
            'presetname' => $params['import'],
            'fieldstocreate' => '',
            'fieldstoremove' => '',
        ];
        $warnings = [];

        try {
            $importer = new preset_existing_importer($manager, $params['import']);
            $result['presetname'] = preset::get_name_from_plugin($params['import']);
            $result['needsmapping'] = $importer->needs_mapping();
            $result['fieldstocreate'] = self::get_field_names($importer->fieldstocreate);
            $result['fieldstoremove'] = self::get_field_names($importer->fieldstoremove);
        } catch (\moodle_exception $e) {
            // The saved preset has not been deleted.
            $warnings[] = [
                'item' => $instance->name,
                'warningcode' => 'exception',
                'message' => $e->getMessage()
            ];
            notification::error($e->getMessage());
        }

        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Return.
     *
     * @return \external_single_structure
     */
    public static function execute_returns(): \external_single_structure {
        return new \external_single_structure([
            'needsmapping' => new \external_value(PARAM_BOOL, 'Whether the importing needs mapping or not'),
            'presetname' => new \external_value(PARAM_TEXT, 'Name of the applied preset'),
            'fieldstocreate' => new \external_value(PARAM_TEXT, 'List of field names to create'),
            'fieldstoremove' => new \external_value(PARAM_TEXT, 'List of field names to remove'),
            'warnings' => new \external_warnings(),
        ]);
    }

    /**
     * Get preset parameters to display in apply preset dialog
     *
     * @param array $fields Array of fields to get name from.
     * @return string   A string listing the names of the fields.
     */
    private static function get_field_names(array $fields): string {
        $fieldnames = array_map(function($field) {
            return $field->name;
        }, $fields);
        return implode(', ', $fieldnames);
    }
}
