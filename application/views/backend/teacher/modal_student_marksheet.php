<style>
    #chartdiv {
        width: 100%;
        height: 250px;
        font-size: 11px;
    }   
</style>

<?php
$teacher_id = $this->session->userdata('teacher_id');
$student_id = $param2; // From URL
$subject_id = $param3; // From URL

$student_info = $this->crud_model->get_student_info($student_id);
foreach ($student_info as $row1):
    // Initialize variables for combined semester calculations
    $combined_total_marks = 0;
    $combined_total_max_marks = 0;
    $combined_total_subjects = 0;
    $combined_all_subjects_complete = true;
?>
    <center>
        <div style="font-size: 20px;font-weight: 200;margin: 10px;"><?php echo $row1['name']; ?></div>

        <div class="panel-group joined" id="accordion-test-2">

            <?php
            // Semester-wise result for the selected subject
            $toggle = true;
            $exams = $this->crud_model->get_exams();
            foreach ($exams as $row0):
                $total_marks = 0;
                $total_max_marks = 0;
                $total_subjects = 0;
                $all_subjects_complete = true;
                $subject_name = [];
                $mark_obtained = [];
                $mark_highest = [];
            ?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion-test-2" href="#collapse<?php echo $row0['exam_id']; ?>">
                                <i class="entypo-rss"></i> <?php echo $row0['name']; ?>
                            </a>
                        </h4>
                    </div>

                    <div id="collapse<?php echo $row0['exam_id']; ?>" class="panel-collapse collapse <?php
                    if ($toggle) {
                        echo 'in';
                        $toggle = false;
                    }
                    ?>">
                        <div class="panel-body">
                            <center>
                                <table class="table table-bordered table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>Assessment 1</th>
                                            <th>Assessment 2</th>
                                            <th>Midterm</th>
                                            <th>Final</th>
                                            <th>Participation</th>
                                            <th>Obtained Marks</th>
                                            <th>Highest Mark</th>
                                            <th>Grade</th>
                                            <th>Comment</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Get the specific subject taught by the teacher
                                        $this->db->select('s.subject_id, s.name');
                                        $this->db->from('subject s');
                                        $this->db->where('s.class_id', $row1['class_id']);
                                        $this->db->where('s.teacher_id', $teacher_id);
                                        $this->db->where('s.subject_id', $subject_id);
                                        $subjects = $this->db->get()->result_array();

                                        if (empty($subjects)) {
                                            echo '<tr><td colspan="10">' . get_phrase('no_subject_data') . '</td></tr>';
                                            continue;
                                        }

                                        foreach ($subjects as $row2):
                                            $total_subjects++;
                                            $combined_total_subjects++;
                                        ?>
                                            <tr>
                                                <td><?php echo $row2['name']; $subject_name[] = $row2['name']; ?></td>
                                                <td>
                                                    <?php
                                                    // Assessment marks
                                                    $verify_data = array(
                                                        'exam_id' => $row0['exam_id'],
                                                        'class_id' => $row1['class_id'],
                                                        'subject_id' => $row2['subject_id'],
                                                        'student_id' => $row1['student_id']
                                                    );
                                                    $query = $this->db->get_where('mark', $verify_data);
                                                    $marks = $query->result_array();
                                                    $current_mark_obtained = 0;
                                                    $current_mark_total = 0;
                                                    foreach ($marks as $row3):
                                                        echo isset($row3['assessment1_obtained']) ? $row3['assessment1_obtained'] . '/' . $row3['assessment1_total'] : '-';
                                                    endforeach;
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    foreach ($marks as $row3):
                                                        echo isset($row3['assessment2_obtained']) ? $row3['assessment2_obtained'] . '/' . $row3['assessment2_total'] : '-';
                                                    endforeach;
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    foreach ($marks as $row3):
                                                        echo isset($row3['midterm_obtained']) ? $row3['midterm_obtained'] . '/' . $row3['midterm_total'] : '-';
                                                    endforeach;
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    foreach ($marks as $row3):
                                                        echo isset($row3['final_obtained']) ? $row3['final_obtained'] . '/' . $row3['final_total'] : '-';
                                                    endforeach;
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    foreach ($marks as $row3):
                                                        echo isset($row3['participation_obtained']) ? $row3['participation_obtained'] . '/' . $row3['participation_total'] : '-';
                                                    endforeach;
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    foreach ($marks as $row3):
                                                        echo isset($row3['mark_obtained']) ? $row3['mark_obtained'] : '-';
                                                        $mark_obtained[] = isset($row3['mark_obtained']) ? $row3['mark_obtained'] : 0;
                                                        $total_marks += isset($row3['mark_obtained']) ? $row3['mark_obtained'] : 0;
                                                        $combined_total_marks += isset($row3['mark_obtained']) ? $row3['mark_obtained'] : 0;
                                                        $current_mark_obtained = isset($row3['mark_obtained']) ? $row3['mark_obtained'] : 0;
                                                        $current_mark_total = isset($row3['mark_total']) ? $row3['mark_total'] : 0;
                                                        $total_max_marks += isset($row3['mark_total']) ? $row3['mark_total'] : 0;
                                                        $combined_total_max_marks += isset($row3['mark_total']) ? $row3['mark_total'] : 0;
                                                    endforeach;
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    // Highest marks
                                                    $verify_data = array(
                                                        'exam_id' => $row0['exam_id'],
                                                        'subject_id' => $row2['subject_id']
                                                    );
                                                    $this->db->select_max('mark_obtained', 'mark_highest');
                                                    $query = $this->db->get_where('mark', $verify_data);
                                                    $marks_highest = $query->result_array();
                                                    foreach ($marks_highest as $row4):
                                                        echo isset($row4['mark_highest']) ? $row4['mark_highest'] : '-';
                                                        $mark_highest[] = isset($row4['mark_highest']) ? $row4['mark_highest'] : 0;
                                                    endforeach;
                                                    // Check if subject total is exactly 100
                                                    if ($current_mark_total != 100) {
                                                        $all_subjects_complete = false;
                                                        $combined_all_subjects_complete = false;
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if ($current_mark_obtained > 0) {
                                                        $grade = $this->crud_model->get_grade($current_mark_obtained);
                                                        echo $grade['name'];
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    foreach ($marks as $row3):
                                                        echo !empty($row3['comment']) ? $row3['comment'] : 'No comment';
                                                    endforeach;
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <!-- Summary Row -->
                                        <tr style="font-weight: bold; background-color: #f5f5f5;">
                                            <td>Summary</td>
                                            <td colspan="9">
                                                <?php if ($all_subjects_complete && $total_subjects > 0): ?>
                                                    <?php
                                                    // Calculate average
                                                    $average = $total_subjects > 0 ? ($total_marks / $total_subjects) : 0;
                                                    // Calculate rank
                                                    $this->db->select('student_id, mark_obtained as total_marks');
                                                    $this->db->where('exam_id', $row0['exam_id']);
                                                    $this->db->where('class_id', $row1['class_id']);
                                                    $this->db->where('subject_id', $subject_id);
                                                    $this->db->order_by('total_marks', 'DESC');
                                                    $class_results = $this->db->get('mark')->result_array();
                                                    $rank = 0;
                                                    foreach ($class_results as $index => $result) {
                                                        if ($result['student_id'] == $row1['student_id']) {
                                                            $rank = $index + 1;
                                                            break;
                                                        }
                                                    }
                                                    ?>
                                                    Total Marks: <?php echo $total_marks; ?> out of <?php echo $total_max_marks; ?><br>
                                                    Average: <?php echo round($average, 2); ?><br>
                                                    Rank: <?php echo $rank; ?> of <?php echo count($class_results); ?><br>
                                                    Status: 
                                                    <span class="label label-<?php echo ($average >= 50) ? 'success' : 'danger'; ?>">
                                                        <?php echo ($average >= 50) ? 'Pass' : 'Fail'; ?>
                                                    </span>
                                                <?php else: ?>
                                                    Pending
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <?php if ($all_subjects_complete && $total_subjects > 0): ?>
                                    <div id="chartdiv"></div>
                                    <script>
                                        setTimeout(function() {
                                            var chart = AmCharts.makeChart("chartdiv", {
                                                "theme": "none",
                                                "type": "serial",
                                                "dataProvider": [
                                                    <?php for ($i = 0; $i < count($subjects); $i++) { ?>
                                                        {
                                                            "subject": "<?php echo $subject_name[$i]; ?>",
                                                            "mark_obtained": <?php echo $mark_obtained[$i]; ?>,
                                                            "mark_highest": <?php echo $mark_highest[$i]; ?>
                                                        },
                                                    <?php } ?>
                                                ],
                                                "valueAxes": [{
                                                    "stackType": "3d",
                                                    "unit": "%",
                                                    "position": "left",
                                                    "title": "Obtained Mark vs Highest Mark"
                                                }],
                                                "startDuration": 1,
                                                "graphs": [{
                                                    "balloonText": "Obtained Mark in [[category]]: <b>[[value]]</b>",
                                                    "fillAlphas": 0.9,
                                                    "lineAlpha": 0.2,
                                                    "title": "Obtained",
                                                    "type": "column",
                                                    "fillColors": "#7f8c8d",
                                                    "valueField": "mark_obtained"
                                                }, {
                                                    "balloonText": "Highest Mark in [[category]]: <b>[[value]]</b>",
                                                    "fillAlphas": 0.9,
                                                    "lineAlpha": 0.2,
                                                    "title": "Highest",
                                                    "type": "column",
                                                    "fillColors": "#34495e",
                                                    "valueField": "mark_highest"
                                                }],
                                                "plotAreaFillAlphas": 0.1,
                                                "depth3D": 20,
                                                "angle": 45,
                                                "categoryField": "subject",
                                                "categoryAxis": {
                                                    "gridPosition": "start"
                                                },
                                                "exportConfig": {
                                                    "menuTop": "20px",
                                                    "menuRight": "20px",
                                                    "menuItems": [{
                                                        "format": 'png'
                                                    }]
                                                }
                                            });
                                        }, 500);
                                    </script>
                                <?php endif; ?>
                            </center>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Combined Semester Results -->
        <div class="panel panel-default" style="margin-top: 20px;">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <i class="entypo-graduation-cap"></i> Combined Semester Results
                </h4>
            </div>
            <div class="panel-body">
                <center>
                    <table class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Summary</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="font-weight: bold; background-color: #f5f5f5;">
                                <td>
                                    <?php
                                    // Verify the subject across all semesters has mark_total = 100
                                    $combined_all_subjects_complete = true;
                                    foreach ($exams as $exam) {
                                        $this->db->select('s.subject_id, m.mark_total');
                                        $this->db->from('subject s');
                                        $this->db->join('mark m', 's.subject_id = m.subject_id AND m.exam_id = ' . $exam['exam_id'] . ' AND m.student_id = ' . $student_id, 'inner');
                                        $this->db->where('s.class_id', $row1['class_id']);
                                        $this->db->where('s.teacher_id', $teacher_id);
                                        $this->db->where('s.subject_id', $subject_id);
                                        $subjects = $this->db->get()->result_array();
                                        foreach ($subjects as $subject) {
                                            if ($subject['mark_total'] != 100) {
                                                $combined_all_subjects_complete = false;
                                                break;
                                            }
                                        }
                                       if (!$combined_all_subjects_complete) break;
                                    }
                                    ?>
                                    <?php if ($combined_all_subjects_complete && $combined_total_subjects > 0): ?>
                                        <?php
                                        // Calculate combined average
                                        $combined_average = $combined_total_subjects > 0 ? ($combined_total_marks / $combined_total_subjects) : 0;
                                        // Calculate combined rank
                                        $this->db->select('student_id, mark_obtained as total_marks');
                                        $this->db->where('class_id', $row1['class_id']);
                                        $this->db->where('subject_id', $subject_id);
                                        $this->db->order_by('total_marks', 'DESC');
                                        $combined_class_results = $this->db->get('mark')->result_array();
                                        $combined_rank = 0;
                                        foreach ($combined_class_results as $index => $result) {
                                            if ($result['student_id'] == $row1['student_id']) {
                                                $combined_rank = $index + 1;
                                                break;
                                            }
                                        }
                                        ?>
                                        Total Marks: <?php echo $combined_total_marks; ?> out of <?php echo $combined_total_max_marks; ?><br>
                                        Average: <?php echo round($combined_average, 2); ?><br>
                                        Rank: <?php echo $combined_rank; ?> of <?php echo count($combined_class_results); ?><br>
                                        Status: 
                                        <span class="label label-<?php echo ($combined_average >= 50) ? 'success' : 'danger'; ?>">
                                            <?php echo ($combined_average >= 50) ? 'Pass' : 'Fail'; ?>
                                        </span>
                                    <?php else: ?>
                                        Pending
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </center>
            </div>
        </div>
    </center>
<?php endforeach; ?>