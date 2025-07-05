<?php
// Verify student login
if ($this->session->userdata('login_type') != 'student') {
    redirect(base_url(), 'refresh');
}

$student_id = $this->session->userdata('login_user_id');
$student = $this->db->get_where('student', array('student_id' => $student_id))->row();
$class_id = $student->class_id;
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    <i class="entypo-calendar"></i> 
                    <?php echo ('My Attendance Records'); ?>
                </div>
            </div>
            <div class="panel-body">
                <!-- Attendance Filter Form -->
                <form method="post" action="<?php echo base_url();?>index.php?student/attendance" class="form-horizontal form-groups-bordered">
                    <div class="row">
                        <div class="col-md-3">
                            <select name="month" class="form-control">
                                <option value=""><?php echo ('Select Month');?></option>
                                <?php 
                                $months = array(
                                    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                                );
                                foreach($months as $num => $name): ?>
                                    <option value="<?php echo $num;?>"
                                        <?php if(isset($month) && $month==$num)echo 'selected="selected"';?>>
                                            <?php echo $name;?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="year" class="form-control">
                                <option value=""><?php echo ('Select Year');?></option>
                                <?php for($i=date('Y');$i>=2010;$i--):?>
                                    <option value="<?php echo $i;?>"
                                        <?php if(isset($year) && $year==$i)echo 'selected="selected"';?>>
                                            <?php echo $i;?>
                                    </option>
                                <?php endfor;?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-info">
                                <i class="entypo-search"></i> <?php echo ('Filter');?>
                            </button>
                        </div>
                    </div>
                </form>

                <hr />

                <!-- Attendance Summary -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="tile-stats tile-green">
                            <div class="icon"><i class="entypo-check"></i></div>
                            <div class="num"><?php echo $this->student_model->get_attendance_percentage($student_id, 1); ?>%</div>
                            <h3>Present Days</h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="tile-stats tile-red">
                            <div class="icon"><i class="entypo-cancel"></i></div>
                            <div class="num"><?php echo $this->student_model->get_attendance_percentage($student_id, 2); ?>%</div>
                            <h3>Absent Days</h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="tile-stats tile-blue">
                            <div class="icon"><i class="entypo-calendar"></i></div>
                            <div class="num"><?php echo $this->student_model->get_total_attendance_days($student_id); ?></div>
                            <h3>Total Days</h3>
                        </div>
                    </div>
                </div>

                <hr />

                <!-- Attendance Records Table -->
                <table class="table table-bordered table-hover table-striped datatable">
                    <thead>
                        <tr>
                            <th width="10%"><div><?php echo ('Date');?></div></th>
                            <th width="15%"><div><?php echo ('Day');?></div></th>
                            <th width="10%"><div><?php echo ('Status');?></div></th>
                            <th><div><?php echo ('Remarks');?></div></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get filtered attendance records
                        $this->db->where('student_id', $student_id);
                        if (isset($month) && $month != '') {
                            $this->db->where('MONTH(date)', $month);
                        }
                        if (isset($year) && $year != '') {
                            $this->db->where('YEAR(date)', $year);
                        }
                        $this->db->order_by('date', 'DESC');
                        $attendance = $this->db->get('attendance')->result_array();
                        
                        foreach($attendance as $row):
                            $date = date('d-m-Y', strtotime($row['date']));
                            $day = date('l', strtotime($row['date']));
                        ?>
                        <tr>
                            <td><?php echo $date; ?></td>
                            <td><?php echo $day; ?></td>
                            <td align="center">
                                <?php if ($row['status'] == 1): ?>
                                    <span class="badge badge-success">Present</span>
                                <?php elseif ($row['status'] == 2): ?>
                                    <span class="badge badge-danger">Absent</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Not Marked</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $row['remarks'] ? $row['remarks'] : '--'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- DataTable Initialization -->
<script type="text/javascript">
    jQuery(document).ready(function($) {
        $('.datatable').DataTable({
            "order": [[0, "desc"]],
            "pageLength": 25,
            "dom": "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
                   "<'row'<'col-sm-12'tr>>" +
                   "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            "language": {
                "emptyTable": "No attendance records found",
                "search": "_INPUT_",
                "searchPlaceholder": "Search records..."
            }
        });
    });
</script>