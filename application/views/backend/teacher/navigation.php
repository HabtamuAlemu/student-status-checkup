<div class="sidebar-menu">
    <header class="logo-env">
        <!-- logo -->
        <div class="logo" style="">
            <a href="<?php echo base_url(); ?>">
                <img src="Uploads/logo.png" style="max-height:60px;"/>
            </a>
        </div>

        <!-- logo collapse icon -->
        <div class="sidebar-collapse" style="">
            <a href="#" class="sidebar-collapse-icon with-animation">
                <i class="entypo-menu"></i>
            </a>
        </div>

        <!-- open/close menu icon (do not remove if you want to enable menu on mobile devices) -->
        <div class="sidebar-mobile-menu visible-xs">
            <a href="#" class="with-animation">
                <i class="entypo-menu"></i>
            </a>
        </div>
    </header>

    <div style="border-top:1px solid rgba(69, 74, 84, 0.7);"></div>	
    <ul id="main-menu" class="">
        <!-- add class "multiple-expanded" to allow multiple submenus to open -->
        <!-- class "auto-inherit-active-class" will automatically add "active" class for parent elements who are marked already with class "active" -->

        <!-- DASHBOARD -->
        <li class="<?php if ($page_name == 'dashboard') echo 'active'; ?> ">
            <a href="<?php echo base_url(); ?>index.php?<?php echo $account_type; ?>/dashboard">
                <i class="entypo-gauge"></i>
                <span><?php echo ('Dashboard'); ?></span>
            </a>
        </li>

        <!-- STUDENT -->
        <li class="<?php
        if (
                $page_name == 'student_information' )
            echo 'opened active has-sub';
        ?> ">
            <a href="#">
                <i class="fa fa-group"></i>
                <span><?php echo ('My Student'); ?></span>
            </a>
            <ul>
                <!-- STUDENT INFORMATION -->
                <li class="<?php if ($page_name == 'student_information') echo 'opened active'; ?> ">
                    <a href="#">
                        <span><i class="entypo-dot"></i> <?php echo ('Student Information'); ?></span>
                    </a>
                    <ul>
<?php
// Get the logged-in teacher's ID
$teacher_id = $this->session->userdata('teacher_id');
// Query to get classes and subjects where the teacher is assigned
$this->db->select('class.class_id, class.name as class_name, subject.subject_id, subject.name as subject_name');
$this->db->from('class');
$this->db->join('subject', 'subject.class_id = class.class_id', 'left');
$this->db->where('subject.teacher_id', $teacher_id);
$this->db->group_by(['class.class_id', 'subject.subject_id']);
$teacher_assignments = $this->db->get()->result_array();

foreach ($teacher_assignments as $row):
?>
                            <li class="<?php if ($page_name == 'student_information' && $class_id == $row['class_id'] && $subject_id == $row['subject_id']) echo 'active'; ?>">
                                <a href="<?php echo base_url(); ?>index.php?<?php echo $account_type; ?>/student_information/<?php echo $row['class_id']; ?>/<?php echo $row['subject_id']; ?>">
                                    <span><?php echo ('Class'); ?> <?php echo $row['class_name']; ?> - <?php echo $row['subject_name']; ?></span>
                                </a>
                            </li>
<?php endforeach; ?>
                    </ul>
                </li>

                
            </ul>
        </li>

        

        <!-- CLASS ROUTINE -->
        <li class="<?php if ($page_name == 'class_routine') echo 'active'; ?> ">
            <a href="<?php echo base_url(); ?>index.php?<?php echo $account_type; ?>/class_routine">
                <i class="entypo-target"></i>
                <span><?php echo ('Class Routine'); ?></span>
            </a>
        </li>
        
        <!-- EXAMS -->
        <li class="<?php
        if ($page_name == 'exam' ||                
                $page_name == 'marks')
                        echo 'opened active';
        ?> ">
            <a href="#">
                <i class="entypo-graduation-cap"></i>
                <span><?php echo ('Exam Section'); ?></span>
            </a>
            <ul>
                <li class="<?php if ($page_name == 'exam') echo 'active'; ?> ">
                    <a href="<?php echo base_url(); ?>index.php?teacher/exam">
                        <span><i class="entypo-dot"></i> <?php echo ('Exam List'); ?></span>
                    </a>
                </li>                
                <li class="<?php if ($page_name == 'marks') echo 'active'; ?> ">
                    <a href="<?php echo base_url(); ?>index.php?teacher/marks">
                        <span><i class="entypo-dot"></i> <?php echo ('Manage Marks'); ?></span>
                    </a>
                </li>               
            </ul>
        </li>   

        <!-- DAILY ATTENDANCE -->
        <li class="<?php if ($page_name == 'manage_attendance') echo 'active'; ?> ">
            <a href="<?php echo base_url(); ?>index.php?<?php echo $account_type; ?>/manage_attendance/<?php echo date("d/m/Y"); ?>">
                <i class="entypo-chart-area"></i>
                <span><?php echo ('Daily Attendance'); ?></span>
            </a>
        </li>

        <!-- NOTICEBOARD -->
        <li class="<?php if ($page_name == 'noticeboard') echo 'active'; ?> ">
            <a href="<?php echo base_url(); ?>index.php?<?php echo $account_type; ?>/noticeboard">
                <i class="entypo-doc-text-inv"></i>
                <span><?php echo ('Noticeboard'); ?></span>
            </a>
        </li>

        <!-- MESSAGE -->
        <li class="<?php if ($page_name == 'message') echo 'active'; ?> ">
            <a href="<?php echo base_url(); ?>index.php?<?php echo $account_type; ?>/message">
                <i class="entypo-mail"></i>
                <span><?php echo ('Message'); ?></span>
            </a>
        </li>

        <!-- ACCOUNT -->
        <li class="<?php if ($page_name == 'manage_profile') echo 'active'; ?> ">
            <a href="<?php echo base_url(); ?>index.php?<?php echo $account_type; ?>/manage_profile">
                <i class="entypo-lock"></i>
                <span><?php echo ('Account'); ?></span>
            </a>
        </li>
    </ul>
</div>
