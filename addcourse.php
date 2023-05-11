
<?php
// This file is part of AHT's plugin Mastercourse in Moodle 

/**
 * This file is a page that adding the course into mastercourse
 * all functions here are self-contained and can be used in ABORT_AFTER_CONFIG scripts.
 *
 * @package local/mastercourse
 * @copyright 2023 Hieu Do <hieu271101@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    require_once(__DIR__ . '/../../config.php'); // load config file of moodle

    global $DB; // 
    
    require_login(); 
    require_admin();
    $context = context_system::instance(); //get context of page
    $PAGE->set_url(new moodle_url('/local/mastercourse/addcourses.php')); // set the url of this add a course in to mastercourse page
    $PAGE->set_context(\context_system::instance()); // set the instance default
    $PAGE->set_title('Add course'); // set title of page
    $PAGE->set_heading('Add Course');// set heading of page
   
    $PAGE->add_body_class('limitedwidth'); // set the layout of page is full screen or limited
    require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php'); // load manager file from mastercourse
    require_once($CFG->dirroot.'/local/mastercourse/classes/form/addcourseform.php'); // load add course form from mastercourse
    
    $mastercourseid = optional_param('mastercourseid', null, PARAM_INT); // get parameter of mastercourse to add course
    
    $mform = new addcourseform(); // initiate the add course form from mastercourse
    
    if ($mform->is_cancelled()) { // check if the add course is cancelled 
    // Go back to index.php page of mastercourse
    redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('cancelled_form', 'local_message')); 

    } else if ($fromform = $mform->get_data()) { // if the form is saved 
    $manager = new manager(); //initiate the manager of mastercourse 
    $manager->addcourse($fromform->idcourse, $fromform->id); // execute add course to mastercourse with id of course and id of master course as paramter of add form

    // Go back to index.php page
    redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('created_form', 'local_message') . $fromform->messagetext);
    }

    // check if the page has paramter mastercourse id
    if ($mastercourseid) {
      
        global $DB;
        $manager = new manager(); //initiate the manager of mastercourse 
        $message = $manager->get_course($mastercourseid); // set data to an object
        
        $mform->set_data($message); // set data to add course form  from the message object
     
        if (!$message) {
            throw new invalid_parameter_exception('Message not found'); 
        }
      
    } 
    else {
        // if the form cancel, redirect to managemastercourse page
        redirect($CFG->wwwroot . '/local/mastercourse/managemastercourse.php', get_string('cancelled_form', 'local_message'));
    }

    echo $OUTPUT->header(); // display header moodle
    $mform->display(); //display add course form
    echo $OUTPUT->footer(); // display footer moodle
 