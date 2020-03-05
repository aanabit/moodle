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
 * Class used to return information to display for the content bank.
 *
 * @package    core_contentbank
 * @copyright  2020 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_contentbank;

defined('MOODLE_INTERNAL') || die();

/**
 * Class used to return information to display for the content bank.
 *
 * @package    core_contentbank
 * @copyright  2020 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api {

    /** Function to get all the folders in a parent folder.
     *
     * @param int $parentid     Parent folder where to look for folders.
     * @return array
     * @throws \dml_exception
     */
    public static function get_folders_in_parent(int $parentid): array {
        global $DB;

        $folders = $DB->get_records('contentbank_folders', ['parent' => $parentid]);
        return $folders;
    }

    /** Get all the content in the given folder.
     *
     * @param int $parentid     Parent folder where to look for content.
     * @return array
     * @throws \dml_exception
     */
    public static function get_contents_in_parent(int $parentid): array {
        global $DB;

        $foldercontents = [];
        $contents = $DB->get_records('contentbank_content', ['parent' => $parentid]);
        foreach ($contents as $content) {
            $plugin = \core_plugin_manager::instance()->get_plugin_info($content->contenttype);
            if (!$plugin || !$plugin->is_enabled()) {
                continue;
            }
            $managerclass = "\\$content->contenttype\\plugin";
            if (class_exists($managerclass)) {
                $manager = new $managerclass($content);
                $foldercontents[] = $manager;
            }
        }
        return $foldercontents;
    }
}