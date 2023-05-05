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
 * Moodle activity modules content type manager class
 *
 * @package    contenttype_modules
 * @copyright  2023 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace contenttype_modules;

use core\event\contentbank_content_viewed;
use core_contentbank\content;
use enrol_self\self_test;
use stdClass;

class contenttype extends \core_contentbank\contenttype {

    /**
     * Returns the HTML content to add to view.php visualizer.
     *
     * @param  content $content The content to be displayed.
     * @return string            HTML code to include in view.php.
     */
    public function get_view_content(\core_contentbank\content $content): string {
        global $OUTPUT;

        // Trigger an event for viewing this content.
        $event = contentbank_content_viewed::create_from_record($content->get_content());
        $event->trigger();

        $configdata = json_decode($content->get_configdata());
        $configdata->icon = $this->get_icon($content);
        $configdata->pluginname = get_string('pluginname', 'mod_' . $configdata->modulename);

        return $OUTPUT->render_from_template('contenttype_modules/view_content', $configdata);
    }

    /**
     * Returns the HTML code to render the icon for H5P content types.
     *
     * @param  content $content The content to be displayed.
     * @return string            HTML code to render the icon
     */
    public function get_icon(\core_contentbank\content $content): string {
        global $OUTPUT;

        $configdata = json_decode($content->get_configdata());
        return $OUTPUT->image_url('monologo', "mod_{$configdata->modulename}")->out(false);
    }

    /**
     * Return an array of implemented features by this plugin.
     *
     * @return array
     */
    protected function get_implemented_features(): array {
        return [self::CAN_UPLOAD, self::CAN_DOWNLOAD, self::CAN_USEINCOURSE];
    }

    /**
     * Return an array of extensions this contenttype could manage.
     *
     * @return array
     */
    public function get_manageable_extensions(): array {
        return ['.mbz'];
    }

    /**
     * Returns user has access capability for the content itself.
     *
     * @return bool     True if content could be accessed. False otherwise.
     */
    protected function is_access_allowed(): bool {
        return true;
    }

    /**
     * Returns the list of different types of the given content type.
     *
     * A content type can have one or more options for creating content. This method will report all of them or only the content
     * type itself if it has no other options.
     *
     * @return array An object for each type:
     *     - string typename: descriptive name of the type.
     *     - string typeeditorparams: params required by this content type editor.
     *     - url typeicon: this type icon.
     */
    public function get_contenttype_types(): array {
        return [];
    }

    /**
     * Returns the HTML content to use the current content in course.
     *
     * @param  content $content The content to be displayed.
     * @return string           URL to instiate page.
     */
    public function get_useincourse_url(content $content): string {
        $url = new \moodle_url('/contentbank/contenttype/modules/useincourse.php', ['id' => $content->get_id()]);
        return $url->out();
    }
}
