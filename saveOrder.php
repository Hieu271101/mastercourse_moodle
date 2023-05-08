
<?php
    require_once(__DIR__ . '/../../config.php');
    require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php');
    global $DB;
     
    require_login();
    require_admin();
    $context = context_system::instance();
    $PAGE->set_url(new moodle_url('/local/mastercourse/saveOrder.php'));

    $coursesInMastercourse = $_POST["courseInMastercourse"];
    $courseOrders = $_POST["courseoder"];
   
    
    $manager = new manager();

    for ($i = 0; $i < count($coursesInMastercourse); $i++) {
        $courseInMastercourse = $coursesInMastercourse[$i];
        $courseOrder = $courseOrders[$i]; 
        $manager->set_order($courseInMastercourse, $courseOrder );
        // $sql = "UPDATE books SET sortorder = $sortOrder WHERE id = $bookId";
        // mysqli_query($conn, $sql);
      }
    // if($manager->deletecourse($courseid,$mastercourseid)){
        redirect($CFG->wwwroot . '/local/mastercourse/managemastercourse.php?mastercourseid='.$mastercourseId, get_string('created_form', 'local_mastercourse') . $fromform->messagetext);
    // }else{
    //     redirect($CFG->wwwroot . '/local/mastercourse/managemastercourse.php?mastercourseid='.$mastercourseid, get_string('cancelled_form', 'local_mastercourse'));
    // }
     

