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

namespace core_calendar\output;

use core\output\templatable;
use core\output\renderable;

/**
 * Class humantimeperiod
 *
 * @package    core_calendar
 * @copyright  2025 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class humantimeperiod implements renderable, templatable {

    /**
     * Class constructor.
     *
     * @param int $starttimestamp The starting timestamp.
     * @param int $endtimestamp The ending timestamp.
     * @param int $near The number of seconds within which a date is considered near. Defaults to DAYSECS.
     * @param \moodle_url|null $link URL to link the date to.
     * @param string|null $langtimeformat Lang date and time format to use to format the date.
     * @param bool $userelatives Whether to use human common words or not.
     */
    public function __construct(
        /** @var int $starttimestamp the starting timestamp. */
        protected int $starttimestamp,
        /** @var int $endtimestamp the ending timestamp. */
        protected int $endtimestamp,
        /** @var int $near the number of seconds within which a date is considered near. 1 day by default. */
        protected int $near = DAYSECS,
        /** @var \moodle_url|null $link Link for the dates. */
        protected \moodle_url|null $link = null,
        /** @var string|null $langtimeformat the format to apply. */
        protected string|null $langtimeformat = null,
        /** @var bool $userelatives whether to use human relative terminology. */
        protected bool $userelatives = true,
    ) {
    }

    #[\Override]
    public function export_for_template(\renderer_base $output): array {
        $period = $this->format_period();
        return [
            'startdate' => $period['startdate']->export_for_template($output),
            'enddate' => $period['enddate'] ? $period['enddate']->export_for_template($output) : null,
        ];
    }

    /**
     * Format a time periods based on 2 dates.
     *
     * @return array An array of one or two humandate elements.
     */
    private function format_period(): array {

        $linkstart = null;
        $linkend = null;
        if ($this->link) {
            $linkstart = new \moodle_url($this->link, ['view' => 'day', 'time' => $this->starttimestamp]);
            $linkend = new \moodle_url($this->link, ['view' => 'day', 'time' => $this->endtimestamp]);
        }

        if ($this->endtimestamp == null || $this->starttimestamp == $this->endtimestamp) {
            return [
                'startdate' => new humandate(
                        timestamp: $this->starttimestamp,
                        near: $this->near,
                        link: $linkstart,
                        langtimeformat: $this->langtimeformat,
                        userelatives: $this->userelatives,
                ),
                'enddate' => null,
            ];
        }

        // Get the midnight of the day the event will start.
        $usermidnightstart = usergetmidnight($this->starttimestamp);
        // Get the midnight of the day the event will end.
        $usermidnightend = usergetmidnight($this->endtimestamp);
        // Check if we will still be on the same day.
        if ($usermidnightstart == $usermidnightend) {
            return [
                'startdate' => new humandate(
                        timestamp: $this->starttimestamp,
                        near: $this->near,
                        link: $linkstart,
                        langtimeformat: $this->langtimeformat,
                        userelatives: $this->userelatives,
                ),
                'enddate' => new humandate(
                        timestamp: $this->endtimestamp,
                        near: $this->near,
                        timeonly: true,
                        link: $linkend,
                        langtimeformat: $this->langtimeformat,
                        userelatives: $this->userelatives,
                ),
            ];
        }

        return [
            'startdate' => new humandate(
                    timestamp: $this->starttimestamp,
                    near: $this->near,
                    link: $linkstart,
                    langtimeformat: $this->langtimeformat,
                    userelatives: $this->userelatives,
            ),
            'enddate' => new humandate(
                    timestamp: $this->endtimestamp,
                    near: $this->near,
                    link: $linkend,
                    langtimeformat: $this->langtimeformat,
                    userelatives: $this->userelatives,
            ),
        ];
    }
}
