<?php

// namespace local_mastercourse;

use dml_exception;
use stdClass;

class manager {
    
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

    public function enrol_mastercourse_byemail(string $idmastercourse, string $roleid,  $email): bool
    {   
        global $DB;        
        
        $user =   $DB->get_record_sql('SELECT id FROM `mdl_user` WHERE `mdl_user`.`email` = '.  '\''.$email. '\'');
        $iduser = $user->id;
        return  $this->enrol_mastercourse( $idmastercourse,  $roleid,  $iduser);

    }


    public function enrol_mastercourse(string $idmastercourse, string $roleid,  $iduser): bool
    {                 
        global $DB;


        $record_to_insert = new stdClass();        
        $record_to_insert->role_id = $roleid;
        $record_to_insert->id_user = $iduser;
        $record_to_insert->id_mastercourse = $idmastercourse;
        
        $courseshasmastercourseid =  (array)$DB->get_records_sql('SELECT `mdl_course`.`id`, `mdl_course`.`fullname`
                                                                    FROM `mdl_coursemaster_course` 
                                                                    INNER JOIN `mdl_course` 
                                                                    ON `mdl_course`.`id` = `mdl_coursemaster_course`.`id_course`  
                                                                    WHERE `mdl_coursemaster_course`.`id_mastercourse`= '.$idmastercourse);
        foreach ($courseshasmastercourseid as $value) {
 
              $this->enrol_try_internal_enrol($value->id,$iduser, $roleid, $timestart = 0, $timeend = 0); //enrol_try_internal_enrol($courseid, $userid, $roleid = null, $timestart = 0, $timeend = 0)
        }

          try {
             return $DB->insert_record('user_enrol_mastercourse', $record_to_insert, false);
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
    
    public function addcourse(string $idcourse, int $idmastercourse)
    {
        global $DB;
        // $course = $this->get_course($idcourse);
      
        $record_to_insert = new stdClass();
        $record_to_insert->id_course = $idcourse;       
        $record_to_insert->id_mastercourse = $idmastercourse;
        
        if ($ue = $DB->get_record('coursemaster_course', array('id_mastercourse'=>$idmastercourse, 'id_course'=>$idcourse))) {
            // weird, user not enrolled
            return false;
        }
        
        try {
            // return $DB->update_record('course', $record_to_insert);
            $DB->insert_record('coursemaster_course', $record_to_insert, false);
          } catch (dml_exception $e) {
            return false;
        }
        
        //add user enrol to new course
        //check user is enrol course
        //get user who enrol this course
        $users = $DB->get_records_sql(' SELECT DISTINCT  `mdl_user`.id,`mdl_user`.`username`, `mdl_user`.email
                                        FROM `mdl_user_enrol_mastercourse` 
                                        INNER JOIN `mdl_user` 
                                        ON `mdl_user`.`id` =`mdl_user_enrol_mastercourse`.`id_user`  
                                        WHERE `mdl_user_enrol_mastercourse`.`id_mastercourse` =' . $idmastercourse);
        
        foreach ($users as $user) {
            if (!$instances = $DB->get_records('enrol', array('enrol'=>'manual', 'courseid'=>$idcourse, 'status'=>ENROL_INSTANCE_ENABLED), 'sortorder,id ASC')) {
                return false;
            }
            $instance = reset($instances);
            if(!$ue = $DB->get_record('user_enrolments', array('enrolid'=>$instance->id, 'userid'=>$user->id))){
                           
                $this->enrol_try_internal_enrol($idcourse, $user->id, $roleid = 0, $timestart = 0, $timeend = 0);
              
            }else{
                $record_to_insert_ue = new stdClass();
                $record_to_insert_ue->id = $ue->id; 
                $record_to_insert_ue->enrolid = $ue->enrolid;
                $record_to_insert_ue->isenrol = 1;
                $record_to_insert_ue->userid = $ue->userid;
                $record_to_insert_ue->status = 1;
                print_r($record_to_insert_ue);
               
                try {
                    $DB->update_record('user_enrolments', $record_to_insert_ue, false);
                    
                  } catch (dml_exception $e) {
                    return false;
                }
            }
             //enrol_try_internal_enrol($courseid, $userid, $roleid = null, $timestart = 0, $timeend = 0)
      }
    }
    public function deletecourse(int $idcourse, int $idmastercourse)
    {
        global $DB;
        // $course = $this->get_course($idcourse);
      
        $record_to_insert = new stdClass();
        $record_to_insert->id_course = $idcourse;       
        $record_to_insert->id_mastercourse = $idmastercourse;
        
        if (!$ue = $DB->get_record('coursemaster_course', array('id_mastercourse'=>$idmastercourse, 'id_course'=>$idcourse))) {
            // weird, user not enrolled
            return false;
        }
        
        try {
            // return $DB->update_record('course', $record_to_insert);
            // $DB->delete_record('coursemaster_course', $record_to_insert, false);
            $DB->delete_records('coursemaster_course', array('id'=>$ue->id));

          } catch (dml_exception $e) {
            return false;
        }
        
        //add user enrol to new course
        //check user is enrol course
        //get user who enrol this course
        $users = $DB->get_records_sql(' SELECT DISTINCT  `mdl_user`.id,`mdl_user`.`username`, `mdl_user`.email
                                 FROM `mdl_user_enrol_mastercourse` 
                                 INNER JOIN `mdl_user` 
                                 ON `mdl_user`.`id` =`mdl_user_enrol_mastercourse`.`id_user`  
                                 WHERE `mdl_user_enrol_mastercourse`.`id_mastercourse` =' . $idmastercourse);
        
        foreach ($users as $user) {
            if (!$instances = $DB->get_records('enrol', array('enrol'=>'manual', 'courseid'=>$idcourse, 'status'=>ENROL_INSTANCE_ENABLED), 'sortorder,id ASC')) {
                return false;
            }
            $instance = reset($instances);
            if(!$ue = $DB->get_record('user_enrolments', array('isenrol'=>1))){
                $this->unenrol_try_internal_unenrol($idcourse, $user->id, $roleid = 0, $timestart = 0, $timeend = 0);
            }
             //enrol_try_internal_enrol($courseid, $userid, $roleid = null, $timestart = 0, $timeend = 0)
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
    public function unenrol_mastercourse_byemail(string $idmastercourse,  $email)
    {   
        global $DB;        
        
        $user =   $DB->get_record_sql('SELECT id FROM `mdl_user` WHERE `mdl_user`.`email` = '.  '\''.$email. '\'');
        $iduser = $user->id;
        return  $this->unenrol_mastercourse( $idmastercourse,  $iduser);

    }
    public function unenrol_mastercourse($idmastercourse, $iduser)
    {   
        global $DB, $CFG;
        
        if (!$ue = $DB->get_record('user_enrol_mastercourse', array('id_mastercourse'=>$idmastercourse, 'id_user'=>$iduser))) {
            // weird, user not enrolled
            return;
        }
        $DB->delete_records('user_enrol_mastercourse', array('id'=>$ue->id));

        
        
        $courseshasmastercourseid =  (array)$DB->get_records_sql('SELECT `mdl_course`.`id`, `mdl_course`.`fullname`
                                                                    FROM `mdl_coursemaster_course` 
                                                                    INNER JOIN `mdl_course` 
                                                                    ON `mdl_course`.`id` = `mdl_coursemaster_course`.`id_course`  
                                                                    WHERE `mdl_coursemaster_course`.`id_mastercourse`= '.$idmastercourse);
        
        foreach ($courseshasmastercourseid as $value) {
       
            $this->unenrol_try_internal_unenrol($value->id, $iduser);
        }

    }

    function unenrol_try_internal_unenrol($courseid, $userid) {
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
    
        $enrol->unenrol_user($instance, $userid);
    
        return true;
    }

    public function unenrol_user(stdClass $instance, $userid) {
        global $CFG, $USER, $DB;
        require_once("$CFG->dirroot/group/lib.php");
        
       
        $name = $this->get_name();
        $courseid = $instance->courseid;

        if ($instance->enrol !== $name) {
            throw new coding_exception('invalid enrol instance!');
        }
        $context = context_course::instance($instance->courseid, MUST_EXIST);

        if (!$ue = $DB->get_record('user_enrolments', array('enrolid'=>$instance->id, 'userid'=>$userid))) {
            // weird, user not enrolled
            return;
        }
        
        // Remove all users groups linked to this enrolment instance.
        if ($gms = $DB->get_records('groups_members', array('userid'=>$userid, 'component'=>'enrol_'.$name, 'itemid'=>$instance->id))) {
            foreach ($gms as $gm) {
                groups_remove_member($gm->groupid, $gm->userid);
            }
        }

        role_unassign_all(array('userid'=>$userid, 'contextid'=>$context->id, 'component'=>'enrol_'.$name, 'itemid'=>$instance->id));
        $DB->delete_records('user_enrolments', array('id'=>$ue->id));

        // add extra info and trigger event
        $ue->courseid  = $courseid;
        $ue->enrol     = $name;

        $sql = "SELECT 'x'
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON (e.id = ue.enrolid)
                 WHERE ue.userid = :userid AND e.courseid = :courseid";
        if ($DB->record_exists_sql($sql, array('userid'=>$userid, 'courseid'=>$courseid))) {
            $ue->lastenrol = false;

        } else {
            // the big cleanup IS necessary!
            require_once("$CFG->libdir/gradelib.php");

            // remove all remaining roles
            role_unassign_all(array('userid'=>$userid, 'contextid'=>$context->id), true, false);

            //clean up ALL invisible user data from course if this is the last enrolment - groups, grades, etc.
            groups_delete_group_members($courseid, $userid);

            grade_user_unenrol($courseid, $userid);

            $DB->delete_records('user_lastaccess', array('userid'=>$userid, 'courseid'=>$courseid));

            $ue->lastenrol = true; // means user not enrolled any more
        }
        // Trigger event.
        $event = \core\event\user_enrolment_deleted::create(
                array(
                    'courseid' => $courseid,
                    'context' => $context,
                    'relateduserid' => $ue->userid,
                    'objectid' => $ue->id,
                    'other' => array(
                        'userenrolment' => (array)$ue,
                        'enrol' => $name
                        )
                    )
                );
        $event->trigger();

        // User enrolments have changed, so mark user as dirty.
        mark_user_dirty($userid);

        // Check if courrse contacts cache needs to be cleared.
        core_course_category::user_enrolment_changed($courseid, $ue->userid, ENROL_USER_SUSPENDED);

        // reset current user enrolment caching
        if ($userid == $USER->id) {
            if (isset($USER->enrol['enrolled'][$courseid])) {
                unset($USER->enrol['enrolled'][$courseid]);
            }
            if (isset($USER->enrol['tempguest'][$courseid])) {
                unset($USER->enrol['tempguest'][$courseid]);
                remove_temp_course_roles($context);
            }
        }
    }
    
    function enrol_get_instances($courseid, $enabled) {
        global $DB, $CFG;
    
        if (!$enabled) {
            return $DB->get_records('enrol', array('courseid'=>$courseid), 'sortorder,id');
        }
    
        $result = $DB->get_records('enrol', array('courseid'=>$courseid, 'status'=>ENROL_INSTANCE_ENABLED), 'sortorder,id');
    
        $enabled = explode(',', $CFG->enrol_plugins_enabled);
        foreach ($result as $key=>$instance) {
            if (!in_array($instance->enrol, $enabled)) {
                unset($result[$key]);
                continue;
            }
            if (!file_exists("$CFG->dirroot/enrol/$instance->enrol/lib.php")) {
                // broken plugin
                unset($result[$key]);
                continue;
            }
        }
    
        return $result;
    }
    public function get_name() {
        // second word in class is always enrol name, sorry, no fancy plugin names with _
        $words = explode('_', get_class($this));
        return $words[1];
    }

    

}
