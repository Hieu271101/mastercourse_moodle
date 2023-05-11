
<?php
    // This file is part of AHT's plugin Mastercourse in Moodle 

    /**
     * This file is a page that display unerol form of manage mastercourse
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
     $context = context_system::instance();
    
     $PAGE->set_url(new moodle_url('/local/mastercourse/unenrolmastercourses.php'));
   
     require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php'); // load manager mastercourse
     
     $mastercourseid = optional_param('mastercourseid', null, PARAM_INT);
     $userid = optional_param('userid', null, PARAM_INT);
     
     $manager = new manager();
     
    if($manager->unenrol_mastercourse($mastercourseid, $userid)){
        redirect($CFG->wwwroot . '/local/mastercourse/managemastercourse.php?mastercourseid='.$mastercourseid, get_string('created_form', 'local_mastercourse') . $fromform->messagetext);
    }else{
        redirect($CFG->wwwroot . '/local/mastercourse/managemastercourse.php?mastercourseid='.$mastercourseid, get_string('cancelled_form', 'local_mastercourse'));
    }
     