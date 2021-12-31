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
 * Class to render a course module inside masonry format.
 *
 * @package   format_masonry
 * @copyright 2021 Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_masonry\output\courseformat\content;

use core_courseformat\output\local\content\cm as cm_baseclass;
use cm_info;
use context_module;
use core\activity_dates;
use core_completion\cm_completion_details;
use core_courseformat\base as course_format;
use core_course\output\activity_information;
use renderable;
use section_info;
use stdClass;
use templatable;
use \core_availability\info_module;

/**
 * Base class to render a course module inside masonry format.
 *
 * @package   format_masonry
 * @copyright 2021 Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cm extends cm_baseclass {

    /**
     * Constructor.
     *
     * @param course_format $format the course format
     * @param section_info $section the section info
     * @param cm_info $mod the course module ionfo
     * @param array $displayoptions optional extra display options
     */
    public function __construct(course_format $format, section_info $section, cm_info $mod, array $displayoptions = []) {
        $this->format = $format;
        $this->section = $section;
        $this->mod = $mod;
        $this->displayoptions = $displayoptions;

        $this->load_classes();

        // Get the necessary classes.
        $this->availabilityclass = $format->get_output_classname('content\\cm\\availability');
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(\renderer_base $output): stdClass {
        global $USER;

        $format = $this->format;
        $mod = $this->mod;
        $displayoptions = $this->displayoptions;
        $course = $mod->get_course();

        // Fetch completion details.
        $showcompletion = $course->showcompletionconditions == COMPLETION_SHOW_CONDITIONS;
        $completiondetails = cm_completion_details::get_instance($mod, $USER->id, $showcompletion);

        // Fetch activity dates.
        $activitydates = [];
        if ($course->showactivitydates) {
            $activitydates = activity_dates::get_dates_for_module($mod, $USER->id);
        }

        $displayoptions['linkclasses'] = $this->get_link_classes();
        $displayoptions['textclasses'] = $this->get_text_classes();

        // Grouping activity.
        $groupinglabel = $mod->get_grouping_label($displayoptions['textclasses']);

        $activityinfodata = (object) ['hasdates' => false, 'hascompletion' => false];
        $showcompletioninfo = $completiondetails->has_completion() && ($showcompletion ||
                        (!$completiondetails->is_automatic() && $completiondetails->show_manual_completion()));
        if ($showcompletioninfo || !empty($activitydates)) {
            $activityinfo = new activity_information($mod, $completiondetails, $activitydates);
            $activityinfodata = $activityinfo->export_for_template($output);
        }

        // Mod availability.
        $availability = new $this->availabilityclass(
            $format,
            $this->section,
            $mod,
            $this->displayoptions
        );

        // TODO: Labels.
        $modavailability = $availability->export_for_template($output);

        $data = (object)[
            'cmname' => ['displayvalue' =>
                $output->pix_icon('icon', $mod->modname, $mod->modname) . ' ' . \html_writer::link($mod->url, $mod->name)],
            'grouping' => $groupinglabel,
            'afterlink' => $mod->afterlink,
            'altcontent' => $mod->get_formatted_content(['overflowdiv' => true, 'noclean' => true]),
            'modavailability' => $mod->visible ? $modavailability : null,
            'modname' => get_string('pluginname', 'mod_' . $mod->modname),
            'url' => $mod->url,
            'activityinfo' => $activityinfodata,
            'activityname' => $mod->get_formatted_name(),
            'textclasses' => $displayoptions['textclasses'],
            'classlist' => [], 
            'altcontent' => (empty($data->altcontent)) ? false : $data->altcontent,
            'hasname' => !empty($data->cmname['displayvalue']),
            'hasurl' => !empty($data->url),
            'modhiddenfromstudents' => !$mod->visible,
            'modstealth' => $mod->is_stealth(),
            'modlinline' => ($mod->modname == 'label' && !$modavailability->hasmodavailability &&
               !$activityinfodata->hascompletion && !isset($data->modhiddenfromstudents) && !isset($data->modstealth))];

        return $data;
    }
}
