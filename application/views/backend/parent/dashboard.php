<!-- Include Bootstrap 5, Font Awesome, and Google Fonts for enhanced styling -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Roboto', sans-serif;
        color: #1a202c; /* Near-black for high contrast */
    }
    .student-card {
        cursor: pointer;
        transition: transform 0.3s, box-shadow 0.3s;
        border: none;
        border-radius: 12px;
        overflow: hidden;
        background: linear-gradient(135deg, #00c4b4, #7b61ff);
        color: #ffffff; /* White text for contrast on gradient */
        margin-bottom: 15px;
        padding: 15px;
        font-size: 1.1rem; /* Slightly larger text */
        font-weight: 500; /* Medium weight for clarity */
    }
    .student-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.25);
    }
    .student-card.active {
        background: linear-gradient(135deg, #ff6b81, #ff9a44);
    }
    .student-card i {
        font-size: 2rem;
        margin-bottom: 10px;
    }
    .student-card h5 {
        font-size: 1.25rem; /* Larger for readability */
        font-weight: 700; /* Bold for emphasis */
        text-shadow: 0 1px 2px rgba(0,0,0,0.3); /* Subtle shadow for contrast */
    }
    .panel-primary {
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transition: all 0.3s;
        background: #ffffff;
    }
    .panel-heading {
        background: linear-gradient(to right, #3b82f6, #10b981);
        border-radius: 12px 12px 0 0;
        color: #ffffff; /* White text for contrast */
        padding: 15px;
    }
    .panel-title {
        font-size: 1.2rem; /* Larger for clarity */
        font-weight: 700; /* Bold for emphasis */
        text-shadow: 0 1px 2px rgba(0,0,0,0.2); /* Subtle shadow for contrast */
    }
    .panel-title i {
        margin-right: 10px;
    }
    .panel-body {
        font-size: 1rem; /* Standard size for body text */
        color: #1a202c; /* Near-black for high contrast */
    }
    .table {
        background: #f8fafc;
        border-radius: 8px;
        border: none;
        font-size: 1rem; /* Consistent size */
        color: #1a202c; /* High contrast */
    }
    .table tr td {
        border: 1px solid #e2e8f0;
        padding: 12px;
        font-weight: 400; /* Regular weight for body */
    }
    .table tr td strong {
        font-weight: 600; /* Semi-bold for emphasis */
    }
    .calendar-env {
        border-radius: 8px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .badge-success {
        background-color: #34d399 !important;
        color: #1a202c; /* Dark text for contrast */
        font-weight: 600;
    }
    .badge-danger {
        background-color: #f87171 !important;
        color: #ffffff; /* White text for contrast */
        font-weight: 600;
    }
    .text-muted {
        color: #4b5563 !important; /* Softer gray for "Pending" */
    }
    #child_academic_summary, #child_attendance {
        font-size: 1rem;
        color: #1a202c;
    }
    #child_academic_summary i, #child_attendance i {
        font-size: 1.1rem;
    }
    @media (max-width: 767px) {
        .student-card {
            margin: 10px 0;
        }
        .panel {
            margin: 15px;
        }
        .col-md-4 {
            padding: 0 15px;
        }
        .panel-title {
            font-size: 1.1rem; /* Slightly smaller on mobile */
        }
        .panel-body, .table, #child_academic_summary, #child_attendance {
            font-size: 0.95rem; /* Slightly smaller on mobile */
        }
    }
</style>

<div class="row">
    <!-- Event Calendar -->
    <div class="col-md-8">
        <div class="row">
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
    
    <!-- Parent-Relevant Information -->
    <div class="col-md-4">
        <div class="row">
            <?php 
                // Assume parent_id is available from session
                $parent_id = $this->session->userdata('parent_id');
                $children = $this->db->get_where('student', array('parent_id' => $parent_id))->result_array();
                $children_data = [];
                
                foreach ($children as $child):
                    $student_id = $child['student_id'];
                    $class_id = $child['class_id'];
                    $class_info = $this->db->get_where('class', array('class_id' => $class_id))->row_array();
                    $teacher_id = $class_info['teacher_id'];
                    $teacher_info = $this->db->get_where('teacher', array('teacher_id' => $teacher_id))->row_array();
                    
                    // Get attendance data
                    $this->db->where('student_id', $student_id);
                    $attendance = $this->db->get('attendance')->result_array();
                    $total_days = count($attendance);
                    $present_days = 0;
                    $absent_days = 0;
                    foreach ($attendance as $record) {
                        if ($record['status'] == 1) $present_days++;
                        elseif ($record['status'] == 2) $absent_days++;
                    }
                    $attendance_rate = ($total_days > 0) ? round(($present_days / $total_days) * 100, 2) : 0;

                    // Combined semester calculations
                    $exams = $this->db->get('exam')->result_array();
                    $combined_total_obtained = 0;
                    $combined_total_max = 0;
                    $combined_subject_count = 0;
                    $combined_all_subjects_complete = true;

                    foreach ($exams as $exam) {
                        $this->db->where('exam_id', $exam['exam_id']);
                        $this->db->where('student_id', $student_id);
                        $marks = $this->db->get('mark')->result_array();
                        
                        foreach ($marks as $mark) {
                            $combined_total_obtained += $mark['mark_obtained'];
                            $combined_total_max += $mark['mark_total'];
                            $combined_subject_count++;
                            if ($mark['mark_total'] != 100) {
                                $combined_all_subjects_complete = false;
                            }
                        }
                    }

                    $combined_average = $combined_subject_count > 0 ? $combined_total_obtained / $combined_subject_count : 0;
                    $combined_status = ($combined_average >= 50) ? 'Promoted' : 'Fail';
                    $combined_status_class = ($combined_average >= 50) ? 'success' : 'danger';

                    // Calculate combined rank
                    $this->db->select('student_id, SUM(mark_obtained) as total_marks');
                    $this->db->where('class_id', $class_id);
                    $this->db->group_by('student_id');
                    $this->db->order_by('total_marks', 'DESC');
                    $combined_class_results = $this->db->get('mark')->result_array();
                    
                    $combined_rank = 0;
                    foreach ($combined_class_results as $index => $result) {
                        if ($result['student_id'] == $student_id) {
                            $combined_rank = $index + 1;
                            break;
                        }
                    }

                    // Store child data for JavaScript
                    $children_data[$student_id] = [
                        'name' => $child['name'],
                        'roll' => $child['roll'],
                        'grade_section' => $class_info['name'] . (isset($child['section_id']) && $child['section_id'] != '' ? ' / ' . $this->db->get_where('section', array('section_id' => $child['section_id']))->row()->name : ''),
                        'teacher' => isset($teacher_info['name']) ? $teacher_info['name'] : 'Not assigned',
                        'academic_summary' => $combined_all_subjects_complete ? 
                            "<div class='d-flex align-items-center mb-2'><i class='fas fa-chart-line me-2 text-primary'></i>Average: " . round($combined_average, 2) . "</div>" .
                            "<div class='d-flex align-items-center mb-2'><i class='fas fa-trophy me-2 text-warning'></i>Total: " . $combined_total_obtained . "/" . $combined_total_max . "</div>" .
                            "<div class='d-flex align-items-center mb-2'><i class='fas fa-flag-checkered me-2 text-success'></i>Status: <span class='badge bg-" . ($combined_status_class == 'success' ? 'success' : 'danger') . " ms-2'>" . $combined_status . "</span></div>" .
                            "<div class='d-flex align-items-center'><i class='fas fa-medal me-2 text-info'></i>Rank: " . $combined_rank . " of " . count($combined_class_results) . "</div>"
                            : "<div class='text-muted'>Pending</div>",
                        'attendance' => 
                            "<div class='d-flex align-items-center mb-2'><i class='fas fa-calendar-check me-2 text-primary'></i><strong>Total Days Recorded:</strong> " . $total_days . "</div>" .
                            "<div class='d-flex align-items-center mb-2'><i class='fas fa-user-check me-2 text-success'></i><strong>Present Days:</strong> " . $present_days . "</div>" .
                            "<div class='d-flex align-items-center mb-2'><i class='fas fa-user-times me-2 text-danger'></i><strong>Absent Days:</strong> " . $absent_days . "</div>" .
                            "<div class='d-flex align-items-center'><i class='fas fa-percentage me-2 text-info'></i><strong>Attendance Rate:</strong> " . $attendance_rate . "%</div>"
                    ];
                endforeach;
            ?>
            <!-- Child Selection Cards -->
            <div class="col-md-12 col-xs-12">
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <i class="fas fa-users"></i> Select Student
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="d-flex flex-wrap" id="student_cards">
                            <?php foreach ($children as $child): ?>
                                <div class="card student-card m-2 p-3 flex-fill text-center" data-student-id="<?php echo $child['student_id']; ?>">
                                    <i class="fas fa-user-circle"></i>
                                    <h5 class="mt-2 mb-0"><?php echo $child['name']; ?></h5>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Child's Basic Information -->
            <div class="col-md-12 col-xs-12" id="basic_info_panel" style="display: none;">
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <i class="fas fa-user"></i> Basic Information
                        </div>
                    </div>
                    <div class="panel-body">
                        <table class="table table-bordered table-striped">
                            <tr>
                                <td><strong><i class="fas fa-user me-2 text-primary"></i>Full Name</strong></td>
                                <td id="child_name"></td>
                            </tr>
                            <tr>
                                <td><strong><i class="fas fa-id-badge me-2 text-primary"></i>Roll Number</strong></td>
                                <td id="child_roll"></td>
                            </tr>
                            <tr>
                                <td><strong><i class="fas fa-school me-2 text-primary"></i>Grade and Section</strong></td>
                                <td id="child_grade_section"></td>
                            </tr>
                            <tr>
                                <td><strong><i class="fas fa-chalkboard-teacher me-2 text-primary"></i>Homeroom Teacher</strong></td>
                                <td id="child_teacher"></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Child's Academic Summary -->
            <div class="col-md-12 col-xs-12" id="academic_panel" style="display: none;">
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <i class="fas fa-star"></i> Academic Summary
                        </div>
                    </div>
                    <div class="panel-body">
                        <div id="child_academic_summary" class="p-3"></div>
                    </div>
                </div>
            </div>

            <!-- Child's Attendance Summary -->
            <div class="col-md-12 col-xs-12" id="attendance_panel" style="display: none;">
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <i class="fas fa-calendar-check"></i> Attendance Summary
                        </div>
                    </div>
                    <div class="panel-body">
                        <div id="child_attendance" class="p-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
                start: new Date(<?php echo date('Y',$row['create_timestamp']);?>, <?php echo date('m',$row['create_timestamp'])-1;?>, <?php echo date('d',$row['create_timestamp']);?>),
                end: new Date(<?php echo date('Y',$row['create_timestamp']);?>, <?php echo date('m',$row['create_timestamp'])-1;?>, <?php echo date('d',$row['create_timestamp']);?>) 
            },
            <?php 
            endforeach
            ?>
        ]
    });

    // Store children data in JavaScript
    var childrenData = <?php echo json_encode($children_data); ?>;

    // Handle card click
    $('.student-card').on('click', function() {
        var studentId = $(this).data('student-id');
        
        // Update active card
        $('.student-card').removeClass('active');
        $(this).addClass('active');

        if (studentId && childrenData[studentId]) {
            var data = childrenData[studentId];
            $('#child_name').html(data.name);
            $('#child_roll').html(data.roll);
            $('#child_grade_section').html(data.grade_section);
            $('#child_teacher').html(data.teacher);
            $('#child_academic_summary').html(data.academic_summary);
            $('#child_attendance').html(data.attendance);
            
            // Show panels with fade-in effect
            $('#basic_info_panel').fadeIn(300);
            $('#academic_panel').fadeIn(300);
            $('#attendance_panel').fadeIn(300);
        } else {
            // Hide panels
            $('#basic_info_panel').fadeOut(300);
            $('#academic_panel').fadeOut(300);
            $('#attendance_panel').fadeOut(300);
        }
    });
});
</script>