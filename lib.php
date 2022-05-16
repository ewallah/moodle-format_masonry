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
require_once($CFG->dirroot . '/course/format/topics/lib.php');

/**
 * Main class for the masonry course format
 *
 * @package    format_masonry
 * @copyright  Renaat Debleu (www.eWallah.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_masonry extends format_topics {

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
     * The URL to use for the specified course (with section)
     *
     * @param int|stdClass $section Section object from database or just field course_sections.section
     *     if omitted the course view page is returned
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
                'help_component' => 'format_masonry']];
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
            $courseformatoptions = [
                'numsections' => ['default' => $courseconfig->numsections, 'type' => PARAM_INT],
                'hiddensections' => ['type' => PARAM_INT, 'default' => 1],
                'coursedisplay' => ['type' => PARAM_INT, 'default' => 1],
                'borderwidth' => ['type' => PARAM_INT, 'default' => 1],
                'bordercolor' => ['type' => PARAM_TEXT, 'default' => '#F0F0F0'],
                'backcolor' => ['type' => PARAM_TEXT, 'default' => '#F0F0F0']];
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
                    'element_attributes' => [$sectionmenu]],
                'hiddensections' => [
                    'label' => 'hidden1',
                    'element_type' => 'hidden',
                    'element_attributes' => [[1 => new \lang_string('hiddensectionsinvisible')]]],
                'coursedisplay' => [
                    'label' => 'hidden2',
                    'element_type' => 'hidden',
                    'element_attributes' => [[COURSE_DISPLAY_SINGLEPAGE => new \lang_string('coursedisplay_single')]]],
                'borderwidth' => [
                    'label' => new \lang_string('borderwidth', 'format_masonry'),
                    'element_type' => 'select',
                    'element_attributes' => [[0 => '0', 1 => '1', 2 => '2']]],
                'bordercolor' => [
                    'label' => new \lang_string('bordercolor', 'format_masonry'),
                    'element_type' => 'text',
                    'element_type' => 'hidden'],
                'backcolor' => [
                    'label' => new \lang_string('bordercolor', 'format_masonry'),
                    'element_type' => 'text',
                    'help' => 'colordisplay',
                    'help_component' => 'format_masonry',
                    'element_attributes' => [['value' => $courseformatoptions['bordercolor']['default']]]]];
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
     * Returns whether this course format allows the activity to
     * have "triple visibility state" - visible always, hidden on course page but available, hidden.
     *
     * @param stdClass|cm_info $cm course module (may be null if we are displaying a form for adding a module)
     * @param stdClass|section_info $section section where this module is located or will be added to
     * @return bool
     */
    public function allow_stealth_module_visibility($cm, $section) {
        return true;
    }

    /**
     * This course format does not support activity indentation.
     *
     * @return bool if the course format uses indentation.
     */
    public function uses_indentation(): bool {
        return false;
    }

    /**
     * This course format does not support drag and drop.
     *
     * @return bool if the course format uses components.
     */
    public function supports_components() {
        return false;
    }

    /**
     * This course format does not support course index.
     *
     * @return bool if the course format uses course index.
     */
    public function uses_course_index() {
        return false;
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
