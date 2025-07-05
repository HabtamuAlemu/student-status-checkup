<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
    $semesters = $this->db->get('semesters')->result_array();
    $classes = $this->db->get('class')->result_array();
    $subjects = $this->db->get('subject')->result_array();
    $teacher_exam_id = $this->uri->segment(3);
    $class_id = $this->uri->segment(4);
    $subject_id = $this->uri->segment(5);
    $semester_id = $this->uri->segment(6);
?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    <i class="entypo-plus-circled"></i>
                    <?php echo get_phrase('manage_exam_marks'); ?>
                </div>
            </div>
            <div class="panel-body">
                <?php echo form_open(base_url() . 'index.php?teacher/marks', array('class' => 'form-horizontal form-groups-bordered validate', 'target' => '_top')); ?>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo get_phrase('semester'); ?></label>
                        <div class="col-sm-5">
                            <select name="semester_id" class="form-control" onchange="update_exams()">
                                <option value=""><?php echo get_phrase('select_semester'); ?></option>
                                <?php foreach ($semesters as $row): ?>
                                    <option value="<?php echo $row['id']; ?>" <?php if ($semester_id == $row['id']) echo 'selected'; ?>>
                                        <?php echo $row['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo get_phrase('class'); ?></label>
                        <div class="col-sm-5">
                            <select name="class_id" class="form-control" onchange="get_class_subjects(this.value)">
                                <option value=""><?php echo get_phrase('select_class'); ?></option>
                                <?php foreach ($classes as $row): ?>
                                    <option value="<?php echo $row['class_id']; ?>" <?php if ($class_id == $row['class_id']) echo 'selected'; ?>>
                                        <?php echo $row['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo get_phrase('subject'); ?></label>
                        <div class="col-sm-5">
                            <select name="subject_id" class="form-control" id="subject_selector_holder" onchange="update_exams()">
                                <option value=""><?php echo get_phrase('select_subject'); ?></option>
                                <?php if ($class_id > 0): ?>
                                    <?php
                                        $subjects = $this->db->get_where('subject', array('class_id' => $class_id, 'teacher_id' => $this->session->userdata('teacher_id')))->result_array();
                                        foreach ($subjects as $row):
                                    ?>
                                        <option value="<?php echo $row['subject_id']; ?>" <?php if ($subject_id == $row['subject_id']) echo 'selected'; ?>>
                                            <?php echo $row['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo get_phrase('exam'); ?></label>
                        <div class="col-sm-5">
                            <select name="teacher_exam_id" class="form-control" id="exam_selector_holder">
                                <option value=""><?php echo get_phrase('select_exam'); ?></option>
                                <?php if ($semester_id > 0 && $class_id > 0 && $subject_id > 0): ?>
                                    <?php
                                        $this->db->select('te.teacher_exam_id, te.name');
                                        $this->db->from('teacher_exam te');
                                        $this->db->join('subject s', 's.subject_id = te.subject_id AND s.class_id = te.class_id AND s.teacher_id = te.teacher_id', 'inner');
                                        $this->db->where(array(
                                            'te.teacher_id' => $this->session->userdata('teacher_id'),
                                            'te.semester_id' => $semester_id,
                                            'te.class_id' => $class_id,
                                            'te.subject_id' => $subject_id
                                        ));
                                        $exams = $this->db->get()->result_array();
                                        foreach ($exams as $row):
                                    ?>
                                        <option value="<?php echo $row['teacher_exam_id']; ?>" <?php if ($teacher_exam_id == $row['teacher_exam_id']) echo 'selected'; ?>>
                                            <?php echo $row['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-5">
                            <input type="hidden" name="operation" value="selection">
                            <button type="submit" class="btn btn-info"><?php echo get_phrase('manage_marks'); ?></button>
                        </div>
                    </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<?php if ($teacher_exam_id > 0 && $class_id > 0 && $subject_id > 0 && $semester_id > 0): ?>
    <?php
        $teacher_id = $this->session->userdata('teacher_id');
        // Validate subject assignment
        $subject_assignment = $this->db->get_where('subject', array(
            'subject_id' => $subject_id,
            'teacher_id' => $teacher_id,
            'class_id' => $class_id
        ))->num_rows();
        
        // Validate exam assignment
        $exam_assignment = $this->db->get_where('teacher_exam', array(
            'teacher_exam_id' => $teacher_exam_id,
            'teacher_id' => $teacher_id,
            'subject_id' => $subject_id,
            'class_id' => $class_id,
            'semester_id' => $semester_id
        ))->row_array();
        
        if ($subject_assignment > 0 && $exam_assignment):
            $semester_id = $exam_assignment['semester_id'];
            $students = $this->crud_model->get_students($class_id);
            if (empty($students)) {
                echo '<div class="alert alert-warning">No students found for the selected class.</div>';
            } else {
                foreach ($students as $row):
                    $verify_data = array(
                        'teacher_exam_id' => $teacher_exam_id,
                        'student_id' => $row['student_id']
                    );
                    $this->db->select('m.*');
                    $this->db->from('mark m');
                    $this->db->join('teacher_exam te', 'te.teacher_exam_id = m.teacher_exam_id', 'inner');
                    $this->db->where(array(
                        'm.teacher_exam_id' => $teacher_exam_id,
                        'm.student_id' => $row['student_id'],
                        'te.semester_id' => $semester_id
                    ));
                    $query = $this->db->get();
                    
                    if ($query->num_rows() < 1) {
                        $this->db->insert('mark', array(
                            'teacher_exam_id' => $teacher_exam_id,
                            'student_id' => $row['student_id'],                            
                            'exam_id' => $exam_assignment['exam_id'],
                            'mark_obtained' => 0,
                            'comment' => ''
                        ));
                    }
                endforeach;
    ?>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <?php echo get_phrase('marks_for') . ' ' . $exam_assignment['name'] . ' (' . $this->db->get_where('class', array('class_id' => $class_id))->row()->name . ' - ' . $this->db->get_where('subject', array('subject_id' => $subject_id))->row()->name . ')'; ?>
                        </div>
                    </div>
                    <div class="panel-body">
                        <?php echo form_open(base_url() . 'index.php?teacher/marks/' . $teacher_exam_id . '/' . $class_id . '/' . $subject_id . '/' . $semester_id, array('class' => 'form-horizontal form-groups-bordered validate')); ?>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th><?php echo get_phrase('roll'); ?></th>
                                        <th><?php echo get_phrase('student'); ?></th>
                                        <th><?php echo get_phrase('marks_obtained') . ' (Out of ' . $exam_assignment['max_score'] . ')'; ?></th>
                                        <th><?php echo get_phrase('percentage'); ?></th>
                                        <th><?php echo get_phrase('comment'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($students as $row):
                                        $this->db->select('m.*');
                                        $this->db->from('mark m');
                                        $this->db->join('teacher_exam te', 'te.teacher_exam_id = m.teacher_exam_id', 'inner');
                                        $this->db->where(array(
                                            'm.teacher_exam_id' => $teacher_exam_id,
                                            'm.student_id' => $row['student_id'],
                                            'te.semester_id' => $semester_id
                                        ));
                                        $query = $this->db->get();
                                        $row2 = $query->num_rows() > 0 ? $query->row_array() : array(
                                            'mark_id' => 0,
                                            'mark_obtained' => 0,
                                            'comment' => ''
                                        );
                                        // Calculate percentage
                                        $percentage = ($exam_assignment['max_score'] > 0) ? number_format(($row2['mark_obtained'] / $exam_assignment['max_score']) * 100, 2) : 0;
                                    ?>
                                        <tr>
                                            <td><?php echo $row['roll']; ?></td>
                                            <td><?php echo $row['name']; ?></td>
                                            <td>
                                                <input type="number" class="form-control" name="mark_obtained_<?php echo $row['student_id']; ?>" value="<?php echo $row2['mark_obtained']; ?>" max="<?php echo $exam_assignment['max_score']; ?>" min="0">
                                            </td>
                                            <td><?php echo $percentage; ?>%</td>
                                            <td>
                                                <textarea name="comment_<?php echo $row['student_id']; ?>" class="form-control"><?php echo $row2['comment']; ?></textarea>
                                            </td>
                                            <input type="hidden" name="mark_id_<?php echo $row['student_id']; ?>" value="<?php echo $row2['mark_id']; ?>">
                                            <input type="hidden" name="student_id[]" value="<?php echo $row['student_id']; ?>">
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <input type="hidden" name="operation" value="update">
                            <input type="hidden" name="teacher_exam_id" value="<?php echo $teacher_exam_id; ?>">
                            <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                            <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
                            <input type="hidden" name="semester_id" value="<?php echo $semester_id; ?>">
                            <div class="form-group">
                                <div class="col-sm-offset-3 col-sm-5">
                                    <button type="submit" class="btn btn-info"><?php echo get_phrase('update_marks'); ?></button>
                                </div>
                            </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    <?php
            }
        else:
            echo '<div class="alert alert-danger">Invalid subject or exam assignment for this teacher.</div>';
        endif; ?>
<?php endif; ?>

<script type="text/javascript">
function get_class_subjects(class_id) {
    if (class_id) {
        $.ajax({
            url: '<?php echo base_url(); ?>index.php?teacher/get_class_subjects/' + class_id,
            success: function(response) {
                console.log('Subjects response:', response);
                jQuery('#subject_selector_holder').html(response);
                var subject_id = '<?php echo $subject_id; ?>';
                if (subject_id) {
                    jQuery('#subject_selector_holder').val(subject_id);
                }
                jQuery('#exam_selector_holder').html('<option value=""><?php echo get_phrase('select_exam'); ?></option>');
                update_exams();
            },
            error: function(xhr, status, error) {
                console.log('Error fetching subjects:', error);
                jQuery('#subject_selector_holder').html('<option value=""><?php echo get_phrase('select_subject'); ?></option>');
                jQuery('#exam_selector_holder').html('<option value=""><?php echo get_phrase('select_exam'); ?></option>');
            }
        });
    } else {
        jQuery('#subject_selector_holder').html('<option value=""><?php echo get_phrase('select_subject'); ?></option>');
        jQuery('#exam_selector_holder').html('<option value=""><?php echo get_phrase('select_exam'); ?></option>');
    }
}

function update_exams() {
    var semester_id = jQuery('select[name="semester_id"]').val();
    var class_id = jQuery('select[name="class_id"]').val();
    var subject_id = jQuery('select[name="subject_id"]').val();
    
    if (semester_id && class_id && subject_id) {
        $.ajax({
            url: '<?php echo base_url(); ?>index.php?teacher/get_teacher_exams/' + semester_id + '/' + class_id + '/' + subject_id,
            success: function(response) {
                console.log('Exams response:', response);
                jQuery('#exam_selector_holder').html(response);
                var teacher_exam_id = '<?php echo $teacher_exam_id; ?>';
                if (teacher_exam_id) {
                    jQuery('#exam_selector_holder').val(teacher_exam_id);
                }
            },
            error: function(xhr, status, error) {
                console.log('Error fetching exams:', error);
                jQuery('#exam_selector_holder').html('<option value=""><?php echo get_phrase('select_exam'); ?></option>');
            }
        });
    } else {
        jQuery('#exam_selector_holder').html('<option value=""><?php echo get_phrase('select_exam'); ?></option>');
    }
}

jQuery(document).ready(function() {
    var class_id = '<?php echo $class_id; ?>';
    var semester_id = '<?php echo $semester_id; ?>';
    var subject_id = '<?php echo $subject_id; ?>';
    var teacher_exam_id = '<?php echo $teacher_exam_id; ?>';
    
    if (class_id && semester_id && subject_id && teacher_exam_id) {
        get_class_subjects(class_id);
    }
});
</script>