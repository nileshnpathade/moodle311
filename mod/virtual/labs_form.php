<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    //  It must be included from a Moodle page.
}
require_once("$CFG->libdir/formslib.php");
require_once('../../config.php'); 
require_once($CFG->libdir.'/filelib.php');
global $DB;

class labs_form extends moodleform 
{
     public function definition() {
        global $CFG;
        global $DB;
        $mform = $this->_form; // Don't forget the underscore! 

        $mform->addElement('text', 'labname', 'Lab Name') ;
        $mform->setType('labname', PARAM_RAW);
        $mform->addRule('labname', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'laburl', 'Lab URL') ;
        $mform->setType('laburl', PARAM_RAW);
        $mform->addRule('laburl', get_string('required'), 'required', null, 'client');
        
        $this->add_action_buttons(true, 'Add Lab');

    }

    //Custom validation should be added here
    function validation($data, $files) {
        global $DB;
        $error = array();

        if (filter_var($data['laburl'], FILTER_VALIDATE_URL) === FALSE) {
            $error['laburl'] = 'Invalid URL format';
        }

        $checklabs = $DB->get_records('lab', array('labname' => $data['labname']));
        if(count($checklabs) >= 1){
            $error['labname'] = 'Already available Lab';
        }
        return $error;
    }   

}