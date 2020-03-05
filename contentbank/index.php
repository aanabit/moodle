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
 * Manage content in content bank.
 *
 * @package    core_contentbank
 * @copyright  2020 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../config.php');

require_login();

$context = context_system::instance();
require_capability('moodle/contentbank:view', $context);

$parentid = optional_param('parent', 0, PARAM_INT);
$errormsg = optional_param('errormsg', '', PARAM_RAW);

$title = get_string('contentbank');
$PAGE->set_url('/contentbank/index.php');
$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('standard');
$PAGE->set_pagetype('contentbank');

$PAGE->requires->js_call_amd('core_contentbank/folders', 'init');

// Get all folders in this path.
$folders = \core_contentbank\api::get_folders_in_parent($parentid);

// Get all contents in this path managed by active plugins to render.
$foldercontents = \core_contentbank\api::get_contents_in_parent($parentid);

// Get the ADD actions ready.
$actions = [];
if (has_capability('moodle/contentbank:createfolder', $context)) {
    // Add the create folder item to the menu.
    $actionurl = new moodle_url('#');
    $actiondata = ['data-action' => 'createfolder', 'data-parentid' => $parentid];
    $actiontext = get_string('newfolder', 'core_contentbank');
    $actions[] = new action_menu_link_secondary($actionurl, new pix_icon('i/folder', $actiontext), $actiontext, $actiondata);

    $actionsmenu = new action_menu($actions);
    $extraclasses = ' btn btn-primary btn-sm float-sm-left px-3';
    $actionsmenu->set_menu_trigger(get_string('add', 'core_contentbank'), $extraclasses);
    $actionsmenu->attributes['class'] .= ' float-sm-left mr-2';
}

// Get the toolbar ready.
$toolbar = [];
if (has_capability('moodle/contentbank:upload', $context)) {
    // Don' show upload button if there's no plugin to support any file extension.
    $extensionmanager = core_contentbank\extensions::instance();
    $accepted = $extensionmanager->get_supported_extensions_as_string();
    if (!empty($accepted)) {
        $importurl = new moodle_url('/contentbank/upload.php', ['parent' => $parentid]);
        $toolbar[] = ['name' => 'Upload', 'link' => $importurl, 'icon' => 'i/upload'];
    }
}
echo $OUTPUT->header();
echo $OUTPUT->box_start('generalbox');

// If needed, display notifications.
if ($errormsg !== '') {
    echo $OUTPUT->notification($errormsg);
}
$folder = new \core_contentbank\output\bankcontent($parentid, $folders, $foldercontents, $toolbar, $actionsmenu);
echo $OUTPUT->render($folder);
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
