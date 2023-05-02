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

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

class content extends \core_contentbank\content {

    /**
     * Import a file as valid.
     * Only Moodle backup .mbz files with one activity only will be valid.
     *
     * @throws \file_exception If file operations fail
     * @param \stored_file $file File to store in the content file area.
     * @return \stored_file|null the stored content file or null if the file is discarted.
     */
    public function import_file(\stored_file $file): ?\stored_file {

        // This may take a long time.
        \core_php_time_limit::raise();

        $isvalid = false;

        $filename = $file->get_filepath() . $file->get_filename();
        $tmpdir =  make_temp_directory('contenbank');
        $tmpfilepath = $tmpdir . '/' . $file->get_filename();
        $file->copy_content_to($tmpfilepath);

        $details = \backup_general_helper::get_backup_information_from_mbz($tmpfilepath);
        if (!isset($details->activities)) {
            throw new \file_exception('invalidfile');
        }
        if (!is_array($details->activities) || count($details->activities) != 1) {
            throw new \moodle_exception('oneactivityonly', 'contentype_modules');
        }


//        if (empty($factory)) {
//            $factory = new factory();
//        }
//        $core = $factory->get_core();
//        $h5pvalidator = $factory->get_validator();
//
//        // Set the H5P file path.
//        $core->h5pF->set_file($file);
//        $path = $core->fs->getTmpPath();
//        $core->h5pF->getUploadedH5pFolderPath($path);
//        // Add manually the extension to the file to avoid the validation fails.
//        $path .= '.h5p';
//        $core->h5pF->getUploadedH5pPath($path);
//        // Copy the .h5p file to the temporary folder.
//        $file->copy_content_to($path);
//
//        if ($h5pvalidator->isValidPackage($skipcontent, $onlyupdatelibs)) {
//            if ($skipcontent) {
//                $isvalid = true;
//            } else if (!empty($h5pvalidator->h5pC->mainJsonData['mainLibrary'])) {
//                $mainlibrary = (object) ['machinename' => $h5pvalidator->h5pC->mainJsonData['mainLibrary']];
//                if (self::is_library_enabled($mainlibrary)) {
//                    $isvalid = true;
//                } else {
//                    // If the main library of the package is disabled, the H5P content will be considered invalid.
//                    $core->h5pF->setErrorMessage(get_string('mainlibrarydisabled', 'core_h5p'));
//                }
//            }
//        }
//
//        if ($deletefiletree) {
//            // Remove temp content folder.
//            H5PCore::deleteFileTree($path);
//        }

        $isvalid = true;

        if (!$isvalid) {
            throw new \file_exception('invalidfile');
        }

        return parent::import_file($file);
    }
}
