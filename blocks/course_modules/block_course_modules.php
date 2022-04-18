<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Block course_modules is defined here.
 *
 * @package     block_course_modules
 * @copyright   2022 Nilesh Pathade <nileshnpathade@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_course_modules extends block_base {

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_course_modules');
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {
        global $CFG, $COURSE, $DB, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        } else {
            $text = '';
            $modinfo = get_fast_modinfo($COURSE->id);
            foreach ($modinfo->instances as $abc) {
                foreach ($abc as $cmd) {
                    $modulecompletion = $DB->get_record('course_modules_completion',
                        array('coursemoduleid' => $cmd->id, 'userid' => $USER->id));
                    $status = "Not completed";
                    if ($modulecompletion->completionstate == 1) {
                        $status = "Completed";
                    } else if ($modulecompletion->completionstate == 2) {
                        $status = "Completed With Passed";
                    } else if ($modulecompletion->completionstate == 3) {
                        $status = "Completed With Failed";
                    }
                    $moduleurl = $CFG->wwwroot.'/mod/'.$cmd->modname.'/view.php?id='.$cmd->id;
                    $text .= $cmd->id.' <a href="'.$moduleurl.'"> '.$cmd->name.'</a> ('.date("d-M-Y", $cmd->added).')';
                    $text .= '  <b>'.$status.'</b> <br/>';
                }
            }
            $this->content->text = $text;
        }

        return $this->content;
    }

    /**
     * Defines configuration data.
     *
     * The function is called immediately after init().
     */
    public function specialization() {

        // Load user defined title and make sure it's never empty.
        if (empty($this->config->title)) {
            $this->title = get_string('blocktitle', 'block_course_modules');
        } else {
            $this->title = $this->config->title;
        }
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats() {
        return array('course' => true, 'mod' => false, 'my' => false);
    }
}
