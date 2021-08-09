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
 * Tree hierarchical profile field definition.
 *
 * @package    profilefield_tree
 * @copyright  2021 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class profile_define_tree
 *
 * @copyright  2021 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class profile_define_tree extends profile_define_base {

    /**
     * Adds elements to the form for creating/editing this type of profile field.
     * @param moodleform $form
     */
    public function define_form_specific($form) {

        global $DB;

        // Param 1 for optional parent.
        $defaults = ['0' => ''];
        $parents = $DB->get_records_menu('user_info_field', ['datatype' => 'tree'], '', 'id, name');
        $parents = $defaults + $parents;
        $form->addElement('select', 'param1', get_string('parent', 'profilefield_tree'), $parents);

        // Param 2 for values.
        $form->addElement('textarea', 'param2', get_string('values', 'profilefield_tree'), array('rows' => 6, 'cols' => 40));
        $form->setType('param2', PARAM_TEXT);
    }

    /**
     * Validates data for the profile field.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function define_validate_specific($data, $files) {
        $errors = [];

        if ($data->param2) {
            $data->param2 = str_replace("\r", '', trim($data->param2));

            // Check that we have at least 2 options.
            if (($values = explode("\n", $data->param2)) === false) {
                $errors['param2'] = get_string('errornotenoughvalues', 'profilefield_tree');
            } elseif (count($values) < 2) {
                $errors['param2'] = get_string('errornotenoughvalues', 'profilefield_tree');
            }
        }

        if ($data->param1 && $data->param1 == $data->id) {
            $errors['param1'] = get_string('errorselfparent', 'profilefield_tree');
        }
        return $errors;
    }

    /**
     * Processes data before it is saved.
     * @param array|stdClass $data
     * @return array|stdClass
     */
    public function define_save_preprocess($data) {
        $data->param2 = str_replace("\r", '', $data->param2);

        return $data;
    }

}


