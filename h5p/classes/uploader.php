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
 * Provides tool_installaddon_installer class.
 *
 * @package     tool_installaddon
 * @subpackage  classes
 * @copyright   2013 David Mudrak <david@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Implements main plugin features.
 *
 * @copyright 2013 David Mudrak <david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_h5p_uploader {

    /** @var tool_installaddon_installfromzip_form */
    protected uploadfromzipform = null;

    /**
     * Factory method returning an instance of this class.
     *
     * @return tool_installaddon_installer
     */
    public static function instance() {
        return new static();
    }

    /**
     * Returns the URL to the main page of this admin tool
     *
     * @param array optional parameters
     * @return moodle_url
     */
    public function get_uploaderpage(array $params = null) {
        return new moodle_url('/h5p/index.php', $params);
    }

    /**
     * @return tool_installaddon_installfromzip_form
     */
    public function get_uploadfromzip_form() {
        if (!is_null($this->installfromzipform)) {
            return $this->installfromzipform;
        }

        $this->uploadfromzipform = new h5p_uploadlibraries_form($this->get_uploaderpage());

        return $this->uploadfromzipform;
    }

    /**
     * Makes a unique writable storage for uploaded ZIP packages.
     *
     * We need the saved ZIP to survive across multiple requests so that it can
     * be used by the plugin manager after the installation is confirmed. In
     * other words, we cannot use make_request_directory() here.
     *
     * @return string full path to the directory
     */
    public function make_installfromzip_storage() {
        return make_unique_writable_directory(make_temp_directory('tool_installaddon'));
    }

}
