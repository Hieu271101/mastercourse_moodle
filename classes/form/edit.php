<?php

// namespace local_mastercourse\form;
use moodleform;

require_once("$CFG->libdir/formslib.php");

class edit extends moodleform {

    //Add elements to form
    public function definition() {
        global $CFG;
        $mform = $this->_form; // Don't forget the underscore!
        
        $mform->addElement('hidden', 'id', 'Enter id master course: '); // Add elements to your form
        $mform->setType('id', PARAM_NOTAGS);         

        $mform->addElement('text', 'idmastercourse', 'Enter user');
        $mform->setType('idmastercourse', PARAM_INT);
        
                //Set type of element
        $mform->setDefault('id', 'Please enter name of user');        //Default value
        
        $choices = array();
        $choices = $this->get_role();
       
        $mform->addElement('select', 'roleid', 'Enter role: ', $choices);
        $mform->setDefault('roleid', '5');

        $this->add_action_buttons();
    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
    
    function get_role(): array {
        global $DB;
        $roles = $DB->get_records('role');
        $choices = array();
        foreach ($roles as $role) {
            if($role->id == 1 || $role->id == 3 || $role->id == 4 || $role->id == 5)
            $choices[$role->id] = $role->shortname;
        }
        return $choices;
    }
}
