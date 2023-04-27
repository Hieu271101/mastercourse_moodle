<?php
    require_once(__DIR__ . '/../../config.php');

    global $DB;
    
    require_login();
    require_admin();
    $context = context_system::instance();
 

    $PAGE->set_url(new moodle_url('/local/mastercourse/enrolmastercourses.php'));
    $PAGE->set_context(\context_system::instance());
    $PAGE->set_title('Enrol master course');
    $PAGE->set_heading('Enrol Master Course');
    $PAGE->requires->js_call_amd('local_message/confirm');

    $PAGE->add_body_class('limitedwidth');
    require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php');
    require_once($CFG->dirroot.'/local/mastercourse/classes/form/edit.php');
    
    $mastercourseid = optional_param('mastercourseid', null, PARAM_INT);
    // this is form of enrol user
    $mform = new edit();
    
    if ($mform->is_cancelled()) {
    // Go back to manage.php page
    redirect($CFG->wwwroot . '/local/mastercourse/managemastercourse.php?mastercourseid=', get_string('cancelled_form', 'local_mastercourse'));

    } else if ($fromform = $mform->get_data()) {
    $manager = new manager();
    $manager->enrol_mastercourse_byemail($fromform->id, $fromform->roleid, $fromform->iduser); 

    // Go back to manage.php page
    redirect($CFG->wwwroot . '/local/mastercourse/managemastercourse.php?mastercourseid='.$fromform->id, get_string('created_form', 'local_mastercourse') . $fromform->messagetext);
    }

    if ($mastercourseid) {
        // Add extra data to the form.
   
        global $DB;
        $manager = new manager();
        $classTemp = new stdClass();
        $classTemp->id = $mastercourseid;
        $message = $classTemp;//$manager->get_course($mastercourseid);
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
        redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('cancelled_form', 'local_mastercourse'));
    }

    if ($mastercourseid) {
        // Add extra data to the form.
        global $DB;
        $manager = new manager();
        $message = $manager->get_course($mastercourseid);
        $mform->set_data($message);
  
        
    } else {
        redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('cancelled_form', 'local_message'));
    }
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
 