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
 * H5P Content bank manager class
 *
 * @package    contentbank_pdf
 * @copyright  2020 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * H5P Content bank manager class
 *
 * @package    contentbank_pdf
 * @copyright  2020 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class contentbank_pdf_plugin extends core_contentbank_base {

    /** The component for pdf. */
    public const COMPONENT   = 'contentbank_pdf';


    /**
     * Fills content_bank table with appropiate information.
     *
     * @return int  Id of the element created or false if the element has not been created.
     */
    function create_content($name) {

        $this->itemtype = self::COMPONENT;
        $this->content->itemtype = self::COMPONENT;

        return parent::create_content($name);
    }

    function get_manageable_extensions() {
        return array('pdf');
    }

    function can_upload() {
        return has_capability('contentbank/pdf:additem', context_system::instance());
    }

    function get_view_url(int $contentid) {
        global $CFG;

        $fileurl = $this->get_file_url($contentid);
        $url = $fileurl."?forcedownload=1";

        return $url;
    }

    function get_icon(int $contentid) {
        global $OUTPUT;

        return $OUTPUT->pix_icon('f/pdf-64', $this->get_name(), 'moodle', ['class' => 'iconsize-big']);
    }

}