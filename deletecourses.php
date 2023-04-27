
<?php
     require_once(__DIR__ . '/../../config.php');
     require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php');
     global $DB;
     
     require_login();
     require_admin();
     $context = context_system::instance();

 
     $PAGE->set_url(new moodle_url('/local/mastercourse/deletecourses.php'));
   
   
     
     $mastercourseid = optional_param('mastercourseid', null, PARAM_INT);
     $courseid = optional_param('courseid', null, PARAM_INT);
     
     $manager = new manager();
     
    if($manager->deletecourse($courseid,$mastercourseid)){
        redirect($CFG->wwwroot . '/local/mastercourse/managemastercourse.php?mastercourseid='.$mastercourseid, get_string('created_form', 'local_mastercourse') . $fromform->messagetext);
    }else{
        redirect($CFG->wwwroot . '/local/mastercourse/managemastercourse.php?mastercourseid='.$mastercourseid, get_string('cancelled_form', 'local_mastercourse'));
    }
     