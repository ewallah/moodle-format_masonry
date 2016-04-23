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
 * @package    course format
 * @subpackage masonry
 * @copyright  2014 Renaat Debleu (www.eWallah.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/format/renderer.php');

class format_masonry_renderer extends format_section_renderer_base {

    /**
     * Constructor method, calls the parent constructor
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);

        // Since format_topics_renderer::section_edit_controls() only displays the 'Set current section'
        // control when editing mode is on we need to be sure that the link 'Turn editing mode on' is
        // available for a user who does not have any other managing capability.
        $page->set_other_editing_capability('moodle/course:setcurrentsection');
    }

    /**
     * Generate the starting masonry container html for a list of brick sections
     * @return string HTML to output.
     */
    protected function start_section_list() {
        return html_writer::start_tag('ul', array('id' => 'coursemasonry', 'class' => "topics masonry"));
    }

    /**
     * Generate the closing container html for a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    /**
     * Generate the title for this section page
     * @return string the page title
     */
    protected function page_title() {
        return get_string('topicoutline');
    }

    /**
     * Generate the content to displayed on the left part of a section
     * before course modules are included
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return string HTML to output.
     */
    protected function section_left_content($section, $course, $onsectionpage) {
        global $PAGE;
        if ($PAGE->user_is_editing()) {
            return parent::section_left_content($section, $course, $onsectionpage);
        }
        return '';
    }

    /**
     * Generate the display of the header part of a section before
     * course modules are included
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param bool $onsectionpage true if being printed on a single-section page
     * @param int $sectionreturn The section to return to after an action
     * @return string HTML to output.
     */
    protected function section_header($section, $course, $onsectionpage, $sectionreturn=null) {
        $class = 'section main';
        $style = 'background:' .$section->backcolor . ' !important;';
        if (!$section->visible) {
            $class .= ' hidden';
            $style .= ' opacity:0.3;filter:alpha(opacity=30);';
        } else {
            // No need for empty first sections.
            if ($section->id == 0 && empty($section->sequence)) {
                return '';
            }
        }
        if ($course->marker == $section->section) {
            $style .= 'border: ' . 2 * $course->borderwidth . 'px solid '.  $course->bordercolor.' !important;';
        } else {
            $style .= 'border: ' . $course->borderwidth . 'px solid '.  $course->bordercolor.' !important;';
        }
        $o = html_writer::start_tag('li', array('id' => 'section-'.$section->section, 'class' => $class, 'style' => $style));
        $o .= html_writer::start_tag('div', array('class' => 'content'));
        $o .= $this->output->heading($this->section_title($section, $course), 3, 'sectionname');
        $o .= html_writer::start_tag('div', array('class' => 'summary'));
        $o .= $this->format_summary_text($section);
        $o .= html_writer::end_tag('div');
        $context = context_course::instance($course->id);
        $o .= $this->section_availability_message($section, has_capability('moodle/course:viewhiddensections', $context));
        return $o;
    }
}
