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
 * Version details
 *
 * @package    course format
 * @subpackage masonry
 * @copyright  2012 Renaat Debleu (www.eWallah.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2013010200;         // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2012110900;         // Requires this Moodle version (2.4)
$plugin->component = 'format_masonry';   // Full name of the plugin (used for diagnostics)
$plugin->maturity = MATURITY_BETA;
$plugin->dependencies = array(
    'format_topics' => 2012112900,
);
