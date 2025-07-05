<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-md-12">
        <!-- Exam List -->
        <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    <i class="entypo-list"></i>
                    <?php echo get_phrase('exam_list'); ?>
                </div>
            </div>
            <div class="panel-body">
                <table class="table table-bordered datatable" id="table_export">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo get_phrase('exam_name'); ?></th>
                            <th><?php echo get_phrase('class'); ?></th>
                            <th><?php echo get_phrase('subject'); ?></th>
                            <th><?php echo get_phrase('semester'); ?></th>
                            <th><?php echo get_phrase('date'); ?></th>
                            <th><?php echo get_phrase('max_score'); ?></th>
                            <th><?php echo get_phrase('exam_percent'); ?></th>
                            <th><?php echo get_phrase('comment'); ?></th>
                            <th><?php echo get_phrase('action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $count = 1; foreach ($exams as $row): ?>
                            <tr>
                                <td><?php echo $count++; ?></td>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['class_name']; ?></td>
                                <td><?php echo $row['subject_name']; ?></td>
                                <td><?php echo $row['semester_name']; ?></td>
                                <td><?php echo $row['date']; ?></td>
                                <td><?php echo $row['max_score']; ?></td>
                                <td><?php echo $row['exam_percent']; ?>%</td>
                                <td><?php echo $row['comment']; ?></td>
                                <td>
                                    <a href="<?php echo base_url(); ?>index.php?teacher/exam/edit/<?php echo $row['teacher_exam_id']; ?>" class="btn btn-info btn-sm">
                                        <i class="entypo-pencil"></i> <?php echo get_phrase('edit'); ?>
                                    </a>
                                    <a href="<?php echo base_url(); ?>index.php?teacher/exam/delete/<?php echo $row['teacher_exam_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure to delete?')">
                                        <i class="entypo-trash"></i> <?php echo get_phrase('delete'); ?>
                                    </a>
                                    <a href="<?php echo base_url(); ?>index.php?teacher/marks/<?php echo $row['teacher_exam_id']; ?>/<?php echo $row['class_id']; ?>/<?php echo $row['subject_id']; ?>/<?php echo $row['semester_id']; ?>" class="btn btn-success btn-sm">
                                        <i class="entypo-gauge"></i> <?php echo get_phrase('manage'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Exam Form (Add/Edit) -->
        <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    <i class="entypo-plus-circled"></i>
                    <?php echo isset($edit_data) ? get_phrase('edit_exam') : get_phrase('add_exam'); ?>
                </div>
            </div>
            <div class="panel-body">
                <?php 
                $action = isset($edit_data) ? 'edit/do_update/' . $edit_data[0]['teacher_exam_id'] : 'create';
                echo form_open(base_url() . 'index.php?teacher/exam/' . $action, array('class' => 'form-horizontal form-groups-bordered validate', 'target' => '_top')); 
                ?>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo get_phrase('semester'); ?></label>
                        <div class="col-sm-5">
                            <select name="semester_id" class="form-control" required>
                                <option value=""><?php echo get_phrase('select_semester'); ?></option>
                                <?php foreach ($semesters as $row): ?>
                                    <option value="<?php echo $row['id']; ?>" 
                                        <?php echo isset($edit_data) && $edit_data[0]['semester_id'] == $row['id'] ? 'selected' : ''; ?>>
                                        <?php echo $row['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo get_phrase('class'); ?></label>
                        <div class="col-sm-5">
                            <select name="class_id" class="form-control" required>
                                <option value=""><?php echo get_phrase('select_class'); ?></option>
                                <?php 
                                // Fetch all classes from the class table
                                $all_classes = $this->db->get('class')->result_array();
                                foreach ($all_classes as $row): 
                                ?>
                                    <option value="<?php echo $row['class_id']; ?>" 
                                        <?php echo isset($edit_data) && $edit_data[0]['class_id'] == $row['class_id'] ? 'selected' : ''; ?>>
                                        <?php echo $row['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo get_phrase('subject'); ?></label>
                        <div class="col-sm-5">
                            <select name="subject_id" class="form-control" required>
                                <option value=""><?php echo get_phrase('select_subject'); ?></option>
                                <?php foreach ($subjects as $row): ?>
                                    <option value="<?php echo $row['subject_id']; ?>" 
                                        <?php echo isset($edit_data) && $edit_data[0]['subject_id'] == $row['subject_id'] ? 'selected' : ''; ?>>
                                        <?php echo $row['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo get_phrase('exam_name'); ?></label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" name="name" 
                                   value="<?php echo isset($edit_data) ? $edit_data[0]['name'] : ''; ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo get_phrase('date'); ?></label>
                        <div class="col-sm-5">
                            <input type="date" class="form-control" name="date" 
                                   value="<?php echo isset($edit_data) ? $edit_data[0]['date'] : ''; ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo get_phrase('max_score'); ?></label>
                        <div class="col-sm-5">
                            <input type="number" class="form-control" name="max_score" min="0" 
                                   value="<?php echo isset($edit_data) ? $edit_data[0]['max_score'] : ''; ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo get_phrase('exam_percent'); ?></label>
                        <div class="col-sm-5">
                            <input type="number" class="form-control" name="exam_percent" min="0" max="100" 
                                   value="<?php echo isset($edit_data) ? $edit_data[0]['exam_percent'] : ''; ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo get_phrase('comment'); ?></label>
                        <div class="col-sm-5">
                            <textarea class="form-control" name="comment"><?php echo isset($edit_data) ? $edit_data[0]['comment'] : ''; ?></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-5">
                            <button type="submit" class="btn btn-info">
                                <?php echo isset($edit_data) ? get_phrase('update_exam') : get_phrase('add_exam'); ?>
                            </button>
                        </div>
                    </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#table_export').DataTable();
    });
</script>