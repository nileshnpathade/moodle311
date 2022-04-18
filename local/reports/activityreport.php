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
	$PAGE->set_url('/local/reports/activityreport.php');
	$PAGE->set_title('Activity Reports');
	//Header and the navigation bar
	$PAGE->set_heading('Activity Reports');
	
	if(!empty($courseid) && $courseid != ''){
		$coursedata = $DB->get_record('course', array('id' => $courseid));
		$PAGE->navbar->add($coursedata->fullname, '/course/view.php?id='.$coursedata->id);
	}
	$PAGE->navbar->add('Activity Reports', 'activityreport.php');

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
	if(!empty($coursedata->id)){
		$where = "  "; 
		$sqlgroup = " ";
		if(!empty($groupid) ){
			$sqlgroup = " 
				JOIN {groups} AS g ON g.courseid = c.id
				JOIN {groups_members} AS m ON g.id = m.groupid and m.userid = u.id";
			$where .= " AND g.id = ".$groupid; 
		}
		$offset = ($page-1) * $length; 

	 	$sql = "SELECT u.id, u.firstname, u.lastname,c.id as courseid, u.username, u.email FROM {user} u
				INNER JOIN {role_assignments} ra ON ra.userid = u.id
				INNER JOIN {context} ct ON ct.id = ra.contextid
				INNER JOIN {course} c ON c.id = ct.instanceid
				INNER JOIN {role} r ON r.id = ra.roleid
				INNER JOIN {course_categories} cc ON cc.id = c.category
				$sqlgroup
				WHERE r.id = 5 and c.id =? $where ";

		$recordssql = $sql." LIMIT $offset, $length ";

		$userdetails = $DB->get_records_sql($recordssql, array($coursedata->id));
		$recordssqlrecordscount = $DB->get_records_sql($sql, array($coursedata->id));
		$countrow = count($recordssqlrecordscount);
		$total_pages = ceil($countrow / $length);
		
		$activitycountrow = get_array_of_activities($coursedata->id);
		//print_object($activitycountrow);
		$activitycount = count($activitycountrow);
		foreach($userdetails as $user){
			$line[] = $user->firstname. " " . $user->lastname;
			//$course_modules = get_array_of_activities($user->courseid);
			$completiondates = $DB->get_record_sql("SELECT timecompleted FROM {course_completions} WHERE course = ? AND userid = ? ", array($coursedata->id, $user->id));
			$timecompleted = '-';
			if(!empty($completiondates->timecompleted)){
				$timecompleted = date('d/m/Y',  $completiondates->timecompleted);
			}
			$i = 0;  $h=4; 
			$line = array('');
			//print_object($course_modules);

			foreach ($activitycountrow as $key => $cm) {
					if(isset($cm->completion) && !empty($cm->completion)){


						$results = $DB->get_records_sql("SELECT * FROM {course_modules_completion} as cmc 
														INNER JOIN {user} as u ON u.id = cmc.userid 
														WHERE userid = ? AND coursemoduleid = ? ", array($user->id, $cm->cm));	
						
						$resultstatus = 'Not Completed';
						if(!empty($results)){
							$resultstatus = 'Completed';
						}

						$coursemodules[$i] = $cm->name;
						$line[0] = $user->firstname. " " . $user->lastname;
						$line[1] = $user->username;
						$line[2] = $user->email;
						$line[3] =  $timecompleted;
						$line[$h] = $resultstatus;
						
						$h++;
					}
					
				$i = $i + 4; 
				}
				
				if(!empty($completiondates->timecompleted)){
					$line[$i] = '<span style="color: green;"> Completed </span>';
				} else {
					$line[$i] = '<span style="color: red;"> Not Completed </span>';
				}
				
				$data[] = $line;
			}
	}

//exit();
	echo "<div  style='padding: 0px 15px;'> <h4> Course: ".$coursedata->fullname." </h4> </div>";
?>
	<style type="text/css">
		table.dataTable thead th {
				vertical-align: middle;
		}
	</style>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

	<div id="completion_report_wrapper" class="dataTables_wrapper no-footer">

	<div>
		<form action="activityreport.php" name="filter" id="filter" method="POST">
			<label>Select Course: </label>
			<select name="courseid" class="form-control" style="width: 25% !important;" onchange="activityreport(this.value)">
				<option> Select course </option>
				<?php 
					$courses = get_courses();
					foreach($courses as $course){
				?>
					<option <?php if($courseid == $course->id) { echo "selected"; } ?> value="<?php echo $course->id; ?>"> <?php echo $course->fullname; ?></option>
				<?php } ?>
			</select>
			<br>
			<label>Select Group: </label>
			<select name="groupname" class="form-control" style="width: 25% !important;" id="groups">
				<option> Select Group </option>
			</select>
			
			<input type="submit" name="submit" value="Filter" class="btn btn-primary">
		</form>
	</div>
	<br>
	<table class="generaltable dataTable no-footer" id="completion_report" width="100%" role="grid" aria-describedby="completion_report_info" style="width: 100%;">
		<thead>
			<tr>
				<th> Users </th>
				<th> Username </th>
				<th> Email </th>
				<th> Completion Date</th>
				<?php
				$c=0;
					foreach($coursemodules as $modules){
						echo "<th> $modules </th>"; $c++;
					}
					//echo $c;
				?>
				<th> Status</th>
			</tr>
		</thead>
		<tbody>
		<?php 
		foreach($data as $datarows){
			echo "<tr>";
				$j = 0;
				foreach($datarows as $datarow){						
					echo "<td>".$datarow."</td>"; 
					$j++;
				}
			echo "</tr>";
			}
		?>
		</tbody>
		<tfoot>
			<tr>
				<?php
				//echo $c;//exit();
				$rowcount = $c+4;
				if($countrow != 0){
					for($p=0; $p< $rowcount; $p++){
					?>
						<th scope="col"></th>
						<?php  } ?>
						
						<th scope="col">Status</th>
					<?php  
				} ?>
			</tr>
		</tfoot>
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
