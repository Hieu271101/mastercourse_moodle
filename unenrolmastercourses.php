
<?php
     require_once(__DIR__ . '/../../config.php');

     global $DB;
     
     require_login();
     require_admin();
     $context = context_system::instance();
    
     $PAGE->set_url(new moodle_url('/local/mastercourse/unenrolmastercourses.php'));
   
     require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php');
     
     $mastercourseid = optional_param('mastercourseid', null, PARAM_INT);
     $userid = optional_param('userid', null, PARAM_INT);
     
     $manager = new manager();
     
    if($manager->unenrol_mastercourse($mastercourseid, $userid)){
        redirect($CFG->wwwroot . '/local/mastercourse/managemastercourse.php?mastercourseid='.$mastercourseid, get_string('created_form', 'local_mastercourse') . $fromform->messagetext);
    }else{
        redirect($CFG->wwwroot . '/local/mastercourse/managemastercourse.php?mastercourseid='.$mastercourseid, get_string('cancelled_form', 'local_mastercourse'));
    }
     