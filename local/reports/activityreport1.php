<?php
require_once(dirname(__FILE__) . '/../../config.php');
$courseid = optional_param('courseid', '', PARAM_INT);
$groupid = optional_param('groupname', '', PARAM_INT);
$id = optional_param('id', -1, PARAM_INT);
global $DB, $USER, $CFG;
$systemcontext = context_system::instance();
//get the admin layout
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$course = array();
require_login();
$PAGE->set_url('/local/reports/activityreport.php');
$PAGE->requires->js(new moodle_url($CFG->wwwroot .'/local/reports/js/lib.js'), true);
$PAGE->set_title('Activity Reports');
//Header and the navigation bar
$PAGE->set_heading('Activity Reports');
$PAGE->navbar->add('Activity Reports');

if(!empty($courseid) && $courseid != ''){
  $coursedata = $DB->get_record('course', array('id' => $courseid));
  $PAGE->navbar->add($coursedata->fullname, '/course/view.php?id='.$coursedata->id);
}


//View Part starts
echo '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>';
echo '<script src="//code.jquery.com/jquery-3.5.1.js"></script>';
echo '<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" />
<script src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.min.js"></script>
<script src="//cdn.datatables.net/buttons/1.6.2/js/buttons.flash.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="//cdn.datatables.net/buttons/1.6.2/js/buttons.html5.min.js"></script>
<script src="//cdn.datatables.net/buttons/1.6.2/js/buttons.print.min.js"></script>
';
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
    $sql = "SELECT u.id, u.firstname, u.lastname,c.id as courseid, u.username, u.email FROM {user} u
        INNER JOIN {role_assignments} ra ON ra.userid = u.id
        INNER JOIN {context} ct ON ct.id = ra.contextid
        INNER JOIN {course} c ON c.id = ct.instanceid
        INNER JOIN {role} r ON r.id = ra.roleid
        INNER JOIN {course_categories} cc ON cc.id = c.category
        $sqlgroup
        WHERE r.id = 5 and c.id =? $where";



    $userdetails = $DB->get_records_sql($sql, array($coursedata->id));

    foreach($userdetails as $user){
        $line[] = $user->firstname. " " . $user->lastname;  
        
        $course_modules = get_array_of_activities($user->courseid);
        //print_object($course_modules); 
        $completiondates = $DB->get_record_sql("SELECT timecompleted FROM {course_completions} WHERE course = ? AND userid = ? ", array($coursedata->id, $user->id));
        $timecompleted = '-';
        if(!empty($completiondates->timecompleted)){
          $timecompleted = date('d/m/Y',  $completiondates->timecompleted);
        }
        $i = 0;  $line = array('');
        foreach ($course_modules as $key => $cm) {
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
              $line[$i] = $resultstatus;
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
echo "<div  style='padding: 0px 15px;'> <h4> Course: ".$coursedata->fullname." </h4> </div>";
?>
<style type="text/css">
    table.dataTable thead th {
        vertical-align: middle;
    }
  </style>
<div class="container">
  <div class="row">
  <div class="col-lg-4 col-12"></div>
  <div class="col-lg-4 col-12">    
    <input type="text" class="custom_search" name="courseid" id="courseid" placeholder="Search by Student Name">
    <input type="text" class="custom_search" name="groupname" id="groupname" placeholder="Search by Class Name">
    
    <input type="hidden" name="custom_search_fields" id="custom_search_fields" value="student_name,class_name,course_name">
  </div>
</div>
</div>
<table class="generaltable dataTable no-footer" id="completion_report" width="100%" role="grid" aria-describedby="completion_report_info" style="width: 100%;">
    <thead>
      <tr>
        <th> Users </th>
        <th> Username </th>
        <th> Email </th>
        <th> Completion Date</th>
        <?php
          foreach($coursemodules as $modules){
            echo "<th> $modules </th>";
          }
        ?>
        <th> Course: <?php echo $coursedata->fullname; ?></th>
      </tr>
    </thead>
</table>

<?php
echo html_writer::script('
     $("#completion_report").DataTable( {
            dom: "Bflrtip",
            buttons: ["copy", "csv", "excel", {
                extend: "pdfHtml5",
                orientation: "landscape",
                pageSize: "LEGAL",
            }, "print"],
          "processing": true,
          "serverSide": true,
          "searching": false,
          ajax: function(data, callback, settings) {
              $.post("ajax_coursereport.php", {dataparam: get_custom_search("#custom_search_fields"),length: data.length, page: data.start} , function(res) {
                res = JSON.parse(res);
                i=0; k=1;
                processresult=[];
                  for(let[key, result] of Object.entries(res.data)){
                    
                    var completiondates = "-";
                    if(result.completiondates != null){
                      completiondates = timeConverter(result.completiondates);
                    }
                    processresult[i] = [k, result.firstname, result.username, completiondates, "", ""];
                    i++; k++;
                  }
                  callback({
                      recordsTotal: res.recordsTotal,
                      recordsFiltered: res.recordsFiltered,
                      data: processresult
                  });
                });
           }
    } );
');
echo html_writer::script("
    oTable = $('#completion_report').DataTable();
    $('.custom_search').on('blur', function(){
          oTable.search($(this).val()).draw() ;
    })
");
echo $OUTPUT->footer();