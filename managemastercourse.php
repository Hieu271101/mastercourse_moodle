<?php
    // This file is part of AHT's plugin Mastercourse in Moodle 

    /**
     * This file is a manage a master course  to add, enrol, delete course and unenrol user
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


    $PAGE->set_url(new moodle_url('/local/mastercourse/managemastercourse.php'));   // set the url of this add a course in to mastercourse page
    $PAGE->set_context(\context_system::instance()); // set default context
    $PAGE->set_title('Manage Master Course'); //set title
    $PAGE->set_heading('Manage Master Course'); // set heading

    require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php'); // load manager of mastercourse
    require_once($CFG->dirroot.'/local/mastercourse/classes/form/enrolmastercourse.php');// load enrolform
    require_once($CFG->dirroot.'/local/mastercourse/classes/form/addcourseform.php'); // load addcourse form
   
    
    $messageid = optional_param('mastercourseid', null, PARAM_INT);  // get parameter mastercourse id
    
    $mform = new enrolmastercourse(); // initiate enrol form
    $addform = new addcourseform(); // initiate add form
    
    if ($addform->is_cancelled()) {
    // Go back to manage.php page
    redirect($CFG->wwwroot . '/local/mastercourse/managemastercourse.php?mastercourseid='.$messageid, get_string('cancelled_form', 'local_mastercourse'));

    } else if ($fromform = $addform->get_data()) {
        $manager = new manager();
        $manager->addcourse($fromform->idcourse, $fromform->id);
    // Go back to manage.php page
        redirect($CFG->wwwroot . '/local/mastercourse/managemastercourse.php?mastercourseid='.$fromform->id, get_string('created_form', 'local_mastercourse') . $fromform->messagetext);
    }

    if ($messageid) {
        // Add extra data to the form.
   
        global $DB;
        $manager = new manager(); // initiate manager of master course
        $classTemp = new stdClass();
        $classTemp->id = $messageid;
        $message = $classTemp;
      
        $addform->set_data($message);
       
        if (!$message) {
            throw new invalid_parameter_exception('Message not found');
        }
    } 
    else {
        redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('cancelled_form', 'local_mastercourse'));
    }

    // get master course from url parameter
    $mastercourse = $DB->get_records('course_master', ['id' => $messageid]);
    // select course form mastercourse
    $course = $DB->get_records_sql('SELECT *, 
                                    `mdl_coursemaster_course`.id as ccid,
                                    `mdl_coursemaster_course`.`sortorder`as cmcso
                                                                FROM `mdl_coursemaster_course` 
                                                                INNER JOIN `mdl_course` 
                                                                ON `mdl_course`.`id` = `mdl_coursemaster_course`.`id_course`  
                                                                WHERE `mdl_coursemaster_course`.`id_mastercourse`= '.$messageid.' ORDER BY cmcso' );
    // get user who enrol mastercourse
    $users = $DB->get_records_sql('SELECT *
                                 FROM `mdl_user_enrol_mastercourse` 
                                 INNER JOIN `mdl_user` 
                                 ON `mdl_user`.`id` = `mdl_user_enrol_mastercourse`.`id_user`  
                                --  INNER JOIN `mdl_role` 
                                --  ON `mdl_role`.`id` = `mdl_user_enrol_mastercourse`.`role_id`  
                                 WHERE `mdl_user_enrol_mastercourse`.`id_mastercourse`= '.$messageid);


    $templatecontext = (object)[
    
        'users' => array_values((array)$users), // user who enrolmastercourse
        'mastercourse' => array_values((array)$mastercourse), // mastercourse from parameterid
        'courses' => array_values((array)$course), // courses who enrol mastercourse
        'mastercourseId' => $messageid,

        'addcourse' => new moodle_url('/local/mastercourse/addcourse.php'), // url of add course 
        'deletecourse'  => new moodle_url('/local/mastercourse/deletecourses.php'), // url of delete course
        'createmastercoursecourse' => new moodle_url('/local/mastercourse/createmastercourse.php'), // url of create mastercourse
        'enrolmastercourse' => new moodle_url('/local/mastercourse/enrolmastercourses.php'), // url enrol mastercourse
        'unenrolmastercourse' => new moodle_url('/local/mastercourse/unenrolmastercourses.php'), // url of unenrolmastercourse
        'courselink' => new moodle_url('/course/view.php'),// url of course 
        
    ];
    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('local_mastercourse/manage', $templatecontext);
    echo "<h4>Add Course</h4>";
    $addform->display();
    echo $OUTPUT->render_from_template('local_mastercourse/listcourse', $templatecontext);
    echo $OUTPUT->render_from_template('local_mastercourse/listuser', $templatecontext);
    echo $OUTPUT->footer();
