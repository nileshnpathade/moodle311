<?php
	require_once(dirname(__FILE__) . '/../../config.php');
	$courseid = optional_param('courseid', '', PARAM_INT);
	$groupid = optional_param('groupname', '', PARAM_INT);
	$page = optional_param("page", 1, PARAM_INT);
	$length = optional_param("length", 1000, PARAM_INT);
	$status = optional_param("status", 0, PARAM_INT);
	$id = optional_param('id', -1, PARAM_INT);
	global $DB, $USER, $CFG;
	
	$systemcontext = context_system::instance();
	//get the admin layout
	$PAGE->set_pagelayout('admin');
	$PAGE->set_context($systemcontext);
	$course = array();
	require_login();
	$PAGE->set_url('/local/reports/users_courses_report.php');
	$PAGE->set_title('Users course completion Report');
	//Header and the navigation bar
	$PAGE->set_heading('Users course completion Report');
	
	if(!empty($courseid) && $courseid != ''){
		$coursedata = $DB->get_record('course', array('id' => $courseid));
		$PAGE->navbar->add($coursedata->fullname, '/course/view.php?id='.$coursedata->id);
	}
	$PAGE->navbar->add('Users course completion Report', 'users_courses_report.php');

	echo $OUTPUT->header();
	echo '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>';
	echo '<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" />
	<script src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
	<script src="//cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js"></script>
	<script src="//cdn.datatables.net/buttons/1.5.6/js/buttons.flash.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
	<script src="//cdn.datatables.net/buttons/1.5.6/js/buttons.html5.min.js"></script>
	<script src="//cdn.datatables.net/buttons/1.5.6/js/buttons.print.min.js"></script>';

 	$line = array();
	$coursemodules = array();
		$where = "  "; 
		$offset = ($page-1) * $length; 
		
		$users = $DB->get_records_sql('Select * FROM {user} as u where id != 1 && id !=	 2', array());
		$countrow = count($users);
		$total_pages = ceil($countrow / $length);
		
		$courses = $DB->get_records_sql('Select * FROM {course} as c Where c.id != 1', array());
		foreach($users as $user){
			$line['username'] = $user->firstname. " " . $user->lastname;
			$line['userid'] = $user->id;
			$data[] = $line;
		}
		
		//$data[] = $line;
	echo "<div  style='padding: 0px 15px;'> <h4> Course: ".$coursedata->fullname." </h4> </div>";
?>
	<style type="text/css">
		table.dataTable thead th {
				vertical-align: middle;
		}
	</style>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

	<div id="completion_report_wrapper" class="dataTables_wrapper no-footer">

	<table class="generaltable dataTable no-footer" id="completion_report" width="100%" role="grid" aria-describedby="completion_report_info" style="width: 100%;">
		<thead>
			<tr>
				<th> Users </th>
				<?php
					$c=0;
					foreach($courses as $course){
						echo "<th> $course->fullname </th>"; $c++;
					}
				?>
			</tr>
		</thead>
		<tbody>
			<?php
			
			foreach($data as $datarows){
					$j = 0;
					//foreach($datarows as $datarow){	
						//$completiondates = $DB->get_record_sql("SELECT * FROM {course_completions} WHERE course = ? AND userid = ? ", array($datarow->id, $user->id));
						//print_object($datarows['username']);
						echo "<tr>";			
							echo "<td>".$datarows['username']."</td>"; 
							foreach($courses as $course){

								$sql = "SELECT u.id userid, c.id as courseid
											FROM mdl_user u
											INNER JOIN mdl_role_assignments ra ON ra.userid = u.id
											INNER JOIN mdl_context ct ON ct.id = ra.contextid
											INNER JOIN mdl_course c ON c.id = ct.instanceid
											INNER JOIN mdl_role r ON r.id = ra.roleid
											WHERE r.id = 5 AND c.id=? AND u.id = ?";
								$ennrolledusers = $DB->get_records_sql($sql, array($course->id, $datarows['userid']));
								//print_object($ennrolledusers);
								if(!empty($ennrolledusers)){
									$completiondates = $DB->get_record_sql("SELECT * FROM {course_completions} WHERE course = ? AND userid = ? ", array($course->id, $datarows['userid']));
									if(!empty($ennrolledusers)){
										echo "<th><input type='hidden' value='".$course->id."' /> Completed </th>";		
									} else {
										echo "<th><input type='hidden' value='".$course->id."' /> Not Completed </th>";		
									}
									
								} else {
									echo "<th><input type='hidden' value='".$course->id."' /> Not Enrolled </th>";
								}
								
							}
							$j++;
						echo "</tr>";
					//}
				}
				//exit();
			?>
		</tbody>
		
	</table>

	<ul class="pagination float-right">
		    <li><a href="<?php echo "?courseid=".$courseid."&groupid=".$groupid."&page=1"; ?>">First</a></li>
		    <li class="<?php if($page <= 1){ echo 'disabled'; } ?>">
		        <a href="<?php if($page <= 1){ echo '#'; } else { echo "?courseid=".$courseid."&groupid=".$groupid."&page=".($page - 1); } ?>">Prev</a>
		    </li>
		    <?php 
		    	$i = 1;
		    	for($i = 1 ; $i <= $total_pages; $i++){
		    ?>
		    <li class="<?php if($i == $page){ echo 'disabled'; } ?>">
		        <a href="<?php echo "?courseid=".$courseid."&groupid=".$groupid."&page=".($i);  ?>"><?php echo $i; ?></a>
		    </li>
		    <?php  } ?>

		    <li class="<?php if($page >= $total_pages){ echo 'disabled'; } ?>">
		        <a href="<?php if($page >= $total_pages){ echo '#'; } else { echo "?courseid=".$courseid."&groupid=".$groupid."&page=".($page + 1); } ?>">Next</a>
		    </li>
		    <li><a href="?page=<?php echo $total_pages; ?>">Last</a></li>
		</ul>

</div>
	<?php


echo $OUTPUT->footer();
echo html_writer::script('
	$("#completion_report").DataTable( {
    dom: "Bfrtip",
   	buttons: [
        "csv", "excel"
    ],
    bPaginate: false,
    bInfo : false,
    initComplete: function () {
        this.api().columns().every( function () {
            var column = this;
            var select = $(`<select><option value=""></option></select>`)
                .appendTo( $(column.footer()).empty() )
                .on( "change", function () {            
                    var val =$(this).find("option:selected").text();            
                    column
                        .search( val ? "^"+val+"$" : "", true, false )
                        .draw();
  				});
                column.data().unique().sort().each( function ( d, j ) {
                    select.append("<option >"+d+"</option>")
                } );
            } )
        }
    } );
');



echo html_writer::script('
	function activityreport(courseid, token = 0){
		$.ajax({
			type: "POST",
			url: "ajax_activityreport.php?courseid="+courseid+"&token="+token,
			success: function(data){
				$("#groups").html(data);
			}
		});
	}
');
if(!empty($courseid)){
	echo html_writer::script('activityreport('.$courseid.', '.$groupid.');');	
}
