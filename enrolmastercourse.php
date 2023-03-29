<?php
    require_once(__DIR__ . '/../../config.php');
    global $DB;
    
    require_login();
    require_admin();
    $context = context_system::instance();
    require_capability('local/message:managemessages', $context);

    $PAGE->set_url(new moodle_url('/local/message/enrolmastercourse.php'));
    $PAGE->set_context(\context_system::instance());
    $PAGE->set_title('Enrol master course');
    $PAGE->set_heading('Enrol Master Course');
    $PAGE->requires->js_call_amd('local_message/confirm');
    $PAGE->requires->css('/local/message/styles.css');
    $PAGE->add_body_class('limitedwidth');
    
    require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php');
    require_once($CFG->dirroot.'/local/mastercourse/classes/form/edit.php');
    require_once($CFG->dirroot.'/local/mastercourse/classes/form/addcourseform.php');
    $messages = $DB->get_records('local_message', null, 'id');
    
    $messageid = optional_param('categoryid', null, PARAM_INT);
    $courseshasmastercourseid =  (array)$DB->get_records_sql('SELECT id FROM `mdl_course` WHERE `mdl_course`.`id_mastercourse` = 2');
    foreach ($courseshasmastercourseid as $value) {
    echo $value;
  }
  die;
    // this is form of enrol user
    $mform = new edit();
    $addform = new addcourseform();

    if ($messageid) {
        // Add extra data to the form.
        global $DB;
        $manager = new manager();
        $message = $manager->get_course($messageid);
        // $fform->idmastercourse = $messageid;
        $mform->set_data($message);
        // $edit = new edit();
        // $message = $manager->idmastercourse($messageid);
        if (!$message) {
            // throw new invalid_parameter_exception('Message not found');
        }
        // $mform->set_data($message);
        
    }
    // die;
    if ($mform->is_cancelled()) {
    // Go back to manage.php page
    redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('cancelled_form', 'local_message'));

    } 
    else if ($fromform = $mform->get_data()) {
    $manager = new manager();
    
    $manager->enrol_mastercourse($fromform->id, $fromform->roleid, $fromform->idmastercourse);   
    // SELECT id FROM `mdl_course` WHERE `mdl_course`.`id_mastercourse` = 1
    
    
    

    // $enrolplugin = $manager->enrol_get_plugin('manual');
    //     $instances = $manager->enrol_get_instances($this->course->id, true);
    //     foreach ($instances as $instance) {
    //         if ($instance->enrol === 'manual') {
    //             break;
    //         }
    //     }
    //     if ($instance->enrol !== 'manual') {
    //         throw new coding_exception('No manual enrol plugin in course');
    //     }
    //     $role = $DB->get_record('role', array('shortname' => 'student'), '*', MUST_EXIST);

    //     for ($number = 1; $number <= $count; $number++) {
    //         // Enrol user.
    //         $enrolplugin->enrol_user($instance, $this->userids[$number], $role->id);
    //         $this->dot($number, $count);
    //     } 


    // // Go back to manage.php page
    redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('created_form', 'local_message') . $fromform->messagetext);
    }

    

    echo $OUTPUT->header();
    $mform->display();
    
    $items = $user = $DB->get_record('course', ['id_mastercourse' => $messageid]);


    $templatecontext = (object)[
        'mastercourse' => array_values((array)$items),
        'viewurl' => new moodle_url('/local/mastercourse/enrolmastercourse.php'),
    ];
  
    echo $OUTPUT->render_from_template('local_mastercourse/index', $templatecontext);

    echo $OUTPUT->footer();
   
    // end...
    // echo $OUTPUT->header();
    // $templatecontext = (object)[
    //     'messages' => array_values($messages),
    //     'editurl' => new moodle_url('/local/message/edit.php'),
    //     'bulkediturl' => new moodle_url('/local/message/bulkedit.php'),
    // ];
    
    // echo $OUTPUT->render_from_template('local_mastercourse/enrolmastercourse', $templatecontext);
    
    // echo $OUTPUT->footer();
    