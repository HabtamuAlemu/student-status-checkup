<div class="row">
    <div class="col-md-8">
        <div class="row">
            <!-- CALENDAR -->
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
            <!-- TEACHER PROFILE -->
            <div class="col-md-12 col-xs-12">
                <div class="panel panel-info" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <i class="fa fa-user"></i>
                            <?php echo ('Teacher Profile');?>
                        </div>
                    </div>
                    <div class="panel-body">
                        <?php
                        $teacher_id = $this->session->userdata('teacher_id');
                        $this->db->where('teacher_id', $teacher_id);
                        $teacher = $this->db->get('teacher')->row_array();
                        ?>
                        <p><strong>Name:</strong> <?php echo $teacher['name']; ?></p>
                        <p><strong>Email:</strong> <?php echo $teacher['email']; ?></p>
                        <p><strong>Phone:</strong> <?php echo $teacher['phone']; ?></p>
                        <p><strong>Address:</strong> <?php echo $teacher['address']; ?></p>
                        <p><strong>Birthday:</strong> <?php echo $teacher['birthday']; ?></p>
                        <p><strong>Sex:</strong> <?php echo $teacher['sex']; ?></p>
                        <?php if (!empty($teacher['blood_group'])): ?>
                            <p><strong>Blood Group:</strong> <?php echo $teacher['blood_group']; ?></p>
                        <?php endif; ?>
                        <?php if (!empty($teacher['religion'])): ?>
                            <p><strong>Religion:</strong> <?php echo $teacher['religion']; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- ASSIGNED CLASSES AND SECTIONS -->
            <div class="col-md-12 col-xs-12">
                <div class="panel panel-success" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <i class="fa fa-chalkboard"></i>
                            <?php echo ('Assigned Classes and Sections');?>
                        </div>
                    </div>
                    <div class="panel-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th>Nickname</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $this->db->select('c.name as class_name, s.name as section_name, s.nick_name');
                                $this->db->from('section s');
                                $this->db->join('class c', 's.class_id = c.class_id', 'left');
                                $this->db->where('s.teacher_id', $teacher_id);
                                $sections = $this->db->get()->result_array();
                                foreach ($sections as $section):
                                ?>
                                <tr>
                                    <td><?php echo $section['class_name']; ?></td>
                                    <td><?php echo $section['section_name']; ?></td>
                                    <td><?php echo $section['nick_name']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- ASSIGNED SUBJECTS -->
            <div class="col-md-12 col-xs-12">
                <div class="panel panel-warning" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <i class="fa fa-book"></i>
                            <?php echo ('Assigned Subjects');?>
                        </div>
                    </div>
                    <div class="panel-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Class</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $this->db->select('s.name as subject_name, c.name as class_name');
                                $this->db->from('subject s');
                                $this->db->join('class c', 's.class_id = c.class_id', 'left');
                                $this->db->where('s.teacher_id', $teacher_id);
                                $subjects = $this->db->get()->result_array();
                                foreach ($subjects as $subject):
                                ?>
                                <tr>
                                    <td><?php echo $subject['subject_name']; ?></td>
                                    <td><?php echo $subject['class_name']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="row">
            <div class="col-md-12">
                <div class="tile-stats tile-red">
                    <div class="icon"><i class="fa fa-university"></i></div>
                    <div class="num" data-start="0" 
                         data-end="<?php 
                            $teacher_id = $this->session->userdata('teacher_id');
                            $this->db->distinct();
                            $this->db->select('class_id');
                            $this->db->where('teacher_id', $teacher_id);
                            $classes = $this->db->get('class')->num_rows();
                            $this->db->distinct();
                            $this->db->select('class_id');
                            $this->db->where('teacher_id', $teacher_id);
                            $sections = $this->db->get('section')->num_rows();
                            echo $classes + $sections;
                         ?>" 
                         data-postfix="" data-duration="1500" data-delay="0">0</div>
                    <h3><?php echo ('Assigned Classes');?></h3>
                    <p>Total classes and sections assigned</p>
                </div>
            </div>
            <div class="col-md-12">
                <div class="tile-stats tile-green">
                    <div class="icon"><i class="fa fa-book"></i></div>
                    <div class="num" data-start="0" 
                         data-end="<?php 
                            $this->db->where('teacher_id', $teacher_id);
                            echo $this->db->count_all_results('subject');
                         ?>" 
                         data-postfix="" data-duration="800" data-delay="0">0</div>
                    <h3><?php echo ('Assigned Subjects');?></h3>
                    <p>Total subjects taught</p>
                </div>
            </div>
            <div class="col-md-12">
                <div class="tile-stats tile-aqua">
                    <div class="icon"><i class="fa fa-users"></i></div>
                    <div class="num" data-start="0" 
                         data-end="<?php 
                            $this->db->select('s.student_id');
                            $this->db->from('student s');
                            $this->db->join('class c', 's.class_id = c.class_id', 'left');
                            $this->db->join('section sec', 's.section_id = sec.section_id', 'left');
                            $this->db->where('c.teacher_id', $teacher_id);
                            $this->db->or_where('sec.teacher_id', $teacher_id);
                            echo $this->db->count_all_results();
                         ?>" 
                         data-postfix="" data-duration="500" data-delay="0">0</div>
                    <h3><?php echo ('Students');?></h3>
                    <p>Total students in assigned classes</p>
                </div>
            </div>
            <div class="col-md-12">
                <div class="tile-stats tile-blue">
                    <div class="icon"><i class="fa fa-check-circle"></i></div>
                    <div class="num" data-start="0" 
                         data-end="<?php 
                            $this->db->select('a.attendance_id');
                            $this->db->from('attendance a');
                            $this->db->join('student s', 'a.student_id = s.student_id', 'inner');
                            $this->db->join('class c', 's.class_id = c.class_id', 'left');
                            $this->db->join('section sec', 's.section_id = sec.section_id', 'left');
                            $this->db->where('a.date', date('Y-m-d'));
                            $this->db->where('a.status', 1);
                            $this->db->where('c.teacher_id', $teacher_id);
                            $this->db->or_where('sec.teacher_id', $teacher_id);
                            echo $this->db->count_all_results();
                         ?>" 
                         data-postfix="" data-duration="500" data-delay="0">0</div>
                    <h3><?php echo ('Attendance');?></h3>
                    <p>Present students today in assigned classes</p>
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