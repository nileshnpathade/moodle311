<?php
	require_once(dirname(__FILE__) . '/../../config.php');
	$courseid = optional_param('courseid', '', PARAM_INT);
	$groupid = optional_param('token', 0, PARAM_INT);
	global $DB, $USER, $CFG;
	
	$systemcontext = context_system::instance();
	//get the admin layout
	$PAGE->set_pagelayout('admin');
	$PAGE->set_context($systemcontext);
	$course = array();
	require_login();

$groups = $DB->get_records('groups', array('courseid' => $courseid));
?>
<option> Select Group </option>
<?php
	foreach ($groups as $key => $group) { ?>
	<option <?php if($groupid == $group->id) { echo "selected"; } ?> value="<?php echo $group->id; ?>"> <?php echo $group->name; ?>  </option>
<?php }