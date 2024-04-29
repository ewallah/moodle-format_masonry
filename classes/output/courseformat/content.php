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
 * Format masonry content class.
 *
 * @package    format_masonry
 * @copyright  2022 eWallah.net
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_masonry\output\courseformat;

use core_courseformat\output\local\content as content_base;

/**
 * Format masonry content class.
 *
 * @package    format_masonry
 * @copyright  2022 eWallah.net
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content extends content_base {
    /**
     * Export this data so it can be used as the context for a mustache template (core/inplace_editable).
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(\renderer_base $output) {
        global $PAGE;
        $colwidth = 1;
        if ($PAGE->user_is_editing()) {
            $PAGE->requires->js('/course/format/masonry/edit.js');
            $colwidth = 20;
        }
        // Render using the masonry js.
        $PAGE->requires->js_init_call(
            'M.masonry.init',
            [[
                   'node' => '.masonry',
                   'itemSelector' => '.masonry-brick',
                   'columnWidth' => $colwidth,
                   'isRTL' => right_to_left(),
                   'gutterWidth' => 0,
                ], ],
            false,
            [
                   'name' => 'course_format_masonry',
                   'fullpath' => '/course/format/masonry/format.js',
                   'requires' => ['base', 'node', 'transition', 'event', 'io-base', 'moodle-core-io'],
                ]
        );
        return parent::export_for_template($output);
    }
}
