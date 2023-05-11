<?php
    // This file is part of AHT's plugin Mastercourse in Moodle 

    /**
     * This file is a page that display create form to create mastercourse
     * all functions here are self-contained and can be used in ABORT_AFTER_CONFIG scripts.
     *
     * @package local/mastercourse
     * @copyright 2023 Hieu Do <hieu271101@gmail.com>
     * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    require_once(__DIR__ . '/../../config.php'); // load config file of moodle

    global $DB;
    
    require_login();
    require_admin();
    $context = context_system::instance(); // get set the instance default
 
    $PAGE->set_url(new moodle_url('/local/mastercourse/enrolmastercourses.php'));  // set the url of this add a course in to mastercourse page
    $PAGE->set_context(\context_system::instance()); // set the instance default
    $PAGE->set_title('Enrol master course');// set title of page
    $PAGE->set_heading('Enrol Master Course'); // set heading of page

    $PAGE->add_body_class('limitedwidth'); // set the layout of page is full screen or limited
    require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php'); // load manager file from mastercourse
    require_once($CFG->dirroot.'/local/mastercourse/classes/form/enrolmastercourse.php'); // load enrol course form from mastercourse
    
    $mastercourseid = optional_param('mastercourseid', null, PARAM_INT); // get parameter of mastercourse to add course
    // this is form of enrol user
    $mform = new enrolmastercourse(); // initiate the enrol course form from mastercourse
    
    if ($mform->is_cancelled()) {
    // Go back to managemastercourse.php page
    redirect($CFG->wwwroot . '/local/mastercourse/managemastercourse.php?mastercourseid=', get_string('cancelled_form', 'local_mastercourse'));

    } else if ($fromform = $mform->get_data()) { // check if the add course is cancelled 
    $manager = new manager(); //initiate the manager of mastercourse 
    $manager->enrol_mastercourse_byemail($fromform->id, $fromform->roleid, $fromform->iduser); // execute enrol student to mastercourse

    // Go back to manage.php page
    redirect($CFG->wwwroot . '/local/mastercourse/managemastercourse.php?mastercourseid='.$fromform->id, get_string('created_form', 'local_mastercourse') . $fromform->messagetext);
    }

     // check if the page has paramter mastercourse id
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
 