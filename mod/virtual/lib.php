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
 * @package mod_virtual
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * List of features supported in virtual module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function virtual_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function virtual_reset_userdata($data) {

    // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
    // See MDL-9367.

    return array();
}

/**
 * List the actions that correspond to a view of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = 'r' and edulevel = LEVEL_PARTICIPATING will
 *       be considered as view action.
 *
 * @return array
 */
function virtual_get_view_actions() {
    return array('view','view all');
}

/**
 * List the actions that correspond to a post of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = ('c' || 'u' || 'd') and edulevel = LEVEL_PARTICIPATING
 *       will be considered as post action.
 *
 * @return array
 */
function virtual_get_post_actions() {
    return array('update', 'add');
}

/**
 * Add virtual instance.
 * @param stdClass $data
 * @param mod_virtual_mod_form $mform
 * @return int new virtual instance id
 */
function virtual_add_instance($data, $mform = null) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    $data->id = $DB->insert_record('virtual', $data);

    // we need to use context now, so we need to make sure all needed info is already in db
   // $DB->set_field('course_modules', 'instance', $data->id, array('id'=>$cmid));
   // $context = context_module::instance($cmid);


    return $data->id;
}

/**
 * Update virtual instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function virtual_update_instance($data, $mform) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    $cmid        = $data->coursemodule;
    $draftitemid = $data->virtual['itemid'];

    $data->timemodified = time();
    $data->id           = $data->instance;
    
    $DB->update_record('virtual', $data);

    return true;
}

/**
 * Delete virtual instance.
 * @param int $id
 * @return bool true
 */
function virtual_delete_instance($id) {
    global $DB;

    if (!$virtual = $DB->get_record('virtual', array('id'=>$id))) {
        return false;
    }

    $cm = get_coursemodule_from_instance('virtual', $id);
    \core_completion\api::update_completion_date_event($cm->id, 'virtual', $id, null);

    // note: all context files are deleted automatically

    $DB->delete_records('virtual', array('id'=>$virtual->id));

    return true;
}


/**
 * Handle a file that has been uploaded
 * @param object $uploadinfo details of the file / content that has been uploaded
 * @return int instance id of the newly created mod
 */
function virtual_dndupload_handle($uploadinfo) {
    // Gather the required info.
    $data = new stdClass();
    $data->course = $uploadinfo->course->id;
    $data->name = $uploadinfo->displayname;
    $data->url = $uploadinfo->url;
    
    return virtual_add_instance($data, null);
}


function getLabsData($labno){
    global $CFG, $DB;
    $getlabs = $DB->get_record('lab', array('id' => $labno));
    return $getlabs->laburl;
}