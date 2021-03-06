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
 * Course format masonry privacy tests.
 *
 * @package   format_masonry
 * @copyright 2017 eWallah.net <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_masonry\privacy;

defined('MOODLE_INTERNAL') || die();

use \core_privacy\tests\provider_testcase;

/**
 * Course format masonry privacy tests.
 *
 * @package   format_masonry
 * @copyright 2017 eWallah.net <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass format_masonry\privacy\provider
 */
class privacy_testcase extends provider_testcase {

    /**
     * Test returning metadata.
     * @covers format_masonry\privacy\provider
     */
    public function test_get_metadata() {
        $collection = new \core_privacy\local\metadata\collection('format_masonry');
        $reason = \format_masonry\privacy\provider::get_reason($collection);
        $this->assertEquals($reason, 'privacy:metadata');
        $this->assertStringContainsString('does not store', get_string($reason, 'format_masonry'));
    }
}