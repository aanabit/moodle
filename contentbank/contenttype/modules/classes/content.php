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
 * Moodle activity modules content manager class
 *
 * @package    contenttype_modules
 * @copyright  2023 contenttype_modules Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace contenttype_modules;

use Matrix\Exception;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

class content extends \core_contentbank\content {

    /**
     * Import a file as valid.
     * Only Moodle backup .mbz files with one activity only will be valid.
     *
     * @throws \moodle_exception If file operations fail
     * @param \stored_file $file File to store in the content file area.
     * @return \stored_file|null the stored content file or null if the file is discarted.
     */
    public function import_file(\stored_file $file): ?\stored_file {
        try {
            // This may take a long time.
            \core_php_time_limit::raise();

            $filename = $file->get_filepath() . $file->get_filename();
            $tmpdir =  make_temp_directory('contenbank');
            $tmpfilepath = $tmpdir . '/' . $file->get_filename();
            $file->copy_content_to($tmpfilepath);

            $details = \backup_general_helper::get_backup_information_from_mbz($tmpfilepath);
            $activities = $details->activities;
        } catch (\Exception $e) {
            throw new \moodle_exception('notvalidpackage');
        }
        if (!is_array($details->activities) || count($details->activities) != 1) {
            throw new \moodle_exception('oneactivityonly', 'contentype_modules');
        }

        return parent::import_file($file);
    }
}
