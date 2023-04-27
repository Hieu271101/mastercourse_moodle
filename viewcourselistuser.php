<?php
    require_once(__DIR__ . '/../../config.php');
    global $DB;    
    require_login();
    $context = context_system::instance();
   

    $PAGE->set_url(new moodle_url('/local/mastercourse/viewcourselistuser.php'));
    $PAGE->set_context(\context_system::instance());
    $PAGE->set_title('List course In Your Master course');
    $PAGE->set_heading('Course list');
    $PAGE->add_body_class('limitedwidth');

    
    require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php');
    require_once($CFG->dirroot.'/local/mastercourse/classes/form/edit.php');
    require_once($CFG->dirroot.'/local/mastercourse/classes/form/addcourseform.php');
    $messages = $DB->get_records('local_message', null, 'id');
    
    $messageid = optional_param('mastercourseid', null, PARAM_INT);

    $mform = new edit();
    $addform = new addcourseform();
    
    
    if ($addform->is_cancelled()) {
    // Go back to manage.php page
    redirect($CFG->wwwroot . '/local/mastercourse/managemastercourse.php?mastercourseid='.$messageid, get_string('cancelled_form', 'local_mastercourse'));

    } else if ($fromform = $addform->get_data()) {
        $manager = new manager();
        $manager->addcourse($fromform->idcourse, $fromform->id);
    // Go back to manage.php page
        redirect($CFG->wwwroot . '/local/mastercourse/managemastercourse.php?mastercourseid='.$fromform->id, get_string('created_form', 'local_mastercourse') . $fromform->messagetext);
    }

    if ($messageid) {
        // Add extra data to the form.
   
        global $DB;
        $manager = new manager();
        $classTemp = new stdClass();
        $classTemp->id = $messageid;
        $message = $classTemp;

        $addform->set_data($message);

        if (!$message) {
            throw new invalid_parameter_exception('Message not found');
        }

        
    } 
    else {
        redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('cancelled_form', 'local_mastercourse'));
    }

    

    echo $OUTPUT->header();
    $mastercourse = $DB->get_records('course_master', ['id' => $messageid]);
   
  
    $course = $DB->get_records_sql('SELECT *
                                    FROM `mdl_coursemaster_course` 
                                    INNER JOIN `mdl_course` 
                                    ON `mdl_course`.`id` = `mdl_coursemaster_course`.`id_course`  
                                    WHERE `mdl_coursemaster_course`.`id_mastercourse`= '.$messageid);
    $users = $DB->get_records_sql('SELECT *
                                 FROM `mdl_user_enrol_mastercourse` 
                                 INNER JOIN `mdl_user` 
                                 ON `mdl_user`.`id` = `mdl_user_enrol_mastercourse`.`id_user`  
                                --  INNER JOIN `mdl_role` 
                                --  ON `mdl_role`.`id` = `mdl_user_enrol_mastercourse`.`role_id`  
                                 WHERE `mdl_user_enrol_mastercourse`.`id_mastercourse`= '.$messageid);


    $templatecontext = (object)[
        // 'id' => array_values((array)$messageid),
        'users' => array_values((array)$users),
        'mastercourse' => array_values((array)$mastercourse),
        'courses' => array_values((array)$course),
        'mastercourseId' => $messageid,
        'addcourse' => new moodle_url('/local/mastercourse/addcourse.php'),
        'deletecourse'  => new moodle_url('/local/mastercourse/deletecourses.php'),
        'createmastercoursecourse' => new moodle_url('/local/mastercourse/createmastercourse.php'),
        'enrolmastercourse' => new moodle_url('/local/mastercourse/enrolmastercourses.php'),
        'unenrolmastercourse' => new moodle_url('/local/mastercourse/unenrolmastercourses.php'),
        'courselink' => new moodle_url('/course/view.php'),
    ];
    
 
   
    echo $OUTPUT->render_from_template('local_mastercourse/viewcourselistuser', $templatecontext);
        
    echo $OUTPUT->footer();

?>
