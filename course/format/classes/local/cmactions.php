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

namespace core_courseformat\local;


use core_courseformat\sectiondelegatemodule;
use course_modinfo;
/**
 * Course module course format actions.
 *
 * @package    core_courseformat
 * @copyright  2023 Ferran Recio <ferran@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cmactions extends baseactions {
    /**
     * Update a course module.
     *
     * @param \stdClass $cm
     * @param array|\stdClass $modulefields to change in module table.
     * @param array|\stdClass $cmfields to change in course_modules table.
     * @param bool $rebuildcache If true (default), perform a partial cache purge and rebuild.
     * @return bool true if the course module was updated, false otherwise.
     */
    public function update(
            \stdClass $cm,
            array|\stdClass $modulefields,
            array|\stdClass $cmfields = [],
            bool $rebuildcache = true
    ): bool {
        global $DB;

        if ($cmfields) {
            $cmfields = (object) $cmfields;
            $cmfields->id = $cm->id;
            $DB->update_record('course_modules', $cmfields);
        }

        if ($modulefields) {
            $modulefields = (object) $modulefields;
        } else {
            $modulefields = new \stdClass();
        }
        $modulefields->id = $cm->instance;
        $modulefields->timemodified = time();
        if (!$DB->update_record($cm->modname, $modulefields)) {
            return false;
        }

        \core\event\course_module_updated::create_from_cm($cm)->trigger();

        if ($delegatedclass = sectiondelegatemodule::has_delegate_class('mod_' . $cm->modname)) {
            // Propagate the changes to delegated section.
            $cminfo = \cm_info::create($cm);
            if ($delegatedsection = $cminfo->get_delegated_section_info()) {
                $sectionactions = new sectionactions($this->course);
                $sectionactions->update($delegatedsection, $cmfields);
            }
        }

        if ($rebuildcache) {
            course_modinfo::purge_course_module_cache($cm->course, $cm->id);
            if ($delegatedclass) {
                course_modinfo::purge_course_section_cache_by_id($cm->course, 12);
            }
            rebuild_course_cache($cm->course, false, true);
        }

        return true;
    }
    /**
     * Rename a course module.
     *
     * @param int $cmid the course module id.
     * @param string $name the new name.
     * @return bool true if the course module was renamed, false otherwise.
     */
    public function rename(int $cmid, string $name): bool {
        global $CFG, $DB;
        require_once($CFG->libdir . '/gradelib.php');

        $paramcleaning = empty($CFG->formatstringstriptags) ? PARAM_CLEANHTML : PARAM_TEXT;
        $name = clean_param($name, $paramcleaning);

        if (empty($name)) {
            return false;
        }
        if (\core_text::strlen($name) > 255) {
            throw new \moodle_exception('maximumchars', 'moodle', '', 255);
        }

        // The name is stored in the activity instance record.
        // However, events, gradebook and calendar API uses a legacy
        // course module data extraction from the DB instead of a section_info.
        if (!$cm = get_coursemodule_from_id('', $cmid, 0, false, MUST_EXIST)) {
            return false;
        }

        if ($name === $cm->name) {
            return false;
        }

        $cm->name = $name;
        $fields = ['name' => $name];

        if (!$this->update($cm, $fields)) {
            return false;
        }

        // Modules may add some logic to renaming.
        $modinfo = get_fast_modinfo($cm->course);
        \core\di::get(\core\hook\manager::class)->dispatch(
                new \core_courseformat\hook\after_cm_name_edited($modinfo->get_cm($cm->id), $name),
        );

        // Attempt to update the grade item if relevant.
        $grademodule = $DB->get_record($cm->modname, ['id' => $cm->instance]);
        $grademodule->cmidnumber = $cm->idnumber;
        $grademodule->modname = $cm->modname;
        grade_update_mod_grades($grademodule);

        // Update calendar events with the new name.
        course_module_update_calendar_events($cm->modname, $grademodule, $cm);

        return true;
    }

    /**
     * Update a course module.
     *
     * @param int $cmid the course module id.
     * @param int $visible state of the module
     * @param int $visibleoncoursepage state of the module on the course page
     * @param bool $rebuildcache If true (default), perform a partial cache purge and rebuild.
     * @return bool whether course module was updated
     */
    public function set_visibility(int $cmid, int $visible, int $visibleoncoursepage = 1, bool $rebuildcache = true): bool {
        global $DB, $CFG;
        require_once($CFG->libdir.'/gradelib.php');
        require_once($CFG->dirroot.'/calendar/lib.php');

        if (!$cm = get_coursemodule_from_id('', $cmid, 0, false, MUST_EXIST)) {
            return false;
        }

        // Create events and propagate visibility to associated grade items if the value has changed.
        // Only do this if it's changed to avoid accidently overwriting manual showing/hiding of student grades.
        if ($cm->visible == $visible && $cm->visibleoncoursepage == $visibleoncoursepage) {
            return true;
        }

        if (!$modulename = $DB->get_field('modules', 'name', ['id' => $cm->module])) {
            return false;
        }

        // Updating visible and visibleold to keep them in sync. Only changing a section visibility will
        // affect visibleold to allow for an original visibility restore. See set_section_visible().
        $cminfo = new \stdClass();
        $cminfo->visible = $visible;
        $cminfo->visibleoncoursepage = $visibleoncoursepage;
        $cminfo->visibleold = $visible;

        $visibleold = $cm->visible;
        if (!$this->update($cm, [], $cminfo, $rebuildcache)) {
            return false;
        }

        if (($visibleold != $visible) &&
                ($events = $DB->get_records('event', ['instance' => $cm->instance, 'modulename' => $modulename]))) {
            foreach($events as $event) {
                if ($visible) {
                    $event = new calendar_event($event);
                    $event->toggle_visibility(true);
                } else {
                    $event = new calendar_event($event);
                    $event->toggle_visibility(false);
                }
            }
        }

        // Hide the associated grade items so the teacher doesn't also have to go to the gradebook and hide them there.
        // Note that this must be done after updating the row in course_modules, in case
        // the modules grade_item_update function needs to access $cm->visible.
        if ($visibleold != $visible &&
                plugin_supports('mod', $modulename, FEATURE_CONTROLS_GRADE_VISIBILITY) &&
                component_callback_exists('mod_' . $modulename, 'grade_item_update')) {
            $instance = $DB->get_record($modulename, ['id' => $cm->instance], '*', MUST_EXIST);
            component_callback('mod_' . $modulename, 'grade_item_update', [$instance]);
        } else if ($visibleold != $visible) {
            $grade_items = \grade_item::fetch_all([
                    'itemtype' => 'mod',
                    'itemmodule' => $modulename,
                    'iteminstance' => $cm->instance,
                    'courseid' => $cm->course
            ]);
            if ($grade_items) {
                foreach ($grade_items as $grade_item) {
                    $grade_item->set_hidden(!$visible);
                }
            }
        }

        // For modules delegating section we should also change section visibility.
        $delegatedsection = \core_courseformat\sectiondelegate::has_delegate_class('mod_' . $modulename);

        if ($rebuildcache) {
            \course_modinfo::purge_course_module_cache($cm->course, $cm->id);
            rebuild_course_cache($cm->course, false, true);
        }
        return true;
    }
}
