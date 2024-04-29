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
 * Masonry topics course format.
 *
 * Display the course topics as bricks using a dynamic grid layout.
 *
 * @package    format_masonry
 * @copyright  2022 eWallah.net
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/completionlib.php');

$format = course_get_format($course);
$course = $format->get_course();
$context = \context_course::instance($course->id);

// Make sure all sections are created.
course_create_sections_if_missing($course, 0);

// All course rendering is controlled by the content output class.
$renderer = $format->get_renderer($PAGE);
$outputclass = $format->get_output_classname('content');
$widget = new $outputclass($format);
echo $renderer->render($widget);
$colwidth = 1;
if ($PAGE->user_is_editing()) {
    // Rely on the standard topics rendering.
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
