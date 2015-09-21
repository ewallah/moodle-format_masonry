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
 * @package    course format
 * @subpackage masonry
 * @copyright  2013 Renaat Debleu (www.eWallah.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("HTML/QuickForm/text.php");

/**
 * HTML class for a url type element
 *
 * @author Jamie Pratt
 * @access public
 */

class MoodleQuickForm_colorpicker extends HTML_QuickForm_text {

    public $_helpbutton = '';
    protected $_hiddenlabel = false;


    /**
     * Constructor
     *
     * @param string $elementName (optional) name of the colorpicker
     * @param string $elementLabel (optional) label
     * @param array $attributes (optional) Either a typical HTML attribute string
     *              or an associative array
     * @param array $options set of options to initalize colorpicker
     */
    function MoodleQuickForm_colorpicker($elementName=null, $elementLabel=null, $attributes=null, $options=null) {
        parent::HTML_QuickForm_text($elementName, $elementLabel, $attributes);
    }

    function setHiddenLabel($hiddenlabel) {
        $this->_hiddenlabel = $hiddenlabel;
    }

    function toHtml() {
        global $CFG, $COURSE, $USER, $PAGE, $OUTPUT;
        $id = $this->getAttribute('id');
        $PAGE->requires->js_init_call('M.util.init_colour_picker', array($id));
        $content  = html_writer::start_tag('div', array('class' => 'form-colourpicker defaultsnext'));
        $content .= html_writer::tag('div',
            $OUTPUT->pix_icon('i/loading', get_string('loading', 'admin'), 'moodle', array('class' => 'loadingicon')),
            array('class' => 'admin_colourpicker clearfix')
        );
        $content .= html_writer::end_tag('div');
        $content .= '<input size="7" name="'.$this->getName().'" value="'.$this->getValue().'" id="'.$id.'" type="text" >';
        return $content;
    }

    function _generateId() {
        static $idx = 1;
        if (!$this->getAttribute('id')) {
            $this->updateAttributes(array('id' => 'id_'. substr(md5(microtime() . $idx++), 0, 6)));
        }
    }

    function setHelpButton($helpbuttonargs, $function='helpbutton') {
        debugging('component setHelpButton() is not used any more, please use $mform->setHelpButton() instead');
    }

    function getHelpButton() {
        return $this->_helpbutton;
    }

    function getElementTemplateType() {
        if ($this->_flagFrozen) {
            return 'static';
        } else {
            return 'default';
        }
    }

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
