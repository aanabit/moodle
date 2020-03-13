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
 * Mustache helper render pix icons including class parameters.
 *
 * @package    core
 * @category   output
 * @copyright  2020 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core\output;

use Mustache_LambdaHelper;
use renderer_base;

/**
 * This class will call pix_icon with the section content.
 *
 * @copyright  2020 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.9
 */
class mustache_pixandclass_helper {

    /** @var renderer_base $renderer A reference to the renderer in use */
    private $renderer;

    /**
     * Save a reference to the renderer.
     * @param renderer_base $renderer
     */
    public function __construct(renderer_base $renderer) {
        $this->renderer = $renderer;
    }

    /**
     * Read a pix icon name and class attribute from a template and get it from pix_icon.
     *
     * {{#andpix}}t/edit,class,component,Anything else is alt text{{/pixandclass}}
     *
     * The args are comma separated and only the first and second are required.
     *
     * @param string $text The text to parse for arguments.
     * @param Mustache_LambdaHelper $helper Used to render nested mustache variables.
     * @return string
     */
    public function pix($text, Mustache_LambdaHelper $helper) {
        // Split the text into an array of variables.
        $key = strtok($text, ",");
        $key = trim($helper->render($key));
        $class = strtok(",");
        $class = trim($helper->render($class));
        $component = strtok(",");
        $component = trim($helper->render($component));
        if (!$component) {
            $component = '';
        }
        $component = $helper->render($component);
        $text = strtok("");
        // Allow mustache tags in the last argument.
        $text = trim($helper->render($text));

        return trim($this->renderer->pix_icon($key, $text, $component, ['class' => $class]));
    }
}

