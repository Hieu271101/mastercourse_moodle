
<?php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/my/lib.php');
require_once($CFG->dirroot . '/course/lib.php');

redirect_if_major_upgrade_required();

require_login();


// $hassiteconfig = has_capability('moodle/site:config', context_system::instance());
// if ($hassiteconfig && moodle_needs_upgrading()) {
//     redirect(new moodle_url('/admin/index.php'));
// }

$context = context_system::instance();

// Get the My Moodle page info.  Should always return something unless the database is broken.
if (!$currentpage = my_get_page(null, MY_PAGE_PUBLIC, MY_PAGE_COURSES)) {
    throw new Exception('mymoodlesetup');
}

// Start setting up the page.
$PAGE->set_context($context);
$PAGE->set_url('/my/mastercourse.php');
$PAGE->add_body_classes(['limitedwidth', 'page-mycourses']);
$PAGE->set_pagelayout('mycourses');

$PAGE->set_pagetype('my-index');
$PAGE->blocks->add_region('content');
$PAGE->set_subpage($currentpage->id);
$PAGE->set_title('Master Course');
$PAGE->set_heading('Master Course');

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
require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php');
$manager = new manager(); //i

echo $OUTPUT->header();
$mastercourse = (array)$DB->get_records_sql('SELECT  cm.id,cm.name ,u.username  FROM `mdl_user` as u 
                                                INNER JOIN `mdl_user_enrol_mastercourse` as uem
                                                ON `u`.`id` = `uem`.id_user 
                                                INNER JOIN `mdl_course_master` as cm
                                                ON `cm`.`id` = uem.id_mastercourse
                                                WHERE u.id ='.$USER->id);

$courseNotInMastercourse = $manager->get_coures_not_in_mastercourse($USER->id);

 
$templatecontext = (object)[
    'mastercourse' => array_values($mastercourse),
    'viewcourselistuser' => new moodle_url('/local/mastercourse/viewcourselistuser.php'),
    'createmastercoursecourse' => new moodle_url('/local/mastercourse/createmastercourse.php'),
    'courses'  => array_values($courseNotInMastercourse),
    'courselink' => new moodle_url('/course/view.php'),
];
echo $OUTPUT->render_from_template('local_mastercourse/listmastercourse', $templatecontext);
echo $OUTPUT->render_from_template('local_mastercourse/viewcourselistuser', $templatecontext);

echo $OUTPUT->footer();

// Trigger dashboard has been viewed event.
$eventparams = array('context' => $context);
$event = \core\event\mycourses_viewed::create($eventparams);
$event->trigger();




// <?php
//     require_once(__DIR__ . '/../../config.php');

//     global $DB;
    
//     require_login();
//     require_admin();
//     $context = context_system::instance(); // get instance context

//     $PAGE->set_url(new moodle_url('/local/mastercourse/mastercourse.php'));
//     $PAGE->set_context(\context_system::instance());
//     $PAGE->set_title('Master Course');
//     $PAGE->set_heading('Master Course');
//     $PAGE->add_body_class('limitedwidth');

//     require_once($CFG->dirroot.'/local/mastercourse/classes/manager.php'); // load manager mastercourse
//     require_once($CFG->dirroot.'/local/mastercourse/classes/form/enrolmastercourse.php'); // load enrolmastercourse 
    
//     $mastercourseid = optional_param('mastercourseid', null, PARAM_INT); // get paramter of mastercourse
//     // this is form of enrol user
//     $mform = new enrolmastercourse();
    
//     if ($mform->is_cancelled()) {
//     // Go back to managemastercourse.php page
//     redirect($CFG->wwwroot . '/local/mastercourse/managemastercourse.php?mastercourseid=', get_string('cancelled_form', 'local_mastercourse'));

//     } else if ($fromform = $mform->get_data()) {
//     $manager = new manager();
//     $manager->enrol_mastercourse_byemail($fromform->id, $fromform->roleid, $fromform->iduser); 

//     // Go back to managemastercourse.php page
//     redirect($CFG->wwwroot . '/local/mastercourse/managemastercourse.php?mastercourseid='.$fromform->id, get_string('created_form', 'local_mastercourse') . $fromform->messagetext);
//     }

//     if ($mastercourseid) {
//         // Add extra data to the form.
   
//         global $DB;
//         $manager = new manager();
//         $classTemp = new stdClass();
//         $classTemp->id = $mastercourseid;
//         $message = $classTemp;
//         $mform->set_data($message);
//         if (!$message) {
//             throw new invalid_parameter_exception('Message not found');
//         }
        
//     } 
//     else {
//         // redirect to index
//         redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('cancelled_form', 'local_mastercourse'));
//     }

//     if ($mastercourseid) {
//         // Add extra data to the form.
//         global $DB;
//         $manager = new manager();
//         $message = $manager->get_course($mastercourseid);
//         $mform->set_data($message);
  
        
//     } else {
//         redirect($CFG->wwwroot . '/local/mastercourse/index.php', get_string('cancelled_form', 'local_message'));
//     }
//     echo $OUTPUT->header();
//     $mform->display();
//     echo $OUTPUT->footer();
 