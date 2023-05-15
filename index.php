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
    require_once(__DIR__ . '/../../config.php'); // load config file of moodle
    require_once($CFG->dirroot. '/course/lib.php'); // lib of plugin course

    $categoryid = optional_param('categoryid', 0, PARAM_INT); // Category id
    // $site = get_site();

    if ($CFG->forcelogin) { // force login
        require_login(); 
    }
    require_admin();
    $heading = 'Master course'; 

    if ($categoryid) {
        $category = core_course_category::get($categoryid); // This will validate access.
        $PAGE->set_category_by_id($categoryid);
        $PAGE->set_url(new moodle_url('local/mastercourse/index.php', array('categoryid' => $categoryid)));
        $PAGE->set_pagetype('course-index-category');
        $heading = $category->get_formatted_name();
    } else if ($category = core_course_category::user_top()) {
        // Check if there is only one top-level category, if so use that.
        $categoryid = $category->id;
        $PAGE->set_url('/local/mastercourse/index.php');
        if ($category->is_uservisible() && $categoryid) {
            $PAGE->set_category_by_id($categoryid);
            $PAGE->set_context($category->get_context());
            if (!core_course_category::is_simple_site()) {
                $PAGE->set_url(new moodle_url('local/mastercourse/index.php', array('categoryid' => $categoryid)));
                $heading = $category->get_formatted_name();
            }
        } else {
            $PAGE->set_context(context_system::instance());
        }
        $PAGE->set_pagetype('course-index-category');
    } else {
        throw new moodle_exception('cannotviewcategory');
    }

    $PAGE->set_pagelayout('coursecategory');
    $PAGE->set_primary_active_tab('home');
    $PAGE->add_body_class('limitedwidth');

    $PAGE->set_heading($heading);

    $PAGE->set_secondary_active_tab('categorymain');

    echo $OUTPUT->header();
    echo $OUTPUT->skip_link_target();
    // get mastercourse from path variable
    $mastercourse = $DB->get_records('course_master', null, 'id');
    //create object variable to use  template
    $templatecontext = (object)[
        'mastercourse' => array_values($mastercourse),
        'viewurl' => new moodle_url('/local/mastercourse/managemastercourse.php'),
        'createmastercoursecourse' => new moodle_url('/local/mastercourse/createmastercourse.php'),
    ];

    echo $OUTPUT->render_from_template('local_mastercourse/index', $templatecontext);

    // Trigger event, course category viewed.
    $eventparams = array('context' => $PAGE->context, 'objectid' => $categoryid);
    $event = \core\event\course_category_viewed::create($eventparams);
    $event->trigger();


    echo $OUTPUT->footer();

