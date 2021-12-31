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
 * Renderer for outputting the masonry course format.
 *
 * @package    format_masonry
 * @copyright  2016 Renaat Debleu (www.eWallah.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_masonry\output;

use core_courseformat\output\section_renderer;
use renderable;
use moodle_page;

/**
 * Renderer for outputting the masonry course format.
 *
 * @package    format_masonry
 * @copyright  2016 Renaat Debleu (www.eWallah.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends section_renderer {

    /**
     * Generate the section title, wraps it in a link to the section page if page is to be displayed on a separate page.
     *
     * @param section_info|stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title($section, $course) {
        return get_section_name($course, $section);
    }

    /**
     * Generate the section title to be displayed on the section page, without a link.
     *
     * @param section_info|stdClass $section The course_section entry from DB
     * @param int|stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title_without_link($section, $course) {
        return get_section_name($course, $section);
    }

    /**
     * Generate the section title to be displayed on the section page, without a link.
     *
     * @param section_info|stdClass $section The course_section entry from DB
     * @param int|stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function render_content($widget) {
        $data = $widget->export_for_template($this);
        $course = $this->page->course;
        $format = course_get_format($course);
        $options = (object) $format->get_format_options();
        $str = '.masonry-brick {';
        $str .= 'background-color:' . $options->backcolor . ' !important;';
        $str .= 'border: ' . trim($options->borderwidth) . 'px solid '. $options->bordercolor . ' !important;}';
        $moduleinfo = $format->get_modinfo();
        $sections = $moduleinfo->get_sections();
        foreach ($sections as $sectionnumber => $section) {
            $options = (object) $format->get_format_options($sectionnumber);
            $str .= '#section-' . $sectionnumber . ' {';
            $str .= 'background-color:' . $options->backcolor . ' !important;} ';
        }
        $extra = "<style>.masonry {margin: auto auto} $str</style>";
        return $this->render_from_template('format_masonry/content', $data) . $extra;
    }
}
