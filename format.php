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
 * Display the course topics as bricks using a dynamic grid layout.
 * @package format_masonry
 * @copyright Renaat Debleu info@ewallah.net
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// make sure all sections are created
$course = course_get_format($course)->get_course();
course_create_sections_if_missing($course, range(0, $course->numsections));

// handle currentsection
if (($marker >=0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
    $course->marker = $marker;
    course_set_marker($course->id, $marker);
}

if ($PAGE->user_is_editing()) {
    //rely on the standard topics rendering
    $PAGE->requires->js('/course/format/topics/format.js');
    $renderer = $PAGE->get_renderer('format_topics');
} else {
    //render using the masonry js
    $PAGE->requires->js_init_call('M.masonry.init', 
        array(array(
           'node' => '#coursemasonry',
           'itemSelector' => '.section.main',
           'columnWidth' => 1,
           'isRTL' => right_to_left(),
           'gutterWidth' => 0
        )),
        true,
        array(
           'name' => 'course_format_masonry',
           'fullpath' => '/course/format/masonry/format.js',
           'requires' => array('base', 'node', 'transition', 'dd', 'event')
        )
    );
    $renderer = $PAGE->get_renderer('format_masonry');
}
$renderer->print_multiple_section_page($course, null, null, null, null);
