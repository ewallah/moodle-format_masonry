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
 * Format masonry section class.
 *
 * @package    format_masonry
 * @copyright  eWallah.net
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_masonry\output\courseformat\content;

use core_courseformat\output\local\content\section as section_base;
use context_course;
use core\output\named_templatable;
use core_courseformat\base as course_format;
use core_courseformat\output\local\courseformat_named_templatable;
use renderable;
use renderer_base;
use section_info;
use stdClass;

/**
 * Format masonry section class.
 *
 * @package    format_masonry
 * @copyright  eWallah.net
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section extends section_base {
    /**
     * Override export for template data.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(renderer_base $output): stdClass {
        global $PAGE;

        $format = $this->format;
        $course = $format->get_course();
        $section = $this->section;
        $context = context_course::instance($course->id);

        $summary = new $this->summaryclass($format, $section);
        $isediting = $PAGE->user_is_editing();

        $data = (object)[
            'num' => $section->section ?? 0,
            'id' => $section->id,
            'insertafter' => false,
            'sitehome' => $course->id == SITEID,
            'editing' => $isediting,
            'summary' => $summary->export_for_template($output),
            'displayonesection' => false,
        ];
        if ($this->isstealth && !has_capability('moodle/course:sectionvisibility', $context)) {
            return $data;
        }

        $haspartials = [];
        $haspartials['availability'] = $this->add_availability_data($data, $output);
        $haspartials['visibility'] = $this->add_visibility_data($data, $output);
        $haspartials['editor'] = $this->add_editor_data($data, $output);
        $haspartials['header'] = $this->add_header_data($data, $output);
        $haspartials['cm'] = $this->add_cm_data($data, $output);
        $this->add_format_data($data, $haspartials, $output);
        return $data;
    }

    /**
     * Add the section header to the data structure.
     *
     * @param stdClass $data the current cm data reference
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return bool if the cm has name data
     */
    protected function add_header_data(stdClass &$data, renderer_base $output): bool {
        $header = new $this->headerclass($this->format, $this->section);
        $data->header = $header->export_for_template($output);
        return true;
    }

    /**
     * Add the section cm list to the data structure.
     *
     * @param stdClass $data the current cm data reference
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return bool if the cm has name data
     */
    protected function add_cm_data(stdClass &$data, renderer_base $output): bool {
        $result = false;
        $section = $this->section;
        if ($section->uservisible) {
            $cmlist = new $this->cmlistclass($this->format, $section);
            $data->cmlist = $cmlist->export_for_template($output);
            $result = true;
        }
        return $result;
    }

    /**
     * Add the section format attributes to the data structure.
     *
     * @param stdClass $data the current cm data reference
     * @param bool[] $haspartials the result of loading partial data elements
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return bool if the cm has name data
     */
    protected function add_format_data(stdClass &$data, array $haspartials, renderer_base $output): bool {
        $course = $this->format->get_course();
        $coursecontext = context_course::instance($course->id);
        $data->onlysummary = 0;
        $data->iscoursedisplaymultipage = true;
        $data->sectionbulk = false;
        $data->uservisible = true;
        if ($data->num === 0) {
            if (count($data->cmlist->cms) == 0) {
                $data->uservisible = false;
            }
        }
        if ($this->isstealth) {
            $data->uservisible = false;
        }
        if (has_capability('moodle/course:sectionvisibility', $coursecontext)) {
            $data->uservisible = true;
        }
        $data->contentcollapsed = false;
        return true;
    }

    /**
     * Returns true if the current section should be shown collapsed.
     *
     * @return bool
     */
    protected function is_section_collapsed(): bool {
        return false;
    }
}
