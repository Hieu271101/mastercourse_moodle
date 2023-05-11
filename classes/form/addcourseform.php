<?php

// namespace local_mastercourse\form;
use moodleform;

require_once("$CFG->libdir/formslib.php");

class addcourseform extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;
        $choices = array();
        $choices = $this->getAllCourse();

        $mform = $this->_form; // Don't forget the underscore!
        $mform->addElement('select', 'idcourse', 'Select Course: ', $choices);
        $mform->setDefault('idcourse', '2');
        $mform->addElement('hidden', 'id', 'Enter id master course: '); // Add elements to your form
        $mform->setType('id', PARAM_NOTAGS);    
        $this->add_action_buttons();
    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
    function getAllCourse(): array {
        $courses = get_courses();
        $choices = array();
        foreach ($courses as $course) {  
            if($course->id!=1) {
                $choices[$course->id] = $course->fullname;
            }       
           
        }
        return $choices;
    }
    
}
