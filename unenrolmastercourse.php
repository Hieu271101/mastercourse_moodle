<?php
    require_once(__DIR__ . '/../../config.php');
    global $DB;    
    require_login();
    require_admin();
    $context = context_system::instance();
    

    $PAGE->set_url(new moodle_url('/local/mastercourse/unenrolmastercourse.php'));
    $PAGE->set_context(\context_system::instance());
    $PAGE->set_title('Unenrol master course');
    $PAGE->set_heading('Unenrol Master Course');

    $PAGE->add_body_class('limitedwidth');
    
    require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php');
    require_once($CFG->dirroot.'/local/mastercourse/classes/form/unenrolmastercourse.php');
    require_once($CFG->dirroot.'/local/mastercourse/classes/form/addcourseform.php');
    
   
    $messageid = optional_param('mastercourseid', null, PARAM_INT);

    
    $mform = new unenrolmastercourseform();

    if ($mform->is_cancelled()) {
        // Go back to manage.php page
        redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('cancelled_form', 'local_mastercourse'));
    
    } else if ($fromform = $mform->get_data()) {
        $manager = new manager();
    
        $manager->unenrol_mastercourse($fromform->id, $fromform->iduser); 
        // Go back to manage.php page
        redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('created_form', 'local_mastercourse') . $fromform->messagetext);
    }

    if ($messageid) {
        // Add extra data to the form.
        global $DB;
        $manager = new manager();
        $message = $manager->get_course($messageid);
        $mform->set_data($message);
    }else{
        redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('cancelled_form', 'local_mastercourse'));
    }
  
    echo $OUTPUT->header();
    $mform->display();
    
    $items = $DB->get_records('course', ['id_mastercourse' => $messageid]);
    $users = $DB->get_records_sql('SELECT *
                                 FROM `mdl_user_enrol_mastercourse` 
                                 INNER JOIN `mdl_user` 
                                 ON `mdl_user`.`id` =`mdl_user_enrol_mastercourse`.`id_user`  
                                 WHERE `mdl_user_enrol_mastercourse`.`id_mastercourse`='.$messageid);

    $templatecontext = (object)[
        'users' => array_values((array)$users),
        'mastercourse' => array_values((array)$items),
        'viewurl' => new moodle_url('/local/mastercourse/enrolmastercourse.php'),
    ];
    // echo $OUTPUT->render_from_template('local_mastercourse/listuser', $templatecontext);

    echo $OUTPUT->footer();