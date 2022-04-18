<?php
require_once('../../config.php'); 
require_once(dirname(__FILE__).'/labs_form.php');
global $CFG, $USER, $DB, $PAGE, $COURSE;
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/mod/virtual/labs.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading('Add labs');

$templatedata = new stdClass();
$args=array();
$mform = new labs_form(null,$args);
$mform->set_data($templatedata);
if ($mform->is_cancelled()) {

} else if ($fromform = $mform->get_data())  {
	// print_object($fromform); exit();
	$lab = new stdClass();
	$lab->labname =(isset($fromform->labname))? $fromform->labname:'';
	$lab->laburl =(isset($fromform->laburl))? $fromform->laburl:'';
	$inserted_id = $DB->insert_record('lab',$lab);
	if($inserted_id>0){
		redirect($CFG->wwwroot.'/mod/virtual/labs.php', 'Labs added successfully...', null, \core\output\notification::NOTIFY_SUCCESS);
	}
}
$PAGE->set_title('Add labs');
echo $OUTPUT->header();

$mform->display();
echo $OUTPUT->footer();