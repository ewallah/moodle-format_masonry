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
 * Contains the default activity availability information.
 *
 * @package   format_masonry
 * @copyright 2013-2024 eWallah.net
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace format_masonry\output\courseformat\content\cm;

use core_courseformat\output\local\content\cm\availability as availability_base;
use core_availability_multiple_messages;
use cm_info;
use core_courseformat\base as course_format;
use section_info;
use stdClass;
use core_availability\info_module;
use core_availability\info;

/**
 * Base class to render a course module availability inside a course format.
 *
 * @package   format_masonry
 * @copyright 2013-2024 eWallah.net
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class availability extends availability_base {

    /**
     * Generate the basic availability information data from a string.
     * Do not shorten availability text to generate the excerpt text.
     *
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @param string $availabilityinfo the avalability info
     * @return stdClass the availability information data
     */
    protected function availability_info_from_string(\renderer_base $output, string $availabilityinfo): stdClass {
        $course = $this->format->get_course();
        $text = info::format_info($availabilityinfo, $course);
        $data = ['text' => $text];
        return (object) $data;
    }

    /**
     * Generate the basic availability information data from a renderable.
     * Do not generate the excerpt text.
     *
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @param core_availability_multiple_messages $availabilityinfo the avalability info
     * @return stdClass the availability information data
     */
    protected function availability_info_from_output(
        \renderer_base $output,
        core_availability_multiple_messages $availabilityinfo
    ): stdClass {
        $course = $this->format->get_course();
        $renderable = new \core_availability\output\availability_info($availabilityinfo);
        $info = $renderable->export_for_template($output);
        $text = $output->render_from_template('core_availability/availability_info', $info);
        $data = ['text' => info::format_info($text, $course)];
        return (object) $data;
    }
}
