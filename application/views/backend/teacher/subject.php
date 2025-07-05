<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>


<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs bordered">
            <li class="active">
                <a href="#home" data-toggle="tab">
                    <span class="hidden-xs"><?php echo get_phrase('manage_subject');?></span>
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="home">
                <table class="table table-bordered datatable" id="table_export">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo get_phrase('class');?></th>
                            <th><?php echo get_phrase('subject');?></th>
                            <th><?php echo get_phrase('semester');?></th>
                            <th><?php echo get_phrase('options');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $count = 1;
                        $teacher_id = $this->session->userdata('teacher_id');
                        $this->db->select('s.*, sem.name as semester_name');
                        $this->db->from('subject s');
                        $this->db->join('subject_assessments sa', 's.subject_id = sa.subject_id', 'left');
                        $this->db->join('semesters sem', 'sa.semester_id = sem.id AND sem.is_current = 1', 'left');
                        $this->db->where('s.teacher_id', $teacher_id);
                        $subjects = $this->db->get()->result_array();
                        foreach($subjects as $row):
                        ?>
                        <tr>
                            <td><?php echo $count++;?></td>
                            <td><?php echo $this->crud_model->get_type_name_by_id('class', $row['class_id']);?></td>
                            <td><?php echo $row['name'];?></td>
                            <td><?php echo $row['semester_name'];?></td>
                            <td>
                                <a href="<?php echo base_url();?>index.php?teacher/student_information/<?php echo $row['class_id'];?>/<?php echo $row['subject_id'];?>" 
                                   class="btn btn-info btn-xs">
                                    <i class="entypo-users"></i> <?php echo get_phrase('view_students');?>
                                </a>
                                <a href="<?php echo base_url();?>index.php?teacher/download_student_list/<?php echo $row['class_id'];?>/<?php echo $row['subject_id'];?>" 
                                   class="btn btn-success btn-xs">
                                    <i class="entypo-download"></i> <?php echo get_phrase('download_student_list');?>
                                </a>
                                <a href="<?php echo base_url();?>index.php?teacher/subject/do_update/<?php echo $row['subject_id'];?>" 
                                   class="btn btn-primary btn-xs">
                                    <i class="entypo-pencil"></i> <?php echo get_phrase('edit');?>
                                </a>
                                <a href="<?php echo base_url();?>index.php?teacher/subject/delete/<?php echo $row['subject_id'];?>/<?php echo $row['class_id'];?>" 
                                   class="btn btn-danger btn-xs" 
                                   onclick="return confirm('<?php echo get_phrase('delete_confirm');?>')">
                                    <i class="entypo-trash"></i> <?php echo get_phrase('delete');?>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach;?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>