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
require_once __DIR__ . '/../../config.php'; // load config lib of mastercourse
require_once $CFG->dirroot . '/my/lib.php'; // load lib of my plugin
require_once $CFG->dirroot . '/course/lib.php'; // load lib of course plugin
global $DB;
require_login();

$context = context_system::instance(); // get context of default page

$PAGE->set_url(new moodle_url('/local/mastercourse/viewcourselistuser.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('List course In Your Master course');
$PAGE->set_heading('Course list');
$PAGE->add_body_class('limitedwidth');

require_once $CFG->dirroot . '/local/mastercourse/classes/manager.php'; // load manager  of master course
$messageid = optional_param('mastercourseid', null, PARAM_INT); // get mastercourse of param

if (isguestuser()) {
    // Force them to see system default, no editing allowed
    // If guests are not allowed my moodle, send them to front page.
    if (empty($CFG->allowguestmymoodle)) {
        redirect(new moodle_url('/', ['redirect' => 0]));
    }

    $userid = null;
    $USER->editing = $edit = 0; // Just in case
    $context = context_system::instance();
    $PAGE->set_blocks_editing_capability('moodle/my:configsyspages'); // unlikely :)
    $strguest = get_string('guest');
    $pagetitle = "$strmymoodle ($strguest)";
} else {
    // We are trying to view or edit our own My Moodle page
    $userid = $USER->id; // Owner of the page
    $context = context_user::instance($USER->id);
    $PAGE->set_blocks_editing_capability('moodle/my:manageblocks');
    $pagetitle = $strmymoodle;
}

// get user who enrol mastercourse
$user = $DB->get_records_sql(
    ' SELECT  DISTINCT *  
                                                FROM `mdl_user_enrol_mastercourse` 
                                                WHERE `mdl_user_enrol_mastercourse`.`id_mastercourse` =' .
        $messageid .
        ' AND `mdl_user_enrol_mastercourse`.`id_user` = ' .
        $USER->id
);
if (!$user) {
    // redirect if user is not enrol mastercourse
    redirect(
        $CFG->wwwroot . '/my/mastercourse.php',
        get_string('cancelled_form', 'local_mastercourse')
    );
}

require_once $CFG->dirroot . '/local/mastercourse/classes/lib.php';
$lib = new lib();
// require_once($CFG->dirroot.'/mod/page/lib.php');
// require_once($CFG->dirroot.'/mod/page/locallib.php');
// require_once($CFG->libdir.'/completionlib.php');

// $id      =  325; // Course Module ID
// $p       = optional_param('p', 0, PARAM_INT);  // Page instance ID
// $inpopup = optional_param('inpopup', 0, PARAM_BOOL);

// if ($p) {
//     if (!$page = $DB->get_record('page', array('id'=>$p))) {
//         throw new \moodle_exception('invalidaccessparameter');
//     }
//     $cm = get_coursemodule_from_instance('page', $page->id, $page->course, false, MUST_EXIST);

// } else {
//     if (!$cm = get_coursemodule_from_id('page', $id)) {
//         throw new \moodle_exception('invalidcoursemodule');
//     }
//     $page = $DB->get_record('page', array('id'=>$cm->instance), '*', MUST_EXIST);
// }

// $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

// $lib->require_course_login($course, true, $cm);

require_once $CFG->dirroot . '/course/lib.php';
require_once $CFG->libdir . '/completionlib.php';

redirect_if_major_upgrade_required();

$id = 6;

$params = ['id' => $id];
$course = $DB->get_record('course', $params, '*', MUST_EXIST);

// Prevent caching of this page to stop confusion when changing page after making AJAX changes
$PAGE->set_cacheable(false);

context_helper::preload_course($course->id);

// $lib->require_login($course);

// get master course from id
$mastercourse = $DB->get_records('course_master', ['id' => $messageid]);
// get  course from in mastercourse from id
$course = $DB->get_records_sql(
    'SELECT *, `mdl_coursemaster_course`.sortorder as ccso
                                    FROM `mdl_coursemaster_course` 
                                    INNER JOIN `mdl_course` 
                                    ON `mdl_course`.`id` = `mdl_coursemaster_course`.`id_course`  
                                    WHERE `mdl_coursemaster_course`.`id_mastercourse`= ' .
        $messageid .
        ' ORDER BY ccso'
);
// get user enrol mastercourse
$users = $DB->get_records_sql(
    'SELECT *
                                 FROM `mdl_user_enrol_mastercourse` 
                                 INNER JOIN `mdl_user` 
                                 ON `mdl_user`.`id` = `mdl_user_enrol_mastercourse`.`id_user`  
                                --  INNER JOIN `mdl_role` 
                                --  ON `mdl_role`.`id` = `mdl_user_enrol_mastercourse`.`role_id`  
                                 WHERE `mdl_user_enrol_mastercourse`.`id_mastercourse`= ' .
        $messageid
);

$sections = (array) $DB->get_records_sql('SELECT *  
                                    FROM `mdl_course_sections`   WHERE `course`= 2');

// foreach ($sections as $value) {
//     // mục tiêu bây giờ là lấy data từ sequence ra sau đó trỏ đến course module sau đó push course module vào trong record đấy nhưng vấn đề ở đây là mỗi cái nó khác nhau đổ ra k kịp nên là bây gi
//     $value->sequence = $course_module = (array) $DB->get_records_sql(
//         'SELECT *
//                                     FROM `mdl_course_modules`   WHERE `id`= ' .
//             $value
//     );
// }
// $courseModule = $DB->get_records_sql(' SELECT cm.*, cs.name
//                                             FROM mdl_course_sections as cs
//                                             JOIN mdl_course_modules as cm
//                                             ON FIND_IN_SET(cm.id, cs.sequence) > 0
//                                             WHERE cs.course = 2
//                                             ');

// print_r($courseModule);
// exit();
// SELECT cm.*
// FROM mdl_course_sections as cs
// JOIN mdl_course_modules as cm
// ON FIND_IN_SET(cm.id, cs.sequence) > 0
// WHERE cs.course = 2
// ;

$templatecontext = (object) [
    'users' => array_values((array) $users),
    'course_section' => array_values((array) $sections),
    'mastercourse' => array_values((array) $mastercourse),
    'courses' => array_values((array) $course),
    'mastercourseId' => $messageid,
    'addcourse' => new moodle_url('/local/mastercourse/addcourse.php'),
    'deletecourse' => new moodle_url('/local/mastercourse/deletecourses.php'),
    'createmastercoursecourse' => new moodle_url(
        '/local/mastercourse/createmastercourse.php'
    ),
    'enrolmastercourse' => new moodle_url(
        '/local/mastercourse/enrolmastercourses.php'
    ),
    'unenrolmastercourse' => new moodle_url(
        '/local/mastercourse/unenrolmastercourses.php'
    ),
    'courselink' => new moodle_url('/course/view.php'),
];
// display page
echo $OUTPUT->header();
echo $OUTPUT->render_from_template(
    'local_mastercourse/viewcourselistuser',
    $templatecontext
);
echo $OUTPUT->render_from_template(
    'local_mastercourse/sidemastercourse',
    $templatecontext
);
echo $OUTPUT->footer();

?>
