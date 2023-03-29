<?php

// use local_mastercourse\form\edit;
// use local_mastercourse\manager;
    
    require_once(__DIR__ . '/../../config.php');

    global $DB;
    
    require_login();
    require_admin();
    $context = context_system::instance();
    require_capability('local/message:managemessages', $context);

    $PAGE->set_url(new moodle_url('/local/message/enrolmastercourse.php'));
    $PAGE->set_context(\context_system::instance());
    $PAGE->set_title('Create master course');
    $PAGE->set_heading('Create Master Course');
    $PAGE->requires->js_call_amd('local_message/confirm');
    $PAGE->requires->css('/local/message/styles.css');
    $PAGE->add_body_class('limitedwidth');
    require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php');
    require_once($CFG->dirroot.'/local/mastercourse/classes/form/createmastercourse.php');
    
    // this is form of enrol user
    $mform = new createmastercourse();
    
    if ($mform->is_cancelled()) {
    // Go back to manage.php page
    redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('cancelled_form', 'local_message'));

    } else if ($fromform = $mform->get_data()) {
    $manager = new manager();

    // if ($fromform->id) {
    //     // We are updating an existing message.
    //     $manager->update_message($fromform->id, $fromform->messagetext, $fromform->messagetype);
    //     redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('updated_form', 'local_message') . $fromform->messagetext);
    // }

     $manager->createmastercourse($fromform->namemastercourse);

    // Go back to manage.php page
    redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('created_form', 'local_message') . $fromform->messagetext);
    }

   

    echo $OUTPUT->header();
    $mform->display();
    
    

    echo $OUTPUT->footer();
 