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
 * @copyright 2013-2024 eWallah.net
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_configcolourpicker(
            'format_masonry/defaultbackgroundcolor',
            get_string('defaultcolor', 'format_masonry'),
            get_string('defaultcolordesc', 'format_masonry'),
            '#F9F9F9'
        ));
    $settings->add(new admin_setting_configcolourpicker(
        'format_masonry/defaultbordercolor',
        get_string('defaultbordercolor', 'format_masonry'),
        get_string('defaultbordercolordesc', 'format_masonry'),
        '#9A9B9C'
    ));
}
