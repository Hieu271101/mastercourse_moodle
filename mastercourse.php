<?php
    require_once(__DIR__ . '/../../config.php');

    global $DB;
    
    require_login();
    require_admin();
    $context = context_system::instance(); // get instance context

    $PAGE->set_url(new moodle_url('/local/mastercourse/mastercourse.php'));
    $PAGE->set_context(\context_system::instance());
    $PAGE->set_title('Master Course');
    $PAGE->set_heading('Master Course');
    $PAGE->add_body_class('limitedwidth');

    require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php'); // load manager mastercourse
    require_once($CFG->dirroot.'/local/mastercourse/classes/form/enrolmastercourse.php'); // load enrolmastercourse 
    
    $mastercourseid = optional_param('mastercourseid', null, PARAM_INT); // get paramter of mastercourse
    // this is form of enrol user
    $mform = new enrolmastercourse();
    
    if ($mform->is_cancelled()) {
    // Go back to managemastercourse.php page
    redirect($CFG->wwwroot . '/local/mastercourse/managemastercourse.php?mastercourseid=', get_string('cancelled_form', 'local_mastercourse'));

    } else if ($fromform = $mform->get_data()) {
    $manager = new manager();
    $manager->enrol_mastercourse_byemail($fromform->id, $fromform->roleid, $fromform->iduser); 

    // Go back to managemastercourse.php page
    redirect($CFG->wwwroot . '/local/mastercourse/managemastercourse.php?mastercourseid='.$fromform->id, get_string('created_form', 'local_mastercourse') . $fromform->messagetext);
    }

    if ($mastercourseid) {
        // Add extra data to the form.
   
        global $DB;
        $manager = new manager();
        $classTemp = new stdClass();
        $classTemp->id = $mastercourseid;
        $message = $classTemp;
        $mform->set_data($message);
        if (!$message) {
            throw new invalid_parameter_exception('Message not found');
        }
        
    } 
    else {
        // redirect to index
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
 