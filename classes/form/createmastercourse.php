<?php
use moodleform;

require_once("$CFG->libdir/formslib.php");

class createmastercourse extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;
        $mform = $this->_form; // Don't forget the underscore!
        $mform->addElement('hidden','id','id: ');

        $mform->addElement('text', 'name', 'Enter name of mastercourse: '); // Add elements to your form
        $mform->setType('name', PARAM_NOTAGS);                   //Set type of element
        $mform->setDefault('name', 'Enter name of mastercourse');        //Default value

        $mform->addElement('textarea','description', 'Enter description: ');
        $mform->setType('description', PARAM_NOTAGS);
      
        $this->add_action_buttons();
    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
    
    
}
