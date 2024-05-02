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
 * Lib for masonry course format.
 *
 * @package    format_masonry
 * @copyright 2013-2024 eWallah.net
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/format/lib.php');

use core\output\inplace_editable;

/**
 * Main class for the masonry course format
 *
 * @package    format_masonry
 * @copyright 2013-2024 eWallah.net
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_masonry extends core_courseformat\base {
    /**
     * Returns instance of page renderer used by this plugin
     *
     * @param moodle_page $page
     * @return renderer_base
     */
    public function get_renderer(moodle_page $page) {
        return $page->get_renderer('format_masonry');
    }

    /**
     * Returns the default section name for the masonry course format.
     *
     * @param stdClass $section Section object from database or just field course_sections section
     * @return string The default value for the section name.
     */
    public function get_default_section_name($section) {
        if ($section->section == 0) {
            // Return the general section.
            return get_string('section0name', 'format_masonry');
        } else {
            // Use course_format::get_default_section_name implementation which
            // will display the section name in "Topic n" format.
            return parent::get_default_section_name($section);
        }
    }

    /**
     * Returns the display name of the given section that the course prefers.
     *
     * @param int|stdClass $section Section object from database or just field section.section
     * @return string Display name that the course format prefers, e.g. "Topic 2"
     */
    public function get_section_name($section) {
        $section = $this->get_section($section);
        if ((string)$section->name !== '') {
            // Return the name the user set.
            return format_string($section->name, true, ['context' => context_course::instance($this->courseid)]);
        } else {
            return $this->get_default_section_name($section);
        }
    }

    /**
     * This course format supports components.
     *
     * @return boolean
     */
    public function supports_components() {
        return true;
    }

    /**
     * The URL to use for the specified course (without section)
     *
     * @param int|stdClass $section Section object from database or just field course_sections.section
     * @param array $options options for view URL. ignored
     * @return null|moodle_url
     */
    public function get_view_url($section, $options = []) {
        $course = $this->get_course();
        return new \moodle_url('/course/view.php', ['id' => $course->id]);
    }

    /**
     * Definitions of the additional options that this course format uses for section
     *
     * @param bool $foreditform
     * @return array
     */
    public function section_format_options($foreditform = false) {
        $color = get_config('format_masonry', 'defaultbordercolor');
        return [
            'backcolor' => [
                'type' => PARAM_RAW,
                'name' => 'bordercolor',
                'label' => new \lang_string('backgroundcolor', 'format_masonry'),
                'element_type' => 'text',
                'default' => $color,
                'cache' => true,
                'cachedefault' => $color,
                'help' => 'colordisplay',
                'help_component' => 'format_masonry',
            ],
        ];
    }

    /**
     * Definitions of the additional options that this course format uses for course
     *
     * @param bool $foreditform
     * @return array of options
     */
    public function course_format_options($foreditform = false) {
        static $courseformatoptions = false;
        if ($courseformatoptions === false) {
            $courseconfig = get_config('moodlecourse');
            $courseformatoptions = [
                'numsections' => ['default' => $courseconfig->numsections, 'type' => PARAM_INT],
                'hiddensections' => ['type' => PARAM_INT, 'default' => 1],
                'coursedisplay' => ['type' => PARAM_INT, 'default' => 1],
                'borderwidth' => ['type' => PARAM_INT, 'default' => 1],
                'bordercolor' => ['type' => PARAM_TEXT, 'default' => '#F0F0F0'],
                'backcolor' => ['type' => PARAM_TEXT, 'default' => '#F0F0F0'], ];
        }
        if ($foreditform && !isset($courseformatoptions['coursedisplay']['label'])) {
            $courseconfig = get_config('moodlecourse');
            $max = (int)$courseconfig->maxsections;
            $sectionmenu = [];
            for ($i = 0; $i <= $max; $i++) {
                $sectionmenu[$i] = "$i";
            }
            $courseoptionsedit = [
                'numsections' => [
                    'label' => new \lang_string('numberweeks'),
                    'element_type' => 'select',
                    'element_attributes' => [$sectionmenu],
                ],
                'hiddensections' => [
                    'label' => 'hidden1',
                    'element_type' => 'hidden',
                    'element_attributes' => [[1 => new \lang_string('hiddensectionsinvisible')]],
                ],
                'coursedisplay' => [
                    'label' => 'hidden2',
                    'element_type' => 'hidden',
                    'element_attributes' => [[COURSE_DISPLAY_SINGLEPAGE => new \lang_string('coursedisplay_single')]],
                ],
                'borderwidth' => [
                    'label' => new \lang_string('borderwidth', 'format_masonry'),
                    'element_type' => 'select',
                    'element_attributes' => [[0 => '0', 1 => '1', 2 => '2']],
                ],
                'bordercolor' => [
                    'label' => new \lang_string('bordercolor', 'format_masonry'),
                    'element_type' => 'text',
                    'element_attributes' => [['value' => $courseformatoptions['bordercolor']['default']]],
                ],
                'backcolor' => [
                    'label' => new \lang_string('colordisplay', 'format_masonry'),
                    'element_type' => 'text',
                    'help' => 'colordisplay',
                    'help_component' => 'format_masonry',
                    'element_attributes' => [['value' => $courseformatoptions['backcolor']['default']]],
                ],
            ];
            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseoptionsedit);
        }
        return $courseformatoptions;
    }

    /**
     * Updates format options for a course
     *
     * @param stdClass|array $data
     * @param stdClass $oldcourse
     * @return bool whether there were any changes to the options values
     */
    public function update_course_format_options($data, $oldcourse = null) {
        if ($oldcourse !== null) {
            $data->backcolor = get_config('format_masonry', 'defaultbackgroundcolor');
            $data->bordercolor = get_config('format_masonry', 'defaultbordercolor');
            return parent::update_course_format_options($data, $oldcourse);
        }
        return $this->update_format_options($data);
    }

    /**
     * Prepares the templateable object to display section name.
     *
     * @param \section_info|\stdClass $section
     * @param bool $linkifneeded
     * @param bool $editable
     * @param null|lang_string|string $edithint
     * @param null|lang_string|string $editlabel
     * @return inplace_editable
     */
    public function inplace_editable_render_section_name(
        $section,
        $linkifneeded = false,
        $editable = null,
        $edithint = null,
        $editlabel = null
    ) {
        if (empty($edithint)) {
            $edithint = ($section->section == 0) ? 'section0name' : 'sectionname';
            $edithint = new lang_string($edithint, 'format_masonry');
        }
        if (empty($editlabel)) {
            $title = get_section_name($section->course, $section);
            $editlabel = new lang_string('newsectionname', 'format_masonry', $title);
        }
        return parent::inplace_editable_render_section_name($section, $linkifneeded, $editable, $edithint, $editlabel);
    }

    /**
     * Returns whether this course format allows the activity to be hidden on course page but available.
     *
     * @param stdClass|cm_info $cm course module (may be null if we are displaying a form for adding a module)
     * @param stdClass|section_info $section section where this module is located or will be added to
     * @return bool
     */
    public function allow_stealth_module_visibility($cm, $section) {
        return !$section->section || $section->visible;
    }

    /**
     * Returns true if this course format uses sections.
     *
     * @return bool
     */
    public function uses_sections() {
        return true;
    }

    /**
     * Whether this format allows to delete sections.
     *
     * @param int|stdClass|section_info $section
     * @return bool
     */
    public function can_delete_section($section) {
        return ($this->get_section($section)->section != 0);
    }

    /**
     * Whether this format allows course index.
     *
     * @return bool
     */
    public function uses_course_index() {
        return false;
    }

    /**
     * Whether this format allows to indentation.
     *
     * @return bool
     */
    public function uses_indentation(): bool {
        return false;
    }

    /**
     * Custom action after section has been moved in AJAX mode.
     *
     * @return array This will be passed in ajax respose
     */
    public function ajax_section_move() {
        global $PAGE;
        $titles = [];
        $course = $this->get_course();
        $modinfo = get_fast_modinfo($course);
        $renderer = $this->get_renderer($PAGE);
        if ($renderer && ($sections = $modinfo->get_section_info_all())) {
            foreach ($sections as $number => $section) {
                $titles[$number] = $renderer->section_title($section, $course);
            }
        }
        return ['sectiontitles' => $titles, 'action' => 'move'];
    }

    /**
     * Callback used in WS core_course_edit_section when teacher performs an AJAX action on a section (show/hide).
     *
     * @param section_info|stdClass $section
     * @param string $action
     * @param int $sr
     * @return null|array any data for the Javascript post-processor (must be json-encodeable)
     */
    public function section_action($section, $action, $sr) {
        global $PAGE;

        // For show/hide actions call the parent method and return the new content for .section_availability element.
        $rv = parent::section_action($section, $action, $sr);
        $renderer = $PAGE->get_renderer('format_masonry');

        if (!($section instanceof section_info)) {
            $modinfo = course_modinfo::instance($this->courseid);
            $section = $modinfo->get_section_info($section->section);
        }
        $elementclass = $this->get_output_classname('content\\section\\availability');
        $availability = new $elementclass($this, $section);

        $rv['section_availability'] = $renderer->render($availability);
        return $rv;
    }

    /**
     * Returns the information about the ajax support in the given source format.
     *
     * @return stdClass
     */
    public function supports_ajax() {
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = true;
        return $ajaxsupport;
    }

    /**
     * Return the plugin configs for external functions.
     *
     * @return array the list of configuration settings
     */
    public function get_config_for_external() {
        // Return everything (nothing to hide).
        return $this->get_format_options();
    }
}

/**
 * Implements callback inplace_editable() allowing to edit values in-place
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return \core\output\inplace_editable
 */
function format_masonry_inplace_editable($itemtype, $itemid, $newvalue) {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/course/lib.php');
    if ($itemtype == 'sectionname' || $itemtype == 'sectionnamenl') {
        $sql = 'SELECT s.* FROM {course_sections} s JOIN {course} c ON s.course = c.id WHERE s.id = ? AND c.format = ?';
        $section = $DB->get_record_sql($sql, [$itemid, 'masonry'], MUST_EXIST);
        return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
    }
}
