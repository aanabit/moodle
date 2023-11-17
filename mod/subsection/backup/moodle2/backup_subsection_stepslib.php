<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Backup steps for mod_subsection are defined here.
 *
 * @package     mod_subsection
 * @category    backup
 * @copyright   2023 Amaia Anabitarte <amaia@moodle.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// More information about the backup process: {@link https://docs.moodle.org/dev/Backup_API}.

/**
 * Define the complete structure for backup, with file and id annotations.
 */
class backup_subsection_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the structure of the resulting xml file.
     *
     * @return backup_nested_element The structure wrapped by the common 'activity' element.
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $subsection = new backup_nested_element('subsection', ['id'], ['name', 'timemodified']);

        // Build the tree
        // (love this)

        // Define sources
        $subsection->set_source_table('subsection', ['id' => backup::VAR_ACTIVITYID]);

        // Define id annotations
        // (none)

        // Define file annotations
        $subsection->annotate_files('mod_subsection', 'intro', null); // This file area hasn't itemid

        // Return the root element (label), wrapped into standard activity structure
        return $this->prepare_activity_structure($subsection);
    }
}
