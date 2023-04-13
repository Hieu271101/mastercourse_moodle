<?php

// namespace local_mastercourse\form;
use moodleform;

require_once("$CFG->libdir/formslib.php");

class unenrolmastercourseform extends moodleform {

    //Add elements to form
    public function definition() {
        global $CFG;
        $mform = $this->_form; // Don't forget the underscore!
        
        $mform->addElement('hidden', 'id', 'Enter id master course: '); // Add elements to your form
        $mform->setType('id', PARAM_NOTAGS);         
        $mform->setDefault('id', 'Please enter name of user');        //Default value
        $mform->addElement('text', 'iduser', 'Enter user');
        $mform->setType('iduser', PARAM_INT);

        $this->add_action_buttons();
    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }

}
