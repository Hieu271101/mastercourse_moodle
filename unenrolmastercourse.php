<?php
    require_once(__DIR__ . '/../../config.php');
    global $DB;    
    require_login();
    require_admin();
    $context = context_system::instance();
    require_capability('local/message:managemessages', $context);

    $PAGE->set_url(new moodle_url('/local/mastercourse/unenrolmastercourse.php'));
    $PAGE->set_context(\context_system::instance());
    $PAGE->set_title('Unenrol master course');
    $PAGE->set_heading('Unenrol Master Course');
    // $PAGE->requires->js_call_amd('local_message/confirm');
    // $PAGE->requires->css('/local/message/styles.css');
    $PAGE->add_body_class('limitedwidth');
    
    require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php');
    require_once($CFG->dirroot.'/local/mastercourse/classes/form/unenrolmastercourse.php');
    require_once($CFG->dirroot.'/local/mastercourse/classes/form/addcourseform.php');
    
    // $ueid    = required_param('ue', PARAM_INT); // user enrolment id
    // $confirm = optional_param('confirm', false, PARAM_BOOL);
    // $filter  = optional_param('ifilter', 0, PARAM_INT);
    

    // $ue = $DB->get_record('user_enrolments', array('id' => $ueid), '*', MUST_EXIST);
    // $user = $DB->get_record('user', array('id'=>$ue->userid), '*', MUST_EXIST);
    // $instance = $DB->get_record('enrol', array('id'=>$ue->enrolid), '*', MUST_EXIST);
    // $course = $DB->get_record('course', array('id'=>$instance->courseid), '*', MUST_EXIST);    
    // $context = context_course::instance($course->id);

    $messageid = optional_param('mastercourseid', null, PARAM_INT);

    
    $mform = new unenrolmastercourseform();

    if ($mform->is_cancelled()) {
        // Go back to manage.php page
        redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('cancelled_form', 'local_message'));
    
    } else if ($fromform = $mform->get_data()) {
        $manager = new manager();
        // $manager->enrol_mastercourse_byemail($fromform->id, $fromform->roleid, $fromform->iduser); 
        $manager->unenrol_mastercourse($fromform->id, $fromform->iduser); 
        // Go back to manage.php page
        redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('created_form', 'local_message') . $fromform->messagetext);
    }

    if ($messageid) {
        // Add extra data to the form.
        global $DB;
        $manager = new manager();
        $message = $manager->get_course($messageid);
        $mform->set_data($message);
    }else{
        redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('cancelled_form', 'local_message'));
    }
  
    // if ($mform->is_cancelled()) {
    // // Go back to manage.php page
    // redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('cancelled_form', 'local_message'));

    // } 
    // else if ($fromform = $mform->get_data()) {
    // $manager = new manager();
    // $manager->unenrol_mastercourse($fromform->id, $fromform->iduser); 
    // // // Go back to manage.php page
    // redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('created_form', 'local_message') . $fromform->messagetext);
    // }

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