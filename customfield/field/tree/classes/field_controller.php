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
 * Class field
 *
 * @package   customfield_tree
 * @copyright 2021 Amaia Anabitarte <amaia@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_tree;

/**
 * Class field
 *
 * @package   customfield_tree
 * @copyright 2021 Amaia Anabitarte <amaia@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class field_controller extends \core_customfield\field_controller {
    /**
     * Customfield type
     */
    const TYPE = 'tree';

    /**
     * Add fields for editing a tree field.
     *
     * @param \MoodleQuickForm $mform
     */
    public function config_form_definition(\MoodleQuickForm $mform) {
        global $DB;

        // Add config form elements.
        $mform->addElement('header', 'header_specificsettings', get_string('specificsettings', 'customfield_tree'));
        $mform->setExpanded('header_specificsettings', true);

        // Add the option to select a parent.
        $parents = $DB->get_records('user_info_field', ['datatype' => 'tree'], '', 'id, name');
        if (!empty($parents)) {
            $mform->addElement('select', 'configdata[parent]', get_string('parent', 'customfield_tree'), $parents);
        }

        // Does the field has leaves?
        $mform->addElement('advcheckbox', 'configdata[hasleaves]', get_string('hasleaves', 'customfield_tree'));
        $mform->addElement('textarea', 'configdata[leaves]', get_string('leaves', 'customfield_tree'));
        $mform->setType('configdata[leaves]', PARAM_TEXT);
        $mform->hideIf('configdata[leaves]', 'configdata[hasleaves]');
    }

    /**
     * Return configured field options
     *
     * @return array
     */
    public function get_options(): array {
        $optionconfig = $this->get_configdata_property('options');
        if ($optionconfig) {
            $options = preg_split("/\s*\n\s*/", trim($optionconfig));
        } else {
            $options = array();
        }
        return array_merge([''], $options);
    }

    /**
     * Validate the data from the config form.
     * Sub classes must reimplement it.
     *
     * @param array $data from the add/edit profile field form
     * @param array $files
     * @return array associative array of error messages
     */
    public function config_form_validation(array $data, $files = array()) : array {
        $leaves = preg_split("/\s*\n\s*/", trim($data['configdata']['leaves']));
        $errors = [];
        if ($hasleaves && count($leaves) < 2) {
            $errors['configdata[leaves]'] = get_string('errornotenoughleaves', 'customfield_tree');
        }
        return $errors;
    }

    /**
     * Locate the value parameter in the field options array, and return its index
     *
     * @param string $value
     * @return int
     */
    public function parse_value(string $value) {
        return (int) array_search($value, $this->get_options());
    }
}