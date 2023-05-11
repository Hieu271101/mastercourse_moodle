
<?php
    // This file is part of AHT's plugin Mastercourse in Moodle 

    /**
     * This file is executing the process delete a course
     * all functions here are self-contained and can be used in ABORT_AFTER_CONFIG scripts.
     *
     * @package local/mastercourse
     * @copyright 2023 Hieu Do <hieu271101@gmail.com>
     * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
     require_once(__DIR__ . '/../../config.php');
     require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php');
     global $DB;
     
     require_login();
     require_admin();
     $context = context_system::instance(); // set the instance default
 
     $PAGE->set_url(new moodle_url('/local/mastercourse/deletecourses.php')); // set the url of this delete a course 
     
     $mastercourseid = optional_param('mastercourseid', null, PARAM_INT); // get paramter mastercourseid from url
     $courseid = optional_param('courseid', null, PARAM_INT); // get paramter courseid from url
     
     $manager = new manager();
     
    if($manager->deletecourse($courseid,$mastercourseid)){ 
        // redirect to instance managermastercourse page
        redirect($CFG->wwwroot . '/local/mastercourse/managemastercourse.php?mastercourseid='.$mastercourseid, get_string('created_form', 'local_mastercourse') . $fromform->messagetext);
    }else{
        redirect($CFG->wwwroot . '/local/mastercourse/managemastercourse.php?mastercourseid='.$mastercourseid, get_string('cancelled_form', 'local_mastercourse'));
    }
     