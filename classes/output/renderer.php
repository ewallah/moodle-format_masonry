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
 * Format masonry renderer class.
 *
 * @package    format_masonry
 * @copyright  2022 eWallah.net
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace format_masonry\output;

use context_course;
use core_courseformat\output\section_renderer;
use html_writer;
use moodle_page;

/**
 * Format masonry renderer class.
 *
 * @package    format_masonry
 * @copyright  2022 eWallah.net
 * @author     Renaat Debleu <info@eWallah.net>
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
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section, false));
    }

    /**
     * Generate the section title to be displayed on the section page, without a link.
     *
     * @param section_info|stdClass $section The course_section entry from DB
     * @param int|stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title_without_link($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section, false));
    }

    /**
     * Render the content.
     *
     * @param renderable $widget
     * @return string
     * @return string HTML to output.
     */
    public function render_content($widget) {
        $data = $widget->export_for_template($this);
        $course = $this->page->course;
        if (is_object($course)) {
            if ($course->id == 1) {
                return '';
            }
            $course = $course->id;
        }
        $format = course_get_format($course);
        $options = (object) $format->get_format_options();
        $str = '.masonry-brick {';
        if (property_exists($options, 'backcolor')) {
            $str .= 'background-color:' . $options->backcolor . ' !important;';
            $str .= 'border: ' . trim($options->borderwidth) . 'px solid ' . $options->bordercolor . ' !important;}';
        }
        $moduleinfo = $format->get_modinfo();
        $sections = array_keys($moduleinfo->get_sections());
        foreach ($sections as $section) {
            $str .= '#section-' . $section . ' {';
            $sectionops = (object) $format->get_format_options($section);
            if (array_key_exists($section, $data->sections)) {
                $data->sections[$section]->backcolor = property_exists($sectionops, 'backcolor') ? $sectionops->backcolor : '#FFF';
                if (property_exists($sectionops, 'backcolor')) {
                    // Give a background color.
                    $str .= 'background-color:' . $sectionops->backcolor . ' !important;} ';
                }
            }
        }
        $extra = "<style>.masonry {margin: 0; padding: 0;} $str</style>";
        return $this->render_from_template('format_masonry/course', $data) . $extra;
    }
}
