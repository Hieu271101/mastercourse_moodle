<?php
    require_once(__DIR__ . '/../../config.php');
    global $DB;    
    require_login();
    require_admin();
    $context = context_system::instance();


    $PAGE->set_url(new moodle_url('/local/mastercourse/managemastercourse.php'));
    $PAGE->set_context(\context_system::instance());
    $PAGE->set_title('Manage Master Course');
    $PAGE->set_heading('Manage Master Course');

    
    require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php');
    require_once($CFG->dirroot.'/local/mastercourse/classes/form/edit.php');
    require_once($CFG->dirroot.'/local/mastercourse/classes/form/addcourseform.php');
   
    
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
        // $message = $manager->get_course($messageid);
        // $fform->idmastercourse = $messageid;
        $addform->set_data($message);
        // $edit = new edit();
        // $message = $manager->idmastercourse($messageid);
        if (!$message) {
            throw new invalid_parameter_exception('Message not found');
        }
        // $mform->set_data($message);
        
    } 
    else {
        redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('cancelled_form', 'local_mastercourse'));
    }

    

    echo $OUTPUT->header();
    $mastercourse = $DB->get_records('course_master', ['id' => $messageid]);
   
  
    $course = $DB->get_records_sql('SELECT *, 
                                    `mdl_coursemaster_course`.id as ccid,
                                    `mdl_coursemaster_course`.`sortorder`as cmcso
                                                                FROM `mdl_coursemaster_course` 
                                                                INNER JOIN `mdl_course` 
                                                                ON `mdl_course`.`id` = `mdl_coursemaster_course`.`id_course`  
                                                                WHERE `mdl_coursemaster_course`.`id_mastercourse`= '.$messageid.' ORDER BY cmcso' );
    $users = $DB->get_records_sql('SELECT *
                                 FROM `mdl_user_enrol_mastercourse` 
                                 INNER JOIN `mdl_user` 
                                 ON `mdl_user`.`id` = `mdl_user_enrol_mastercourse`.`id_user`  
                                --  INNER JOIN `mdl_role` 
                                --  ON `mdl_role`.`id` = `mdl_user_enrol_mastercourse`.`role_id`  
                                 WHERE `mdl_user_enrol_mastercourse`.`id_mastercourse`= '.$messageid);
//   SELECT u.id ,u.firstname, u.lastname, u.email , r.shortname as rolename 
        
//   FROM `mdl_user_enrol_mastercourse` as uem
//   INNER JOIN `mdl_user` as u
//   ON `u`.`id` = `uem`.`id_user`  
//   INNER JOIN `mdl_role` as r
//   ON `r`.`id` = `uem`.`role_id`  
//   WHERE `uem`.`id_mastercourse`= 2

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
    
    echo $OUTPUT->render_from_template('local_mastercourse/manage', $templatecontext);
    echo "<h4>Add Course</h4>";
    $addform->display();
    echo $OUTPUT->render_from_template('local_mastercourse/listcourse', $templatecontext);
    
    echo $OUTPUT->render_from_template('local_mastercourse/listuser', $templatecontext);

    
    echo $OUTPUT->footer();

?>
