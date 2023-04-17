<?php

// namespace local_mastercourse\form;
use moodleform;

require_once("$CFG->libdir/formslib.php");

class deletecourseform extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;
        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement('text', 'id', 'Enter id master course: '); // Add elements to your form
        $mform->setType('id', PARAM_NOTAGS);  
        $mform->addElement('text', 'idcourse', 'Enter course name: '); // Add elements to your form
        $mform->setType('idcourse', PARAM_NOTAGS);                   //Set type of element
        // $mform->setDefault('coursename', 'Please enter course name');        //Default value
        
        $this->add_action_buttons();
    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
    
    
}
