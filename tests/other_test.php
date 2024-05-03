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
 * format_masonry related other unit tests
 *
 * @package   format_masonry
 * @copyright 2013-2024 eWallah.net
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_masonry;

/**
 * format_masonry related other unit tests
 *
 * @package   format_masonry
 * @copyright 2013-2024 eWallah.net
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class other_test extends \advanced_testcase {
    /**
     * Test upgrade.
     * @coversNothing
     */
    public function test_upgrade(): void {
        global $CFG;
        $this->resetAfterTest(true);
        require_once($CFG->dirroot . '/course/format/masonry/db/upgrade.php');
        require_once($CFG->libdir . '/upgradelib.php');
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage('Cannot downgrade');
        xmldb_format_masonry_upgrade(time());
    }

    /**
     * Settings testcase.
     * @coversNothing
     */
    public function test_settings(): void {
        global $ADMIN, $CFG, $USER;
        require_once($CFG->libdir . '/adminlib.php');
        $this->resetAfterTest(true);
        $this->setAdminUser();
        $ADMIN = $USER;
        $ADMIN->fulltree = true;
        $settings = new \admin_settingpage('test', 'test');
        require_once($CFG->dirroot . '/course/format/masonry/settings.php');
        $this->assertNotEmpty($settings);
    }

    /**
     * Inplace edit.
     * @covers \format_masonry
     */
    public function test_inplace_edit(): void {
        global $CFG;
        require_once($CFG->libdir . '/adminlib.php');
        format_masonry_inplace_editable('section', 1, 'newvalue');
    }
}
