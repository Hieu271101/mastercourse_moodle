<?php

// namespace local_mastercourse;

use dml_exception;
use stdClass;

class manager {

    public function enrol_mastercourse(string $iduser, string $roleid,  $idmastercourse): bool
    {                 

        global $DB;
        $record_to_insert = new stdClass();        
        $record_to_insert->role_id = $roleid;
        $record_to_insert->id_user = $iduser;
        $courseshasmastercourseid =  (array)$DB->get_records_sql('SELECT id FROM `mdl_course` WHERE `mdl_course`.`id_mastercourse` = '.$idmastercourse);
        $record_to_insert->id_mastercourse = $idmastercourse;
 
        foreach ($courseshasmastercourseid as $value) {
 
              $this->enrol_try_internal_enrol($value->id,$iduser, $roleid, $timestart = 0, $timeend = 0); //enrol_try_internal_enrol($courseid, $userid, $roleid = null, $timestart = 0, $timeend = 0)
        }

          try {
             return $DB->insert_record('user_enrol_mastercourse', $record_to_insert, false);
            } catch (dml_exception $e) {
              return false;
          }
    }
    public function createmastercourse(string $name): bool
    {
         global $DB;
          $record_to_insert = new stdClass();
          $record_to_insert->name = $name;
          try {
             return $DB->insert_record('course_master', $record_to_insert, false);
            } catch (dml_exception $e) {
              return false;
          }
    }

    function enrol_try_internal_enrol($courseid, $userid, $roleid = null, $timestart = 0, $timeend = 0) {
        global $DB;
    
        //note: this is hardcoded to manual plugin for now
    
        if (!enrol_is_enabled('manual')) {
            return false;
        }
    
        if (!$enrol = enrol_get_plugin('manual')) {
            return false;
        }
        if (!$instances = $DB->get_records('enrol', array('enrol'=>'manual', 'courseid'=>$courseid, 'status'=>ENROL_INSTANCE_ENABLED), 'sortorder,id ASC')) {
            return false;
        }
        $instance = reset($instances);
    
        $enrol->enrol_user($instance, $userid, $roleid, $timestart, $timeend);
    
        return true;
    }
    
    public function addcourse(int $idcourse, int $idmastercourse): bool
    {
        global $DB;
        $course = $this->get_course($idcourse);
      
        $record_to_insert = new stdClass();
        $record_to_insert->id = $course->id;       
        $record_to_insert->id_mastercourse = $idmastercourse;
        try {
            return $DB->update_record('course', $record_to_insert);
            
          } catch (dml_exception $e) {
            return false;
        }
      
    }

    public function get_course(int $idcourse)
    {
        global $DB;
        return $DB->get_record('course', ['id' => $idcourse]);
    }

    public function update_course(array $messageids, $idmastercourse): bool
    {
        global $DB;
        list($ids, $params) = $DB->get_in_or_equal($messageids);
        return $DB->set_field_select('course', 'requested', $idmastercourse, "id $ids", $params);
    }

}
