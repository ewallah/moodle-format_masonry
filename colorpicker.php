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
 * Colorpicker for the masonry course format.
 *
 * @package    format_masonry
 * @copyright  2016 Renaat Debleu (www.eWallah.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once("HTML/QuickForm/text.php");

/**
 * Colorpicker type form element
 *
 * HTML class for a colorpicker type element
 *
 * @package    format_masonry
 * @copyright  2013 Jamie Pratt
 * @author     Renaat Debleu - modified from ColourPicker by Jamie Pratt [thanks]
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleQuickForm_colorpicker extends HTML_QuickForm_text {

    /** @var string html for help button, if empty then no help */
    public $_helpbutton = '';

    /** @var bool if true label will be hidden */
    protected $_hiddenlabel = false;

    /**
     * Sets label to be hidden
     *
     * @param bool $hiddenlabel sets if label should be hidden
     */
    public function sethiddenlabel($hiddenlabel) {
        $this->_hiddenlabel = $hiddenlabel;
    }

    /**
     * Automatically generates and assigns an 'id' attribute for the element.
     *
     * Currently used to ensure that labels work on radio buttons and
     * checkboxes. Per idea of Alexander Radivanovich.
     * Overriden in moodleforms to remove qf_ prefix.
     *
     * @return void
     */
    public function tohtml() {
        global $PAGE, $OUTPUT;
        $id = $this->getAttribute('id');
        $PAGE->requires->js_init_call('M.util.init_colour_picker', [$id]);
        $content  = html_writer::start_tag('div', ['class' => 'form-colourpicker defaultsnext']);
        $content .= html_writer::tag('div',
            $OUTPUT->pix_icon('i/loading', get_string('loading', 'admin'), 'moodle', ['class' => 'loadingicon']),
            ['class' => 'admin_colourpicker clearfix']
        );
        $content .= html_writer::end_tag('div');
        $content .= '<input size="47" name="'.$this->getName().'" value="'.$this->getValue().'" id="'.$id.'" type="text" >';
        return $content;
    }

    /**
     * Automatically generates and assigns an 'id' attribute for the element.
     *
     * Currently used to ensure that labels work on radio buttons and
     * checkboxes. Per idea of Alexander Radivanovich.
     * Overriden in moodleforms to remove qf_ prefix.
     *
     * @return void
     */
    public function _generateid() {
        static $idx = 1;
        if (!$this->getAttribute('id')) {
            $this->updateAttributes(['id' => 'id_'. substr(md5(microtime() . $idx++), 0, 6)]);
        }
    }

    /**
     * set html for help button
     *
     * @param array $helpargs array of arguments to make a help button
     * @param string $function function name to call to get html
     */
    public function sethelpbutton($helpargs, $function='helpbutton') {
        debugging('component setHelpButton() is not used any more, please use $mform->setHelpButton() instead');
    }

    /**
     * get html for help button
     *
     * @return  string html for help button
     */
    public function gethelpbutton() {
        return $this->_helpbutton;
    }

    /**
     * Slightly different container template when frozen. Don't want to use a label tag
     * with a for attribute in that case for the element label but instead use a div.
     * Templates are defined in renderer constructor.
     *
     * @return string
     */
    public function getelementtemplatetype() {
        if ($this->_flagFrozen) {
            return 'static';
        } else {
            return 'default';
        }
    }

    /**
     * Checks input and challenged field
     *
     * @param string $data color to be verified
     * @return bool
     */
    public function verify($data) {
        // TODO : no verification yet.
        return $data;
        if (preg_match('/^#?([a-fA-F0-9]{3}){1,2}$/', $data)) {
            if (strpos($data, '#') !== 0) {
                $data = '#'.$data;
            }
            return $data;
        } else if (preg_match('/^[a-zA-Z]{3, 25}$/', $data)) {
            return $data;
        } else if (empty($data)) {
            return $this->defaultsetting;
        } else {
            return false;
        }
    }
}
