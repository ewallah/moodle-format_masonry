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
 * format_masonry related unit tests
 *
 * @package   format_masonry
 * @copyright 2013-2024 eWallah.net
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_masonry;

use advanced_testcase;
use context_course;
use stdClass;


/**
 * format_masonry related unit tests
 *
 * @package   format_masonry
 * @copyright 2013-2024 eWallah.net
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class masonry_test extends advanced_testcase {
    /** @var stdClass Course. */
    private $course;

    /**
     * Load required classes.
     */
    public function setUp(): void {
        global $CFG, $DB;
        parent::setUp();
        $this->resetAfterTest(true);
        $CFG->enablecompletion = true;
        $CFG->enableavailability = true;
        $gen = $this->getDataGenerator();
        $params = ['format' => 'masonry', 'numsections' => 6, 'startdate' => time() - 3000,
                   'enablecompletion' => 1, 'showactivitydates' => true, ];
        $course = $gen->create_course($params, ['createsections' => true]);
        $DB->set_field('course', 'groupmode', SEPARATEGROUPS);
        $DB->set_field('course', 'groupmodeforce', 1);

        $group1 = $gen->create_group(['courseid' => $course->id])->id;
        $group2 = $gen->create_group(['courseid' => $course->id])->id;
        $user = $gen->create_and_enrol($course, 'student');
        groups_add_member($group1, $user->id);
        $assign = $gen->create_module(
            'assign',
            ['name' => "Test assign 1", 'course' => $course->id, 'section' => 1, 'completion' => 1]
        );
        $not1 = '{"op":"|","show":true,"c":[{"type":"group","id":' . $group1 . '}]}';
        $not2 = '{"op":"|","show":true,"c":[{"type":"group","id":' . $group1 . '}, {"type":"group","id":' . $group2 . '}]}';

        $modcontext = get_coursemodule_from_instance('assign', $assign->id, $course->id);
        $DB->set_field('course_modules', 'availability', $not1, ['id' => $modcontext->id]);
        $section = $DB->get_field('course_sections', 'id', ['course' => $course->id, 'section' => 2]);
        $DB->set_field('course_sections', 'availability', $not1, ['id' => $section]);
        $section = $DB->get_field('course_sections', 'id', ['course' => $course->id, 'section' => 1]);
        $DB->set_field('course_sections', 'availability', $not2, ['id' => $section]);

        $page = $gen->get_plugin_generator('mod_page')->create_instance(['course' => $course->id, 'section' => 1]);
        $modcontext = get_coursemodule_from_instance('page', $page->id, $course->id);
        $DB->set_field('course_modules', 'availability', $not2, ['id' => $modcontext->id]);
        $gen->get_plugin_generator('mod_page')->create_instance(['course' => $course->id, 'section' => 2]);
        $gen->get_plugin_generator('mod_page')->create_instance(['course' => $course->id, 'section' => 3]);
        $gen->get_plugin_generator('mod_page')->create_instance(['course' => $course->id, 'section' => 4]);
        $gen->get_plugin_generator('mod_label')->create_instance(['course' => $course->id, 'section' => 5]);
        $gen->get_plugin_generator('mod_forum')->create_instance(['course' => $course->id, 'section' => 6]);
        $gen->get_plugin_generator('mod_forum')->create_instance(['course' => $course->id, 'section' => 7]);
        $this->course = $course;
    }

    /**
     * Tests for format_masonry::get_section_name method with default section names.
     * @covers \format_masonry
     */
    public function test_get_section_name(): void {
        $sections = get_fast_modinfo($this->course)->get_section_info_all();
        $courseformat = course_get_format($this->course);
        foreach ($sections as $section) {
            // Assert that with unmodified section names, get_section_name returns the same result as get_default_section_name.
            $this->assertEquals($courseformat->get_default_section_name($section), $courseformat->get_section_name($section));
            if ($section->section == 0) {
                $sectionname = get_string('section0name', 'format_masonry');
                $this->assertEquals($sectionname, $courseformat->get_default_section_name($section));
            } else {
                $sectionname = get_string('sectionname', 'format_masonry') . ' ' . $section->section;
                $this->assertEquals($sectionname, $courseformat->get_default_section_name($section));
            }
            $this->assertNotEmpty($courseformat->inplace_editable_render_section_name($section));
        }
    }

    /**
     * Tests for format_masonry::get_section_name method with modified section names.
     * @covers \format_masonry
     */
    public function test_get_section_name_customised(): void {
        global $DB;
        $coursesections = $DB->get_records('course_sections', ['course' => $this->course->id]);
        // Modify section names.
        $customname = "Custom Section";
        foreach ($coursesections as $section) {
            $section->name = "$customname $section->section";
            $DB->update_record('course_sections', $section);
        }

        // Requery updated section names then test get_section_name.
        $sections = get_fast_modinfo($this->course)->get_section_info_all();
        $courseformat = course_get_format($this->course);
        foreach ($sections as $section) {
            // Assert that with modified section names, get_section_name returns the modified section name.
            $this->assertEquals($section->name, $courseformat->get_section_name($section));
        }
    }

    /**
     * Test get_default_course_enddate.
     * @covers \format_masonry
     */
    public function test_default_course_enddate(): void {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/course/tests/fixtures/testable_course_edit_form.php');
        $this->setTimezone('UTC');
        $category = $DB->get_record('course_categories', ['id' => $this->course->category]);

        $args = [
            'course' => $this->course,
            'category' => $category,
            'editoroptions' => [
                'context' => context_course::instance($this->course->id),
                'subdirs' => 0,
            ],
            'returnto' => new \moodle_url('/'),
            'returnurl' => new \moodle_url('/'),
        ];

        $courseform = new \testable_course_edit_form(null, $args);
        $courseform->definition_after_data();
        $enddate = time() - 3000 + (int)get_config('moodlecourse', 'courseduration');
        $masonryformat = course_get_format($this->course->id);
        $form = $courseform->get_quick_form();
        $this->assertGreaterThanOrEqual($masonryformat->get_default_course_enddate($form), $enddate);
        $format = course_get_format($this->course);
        $format->create_edit_form_elements($form, $this->course);
        $format->create_edit_form_elements($form, null);
        $this->assertCount(6, $format->course_format_options());
    }

    /**
     * Test renderer.
     * @covers \format_masonry\output\renderer
     * @covers \format_masonry\output\courseformat\content
     * @covers \format_masonry\output\courseformat\content\section
     * @covers \format_masonry\output\courseformat\content\section\controlmenu
     * @covers \format_masonry\output\courseformat\content\section\availability
     * @covers \format_masonry\output\courseformat\content\cm\availability
     */
    public function test_renderer(): void {
        global $PAGE, $USER;
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $generator->enrol_user($USER->id, $this->course->id, 5);
        $USER->editing = true;
        set_section_visible($this->course->id, 2, 0);
        $page = new \moodle_page();
        $page->set_course($this->course);
        $page->set_pagelayout('standard');
        $page->set_pagetype('course-view');
        $page->set_url('/course/view.php?id=' . $this->course->id);
        $PAGE->set_url('/course/view.php?id=' . $this->course->id);
        $page->requires->js_init_call(
            'M.masonry.init',
            [[
            'node' => '.masonry', 'itemSelector' => '.masonry-brick', 'columnWidth' => 1, 'isRTL' => right_to_left(), ], ],
            false,
            ['name' => 'course_format_masonry', 'fullpath' => '/course/format/masonry/format.js',
            'requires' => ['base',
            'node',
            'transition',
            'event',
            'io-base',
            'moodle-core-io'],
            ]
        );
        $renderer = new \format_masonry\output\renderer($PAGE, null);
        $modinfo = get_fast_modinfo($this->course);
        $section = $modinfo->get_section_info(0);
        $this->assertStringContainsString('General', $renderer->section_title($section, $this->course));
        $section = $modinfo->get_section_info(1);
        $this->assertStringContainsString('Topic 1', $renderer->section_title($section, $this->course));
        $section = $modinfo->get_section_info(2);
        $this->assertStringContainsString('Topic 2', $renderer->section_title_without_link($section, $this->course));
        set_section_visible($this->course->id, 2, 0);
        $this->assertStringContainsString('Topic 2', $renderer->section_title_without_link($section, $this->course));
        $format = course_get_format($this->course);
        $outputclass = $format->get_output_classname('content');
        $widget = new $outputclass($format);
        $this->assertEquals('', $renderer->render($widget));
        $format = course_get_format($this->course->id);
        $modinfo = $format->get_modinfo();
        $sections = $modinfo->get_section_info_all();
        foreach ($sections as $section) {
            $cmb = new \format_masonry\output\courseformat\content\section($format, $section);
            $cmb->export_for_template($renderer);
        }
        $this->setUser($generator->create_and_enrol($this->course, 'student'));
        foreach ($sections as $section) {
            $cmb = new \format_masonry\output\courseformat\content\section($format, $section);
            $cmb->export_for_template($renderer);
        }
    }

    /**
     * Test format.
     * @covers \format_masonry
     * @covers \format_masonry\output\renderer
     * @covers \format_masonry\output\courseformat\content
     * @covers \format_masonry\output\courseformat\content\section
     * @covers \format_masonry\output\courseformat\content\section\controlmenu
     * @covers \format_masonry\output\courseformat\content\section\availability
     * @covers \format_masonry\output\courseformat\content\cm\availability
     */
    public function test_format(): void {
        global $CFG, $PAGE, $USER;
        $format = course_get_format($this->course);
        $this->assertEquals('masonry', $format->get_format());
        $this->setAdminUser();
        $USER->editing = false;
        $PAGE->set_course($this->course);
        $PAGE->set_context(context_course::instance($this->course->id));
        $PAGE->get_renderer('core', 'course');
        $PAGE->set_url('/course/view.php?id=' . $this->course->id);
        $this->assertInstanceOf('format_masonry\output\renderer', $format->get_renderer($PAGE));
        $course = $this->course;
        $_POST['sesskey'] = sesskey();
        ob_start();
        include_once($CFG->dirroot . '/course/format/masonry/format.php');
        ob_end_clean();
        $this->assertNotEmpty($course);
        $this->assertNotEmpty($this->course);
        $USER->editing = true;
        ob_start();
        include_once($CFG->dirroot . '/course/format/masonry/format.php');
        ob_end_clean();
    }

    /**
     * Test format editing.
     * @covers \format_masonry
     * @covers \format_masonry\output\renderer
     * @covers \format_masonry\output\courseformat\content
     * @covers \format_masonry\output\courseformat\content\section
     * @covers \format_masonry\output\courseformat\content\section\controlmenu
     * @covers \format_masonry\output\courseformat\content\section\availability
     * @covers \format_masonry\output\courseformat\content\cm\availability
     */
    public function test_format_editing(): void {
        global $CFG, $PAGE, $USER;
        $course = $this->course;
        $format = course_get_format($course);
        $this->assertEquals('masonry', $format->get_format());
        $this->setAdminUser();
        $USER->editing = true;
        $PAGE->set_context(context_course::instance($this->course->id));
        $PAGE->get_renderer('core', 'course');
        $this->assertInstanceOf('format_masonry\output\renderer', $format->get_renderer($PAGE));
        sesskey();
        $_POST['marker'] = 2;
        ob_start();
        include_once($CFG->dirroot . '/course/format/masonry/format.php');
        ob_end_clean();
        $this->assertEquals($course->fullname, $this->course->fullname);
        $modinfo = $format->get_modinfo();
        $sections = $modinfo->get_section_info_all();
        $section = new stdClass();
        $section->section = $sections[1]->section;
        $format->section_action($section, 'hide', 1);
    }

    /**
     * Test other.
     * @covers \format_masonry
     * @covers \format_masonry\output\renderer
     * @covers \format_masonry\output\courseformat\content
     * @covers \format_masonry\output\courseformat\content\section
     * @covers \format_masonry\output\courseformat\content\section\controlmenu
     * @covers \format_masonry\output\courseformat\content\section\availability
     * @covers \format_masonry\output\courseformat\content\cm\availability
     */
    public function test_other(): void {
        $this->setAdminUser();
        $format = course_get_format($this->course);
        $section = $format->get_modinfo()->get_section_info_all()[1];
        $data = new stdClass();
        $data->bordercolor = '#FFF';
        $data->backcolor = '#000';
        $format->update_course_format_options($data, $this->course);
        $this->assertCount(6, $format->course_format_options());
        $this->assertTrue($format->allow_stealth_module_visibility(null, $section));
        $this->assertTrue($format->uses_sections());
        $this->assertFalse($format->uses_indentation());
        $this->assertTrue($format->supports_components());
        $this->assertTrue($format->can_delete_section($section));
        $this->assertFalse($format->uses_course_index());
        $this->assertCount(6, $format->get_config_for_external());
        $this->assertCount(2, $format->ajax_section_move());
        $this->assertTrue($format->supports_ajax()->capable);
    }
}
