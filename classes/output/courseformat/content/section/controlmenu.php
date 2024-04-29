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
 * Format masonry controlmentu class.
 *
 * @package    format_masonry
 * @copyright  2024 eWallah.net
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_masonry\output\courseformat\content\section;

use core_courseformat\output\local\content\section\controlmenu as controlmenu_base;

/**
 * Format masonry controlmentu class.
 *
 * @package    format_masonry
 * @copyright  2024 eWallah.net
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class controlmenu extends controlmenu_base {

    /**
     * Generate the edit control items of a section.
     *
     * @return array of edit control items
     */
    public function section_control_items() {
        $controls = parent::section_control_items();
        unset($controls['view']);
        unset($controls['permalink']);
        return $controls;
    }
}
