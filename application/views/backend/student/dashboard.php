<?php
// Get the logged-in student's ID from session
$student_id = $this->session->userdata('student_id');

// Verify student is logged in
if (!$student_id) {
    echo '<div class="alert alert-danger">You must be logged in to view your dashboard.</div>';
    exit;
}

// Fetch student data
$student = $this->db->get_where('student', array('student_id' => $student_id))->row_array();
if (!$student) {
    echo '<div class="alert alert-danger">Invalid student ID.</div>';
    exit;
}

// Get class and section information
$class_id = $student['class_id'];
$class_info = $this->db->get_where('class', array('class_id' => $class_id))->row_array();
$section_info = $student['section_id'] ? $this->db->get_where('section', array('section_id' => $student['section_id']))->row_array() : null;

// Get homeroom teacher
$teacher_id = $class_info['teacher_id'];
$teacher_info = $this->db->get_where('teacher', array('teacher_id' => $teacher_id))->row_array();

// Get current academic session
$academic_session = $this->db->get_where('acd_session', array('is_open' => 1))->row_array();
?>
<div class="row">
    <div class="col-md-8">
        <div class="row">
            <!-- CALENDAR-->
            <div class="col-md-12 col-xs-12">    
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <i class="fa fa-calendar"></i>
                            <?php echo ('Event Schedule');?>
                        </div>
                    </div>
                    <div class="panel-body" style="padding:0px;">
                        <div class="calendar-env">
                            <div class="calendar-body">
                                <div id="notice_calendar"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="row">
            <div class="col-md-12">
                <div class="tile-stats tile-red">
                    <div class="icon"><i class="fa fa-id-card"></i></div>
                    <div class="num" data-start="0" data-end="<?php echo $student['roll']; ?>" 
                        data-postfix="" data-duration="1500" data-delay="0">0</div>
                    <h3><?php echo ('Roll Number');?></h3>
                    <p>Your roll number</p>
                </div>
            </div>
            <div class="col-md-12">
                <div class="tile-stats tile-green">
                    <div class="icon"><i class="fa fa-university"></i></div>
                    <div class="num"><?php echo $class_info['name'] . ($section_info ? ' / ' . $section_info['name'] : ''); ?></div>
                    <h3><?php echo ('Class and Section');?></h3>
                    <p>Your class and section</p>
                </div>
            </div>
            <div class="col-md-12">
                <div class="tile-stats tile-aqua">
                    <div class="icon"><i class="entypo-user"></i></div>
                    <div class="num"><?php echo isset($teacher_info['name']) ? $teacher_info['name'] : 'Not assigned'; ?></div>
                    <h3><?php echo ('Homeroom Teacher');?></h3>
                    <p>Your homeroom teacher</p>
                </div>
            </div>
            <div class="col-md-12">
                <div class="tile-stats tile-blue">
                    <div class="icon"><i class="fa fa-calendar"></i></div>
                    <div class="num"><?php echo isset($academic_session['name']) ? $academic_session['name'] : 'Not set'; ?></div>
                    <h3><?php echo ('Academic Year');?></h3>
                    <p>Current academic year</p>
                </div>
            </div>
            <div class="col-md-12">
                <div class="tile-stats tile-purple">
                    <div class="icon"><i class="fa fa-check-circle"></i></div>
                    <?php 
                        $check = array('date' => date('Y-m-d'), 'student_id' => $student_id);
                        $query = $this->db->get_where('attendance', $check);
                        $attendance_status = $query->row_array();
                        $status_text = $attendance_status ? ($attendance_status['status'] == 1 ? 'Present' : ($attendance_status['status'] == 2 ? 'Absent' : 'Not Taken')) : 'Not Taken';
                    ?>
                    <div class="num"><?php echo $status_text; ?></div>
                    <h3><?php echo ('Today\'s Attendance');?></h3>
                    <p>Your attendance status today</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var calendar = $('#notice_calendar');
                
    $('#notice_calendar').fullCalendar({
        header: {
            left: 'title',
            right: 'today prev,next'
        },
        editable: false,
        firstDay: 1,
        height: 530,
        droppable: false,
        events: [
            <?php 
            $notices = $this->db->get('noticeboard')->result_array();
            foreach($notices as $row):
            ?>
            {
                title: "<?php echo $row['notice_title'];?>",
                start: new Date(<?php echo date('Y', $row['create_timestamp']);?>, <?php echo date('m', $row['create_timestamp'])-1;?>, <?php echo date('d', $row['create_timestamp']);?>),
                end: new Date(<?php echo date('Y', $row['create_timestamp']);?>, <?php echo date('m', $row['create_timestamp'])-1;?>, <?php echo date('d', $row['create_timestamp']);?>) 
            },
            <?php 
            endforeach
            ?>
        ]
    });
});
</script>