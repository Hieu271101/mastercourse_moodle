<?php
// This file is part of AHT's plugin Mastercourse in Moodle 

    /**
     * This file is a page that display setting of admin manage master course
     * all functions here are self-contained and can be used in ABORT_AFTER_CONFIG scripts.
     *
     * @package local/mastercourse
     * @copyright 2023 Hieu Do <hieu271101@gmail.com>
     * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */

// set manage mastercourse in admin site
$ADMIN->add('localplugins',new admin_externalpage('local_mastercourse', "Master course settings", $CFG->wwwroot . '/local/mastercourse/index.php'));
