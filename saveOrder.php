
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

    require_once(__DIR__ . '/../../config.php'); // load config.php
    require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php'); // load manager
    global $DB;
     
    require_login();
    require_admin();
    $context = context_system::instance(); // get context of default page
    $PAGE->set_url(new moodle_url('/local/mastercourse/saveOrder.php')); // set url saveOrder

    $coursesInMastercourse = $_POST["courseInMastercourse"]; // get _POST of mustache template
    $courseOrders = $_POST["courseoder"]; // get _POST of mustache template
   
    $manager = new manager(); // load manager mastercourse

    // set number order according to id of enrolmastercourse
    for ($i = 0; $i < count($coursesInMastercourse); $i++) {
        $courseInMastercourse = $coursesInMastercourse[$i];
        $courseOrder = $courseOrders[$i]; 
        $manager->set_order($courseInMastercourse, $courseOrder );
  
    } 
    // end state and redirect managemastercourse
    redirect($CFG->wwwroot . '/local/mastercourse/managemastercourse.php?mastercourseid='.$mastercourseId, get_string('created_form', 'local_mastercourse') . $fromform->messagetext);
  
     

