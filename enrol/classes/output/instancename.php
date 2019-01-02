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
 * Contains class core_enol\output\instancename
 *
 * @package   core_enrol
 * @copyright 2019 Amaia Anabitarte <amaia@moodle.org>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_enrol\output;

use lang_string;

defined('MOODLE_INTERNAL') || die();

/**
 * Class to prepare an enrolment instance name for display and inline edit.
 *
 * @package   core_enrol
 * @copyright 2019 Amaia Anabitarte <amaia@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class instancename extends \core\output\inplace_editable {
    /**
     * Constructor.
     *
     * @param stdClass $instance
     */
    public function __construct($instance) {
        $coursecontext = \context_course::instance($instance->courseid);
        $editable = has_capability('enrol/cohort:config', $coursecontext);
        $displayvalue = format_string($instance->name, true, $instance->courseid);

        parent::__construct('core_enrol',
            'instancename',
            $instance->id, $editable,
            $displayvalue,
            $instance->name,
            new lang_string('editcustominstancename', 'enrol'),
            new lang_string('newnamefor', 'enrol', $displayvalue));
    }

    /**
     * Updates enrolment instance name and returns instance of this object
     *
     * @param int $instanceid
     * @param string $newvalue
     * @return static
     */
    public static function update($instanceid, $newvalue) {
        global $DB;

        $instance = $DB->get_record('enrol', array('id' => $instanceid), '*', MUST_EXIST);
        $coursecontext = \context::instance_by_id($instance->id);
        \external_api::validate_context($coursecontext);
        require_capability('enrol/cohort:config', $coursecontext);

        $newvalue = clean_param($newvalue, PARAM_TEXT);
        if (strval($newvalue) !== '') {
            $newdata = new \stdClass();
            $newdata->name = $newvalue;
            $plugin = enrol_get_plugin($instance->enrol);
            $plugin->update_instance($instance, $newdata);
            $instance->name = $newvalue;
        }
        return new static($instance);
    }
}
