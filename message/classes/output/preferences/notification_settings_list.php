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
 * Contains notification_settings_list class for displaying on message preferences page.
 *
 * @package   core_message
 * @copyright 2019 Amaia Anabitarte <amaia@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_message\output\preferences;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use context_user;

/**
 * Class to create context for the list of notifications on the message settings page.
 *
 * @package   core_message
 * @copyright 2019 Amaia Anabitarte <amaia@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notification_settings_list implements templatable, renderable {

    /**
     * @var array A list of message processors.
     */
    protected $processors;

    /**
     * @var array A list of message providers.
     */
    protected $providers;

    /**
     * @var array A list of message preferences.
     */
    protected $preferences;

    /**
     * Constructor.
     *
     * @param array $processors
     * @param array $providers
     * @param \stdClass $preferences
     */
    public function __construct($processors, $providers, $preferences) {
        $this->processors = $processors;
        $this->providers = $providers;
        $this->preferences = $preferences;
    }

    /**
     * Create the list component output object.
     *
     * @param string $component
     * @param array $readyprocessors
     * @param array $providers
     * @param \stdClass $preferences
     * @return notification_list_component
     */
    protected function create_list_component($component, $readyprocessors, $providers, $preferences) {
        return new notification_settings_list_component($component, $readyprocessors, $providers, $preferences);
    }

    public function export_for_template(\renderer_base $output) {
        $processors = $this->processors;
        $providers = $this->providers;
        $preferences = $this->preferences;
        $activitycomponents = [];
        $othercomponents = [];

        // Order the components so that the activities appear first, followed
        // by the system and then anything else.
        foreach ($providers as $provider) {
            if ($provider->component != 'moodle') {
                if (substr($provider->component, 0, 4) == 'mod_') {
                    // Activities.
                    $activitycomponents[] = $provider->component;
                } else {
                    // Other stuff.
                    $othercomponents[] = $provider->component;
                }
            }
        }

        $activitycomponents = array_unique($activitycomponents);
        asort($activitycomponents);
        $othercomponents = array_unique($othercomponents);
        asort($othercomponents);
        $components = array_merge($activitycomponents, ['moodle'], $othercomponents);
        asort($providers);

        $context = [
            'disableall' => 0,
            'processors' => [],
        ];

        $readyprocessors = [];
        foreach ($processors as $processor) {
            $context['processors'][] = [
                'displayname' => get_string('pluginname', 'message_'.$processor->name),
                'name' => $processor->name,
                'hassettings' => !empty($processor->object->config_form($preferences)),
            ];

            $readyprocessors[] = $processor;
        }

        foreach ($components as $component) {
            $notificationcomponent = $this->create_list_component($component, $readyprocessors,
                $providers, $preferences);

            $context['components'][] = $notificationcomponent->export_for_template($output);
        }

        // This is fairly nasty but we don't currently have a way to add help icons
        // in templates, so we'll need to provide it in the context.
        //
        // We only want the first component to render with the help icon.
        if (!empty($context['components'])) {
            $context['components'][0]['onlinehelphtml'] = $output->help_icon('loggedin', 'message');
            $context['components'][0]['offlinehelphtml'] = $output->help_icon('loggedoff', 'message');
        }

        return $context;
    }
}
