<?php
    require_once(__DIR__ . '/../../config.php');

    global $DB;
    
    require_login();
    require_admin();
    $context = context_system::instance();
    require_capability('local/message:managemessages', $context);

    $PAGE->set_url(new moodle_url('/local/message/deletecourses.php'));
    $PAGE->set_context(\context_system::instance());
    $PAGE->set_title('Delete course');
    $PAGE->set_heading('Delete Course');
    $PAGE->requires->js_call_amd('local_message/confirm');
    $PAGE->requires->css('/local/message/styles.css');
    $PAGE->add_body_class('limitedwidth');
    require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php');
    require_once($CFG->dirroot.'/local/mastercourse/classes/form/addcourseform.php');
    
    $mastercourseid = optional_param('mastercourseid', null, PARAM_INT);
    // this is form of enrol user
    $mform = new addcourseform();
    
    if ($mform->is_cancelled()) {
    // Go back to manage.php page
    redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('cancelled_form', 'local_message'));

    } else if ($fromform = $mform->get_data()) {
    $manager = new manager();
    $manager->deletecourse($fromform->idcourse, $fromform->id);

    // Go back to manage.php page
    redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('created_form', 'local_message') . $fromform->messagetext);
    }

    if ($mastercourseid) {
        // Add extra data to the form.
   
        global $DB;
        $manager = new manager();
        $message = $manager->get_course($mastercourseid);
        // $fform->idmastercourse = $messageid;
        $mform->set_data($message);
        // $edit = new edit();
        // $message = $manager->idmastercourse($messageid);
        if (!$message) {
            throw new invalid_parameter_exception('Message not found');
        }
        // $mform->set_data($message);
        
    } 
    else {
        redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('cancelled_form', 'local_message'));
    }

    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
 