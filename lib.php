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
 * lib for masonry course format.
 *
 * @package    format_masonry
 * @copyright  2016 Renaat Debleu (www.eWallah.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot. '/course/format/topics/lib.php');

/**
 * Main class for the masonry course format
 *
 * @package    format_masonry
 * @copyright  Renaat Debleu (www.eWallah.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_masonry extends format_topics {

    /**
     * Definitions of the additional options that this course format uses for section
     *
     * @param bool $foreditform
     * @return array
     */
    public function section_format_options($foreditform = false) {
        global $CFG;
        if (empty($CFG->format_masonry_defaultbordercolor)) {
            $CFG->format_masonry_defaultbordercolor = '#9A9B9C';
        }
        return array(
            'backcolor' => array(
                'type' => PARAM_RAW,
                'name' => 'bordercolor',
                'label' => get_string('backgroundcolor', 'format_masonry'),
                'element_type' => 'masonrycolorpicker',
                'default' => $CFG->format_masonry_defaultbackgroundcolor,
                'cache' => true,
                'help' => 'colordisplay',
                'help_component' => 'format_masonry',
            )
        );
    }

    /**
     * Definitions of the additional options that this course format uses for course
     *
     * Topics format uses the following options:
     * - coursedisplay : hidden and forced to be single_page_view
     * - numsections
     * - hiddensections : hidden and forced to be 1
     * - borderwith
     * - backgroundcolor
     *
     * @param bool $foreditform
     * @return array of options
     */
    public function course_format_options($foreditform = false) {
        static $courseformatoptions = false;
        if ($courseformatoptions === false) {
            $courseconfig = get_config('moodlecourse');
            $courseformatoptions = array(
                'numsections' => array(
                    'default' => $courseconfig->numsections,
                    'type' => PARAM_INT,
                ),
                'hiddensections' => array(
                    'type' => PARAM_INT,
                    'default' => 1
                ),
                'coursedisplay' => array(
                    'type' => PARAM_INT,
                    'default' => 1
                ),
                'borderwidth' => array(
                    'type' => PARAM_INT,
                    'default' => 1
                ),
                'bordercolor' => array(
                    'type' => PARAM_TEXT,
                    'default' => '#F0F0F0'
                ),
                'backcolor' => array(
                    'type' => PARAM_TEXT,
                    'default' => '#F0F0F0'
                )
            );
        }
        if ($foreditform && !isset($courseformatoptions['coursedisplay']['label'])) {
            $courseconfig = get_config('moodlecourse');
            $max = $courseconfig->maxsections;
            if (!isset($max) || !is_numeric($max)) {
                $max = 100;
            }
            $sectionmenu = array();
            for ($i = 0; $i <= $max; $i++) {
                $sectionmenu[$i] = "$i";
            }
            $courseoptionsedit = array(
                'numsections' => array(
                    'label' => new lang_string('numberweeks'),
                    'element_type' => 'select',
                    'element_attributes' => array($sectionmenu),
                ),
                'hiddensections' => array(
                    'label' => 'hidden1',
                    'element_type' => 'hidden',
                    'element_attributes' => array(
                        array(
                            // Forced for Masonry course format.
                            1 => new lang_string('hiddensectionsinvisible')
                        )
                    ),
                ),
                'coursedisplay' => array(
                    'label' => 'hidden2',
                    'element_type' => 'hidden',
                    'element_attributes' => array(
                        array(
                            COURSE_DISPLAY_SINGLEPAGE => new lang_string('coursedisplay_single')
                            // Disabled for Masonry course format.
                            // COURSE_DISPLAY_MULTIPAGE => new lang_string('coursedisplay_multi').
                        )
                    ),
                ),
                'borderwidth' => array(
                    'label' => get_string('borderwidth', 'format_masonry'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(0 => '0', 1 => '1', 2 => '2')
                    ),
                ),
                'bordercolor' => array(
                    'label' => get_string('bordercolor', 'format_masonry'),
                    'element_type' => 'text',
                    'element_type' => 'hidden'
                ),
                'backcolor' => array(
                    'label' => get_string('bordercolor', 'format_masonry'),
                    'element_type' => 'masonrycolorpicker',
                    'element_attributes' => array(array('value' => $courseformatoptions['bordercolor']['default']))
                )

            );
            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseoptionsedit);
        }
        return $courseformatoptions;
    }

    /**
     * Adds format options elements to the course/section edit form.
     *
     * This function is called from {@link course_edit_form::definition_after_data()}.
     *
     * @param MoodleQuickForm $mform form the elements are added to.
     * @param bool $forsection 'true' if this is a section edit form, 'false' if this is course edit form.
     * @return array array of references to the added form elements.
     */
    public function create_edit_form_elements(&$mform, $forsection = false) {
        global $CFG;
        MoodleQuickForm::registerElementType(
            "masonrycolorpicker",
            "$CFG->dirroot/course/format/masonry/colorpicker.php",
            "MoodleQuickForm_colorpicker");
        return parent::create_edit_form_elements($mform, $forsection);
    }


    /**
     * Updates format options for a course
     *
     * @param stdClass|array $data return value from {@link moodleform::get_data()} or array with data
     * @param stdClass $oldcourse if this function is called from {@link update_course()}
     *     this object contains information about the course before update
     * @return bool whether there were any changes to the options values
     */
    public function update_course_format_options($data, $oldcourse = null) {
        if ($oldcourse !== null) {
            $data->bordercolor = $data->backcolor;
            return parent::update_course_format_options($data, $oldcourse);
        }
        return $this->update_format_options($data);
    }

    /**
     * Prepares the templateable object to display section name
     *
     * @param \section_info|\stdClass $section
     * @param bool $linkifneeded
     * @param bool $editable
     * @param null|lang_string|string $edithint
     * @param null|lang_string|string $editlabel
     * @return \core\output\inplace_editable
     */
    public function inplace_editable_render_section_name($section, $linkifneeded = true,
                                                         $editable = null, $edithint = null, $editlabel = null) {
        if (empty($edithint)) {
            $edithint = new lang_string('editsectionname', 'format_topics');
        }
        if (empty($editlabel)) {
            $title = get_section_name($section->course, $section);
            $editlabel = new lang_string('newsectionname', 'format_topics', $title);
        }
        return parent::inplace_editable_render_section_name($section, $linkifneeded, $editable, $edithint, $editlabel);
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
    global $DB, $CFG;
    require_once($CFG->dirroot . '/course/lib.php');
    if ($itemtype === 'sectionname' || $itemtype === 'sectionnamenl') {
        $section = $DB->get_record_sql(
            'SELECT s.* FROM {course_sections} s JOIN {course} c ON s.course = c.id WHERE s.id = ? AND c.format = ?',
            array($itemid, 'masonry'), MUST_EXIST);
        return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
    }
}