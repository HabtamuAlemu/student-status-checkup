<?php 
    $child_of_parent = $this->db->get_where('student', array('student_id' => $student_id))->result_array();
    foreach ($child_of_parent as $row):
        // Get additional student information
        $class_id = $row['class_id'];
        $class_info = $this->db->get_where('class', array('class_id' => $class_id))->row_array();
        $teacher_id = $class_info['teacher_id'];
        $teacher_info = $this->db->get_where('teacher', array('teacher_id' => $teacher_id))->row_array();
        $academic_session = $this->db->get_where('acd_session', array('is_open' => 1))->row_array();
        
        // Calculate age from birthday
        $birthday = new DateTime($row['birthday']);
        $today = new DateTime();
        $age = $birthday->diff($today)->y;
        
        // Get attendance data
        $this->db->where('student_id', $student_id);
        $this->db->order_by('date', 'DESC');
        $attendance = $this->db->get('attendance')->result_array();
        
        // Calculate attendance summary
        $total_days = count($attendance);
        $present_days = 0;
        $absent_days = 0;
        $not_taken_days = 0;
        
        foreach ($attendance as $record) {
            if ($record['status'] == 1) $present_days++;
            elseif ($record['status'] == 2) $absent_days++;
            else $not_taken_days++;
        }
        
        // Get current month attendance
        $current_month = date('m');
        $current_year = date('Y');
        $this->db->where('student_id', $student_id);
        $this->db->where('MONTH(date)', $current_month);
        $this->db->where('YEAR(date)', $current_year);
        $monthly_attendance = $this->db->get('attendance')->result_array();
        
        $monthly_present = 0;
        $monthly_absent = 0;
        
        foreach ($monthly_attendance as $record) {
            if ($record['status'] == 1) $monthly_present++;
            elseif ($record['status'] == 2) $monthly_absent++;
        }
?>
<hr />
<div class="row">
    <!-- Student Basic Information Section -->
    <div class="col-md-4">
        <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    <i class="entypo-user"></i> Basic Student Information
                </div>
            </div>
            <div class="panel-body">
                <table class="table table-bordered">
                    <tr>
                        <td><strong>Full Name</strong></td>
                        <td><?php echo $row['name']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Roll Number</strong></td>
                        <td><?php echo $row['roll']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Grade and Section</strong></td>
                        <td>
                            <?php 
                                echo $class_info['name'];
                                if (isset($row['section_id']) && $row['section_id'] != '') {
                                    $section = $this->db->get_where('section', array('section_id' => $row['section_id']))->row();
                                    echo ' / ' . $section->name;
                                }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Age / Date of Birth</strong></td>
                        <td><?php echo $age . ' years / ' . date('d M Y', strtotime($row['birthday'])); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Homeroom Teacher</strong></td>
                        <td><?php echo isset($teacher_info['name']) ? $teacher_info['name'] : 'Not assigned'; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Current Status</strong></td>
                        <td>
                            <span class="label label-<?php echo ($row['status'] == 1) ? 'success' : 'success'; ?>">
                                <?php echo ($row['status'] == 1) ? 'Registered' : 'Registered'; ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Academic Year</strong></td>
                        <td><?php echo isset($academic_session['name']) ? $academic_session['name'] : 'Not set'; ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Student Photo -->
        <?php if (!empty($row['image'])): ?>
        <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    <i class="entypo-camera"></i> Photo
                </div>
            </div>
            <div class="panel-body" style="text-align: center;">
                <img src="<?php echo base_url('Uploads/student_image/' . $row['image']); ?>" 
                     class="img-circle" width="150" alt="<?php echo $row['name']; ?>">
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Marks and Attendance Section -->
    <div class="col-md-8">
        <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    <i class="entypo-star"></i> Academic Progress  
                </div>
            </div>
            <div class="panel-body">
                <div class="tabs-vertical-env">
                    <ul class="nav tabs-vertical">
                        <li class="active">
                            <a href="#marks" data-toggle="tab">
                                <i class="entypo-docs"></i> Marks
                            </a>
                        </li>
                        <li>
                            <a href="#attendance" data-toggle="tab">
                                <i class="entypo-calendar"></i> Attendance Records
                            </a>
                        </li>
                    </ul>
                    
                    <div class="tab-content">
                        <!-- Marks Tab -->
                        <div class="tab-pane active" id="marks">
                            <!-- Semester Selection Form -->
                            <form method="post" action="">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="semester_id">Select Semester</label>
                                            <select name="semester_id" id="semester_id" class="form-control" onchange="this.form.submit()">
                                                <option value="">Select a Semester</option>
                                                <?php 
                                                    $semesters = $this->db->get('semesters')->result_array();
                                                    $selected_semester_id = isset($_POST['semester_id']) ? $_POST['semester_id'] : '';
                                                    foreach ($semesters as $semester):
                                                ?>
                                                    <option value="<?php echo $semester['id']; ?>" <?php echo $selected_semester_id == $semester['id'] ? 'selected' : ''; ?>>
                                                        <?php echo $semester['name'] . ' (' . date('d M Y', strtotime($semester['start_date'])) . ' - ' . date('d M Y', strtotime($semester['end_date'])) . ')'; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            
                            <!-- Semester Summary (Total Mark, Out Of, Total Average, and Rank) -->
                            <?php if (!empty($selected_semester_id)): ?>
                                <?php
                                    // Calculate total mark, out of, average, and rank for the selected semester
                                    $semester_total_obtained = 0;
                                    $semester_total_max = 0;
                                    $semester_mark_count = 0;
                                    
                                    // Get all teacher_exams for the selected semester and class
                                    $this->db->where('semester_id', $selected_semester_id);
                                    $this->db->where('class_id', $class_id);
                                    $semester_exams = $this->db->get('teacher_exam')->result_array();
                                    
                                    foreach ($semester_exams as $exam) {
                                        $this->db->where('teacher_exam_id', $exam['teacher_exam_id']);
                                        $this->db->where('student_id', $student_id);
                                        $marks = $this->db->get('mark')->result_array();
                                        
                                        foreach ($marks as $mark) {
                                            $semester_total_obtained += $mark['mark_obtained'];
                                            $semester_total_max += $exam['max_score'];
                                            $semester_mark_count++;
                                        }
                                    }
                                    
                                    // Calculate semester average
                                    $semester_average = $semester_mark_count > 0 ? $semester_total_obtained / $semester_mark_count : 0;
                                    
                                    // Calculate semester rank
                                    $this->db->select('m.student_id, SUM(m.mark_obtained) as total_marks');
                                    $this->db->from('mark m');
                                    $this->db->join('teacher_exam te', 'm.teacher_exam_id = te.teacher_exam_id');
                                    $this->db->where('te.semester_id', $selected_semester_id);
                                    $this->db->where('te.class_id', $class_id);
                                    $this->db->group_by('m.student_id');
                                    $this->db->order_by('total_marks', 'DESC');
                                    $semester_class_results = $this->db->get()->result_array();
                                    
                                    $semester_rank = 0;
                                    foreach ($semester_class_results as $index => $result) {
                                        if ($result['student_id'] == $student_id) {
                                            $semester_rank = $index + 1;
                                            break;
                                        }
                                    }
                                ?>
                                <div class="panel panel-info">
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                            <i class="entypo-chart-line"></i> Semester Summary
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <table class="table table-bordered">
                                            <tr>
                                                <td><strong>Total Mark</strong></td>
                                                <td><?php echo $semester_total_obtained; ?> / <?php echo $semester_total_max; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Total Average</strong></td>
                                                <td><?php echo round($semester_average, 2); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Semester Rank</strong></td>
                                                <td><?php echo $semester_rank; ?> of <?php echo count($semester_class_results); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Subject and Exam Selection Form -->
                            <?php if (!empty($selected_semester_id)): ?>
                            <form method="post" action="">
                                <input type="hidden" name="semester_id" value="<?php echo $selected_semester_id; ?>">
                                <div class="row">
                                    <!-- Subject Selection -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="subject_id">Select Subject</label>
                                            <select name="subject_id" id="subject_id" class="form-control" onchange="this.form.submit()" <?php echo empty($selected_semester_id) ? 'disabled' : ''; ?>>
                                                <option value="">Select a Subject</option>
                                                <?php 
                                                    $this->db->where('class_id', $class_id);
                                                    $subjects = $this->db->get('subject')->result_array();
                                                    $selected_subject_id = isset($_POST['subject_id']) ? $_POST['subject_id'] : '';
                                                    foreach ($subjects as $subject):
                                                        // Check if subject has exams in the selected semester
                                                        $this->db->where('semester_id', $selected_semester_id);
                                                        $this->db->where('subject_id', $subject['subject_id']);
                                                        $this->db->where('class_id', $class_id);
                                                        $exam_exists = $this->db->get('teacher_exam')->num_rows() > 0;
                                                        if ($exam_exists):
                                                ?>
                                                            <option value="<?php echo $subject['subject_id']; ?>" <?php echo $selected_subject_id == $subject['subject_id'] ? 'selected' : ''; ?>>
                                                                <?php echo $subject['name']; ?>
                                                            </option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <!-- Exam Type Selection -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="teacher_exam_id">Select Exam Type</label>
                                            <select name="teacher_exam_id" id="teacher_exam_id" class="form-control" onchange="this.form.submit()" <?php echo empty($selected_subject_id) ? 'disabled' : ''; ?>>
                                                <option value="">Select an Exam Type</option>
                                                <?php 
                                                    if (!empty($selected_subject_id)) {
                                                        $this->db->where('semester_id', $selected_semester_id);
                                                        $this->db->where('subject_id', $selected_subject_id);
                                                        $this->db->where('class_id', $class_id);
                                                        $teacher_exams = $this->db->get('teacher_exam')->result_array();
                                                        $selected_teacher_exam_id = isset($_POST['teacher_exam_id']) ? $_POST['teacher_exam_id'] : '';
                                                        foreach ($teacher_exams as $exam):
                                                ?>
                                                            <option value="<?php echo $exam['teacher_exam_id']; ?>" <?php echo $selected_teacher_exam_id == $exam['teacher_exam_id'] ? 'selected' : ''; ?>>
                                                                <?php echo $exam['name'] . ' (' . $exam['date'] . ')'; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <?php endif; ?>
                            
                            <!-- Marks Table -->
                            <?php 
                                if (!empty($selected_teacher_exam_id)) {
                                    $this->db->where('teacher_exam_id', $selected_teacher_exam_id);
                                    $this->db->where('student_id', $student_id);
                                    $marks = $this->db->get('mark')->result_array();
                                    
                                    // Get exam details
                                    $this->db->where('teacher_exam_id', $selected_teacher_exam_id);
                                    $exam = $this->db->get('teacher_exam')->row_array();
                                    
                                    // Calculate totals for summary row
                                    $total_obtained = 0;
                                    $total_max = 0;
                                    $subject_count = 0;
                                    $all_subjects_complete = true;
                                    
                                    foreach ($marks as $mark) {
                                        $total_obtained += $mark['mark_obtained'];
                                        $total_max += $exam['max_score'];
                                        $subject_count++;
                                        if ($exam['max_score'] != 100) {
                                            $all_subjects_complete = false;
                                        }
                                    }
                                    
                                    // Calculate average and percentage
                                    $average = $subject_count > 0 ? $total_obtained / $subject_count : 0;
                                    $percentage = $total_max > 0 ? ($total_obtained / $total_max) * 100 : 0;
                                    // Determine pass/fail status
                                    $status = ($average >= 50) ? 'Promoted' : 'Fail';
                                    $status_class = ($average >= 50) ? 'success' : 'danger';
                                    
                                    // Get student rank in class for this exam
                                    $this->db->select('student_id, SUM(mark_obtained) as total_marks');
                                    $this->db->where('teacher_exam_id', $selected_teacher_exam_id);
                                    $this->db->where('class_id', $class_id);
                                    $this->db->group_by('student_id');
                                    $this->db->order_by('total_marks', 'DESC');
                                    $class_results = $this->db->get('mark')->result_array();
                                    
                                    $rank = 0;
                                    foreach ($class_results as $index => $result) {
                                        if ($result['student_id'] == $student_id) {
                                            $rank = $index + 1;
                                            break;
                                        }
                                    }
                            ?>
                            <table class="table table-bordered table-hover table-striped responsive">
                                <thead>
                                    <tr>
                                        <th><?php echo ('Subject');?></th>
                                        <th><?php echo ('Mark Obtained');?></th>
                                        <th><?php echo ('Out Of');?></th>
                                        <th><?php echo ('Percentile');?></th>
                                        <th><?php echo ('Comment');?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($marks as $mark): 
                                    $subject_info = $this->db->get_where('subject', array('subject_id' => $mark['subject_id']))->row();
                                    // Calculate percentile for this subject
                                    $this->db->select('mark_obtained');
                                    $this->db->where('teacher_exam_id', $selected_teacher_exam_id);
                                    $this->db->where('subject_id', $mark['subject_id']);
                                    $this->db->where('class_id', $class_id);
                                    $this->db->order_by('mark_obtained', 'DESC');
                                    $subject_results = $this->db->get('mark')->result_array();
                                    
                                    $subject_rank = 0;
                                    $total_students = count($subject_results);
                                    foreach ($subject_results as $index => $result) {
                                        if ($result['mark_obtained'] == $mark['mark_obtained']) {
                                            $subject_rank = $index + 1;
                                            break;
                                        }
                                    }
                                    $percentile = $total_students > 0 ? round((1 - ($subject_rank - 1) / $total_students) * 100, 2) : 0;
                                ?>
                                    <tr>
                                        <td><?php echo $subject_info->name; ?></td>
                                        <td><?php echo $mark['mark_obtained']; ?></td>
                                        <td><?php echo $exam['max_score']; ?></td>
                                        <td><?php echo $percentile; ?>%</td>
                                        <td><?php echo !empty($mark['comment']) ? $mark['comment'] : 'No comment'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <!-- Summary Row -->
                                <tr style="font-weight: bold; background-color: #f5f5f5;">
                                    <td>Summary</td>
                                    <td>
                                        <?php if ($all_subjects_complete): ?>
                                            Average: <?php echo round($average, 2); ?><br>
                                            Total: <?php echo $total_obtained; ?><br>
                                            Status: <span class="label label-<?php echo $status_class; ?>">
                                                <?php echo $status; ?>
                                            </span>
                                        <?php else: ?>
                                            Pending
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $total_max; ?></td>
                                    <td></td>
                                    <td>
                                        <?php if ($all_subjects_complete): ?>
                                            Rank: <?php echo $rank; ?> of <?php echo count($class_results); ?>
                                        <?php else: ?>
                                            Pending
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <?php } else if (!empty($selected_semester_id)) { ?>
                                <div class="alert alert-info">
                                    Please select a subject and exam type to view marks.
                                </div>
                            <?php } else { ?>
                                <div class="alert alert-info">
                                    Please select a semester to view marks.
                                </div>
                            <?php } ?>
                        </div>
                        
                        <!-- Attendance Tab -->
                        <div class="tab-pane" id="attendance">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="panel panel-info">
                                        <div class="panel-heading">
                                            <div class="panel-title">
                                                <i class="entypo-chart-bar"></i> Attendance Summary
                                            </div>
                                        </div>
                                        <div class="panel-body">
                                            <table class="table table-bordered">
                                                <tr>
                                                    <td><strong>Total Days Recorded</strong></td>
                                                    <td><?php echo $total_days; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Present Days</strong></td>
                                                    <td><?php echo $present_days; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Absent Days</strong></td>
                                                    <td><?php echo $absent_days; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Attendance Rate</strong></td>
                                                    <td>
                                                        <?php 
                                                            $attendance_rate = ($total_days > 0) ? round(($present_days / $total_days) * 100, 2) : 0;
                                                            echo $attendance_rate . '%';
                                                        ?>
                                                    </td>
                                                </tr>
                                            </table>
                                            
                                            <!-- Monthly Attendance -->
                                            <h5>This Month (<?php echo date('F Y'); ?>)</h5>
                                            <table class="table table-bordered">
                                                <tr>
                                                    <td><strong>Present</strong></td>
                                                    <td><?php echo $monthly_present; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Absent</strong></td>
                                                    <td><?php echo $monthly_absent; ?></td>
                                                </tr>
                                            </table>
                                            
                                            <!-- Absence Alert -->
                                            <?php if ($monthly_absent > 3): ?>
                                            <div class="alert alert-danger">
                                                <i class="entypo-warning"></i> 
                                                <strong>Warning:</strong> This student has been absent <?php echo $monthly_absent; ?> times this month.
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="panel panel-info">
                                        <div class="panel-heading">
                                            <div class="panel-title">
                                                <i class="entypo-list"></i> Recent Attendance Records
                                            </div>
                                        </div>
                                        <div class="panel-body">
                                            <table class="table table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $recent_attendance = array_slice($attendance, 0, 10); // Show last 10 records
                                                    foreach ($recent_attendance as $record): 
                                                    ?>
                                                    <tr>
                                                        <td><?php echo date('d M Y', strtotime($record['date'])); ?></td>
                                                        <td>
                                                            <?php if ($record['status'] == 1): ?>
                                                                <span class="label label-success">Present</span>
                                                            <?php elseif ($record['status'] == 2): ?>
                                                                <span class="label label-danger">Absent</span>
                                                            <?php else: ?>
                                                                <span class="label label-default">Not Taken</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                            <?php if (count($attendance) > 10): ?>
                                            <div class="text-center">
                                                <a href="#" class="btn btn-default btn-sm">View All Records</a>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Combined Semester Results -->
        <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    <i class="entypo-graduation-cap"></i> Combined Semester Results
                </div>
            </div>
            <div class="panel-body">
                <?php
                // Initialize combined semester calculations
                $combined_total_obtained = 0;
                $combined_total_max = 0;
                $combined_subject_count = 0;
                $combined_all_subjects_complete = true;

                // Check all teacher_exams and subjects for max_score = 100
                $this->db->where('class_id', $class_id);
                $teacher_exams = $this->db->get('teacher_exam')->result_array();
                foreach ($teacher_exams as $exam) {
                    $this->db->where('teacher_exam_id', $exam['teacher_exam_id']);
                    $this->db->where('student_id', $student_id);
                    $marks = $this->db->get('mark')->result_array();
                    
                    foreach ($marks as $mark) {
                        $combined_total_obtained += $mark['mark_obtained'];
                        $combined_total_max += $exam['max_score'];
                        $combined_subject_count++;
                        if ($exam['max_score'] != 100) {
                            $combined_all_subjects_complete = false;
                        }
                    }
                }

                // Calculate combined average and status
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
                ?>
                <table class="table table-bordered table-hover table-striped responsive">
                    <thead>
                        <tr>
                            <th>Summary</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="font-weight: bold; background-color: #f5f5f5;">
                            <td>
                                <?php if ($combined_all_subjects_complete): ?>
                                    Average: <?php echo round($combined_average, 2); ?><br>
                                    Total: <?php echo $combined_total_obtained; ?><br>
                                    Status: <span class="label label-<?php echo $combined_status_class; ?>">
                                        <?php echo $combined_status; ?>
                                    </span><br>
                                    Rank: <?php echo $combined_rank; ?> of <?php echo count($combined_class_results); ?>
                                <?php else: ?>
                                    Pending
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endforeach;?>