<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*  
 *  @author: Farid Ahmed
 *  date: 27 September 2014
 *  SIgnetBD
 *  efarid08@gmail.com
 */

class Teacher extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->model('Crud_model');

        // Set cache control headers
        $this->output->set_header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

        // Ensure teacher is logged in
        $this->_require_teacher();
    }

    // Centralized authentication check
    private function _require_teacher()
    {
        if ($this->session->userdata('teacher_login') != 1) {
            $this->session->set_userdata('last_page', current_url());
            redirect(base_url() . 'index.php?login', 'refresh');
        }
    }

    // Helper to load views with common page data
    private function _load_view($page_name, $page_title, $additional_data = [])
    {
        $page_data = array_merge([
            'page_name' => $page_name,
            'page_title' => get_phrase($page_title)
        ], $additional_data);
        $this->load->view('backend/index', $page_data);
    }

    public function index()
    {
        redirect(base_url() . 'index.php?teacher/dashboard', 'refresh');
    }

    public function dashboard()
    {
        $this->_load_view('dashboard', 'teacher_dashboard');
    }

    // Manage Students
    public function student_add()
    {
        $this->_load_view('student_add', 'add_student');
    }

    public function student_information($class_id = '', $subject_id = '')
{
    $teacher_id = $this->session->userdata('teacher_id');

    // Validate if teacher is assigned to the subject in the class
    $this->db->where([
        'class_id' => $class_id,
        'teacher_id' => $teacher_id,
        'subject_id' => $subject_id
    ]);
    if ($this->db->get('subject')->num_rows() == 0) {
        $this->session->set_flashdata('flash_message', get_phrase('no_subjects_assigned'));
        redirect(base_url() . 'index.php?teacher/dashboard', 'refresh');
    }

    $this->_load_view('student_information', 'student_information - class: ' . $this->crud_model->get_class_name($class_id), [
        'class_id' => $class_id,
        'teacher_id' => $teacher_id,
        'subject_id' => $subject_id
    ]);
}
 public function student_marksheet($class_id = '', $subject_id = '')
{
    $teacher_id = $this->session->userdata('teacher_id');

    // Validate if teacher is assigned to the subject in the class
    $this->db->where([
        'class_id' => $class_id,
        'teacher_id' => $teacher_id,
        'subject_id' => $subject_id
    ]);
    if ($this->db->get('subject')->num_rows() == 0) {
        $this->session->set_flashdata('flash_message', get_phrase('no_subjects_assigned'));
        redirect(base_url() . 'index.php?teacher/dashboard', 'refresh');
    }

    $this->_load_view('student_marksheet', 'student_marksheet - class: ' . $this->crud_model->get_class_name($class_id), [
        'class_id' => $class_id,
        'teacher_id' => $teacher_id,
        'subject_id' => $subject_id
    ]);
}

public function download_student_list($class_id = '', $subject_id = '')
    {
        $teacher_id = $this->session->userdata('teacher_id');

        // Validate if teacher is assigned to the subject in the class
        $this->db->where([
            'class_id' => $class_id,
            'teacher_id' => $teacher_id,
            'subject_id' => $subject_id
        ]);
        if ($this->db->get('subject')->num_rows() == 0) {
            $this->session->set_flashdata('flash_message', get_phrase('no_subjects_assigned'));
            redirect(base_url() . 'index.php?teacher/dashboard', 'refresh');
        }

        // Fetch student information
        $this->db->select('s.student_id, s.name, s.roll, s.email, s.phone, s.address, c.name as class_name');
        $this->db->from('student s');
        $this->db->join('class c', 's.class_id = c.class_id', 'left');
        $this->db->where('s.class_id', $class_id);
        $students = $this->db->get()->result_array();

        // Generate Excel file
        $this->load->library('excel');
        
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        
        // Set headers
        $sheet->setCellValue('A1', 'Student ID');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'Roll');
        $sheet->setCellValue('D1', 'Email');
        $sheet->setCellValue('E1', 'Phone');
        $sheet->setCellValue('F1', 'Address');
        $sheet->setCellValue('G1', 'Class');

        // Add data
        $row_number = 2;
        foreach ($students as $student) {
            $sheet->setCellValue('A' . $row_number, $student['student_id']);
            $sheet->setCellValue('B' . $row_number, $student['name']);
            $sheet->setCellValue('C' . $row_number, $student['roll']);
            $sheet->setCellValue('D' . $row_number, $student['email']);
            $sheet->setCellValue('E' . $row_number, $student['phone']);
            $sheet->setCellValue('F' . $row_number, $student['address']);
            $sheet->setCellValue('G' . $row_number, $student['class_name']);
            $row_number++;
        }

        // Set column widths
        foreach(range('A','G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Set headers for download
        $subject_name = $this->db->get_where('subject', ['subject_id' => $subject_id])->row()->name;
        $class_name = $this->db->get_where('class', ['class_id' => $class_id])->row()->name;
        $filename = "Student_List_{$class_name}_{$subject_name}.xlsx";
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $writer->save('php://output');
        exit;
    }
    public function student($param1 = '', $param2 = '', $param3 = '')
    {
        if ($param1 == 'create') {
            $data = [
                'name' => $this->input->post('name'),
                'birthday' => $this->input->post('birthday'),
                'sex' => $this->input->post('sex'),
                'address' => $this->input->post('address'),
                'phone' => $this->input->post('phone'),
                'email' => $this->input->post('email'),
                'password' => $this->input->post('password'),
                'class_id' => $this->input->post('class_id'),
                'section_id' => $this->input->post('section_id'),
                'parent_id' => $this->input->post('parent_id'),
                'roll' => $this->input->post('roll')
            ];
            $this->db->insert('student', $data);
            $student_id = $this->db->insert_id();
            move_uploaded_file($_FILES['userfile']['tmp_name'], 'Uploads/student_image/' . $student_id . '.jpg');
            $this->email_model->account_opening_email('student', $data['email']);
            $this->session->set_flashdata('flash_message', get_phrase('data_added_successfully'));
            redirect(base_url() . 'index.php?teacher/student_add/' . $data['class_id'], 'refresh');
        }
        if ($param2 == 'do_update') {
            $data = [
                'name' => $this->input->post('name'),
                'birthday' => $this->input->post('birthday'),
                'sex' => $this->input->post('sex'),
                'address' => $this->input->post('address'),
                'phone' => $this->input->post('phone'),
                'email' => $this->input->post('email'),
                'class_id' => $this->input->post('class_id'),
                'section_id' => $this->input->post('section_id'),
                'parent_id' => $this->input->post('parent_id'),
                'roll' => $this->input->post('roll')
            ];
            $this->db->where('student_id', $param3)->update('student', $data);
            move_uploaded_file($_FILES['userfile']['tmp_name'], 'Uploads/student_image/' . $param3 . '.jpg');
            $this->crud_model->clear_cache();
            $this->session->set_flashdata('flash_message', get_phrase('data_updated'));
            redirect(base_url() . 'index.php?teacher/student_information/' . $param1, 'refresh');
        }
        if ($param2 == 'delete') {
            $this->db->where('student_id', $param3)->delete('student');
            $this->session->set_flashdata('flash_message', get_phrase('data_deleted'));
            redirect(base_url() . 'index.php?teacher/student_information/' . $param1, 'refresh');
        }
    }

    public function get_class_section($class_id)
    {
        $sections = $this->db->get_where('section', ['class_id' => $class_id])->result_array();
        foreach ($sections as $row) {
            echo '<option value="' . $row['section_id'] . '">' . $row['name'] . '</option>';
        }
    }

    // Manage Teachers
    public function teacher_list($param1 = '', $param2 = '')
    {
        $page_data = ['teachers' => $this->db->get('teacher')->result_array()];
        if ($param1 == 'personal_profile') {
            $page_data['personal_profile'] = true;
            $page_data['current_teacher_id'] = $param2;
        }
        $this->_load_view('teacher', 'teacher_list', $page_data);
    }

    // Manage Subjects
    public function subject($param1 = '', $param2 = '', $param3 = '')
    {
        if ($param1 == 'create') {
            $data = [
                'name' => $this->input->post('name'),
                'class_id' => $this->input->post('class_id'),
                'teacher_id' => $this->input->post('teacher_id')
            ];
            $this->db->insert('subject', $data);
            $this->session->set_flashdata('flash_message', get_phrase('data_added_successfully'));
            redirect(base_url() . 'index.php?teacher/subject/' . $data['class_id'], 'refresh');
        }
        if ($param1 == 'do_update') {
            $data = [
                'name' => $this->input->post('name'),
                'class_id' => $this->input->post('class_id'),
                'teacher_id' => $this->input->post('teacher_id')
            ];
            $this->db->where('subject_id', $param2)->update('subject', $data);
            $this->session->set_flashdata('flash_message', get_phrase('data_updated'));
            redirect(base_url() . 'index.php?teacher/subject/' . $data['class_id'], 'refresh');
        }
        if ($param1 == 'edit') {
            $page_data['edit_data'] = $this->db->get_where('subject', ['subject_id' => $param2])->result_array();
        }
        if ($param1 == 'delete') {
            $this->db->where('subject_id', $param2)->delete('subject');
            $this->session->set_flashdata('flash_message', get_phrase('data_deleted'));
            redirect(base_url() . 'index.php?teacher/subject/' . $param3, 'refresh');
        }
        $page_data = [
            'class_id' => $param1,
            'subjects' => $this->db->get_where('subject', ['class_id' => $param1])->result_array()
        ];
        $this->_load_view('subject', 'manage_subject', $page_data);
    }

    // Manage Exams (Using teacher_exam table)
    public function exam($param1 = '', $param2 = '', $param3 = '')
    {
        // Allow both admin and teacher access
        if ($this->session->userdata('admin_login') != 1 && $this->session->userdata('teacher_login') != 1) {
            redirect(base_url() . 'index.php?login', 'refresh');
        }

        // Get the logged-in teacher's ID
        $teacher_id = $this->session->userdata('teacher_id');

        if ($param1 == 'create') {
            // Fetch the exam_id based on the provided semester_id
            $semester_id = $this->input->post('semester_id');
            $exam = $this->db->get_where('exam', ['semester_id' => $semester_id])->row_array();
            if (!$exam) {
                $this->session->set_flashdata('flash_message', get_phrase('invalid_semester'));
                redirect(base_url() . 'index.php?teacher/exam', 'refresh');
            }

            $data = [
                'exam_id' => $exam['exam_id'],
                'teacher_id' => $teacher_id,
                'subject_id' => $this->input->post('subject_id'),
                'class_id' => $this->input->post('class_id'),
                'name' => $this->input->post('name'),
                'date' => $this->input->post('date'),
                'max_score' => $this->input->post('max_score'),
                'exam_percent' => $this->input->post('exam_percent'),
                'semester_id' => $semester_id,
                'comment' => $this->input->post('comment')
            ];

            // Validate teacher assignment
            $this->db->where(['teacher_id' => $teacher_id, 'subject_id' => $data['subject_id'], 'class_id' => $data['class_id']]);
            if ($this->db->get('subject')->num_rows() == 0) {
                $this->session->set_flashdata('flash_message', get_phrase('invalid_subject_or_class'));
                redirect(base_url() . 'index.php?teacher/exam', 'refresh');
            }

            $this->db->insert('teacher_exam', $data);
            $this->session->set_flashdata('flash_message', get_phrase('data_added_successfully'));
            redirect(base_url() . 'index.php?teacher/exam', 'refresh');
        }

        if ($param1 == 'edit' && $param2 == 'do_update') {
            // Fetch the exam_id based on the provided semester_id
            $semester_id = $this->input->post('semester_id');
            $exam = $this->db->get_where('exam', ['semester_id' => $semester_id])->row_array();
            if (!$exam) {
                $this->session->set_flashdata('flash_message', get_phrase('invalid_semester'));
                redirect(base_url() . 'index.php?teacher/exam', 'refresh');
            }

            $data = [
                'exam_id' => $exam['exam_id'],
                'teacher_id' => $teacher_id,
                'subject_id' => $this->input->post('subject_id'),
                'class_id' => $this->input->post('class_id'),
                'name' => $this->input->post('name'),
                'date' => $this->input->post('date'),
                'max_score' => $this->input->post('max_score'),
                'exam_percent' => $this->input->post('exam_percent'),
                'semester_id' => $semester_id,
                'comment' => $this->input->post('comment')
            ];

            // Validate teacher assignment
            $this->db->where(['teacher_id' => $teacher_id, 'subject_id' => $data['subject_id'], 'class_id' => $data['class_id']]);
            if ($this->db->get('subject')->num_rows() == 0) {
                $this->session->set_flashdata('flash_message', get_phrase('invalid_subject_or_class'));
                redirect(base_url() . 'index.php?teacher/exam', 'refresh');
            }

            $this->db->where(['teacher_exam_id' => $param3, 'teacher_id' => $teacher_id]);
            $this->db->update('teacher_exam', $data);
            $this->session->set_flashdata('flash_message', get_phrase('data_updated'));
            redirect(base_url() . 'index.php?teacher/exam', 'refresh');
        }

        if ($param1 == 'edit') {
            $page_data['edit_data'] = $this->db->get_where('teacher_exam', ['teacher_exam_id' => $param2, 'teacher_id' => $teacher_id])->result_array();
            if (empty($page_data['edit_data'])) {
                $this->session->set_flashdata('flash_message', get_phrase('invalid_exam'));
                redirect(base_url() . 'index.php?teacher/exam', 'refresh');
            }
        }

        if ($param1 == 'delete') {
            $this->db->where(['teacher_exam_id' => $param2, 'teacher_id' => $teacher_id]);
            $this->db->delete('teacher_exam');
            $this->session->set_flashdata('flash_message', get_phrase('data_deleted'));
            redirect(base_url() . 'index.php?teacher/exam', 'refresh');
        }

        // Fetch exams for the logged-in teacher
        $this->db->select('te.*, c.name as class_name, s.name as subject_name, sem.name as semester_name');
        $this->db->from('teacher_exam te');
        $this->db->join('class c', 'te.class_id = c.class_id', 'left');
        $this->db->join('subject s', 'te.subject_id = s.subject_id', 'left');
        $this->db->join('semesters sem', 'te.semester_id = sem.id', 'left');
        $this->db->where('te.teacher_id', $teacher_id);
        $page_data['exams'] = $this->db->get()->result_array();

        // Fetch classes and subjects for the teacher
        $page_data['classes'] = $this->db->get_where('class', ['teacher_id' => $teacher_id])->result_array();
        $page_data['subjects'] = $this->db->get_where('subject', ['teacher_id' => $teacher_id])->result_array();
        $page_data['semesters'] = $this->db->get('semesters')->result_array();
        $this->_load_view('exam', 'manage_exam', $page_data);
    }

    // Manage Teacher-Specific Exams
    public function teacher_exam($param1 = '', $param2 = '')
    {
        if ($param1 == 'create') {
            // Fetch the exam_id based on the provided semester_id
            $semester_id = $this->input->post('semester_id');
            $exam = $this->db->get_where('exam', ['semester_id' => $semester_id])->row_array();
            if (!$exam) {
                $this->session->set_flashdata('flash_message', get_phrase('invalid_semester'));
                redirect(base_url() . 'index.php?teacher/teacher_exam', 'refresh');
            }

            $data = [
                'exam_id' => $exam['exam_id'],
                'teacher_id' => $this->session->userdata('teacher_id'),
                'subject_id' => $this->input->post('subject_id'),
                'class_id' => $this->input->post('class_id'),
                'name' => $this->input->post('name'),
                'date' => $this->input->post('date'),
                'max_score' => $this->input->post('max_score'),
                'exam_percent' => $this->input->post('exam_percent'),
                'semester_id' => $semester_id,
                'comment' => $this->input->post('comment')
            ];
            // Validate teacher assignment
            $this->db->where(['teacher_id' => $data['teacher_id'], 'subject_id' => $data['subject_id'], 'class_id' => $data['class_id']]);
            if ($this->db->get('subject')->num_rows() == 0) {
                $this->session->set_flashdata('flash_message', get_phrase('invalid_subject_or_class'));
                redirect(base_url() . 'index.php?teacher/teacher_exam', 'refresh');
            }
            $this->db->insert('teacher_exam', $data);
            $this->session->set_flashdata('flash_message', get_phrase('exam_added_successfully'));
            redirect(base_url() . 'index.php?teacher/teacher_exam', 'refresh');
        }
        if ($param1 == 'edit' && $param2) {
            // Fetch the exam_id based on the provided semester_id
            $semester_id = $this->input->post('semester_id');
            $exam = $this->db->get_where('exam', ['semester_id' => $semester_id])->row_array();
            if (!$exam) {
                $this->session->set_flashdata('flash_message', get_phrase('invalid_semester'));
                redirect(base_url() . 'index.php?teacher/teacher_exam', 'refresh');
            }

            $data = [
                'exam_id' => $exam['exam_id'],
                'name' => $this->input->post('name'),
                'date' => $this->input->post('date'),
                'max_score' => $this->input->post('max_score'),
                'exam_percent' => $this->input->post('exam_percent'),
                'semester_id' => $semester_id,
                'class_id' => $this->input->post('class_id'),
                'subject_id' => $this->input->post('subject_id'),
                'comment' => $this->input->post('comment')
            ];
            $this->db->where(['teacher_id' => $this->session->userdata('teacher_id'), 'subject_id' => $data['subject_id'], 'class_id' => $data['class_id']]);
            if ($this->db->get('subject')->num_rows() == 0) {
                $this->session->set_flashdata('flash_message', get_phrase('invalid_subject_or_class'));
                redirect(base_url() . 'index.php?teacher/teacher_exam', 'refresh');
            }
            $this->db->where(['teacher_exam_id' => $param2, 'teacher_id' => $this->session->userdata('teacher_id')])->update('teacher_exam', $data);
            $this->session->set_flashdata('flash_message', get_phrase('exam_updated_successfully'));
            redirect(base_url() . 'index.php?teacher/teacher_exam', 'refresh');
        }
        if ($param1 == 'delete' && $param2) {
            $this->db->where(['teacher_exam_id' => $param2, 'teacher_id' => $this->session->userdata('teacher_id')])->delete('teacher_exam');
            $this->session->set_flashdata('flash_message', get_phrase('exam_deleted_successfully'));
            redirect(base_url() . 'index.php?teacher/teacher_exam', 'refresh');
        }
        $page_data['teacher_exams'] = $this->db->get('teacher_exam')->result_array();
        $this->_load_view('teacher_exam', 'manage_teacher_exams', $page_data);
    }

    // Manage Marks
    public function marks($teacher_exam_id = '', $class_id = '', $subject_id = '', $semester_id = '')
    {
        if ($this->input->post('operation') == 'selection') {
            $teacher_exam_id = $this->input->post('teacher_exam_id');
            $class_id = $this->input->post('class_id');
            $subject_id = $this->input->post('subject_id');
            $semester_id = $this->input->post('semester_id');

            if ($teacher_exam_id > 0 && $class_id > 0 && $subject_id > 0 && $semester_id > 0) {
                redirect(base_url() . 'index.php?teacher/marks/' . $teacher_exam_id . '/' . $class_id . '/' . $subject_id . '/' . $semester_id, 'refresh');
            }
            $this->session->set_flashdata('mark_message', get_phrase('choose_semester_exam_class_subject'));
            redirect(base_url() . 'index.php?teacher/marks', 'refresh');
        }

        if ($this->input->post('operation') == 'update') {
            $teacher_id = $this->session->userdata('teacher_id');
            $teacher_exam_id = $this->input->post('teacher_exam_id');
            $class_id = $this->input->post('class_id');
            $subject_id = $this->input->post('subject_id');
            $semester_id = $this->input->post('semester_id');

            // Validate subject assignment
            $this->db->where([
                'subject_id' => $subject_id,
                'class_id' => $class_id,
                'teacher_id' => $teacher_id
            ]);
            if ($this->db->get('subject')->num_rows() == 0) {
                $this->session->set_flashdata('flash_message', get_phrase('invalid_subject_or_class'));
                redirect(base_url() . 'index.php?teacher/marks', 'refresh');
            }

            // Validate exam assignment
            $this->db->where([
                'teacher_exam_id' => $teacher_exam_id,
                'teacher_id' => $teacher_id,
                'subject_id' => $subject_id,
                'class_id' => $class_id,
                'semester_id' => $semester_id
            ]);
            $exam = $this->db->get('teacher_exam')->row_array();

            if (!$exam) {
                $this->session->set_flashdata('flash_message', get_phrase('invalid_exam'));
                redirect(base_url() . 'index.php?teacher/marks', 'refresh');
            }

            $students = $this->input->post('student_id');

            if (empty($students)) {
                $this->session->set_flashdata('flash_message', get_phrase('no_students_selected'));
                redirect(base_url() . 'index.php?teacher/marks/' . $teacher_exam_id . '/' . $class_id . '/' . $subject_id . '/' . $semester_id, 'refresh');
            }

            foreach ($students as $student_id) {
                $mark_obtained = $this->input->post('mark_obtained_' . $student_id) !== '' ? $this->input->post('mark_obtained_' . $student_id) : 0;
                $comment = $this->input->post('comment_' . $student_id) ?: '';
                $mark_id = $this->input->post('mark_id_' . $student_id);

                // Validate mark_obtained against max_score
                if ($mark_obtained > $exam['max_score'] || $mark_obtained < 0) {
                    $this->session->set_flashdata('flash_message', get_phrase('invalid_mark_for_student') . ' ' . $student_id);
                    continue;
                }

                // Prepare data for insertion or update
                $data = [
                    'teacher_exam_id' => $teacher_exam_id,
                    'student_id' => $student_id,
                    'subject_id' => $subject_id,
                    'class_id' => $class_id,
                    'mark_obtained' => $mark_obtained,
                    'comment' => $comment,
                    'semester_id' => $semester_id,
                    'exam_id' => $exam['exam_id']
                ];

                // Check for existing mark
                $this->db->where([
                    'teacher_exam_id' => $teacher_exam_id,
                    'student_id' => $student_id
                ]);
                $existing_mark = $this->db->get('mark')->row_array();

                if ($existing_mark) {
                    // Update existing mark
                    $this->db->where(['mark_id' => $existing_mark['mark_id']]);
                    $this->db->update('mark', $data);
                } else {
                    // Insert new mark directly with all fields
                    $this->db->insert('mark', $data);
                }
            }

            $this->session->set_flashdata('flash_message', get_phrase('marks_updated_successfully'));
            redirect(base_url() . 'index.php?teacher/marks/' . $teacher_exam_id . '/' . $class_id . '/' . $subject_id . '/' . $semester_id, 'refresh');
        }

        $page_data = [
            'teacher_exam_id' => $teacher_exam_id,
            'class_id' => $class_id,
            'subject_id' => $subject_id,
            'semester_id' => $semester_id,
            'page_info' => 'Exam marks'
        ];
        $this->_load_view('marks', 'manage_exam_marks', $page_data);
    }

    // Fetch Subjects for AJAX
    public function get_class_subjects($class_id)
    {
        $teacher_id = $this->session->userdata('teacher_id');
        $subjects = $this->db->get_where('subject', [
            'class_id' => $class_id,
            'teacher_id' => $teacher_id
        ])->result_array();

        $html = '<option value="">' . get_phrase('select_subject') . '</option>';
        foreach ($subjects as $row) {
            $html .= '<option value="' . $row['subject_id'] . '">' . $row['name'] . '</option>';
        }
        echo $html;
    }

    // Fetch Exams for AJAX
    public function get_teacher_exams($semester_id, $class_id, $subject_id)
    {
        $teacher_id = $this->session->userdata('teacher_id');

        // Select exams from teacher_exam, joining with subject to validate teacher assignment
        $this->db->select('te.teacher_exam_id, te.name');
        $this->db->from('teacher_exam te');
        $this->db->join('subject s', 's.subject_id = te.subject_id AND s.class_id = te.class_id AND s.teacher_id = te.teacher_id', 'inner');
        $this->db->where([
            'te.teacher_id' => $teacher_id,
            'te.semester_id' => $semester_id,
            'te.class_id' => $class_id,
            'te.subject_id' => $subject_id
        ]);
        $exams = $this->db->get()->result_array();

        // Build HTML for dropdown
        $html = '<option value="">' . get_phrase('select_exam') . '</option>';
        foreach ($exams as $row) {
            $html .= '<option value="' . $row['teacher_exam_id'] . '">' . $row['name'] . '</option>';
        }
        echo $html;
    }

    // Backup and Restore
    public function backup_restore($operation = '', $type = '')
    {
        if ($operation == 'create') {
            $this->crud_model->create_backup($type);
        }
        if ($operation == 'restore') {
            $this->crud_model->restore_backup();
            $this->session->set_flashdata('backup_message', 'Backup Restored');
        }
        if ($operation == 'delete') {
            $this->crud_model->truncate($type);
            $this->session->set_flashdata('backup_message', 'Data removed');
        }
        if ($operation) {
            redirect(base_url() . 'index.php?teacher/backup_restore', 'refresh');
        }
        $this->_load_view('backup_restore', 'manage_backup_restore', ['page_info' => 'Create backup / restore from backup']);
    }

    // Manage Profile
    public function manage_profile($param1 = '')
    {
        if ($param1 == 'update_profile_info') {
            $data = [
                'name' => $this->input->post('name'),
                'email' => $this->input->post('email')
            ];
            $this->db->where('teacher_id', $this->session->userdata('teacher_id'))->update('teacher', $data);
            move_uploaded_file($_FILES['userfile']['tmp_name'], 'Uploads/teacher_image/' . $this->session->userdata('teacher_id') . '.jpg');
            $this->session->set_flashdata('flash_message', get_phrase('account_updated'));
            redirect(base_url() . 'index.php?teacher/manage_profile', 'refresh');
        }
        if ($param1 == 'change_password') {
            $current_password = $this->db->get_where('teacher', ['teacher_id' => $this->session->userdata('teacher_id')])->row()->password;
            $password_data = [
                'password' => $this->input->post('password'),
                'new_password' => $this->input->post('new_password'),
                'confirm_new_password' => $this->input->post('confirm_new_password')
            ];
            if ($current_password == $password_data['password'] && $password_data['new_password'] == $password_data['confirm_new_password']) {
                $this->db->where('teacher_id', $this->session->userdata('teacher_id'))->update('teacher', ['password' => $password_data['new_password']]);
                $this->session->set_flashdata('flash_message', get_phrase('password_updated'));
            } else {
                $this->session->set_flashdata('flash_message', get_phrase('password_mismatch'));
            }
            redirect(base_url() . 'index.php?teacher/manage_profile', 'refresh');
        }
        $page_data['edit_data'] = $this->db->get_where('teacher', ['teacher_id' => $this->session->userdata('teacher_id')])->result_array();
        $this->_load_view('manage_profile', 'manage_profile', $page_data);
    }

    // Manage Class Routine
    public function class_routine($param1 = '', $param2 = '', $param3 = '')
    {
        if ($param1 == 'create') {
            $data = [
                'class_id' => $this->input->post('class_id'),
                'subject_id' => $this->input->post('subject_id'),
                'time_start' => $this->input->post('time_start'),
                'time_end' => $this->input->post('time_end'),
                'day' => $this->input->post('day')
            ];
            $this->db->insert('class_routine', $data);
            $this->session->set_flashdata('flash_message', get_phrase('data_added_successfully'));
            redirect(base_url() . 'index.php?teacher/class_routine', 'refresh');
        }
        if ($param1 == 'edit' && $param2 == 'do_update') {
            $data = [
                'class_id' => $this->input->post('class_id'),
                'subject_id' => $this->input->post('subject_id'),
                'time_start' => $this->input->post('time_start'),
                'time_end' => $this->input->post('time_end'),
                'day' => $this->input->post('day')
            ];
            $this->db->where('class_routine_id', $param3)->update('class_routine', $data);
            $this->session->set_flashdata('flash_message', get_phrase('data_updated'));
            redirect(base_url() . 'index.php?teacher/class_routine', 'refresh');
        }
        if ($param1 == 'edit') {
            $page_data['edit_data'] = $this->db->get_where('class_routine', ['class_routine_id' => $param2])->result_array();
        }
        if ($param1 == 'delete') {
            $this->db->where('class_routine_id', $param2)->delete('class_routine');
            $this->session->set_flashdata('flash_message', get_phrase('data_deleted'));
            redirect(base_url() . 'index.php?teacher/class_routine', 'refresh');
        }
        $this->_load_view('class_routine', 'manage_class_routine');
    }

    // Manage Attendance
    public function manage_attendance($date = '', $month = '', $year = '', $class_id = '')
    {
        if ($_POST) {
            $students = $this->db->get_where('student', ['class_id' => $class_id])->result_array();
            foreach ($students as $row) {
                $attendance_status = $this->input->post('status_' . $row['student_id']);
                $this->db->where(['student_id' => $row['student_id'], 'date' => $this->input->post('date')])
                         ->update('attendance', ['status' => $attendance_status]);
            }
            $this->session->set_flashdata('flash_message', get_phrase('data_updated'));
            redirect(base_url() . 'index.php?teacher/manage_attendance/' . $date . '/' . $month . '/' . $year . '/' . $class_id, 'refresh');
        }
        $page_data = [
            'date' => $date,
            'month' => $month,
            'year' => $year,
            'class_id' => $class_id
        ];
        $this->_load_view('manage_attendance', 'manage_daily_attendance', $page_data);
    }

    public function attendance_selector()
    {
        redirect(base_url() . 'index.php?teacher/manage_attendance/' . $this->input->post('date') . '/' .
                $this->input->post('month') . '/' . $this->input->post('year') . '/' . $this->input->post('class_id'), 'refresh');
    }

    // Manage Library
    public function book()
    {
        $page_data['books'] = $this->db->get('book')->result_array();
        $this->_load_view('book', 'manage_library_books', $page_data);
    }

    // Manage Transport
    public function transport()
    {
        $page_data['transports'] = $this->db->get('transport')->result_array();
        $this->_load_view('transport', 'manage_transport', $page_data);
    }

    // Manage Noticeboard
    public function noticeboard($param1 = '', $param2 = '')
    {
        if ($param1 == 'create') {
            $data = [
                'notice_title' => $this->input->post('notice_title'),
                'notice' => $this->input->post('notice'),
                'create_timestamp' => strtotime($this->input->post('create_timestamp'))
            ];
            $this->db->insert('noticeboard', $data);
            $this->session->set_flashdata('flash_message', get_phrase('data_added_successfully'));
            redirect(base_url() . 'index.php?teacher/noticeboard', 'refresh');
        }
        if ($param1 == 'do_update') {
            $data = [
                'notice_title' => $this->input->post('notice_title'),
                'notice' => $this->input->post('notice'),
                'create_timestamp' => strtotime($this->input->post('create_timestamp'))
            ];
            $this->db->where('notice_id', $param2)->update('noticeboard', $data);
            $this->session->set_flashdata('flash_message', get_phrase('notice_updated'));
            redirect(base_url() . 'index.php?teacher/noticeboard', 'refresh');
        }
        if ($param1 == 'edit') {
            $page_data['edit_data'] = $this->db->get_where('noticeboard', ['notice_id' => $param2])->result_array();
        }
        if ($param1 == 'delete') {
            $this->db->where('notice_id', $param2)->delete('noticeboard');
            $this->session->set_flashdata('flash_message', get_phrase('data_deleted'));
            redirect(base_url() . 'index.php?teacher/noticeboard', 'refresh');
        }
        $page_data['notices'] = $this->db->get('noticeboard')->result_array();
        $this->_load_view('noticeboard', 'manage_noticeboard', $page_data);
    }

    // Manage Documents
    public function document($do = '', $document_id = '')
    {
        if ($do == 'upload') {
            $file_name = $_FILES["userfile"]["name"];
            move_uploaded_file($_FILES["userfile"]["tmp_name"], "uploads/document/" . $file_name);
            $data = [
                'document_name' => $this->input->post('document_name'),
                'file_name' => $file_name,
                'file_size' => $_FILES["userfile"]["size"]
            ];
            $this->db->insert('document', $data);
            $this->session->set_flashdata('flash_message', get_phrase('data_added_successfully'));
            redirect(base_url() . 'index.php?teacher/manage_document', 'refresh');
        }
        if ($do == 'delete') {
            $this->db->where('document_id', $document_id)->delete('document');
            $this->session->set_flashdata('flash_message', get_phrase('data_deleted'));
            redirect(base_url() . 'index.php?teacher/manage_document', 'refresh');
        }
        $page_data['documents'] = $this->db->get('document')->result_array();
        $this->_load_view('manage_document', 'manage_documents', $page_data);
    }

    // Manage Study Material
    public function study_material($task = '', $document_id = '')
    {
        if ($task == 'create') {
            $this->crud_model->save_study_material_info();
            $this->session->set_flashdata('flash_message', get_phrase('study_material_info_saved_successfuly'));
            redirect(base_url() . 'index.php?teacher/study_material', 'refresh');
        }
        if ($task == 'update') {
            $this->crud_model->update_study_material_info($document_id);
            $this->session->set_flashdata('flash_message', get_phrase('study_material_info_updated_successfuly'));
            redirect(base_url() . 'index.php?teacher/study_material', 'refresh');
        }
        if ($task == 'delete') {
            $this->crud_model->delete_study_material_info($document_id);
            $this->session->set_flashdata('flash_message', get_phrase('data_deleted'));
            redirect(base_url() . 'index.php?teacher/study_material', 'refresh');
        }
        $page_data['study_material_info'] = $this->crud_model->select_study_material_info();
        $this->_load_view('study_material', 'study_material', $page_data);
    }

    // Private Messaging
    public function message($param1 = 'message_home', $param2 = '')
    {
        if ($param1 == 'send_new') {
            $message_thread_code = $this->crud_model->send_new_private_message();
            $this->session->set_flashdata('flash_message', get_phrase('message_sent'));
            redirect(base_url() . 'index.php?teacher/message/message_read/' . $message_thread_code, 'refresh');
        }
        if ($param1 == 'send_reply') {
            $this->crud_model->send_reply_message($param2);
            $this->session->set_flashdata('flash_message', get_phrase('message_sent'));
            redirect(base_url() . 'index.php?teacher/message/message_read/' . $param2, 'refresh');
        }
        if ($param1 == 'message_read') {
            $page_data['current_message_thread_code'] = $param2;
            $this->crud_model->mark_thread_messages_read($param2);
        }
        $page_data['message_inner_page_name'] = $param1;
        $this->_load_view('message', 'private_messaging', $page_data);
    }
}