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
    require_once(__DIR__ . '/../../config.php'); // load config lib of mastercourse
    require_once($CFG->dirroot . '/my/lib.php'); // load lib of my plugin
    require_once($CFG->dirroot . '/course/lib.php'); // load lib of course plugin
    global $DB;    
    require_login();

    $context = context_system::instance();  // get context of default page
   
    $PAGE->set_url(new moodle_url('/local/mastercourse/viewcourselistuser.php'));
    $PAGE->set_context(\context_system::instance());
    $PAGE->set_title('List course In Your Master course');
    $PAGE->set_heading('Course list');
    $PAGE->add_body_class('limitedwidth');

    require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php'); // load manager  of master course   
    $messageid = optional_param('mastercourseid', null, PARAM_INT); // get mastercourse of param

    if (isguestuser()) {  // Force them to see system default, no editing allowed
        // If guests are not allowed my moodle, send them to front page.
        if (empty($CFG->allowguestmymoodle)) {
            redirect(new moodle_url('/', array('redirect' => 0)));
        }
    
        $userid = null;
        $USER->editing = $edit = 0;  // Just in case
        $context = context_system::instance();
        $PAGE->set_blocks_editing_capability('moodle/my:configsyspages');  // unlikely :)
        $strguest = get_string('guest');
        $pagetitle = "$strmymoodle ($strguest)";
    
        } else {        // We are trying to view or edit our own My Moodle page
            $userid = $USER->id;  // Owner of the page
            $context = context_user::instance($USER->id);
            $PAGE->set_blocks_editing_capability('moodle/my:manageblocks');
            $pagetitle = $strmymoodle;
        }

        // get user who enrol mastercourse
        $user =  $DB->get_records_sql(' SELECT  DISTINCT *  
                                                FROM `mdl_user_enrol_mastercourse` 
                                                WHERE `mdl_user_enrol_mastercourse`.`id_mastercourse` =' . $messageid 
                                                . ' AND `mdl_user_enrol_mastercourse`.`id_user` = ' .$USER->id);
        if(!$user){
            // redirect if user is not enrol mastercourse
            redirect($CFG->wwwroot . '/my/mastercourse.php', get_string('cancelled_form', 'local_mastercourse'));
        }                               
       
    // get master course from id
    $mastercourse = $DB->get_records('course_master', ['id' => $messageid]);
    // get  course from in mastercourse from id
    $course = $DB->get_records_sql('SELECT *, `mdl_coursemaster_course`.sortorder as ccso
                                    FROM `mdl_coursemaster_course` 
                                    INNER JOIN `mdl_course` 
                                    ON `mdl_course`.`id` = `mdl_coursemaster_course`.`id_course`  
                                    WHERE `mdl_coursemaster_course`.`id_mastercourse`= '.$messageid. ' ORDER BY ccso' );
    // get user enrol mastercourse                                
    $users = $DB->get_records_sql('SELECT *
                                 FROM `mdl_user_enrol_mastercourse` 
                                 INNER JOIN `mdl_user` 
                                 ON `mdl_user`.`id` = `mdl_user_enrol_mastercourse`.`id_user`  
                                --  INNER JOIN `mdl_role` 
                                --  ON `mdl_role`.`id` = `mdl_user_enrol_mastercourse`.`role_id`  
                                 WHERE `mdl_user_enrol_mastercourse`.`id_mastercourse`= '.$messageid);

    $templatecontext = (object)[
        'users' => array_values((array)$users),
        'mastercourse' => array_values((array)$mastercourse),
        'courses' => array_values((array)$course),
        'mastercourseId' => $messageid,
        'addcourse' => new moodle_url('/local/mastercourse/addcourse.php'),
        'deletecourse'  => new moodle_url('/local/mastercourse/deletecourses.php'),
        'createmastercoursecourse' => new moodle_url('/local/mastercourse/createmastercourse.php'),
        'enrolmastercourse' => new moodle_url('/local/mastercourse/enrolmastercourses.php'),
        'unenrolmastercourse' => new moodle_url('/local/mastercourse/unenrolmastercourses.php'),
        'courselink' => new moodle_url('/course/view.php'),
    ];
    // display page
    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('local_mastercourse/viewcourselistuser', $templatecontext);
    echo $OUTPUT->footer();

?>
