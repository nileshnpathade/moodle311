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
 * virtual module version information
 *
 * @package mod_virtual
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/virtual/lib.php');
require_once($CFG->dirroot.'/mod/virtual/locallib.php');
require_once($CFG->libdir.'/completionlib.php');

$id      = optional_param('id', 0, PARAM_INT); // Course Module ID
$p       = optional_param('p', 0, PARAM_INT);  // virtual instance ID
$inpopup = optional_param('inpopup', 0, PARAM_BOOL);

if ($p) {
    if (!$virtual = $DB->get_record('virtual', array('id'=>$p))) {
        print_error('invalidaccessparameter');
    }
    $cm = get_coursemodule_from_instance('virtual', $virtual->id, $virtual->course, false, MUST_EXIST);

} else {
    if (!$cm = get_coursemodule_from_id('virtual', $id)) {
        print_error('invalidcoursemodule');
    }
    $virtual = $DB->get_record('virtual', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/virtual:view', $context);

$PAGE->set_url('/mod/virtual/view.php', array('id' => $cm->id));

$options = empty($virtual->displayoptions) ? [] : (array) unserialize_array($virtual->displayoptions);

if ($inpopup and $virtual->display == RESOURCELIB_DISPLAY_POPUP) {
    $PAGE->set_virtuallayout('popup');
    $PAGE->set_title($course->shortname.': '.$virtual->name);
    $PAGE->set_heading($course->fullname);
} else {
    $PAGE->set_title($course->shortname.': '.$virtual->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($virtual);
}

echo $OUTPUT->header();
$url = getLabsData($virtual->url);
?>
<iframe src="<?php echo $url; ?>" width="100%" height="950px"></iframe>
<?php

echo $OUTPUT->footer();
