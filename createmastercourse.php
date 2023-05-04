<?php
    require_once(__DIR__ . '/../../config.php');

    global $DB;
    
    require_login();
    require_admin();
    $context = context_system::instance();


    $PAGE->set_url(new moodle_url('/local/mastercourse/enrolmastercourse.php'));
    $PAGE->set_context(\context_system::instance());
    $PAGE->set_title('Create master course');
    $PAGE->set_heading('Create Master Course');
    $PAGE->requires->js_call_amd('local_message/confirm');
 
    $PAGE->add_body_class('limitedwidth');
    require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php');
    require_once($CFG->dirroot.'/local/mastercourse/classes/form/createmastercourse.php');
    $mastercourseid = optional_param('mastercourseid', null, PARAM_INT);
    // this is form of enrol user
    $mform = new createmastercourse();
    
    if ($mform->is_cancelled()) {
    // Go back to manage.php page
    redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('cancelled_form', 'local_mastercourse'));

    } else if ($fromform = $mform->get_data()) {
    $manager = new manager();
    
    $manager->createmastercourse($fromform->id, $fromform->name,$fromform->description);

    // Go back to manage.php page
    redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('created_form', 'local_mastercourse') . $fromform->messagetext);
    }

    if ($mastercourseid) {
        // Add extra data to the form.
   
        global $DB;
        $manager = new manager();
        $message = $manager->get_mastercourse($mastercourseid);
        // $fform->idmastercourse = $messageid;
        // print_r($message);
        // exit();
        $mform->set_data($message);
        // $edit = new edit();
        // $message = $manager->idmastercourse($messageid);
        if (!$message) {
            throw new invalid_parameter_exception('Message not found');
        }
        // $mform->set_data($message);
        
    } 

    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
 