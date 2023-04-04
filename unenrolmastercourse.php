<?php
    require_once(__DIR__ . '/../../config.php');
    global $DB;    
    require_login();
    require_admin();
    $context = context_system::instance();
    require_capability('local/message:managemessages', $context);

    $PAGE->set_url(new moodle_url('/local/message/unenrolmastercourse.php'));
    $PAGE->set_context(\context_system::instance());
    $PAGE->set_title('Unenrol master course');
    $PAGE->set_heading('Unenrol Master Course');
    // $PAGE->requires->js_call_amd('local_message/confirm');
    // $PAGE->requires->css('/local/message/styles.css');
    $PAGE->add_body_class('limitedwidth');
    
    require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php');
    require_once($CFG->dirroot.'/local/mastercourse/classes/form/unenrolmastercourse.php');
    require_once($CFG->dirroot.'/local/mastercourse/classes/form/addcourseform.php');
    $messages = $DB->get_records('local_message', null, 'id');
    
    $messageid = optional_param('mastercourseid', null, PARAM_INT);

    $mform = new unenrolmastercourseform();
    $addform = new addcourseform();

    if ($messageid) {
        // Add extra data to the form.
        global $DB;
        $manager = new manager();
        $message = $manager->get_course($messageid);
        $mform->set_data($message);
  
        
    }
    // die;
    if ($mform->is_cancelled()) {
    // Go back to manage.php page
    redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('cancelled_form', 'local_message'));

    } 
    else if ($fromform = $mform->get_data()) {
    $manager = new manager();
    
    // $manager->enrol_mastercourse($fromform->id, $fromform->roleid, $fromform->idmastercourse);   
    
    $manager->enrol_mastercourse_byemail($fromform->id, $fromform->roleid, $fromform->idmastercourse); 
    // // Go back to manage.php page
    redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('created_form', 'local_message') . $fromform->messagetext);
    }

    echo $OUTPUT->header();
    $mform->display();
    
    $items = $DB->get_records('course', ['id_mastercourse' => $messageid]);
    // $users = 

    $templatecontext = (object)[
        'users' => array_values((array)$users),
        'mastercourse' => array_values((array)$items),
        'viewurl' => new moodle_url('/local/mastercourse/enrolmastercourse.php'),
    ];
    echo $OUTPUT->render_from_template('local_mastercourse/listuser', $templatecontext);

    echo $OUTPUT->footer();