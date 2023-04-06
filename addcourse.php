<?php
    require_once(__DIR__ . '/../../config.php');
    global $DB;
    
    require_login();
    require_admin();
    $context = context_system::instance();
    require_capability('local/message:managemessages', $context);

    $PAGE->set_url(new moodle_url('/local/mastercourse/addcourse.php'));
    $PAGE->set_context(\context_system::instance());
    $PAGE->set_title('Add course');
    $PAGE->set_heading('Add Course');
    $PAGE->requires->js_call_amd('local_message/confirm');
    $PAGE->requires->css('/local/message/styles.css');
    $PAGE->add_body_class('limitedwidth');

    require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php');
    require_once($CFG->dirroot.'/local/mastercourse/classes/form/addcourseform.php');
    
    $messages = $DB->get_records('local_message', null, 'id');
    
    $mastercourseid = optional_param('mastercourseid', null, PARAM_INT);
    
    // this is add course to mastercourse
    $addform = new addcourseform();
   
    if ($mastercourseid) {
        // Add extra data to the form.
   
        global $DB;
        $manager = new manager();
        $message = $manager->get_course($mastercourseid);
        // $fform->idmastercourse = $messageid;
        $addform->set_data($message);
        // $edit = new edit();
        // $message = $manager->idmastercourse($messageid);
        if (!$message) {
            // throw new invalid_parameter_exception('Message not found');
        }
        // $mform->set_data($message);
        
    } else {
        redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('cancelled_form', 'local_message'));
    }

    if ($addform->is_cancelled()) {
    // Go back to manage.php page
    redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('cancelled_form', 'local_message'));

    } else if ($fromform = $addform->get_data()) {
    $manager = new manager();
    $manager->addcourse($fromform->idcourse, $fromform->id);

    // Go back to index.php page
    redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('created_form', 'local_message') . $fromform->messagetext);
    }

    echo $OUTPUT->header();
    $addform->display();
    $templatecontext = (object)[
        'viewurl' => new moodle_url('/local/mastercourse/enrolmastercourse.php'),
    ];
    
    echo $OUTPUT->render_from_template('local_mastercourse/index', $templatecontext);

    echo $OUTPUT->footer();
    