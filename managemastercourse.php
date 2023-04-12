<?php
    require_once(__DIR__ . '/../../config.php');
    global $DB;    
    require_login();
    require_admin();
    $context = context_system::instance();
    require_capability('local/message:managemessages', $context);

    $PAGE->set_url(new moodle_url('/local/mastercourse/managemastercourse.php'));
    $PAGE->set_context(\context_system::instance());
    $PAGE->set_title('Manage Master Course');
    $PAGE->set_heading('Manage Master Course');
    // $PAGE->requires->js_call_amd('local_message/confirm');
    // $PAGE->requires->css('/local/message/styles.css');
    $PAGE->add_body_class('limitedwidth');
    
    require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php');
    require_once($CFG->dirroot.'/local/mastercourse/classes/form/edit.php');
    require_once($CFG->dirroot.'/local/mastercourse/classes/form/addcourseform.php');
    $messages = $DB->get_records('local_message', null, 'id');
    
    $messageid = optional_param('mastercourseid', null, PARAM_INT);

    $mform = new edit();
    $addform = new addcourseform();

    if ($messageid) {
        // Add extra data to the form.
        global $DB;
        $manager = new manager();
        $message = $manager->get_course($messageid);
        $mform->set_data($message);
  
        
    }else{
        redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('cancelled_form', 'local_message'));
    }
  
    if ($mform->is_cancelled()) {
    // Go back to manage.php page
    redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('cancelled_form', 'local_message'));

    } 
    else if ($fromform = $mform->get_data()) {
    $manager = new manager();
    
    // $manager->enrol_mastercourse($fromform->id, $fromform->roleid, $fromform->idmastercourse);   
    
    $manager->enrol_mastercourse_byemail($fromform->id, $fromform->roleid, $fromform->iduser); 
    // // Go back to manage.php page
    redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('created_form', 'local_message') . $fromform->messagetext);
    }

    echo $OUTPUT->header();
    $mastercourse = $DB->get_records('course_master', ['id' => $messageid]);
    $courses = $DB->get_records('course', ['id_mastercourse' => $messageid]);
  
    $course = $DB->get_records_sql('SELECT *
                                    FROM `mdl_coursemaster_course` 
                                    INNER JOIN `mdl_course` 
                                    ON `mdl_course`.`id` = `mdl_coursemaster_course`.`id_course`  
                                    WHERE `mdl_coursemaster_course`.`id_mastercourse`= '.$messageid);
    $users = $DB->get_records_sql('SELECT *
                                 FROM `mdl_user_enrol_mastercourse` 
                                 INNER JOIN `mdl_user` 
                                 ON `mdl_user`.`id` = `mdl_user_enrol_mastercourse`.`id_user`  
                                 WHERE `mdl_user_enrol_mastercourse`.`id_mastercourse`= '.$messageid);


    $templatecontext = (object)[
        // 'id' => array_values((array)$messageid),
        'users' => array_values((array)$users),
        'mastercourse' => array_values((array)$mastercourse),
        'courses' => array_values((array)$course),
        'addcourse' => new moodle_url('/local/mastercourse/addcourse.php'),
        'deletecourse'  => new moodle_url('/local/mastercourse/deletecourse.php'),
        'createmastercoursecourse' => new moodle_url('/local/mastercourse/createmastercourse.php'),
        'enrolmastercourse' => new moodle_url('/local/mastercourse/enrolmastercourses.php'),
        'unenrolmastercourse' => new moodle_url('/local/mastercourse/unenrolmastercourse.php'),
    ];
    echo $OUTPUT->render_from_template('local_mastercourse/manage', $templatecontext);
    echo $OUTPUT->render_from_template('local_mastercourse/listuser', $templatecontext);
    echo $OUTPUT->render_from_template('local_mastercourse/listcourse', $templatecontext);

    
    echo $OUTPUT->footer();