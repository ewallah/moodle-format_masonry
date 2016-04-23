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
 * Settings used by the animbuttons format
 *
 * @package    format_masonry
 * @copyright  2013 Renaat Debleu www.eWallah.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or late
 **/

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    global $CFG;
    // Brick background colour setting.
    if (empty($CFG->format_masonry_defaultbackgroundcolor)) {
        $CFG->format_masonry_defaultbackgroundcolor = '#F9F9F9';
    }
    $name = 'format_masonry_defaultbackgroundcolor';
    $title = get_string('defaultcolor', 'format_masonry');
    $description = get_string('defaultcolordesc', 'format_masonry');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '#F9F9F9');
    $settings->add($setting);

    // Brick border colour setting.
    if (empty($CFG->format_masonry_defaultbordercolor)) {
        $CFG->format_masonry_defaultbordercolor = '#9A9B9C';
    }
    $name = 'format_masonry_defaultbordercolor';
    $title = get_string('defaultbordercolor', 'format_masonry');
    $description = get_string('defaultbordercolordesc', 'format_masonry');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '#9A9B9C');
    $settings->add($setting);
}
