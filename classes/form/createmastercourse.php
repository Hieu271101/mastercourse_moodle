<?php

// namespace local_mastercourse\form;
use moodleform;

require_once("$CFG->libdir/formslib.php");

class createmastercourse extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;
        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement('text', 'namemastercourse', 'Enter name of mastercourse: '); // Add elements to your form
        $mform->setType('namemastercourse', PARAM_NOTAGS);                   //Set type of element
        $mform->setDefault('namemastercourse', 'Enter name of mastercourse');        //Default value

        $this->add_action_buttons();
    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
    
    
}
