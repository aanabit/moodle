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
 * core_contentbank specific renderers
 *
 * @package   core_contentbank
 * @copyright  2020 Ferran Recio <ferran@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_contentbank\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;

/**
 * Class containing data for bank content
 *
 * @copyright  2020 Ferran Recio <ferran@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bankcontent implements renderable, templatable {

    /**
     * @var string    Path of the folder.
     */
    private $path = '/';

    /**
     * @var \core_contentbank\folder[]    Array of folders.
     */
    private $folders;

    /**
     * @var \core_contentbank\base[]    Array of content bank contents.
     */
    private $contents;

    /**
     * @var array   $toolbar object.
     */
    private $toolbar;

    /**
     * @var array   $actions array.
     */
    private $actions;

    /**
     * Construct this renderable.
     *
     * @param int $parentid   Current folder id.
     * @param \core_contentbank\folder[] $folders   Array of folders.
     * @param \core_contentbank\base[] $contents   Array of folder contents.
     * @param array $toolbar     Array of folder toolbar options.
     * @param \action_menu $actions     Array of menu actions.
     */
    public function __construct(int $parentid, array $folders, array $contents, array $toolbar, \action_menu $actions) {
        global $DB;

        if ($parentid) {
            $this->path = $DB->get_field('contentbank_folders', 'path', ['id' => $parentid]);
        }
        $this->actions = $actions;
        $this->folders = $folders;
        $this->contents = $contents;
        $this->toolbar = $toolbar;
    }

    /**
     * Export the data.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output): stdClass {
        global $DB;

        $data = new stdClass();

        $url = new \moodle_url('/contentbank/index.php');
        $data->root = $url->out();

        $breadcrumb = [];
        $levels = explode('/', $this->path);
        foreach ($levels as $level) {
            if ($level == '') {
                continue;
            }
            if ($name = $DB->get_field('contentbank_folders', 'name', ['id' => $level])) {
                $url->params(['parent' => $level]);
                $breadcrumb[] = [
                    'name' => $name,
                    'link' => $url->out()
                ];
            }
        }
        $data->breadcrumb = $breadcrumb;

        $folderdata = [];
        foreach ($this->folders as $folder) {
            $link = new \moodle_url('/contentbank/index.php', ['parent' => $folder->id]);
            $folderdata[] = [
                'name' => $folder->name,
                'link' => $link->out(false),
                'icon' => \core_contentbank\folder::get_icon(),
                'timecreated' => $folder->timecreated,
                'timemodified' => $folder->timemodified,
            ];
        }
        $data->folders = $folderdata;

        $contentdata = [];
        foreach ($this->contents as $manager) {
            $contentdata[] = [
                'name' => $manager->get_name(),
                'link' => $manager->get_view_url(),
                'icon' => $manager->get_icon()
            ];
        }
        $data->contents = $contentdata;
        $data->tools = $this->toolbar;
        $data->actions = $this->actions->export_for_template($output);
        return $data;
    }
}
