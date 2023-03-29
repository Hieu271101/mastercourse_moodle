<?php

// namespace local_mastercourse;

use dml_exception;
use stdClass;

class manager {

    /** Insert the data into our database table.
     * @param string $message_text
     * @param string $message_type
     * @return bool true if successful
     */
    public function enrol_mastercourse(string $iduser, string $roleid,  $idmastercourse): bool
    {                 

          global $DB;
          $record_to_insert = new stdClass();        
          $record_to_insert->id_user = $iduser;
          $record_to_insert->role_id = $roleid;
          $record_to_insert->id_mastercourse = $idmastercourse;
          $courseshasmastercourseid =  (array)$DB->get_records_sql('SELECT id FROM `mdl_course` WHERE `mdl_course`.`id_mastercourse` = '.$idmastercourse);
          var_dump($courseshasmastercourseid);
        //   die;
        foreach ($courseshasmastercourseid as $value) {
              $instances = $this->enrol_get_instances($value, true);
              $this->enrol_user($instances, $iduser, $roleid);
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

    
    function enrol_get_plugin($name) {
        global $CFG;
    
        $name = clean_param($name, PARAM_PLUGIN);
    
        if (empty($name)) {
            // ignore malformed or missing plugin names completely
            return null;
        }
    
        $location = "$CFG->dirroot/enrol/$name";
    
        $class = "enrol_{$name}_plugin";
        if (!class_exists($class)) {
            if (!file_exists("$location/lib.php")) {
                return null;
            }
            include_once("$location/lib.php");
            if (!class_exists($class)) {
                return null;
            }
        }
    
        return new $class();
    }

    public function enrol_user(stdClass $instance, $userid, $roleid = null, $timestart = 0, $timeend = 0, $status = null, $recovergrades = null) {
        global $DB, $USER, $CFG; // CFG necessary!!!

        if ($instance->courseid == SITEID) {
            throw new coding_exception('invalid attempt to enrol into frontpage course!');
        }

        $name = $this->get_name();
        $courseid = $instance->courseid;

        if ($instance->enrol !== $name) {
            throw new coding_exception('invalid enrol instance!');
        }
        $context = context_course::instance($instance->courseid, MUST_EXIST);
        if (!isset($recovergrades)) {
            $recovergrades = $CFG->recovergradesdefault;
        }

        $inserted = false;
        $updated  = false;
        if ($ue = $DB->get_record('user_enrolments', array('enrolid'=>$instance->id, 'userid'=>$userid))) {
            //only update if timestart or timeend or status are different.
            if ($ue->timestart != $timestart or $ue->timeend != $timeend or (!is_null($status) and $ue->status != $status)) {
                $this->update_user_enrol($instance, $userid, $status, $timestart, $timeend);
            }
        } else {
            $ue = new stdClass();
            $ue->enrolid      = $instance->id;
            $ue->status       = is_null($status) ? ENROL_USER_ACTIVE : $status;
            $ue->userid       = $userid;
            $ue->timestart    = $timestart;
            $ue->timeend      = $timeend;
            $ue->modifierid   = $USER->id;
            $ue->timecreated  = time();
            $ue->timemodified = $ue->timecreated;
            $ue->id = $DB->insert_record('user_enrolments', $ue);

            $inserted = true;
        }

        if ($inserted) {
            // Trigger event.
            $event = \core\event\user_enrolment_created::create(
                    array(
                        'objectid' => $ue->id,
                        'courseid' => $courseid,
                        'context' => $context,
                        'relateduserid' => $ue->userid,
                        'other' => array('enrol' => $name)
                        )
                    );
            $event->trigger();
            // Check if course contacts cache needs to be cleared.
            core_course_category::user_enrolment_changed($courseid, $ue->userid,
                    $ue->status, $ue->timestart, $ue->timeend);
        }

        if ($roleid) {
            // this must be done after the enrolment event so that the role_assigned event is triggered afterwards
            if ($this->roles_protected()) {
                role_assign($roleid, $userid, $context->id, 'enrol_'.$name, $instance->id);
            } else {
                role_assign($roleid, $userid, $context->id);
            }
        }

        // Recover old grades if present.
        if ($recovergrades) {
            require_once("$CFG->libdir/gradelib.php");
            grade_recover_history_grades($userid, $courseid);
        }

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

    public function update_user_enrol(stdClass $instance, $userid, $status = NULL, $timestart = NULL, $timeend = NULL) {
        global $DB, $USER, $CFG;

        $name = $this->get_name();

        if ($instance->enrol !== $name) {
            throw new coding_exception('invalid enrol instance!');
        }

        if (!$ue = $DB->get_record('user_enrolments', array('enrolid'=>$instance->id, 'userid'=>$userid))) {
            // weird, user not enrolled
            return;
        }

        $modified = false;
        if (isset($status) and $ue->status != $status) {
            $ue->status = $status;
            $modified = true;
        }
        if (isset($timestart) and $ue->timestart != $timestart) {
            $ue->timestart = $timestart;
            $modified = true;
        }
        if (isset($timeend) and $ue->timeend != $timeend) {
            $ue->timeend = $timeend;
            $modified = true;
        }

        if (!$modified) {
            // no change
            return;
        }

        $ue->modifierid = $USER->id;
        $ue->timemodified = time();
        $DB->update_record('user_enrolments', $ue);

        // User enrolments have changed, so mark user as dirty.
        mark_user_dirty($userid);

        // Invalidate core_access cache for get_suspended_userids.
        cache_helper::invalidate_by_definition('core', 'suspended_userids', array(), array($instance->courseid));

        // Trigger event.
        $event = \core\event\user_enrolment_updated::create(
                array(
                    'objectid' => $ue->id,
                    'courseid' => $instance->courseid,
                    'context' => context_course::instance($instance->courseid),
                    'relateduserid' => $ue->userid,
                    'other' => array('enrol' => $name)
                    )
                );
        $event->trigger();

        core_course_category::user_enrolment_changed($instance->courseid, $ue->userid,
                $ue->status, $ue->timestart, $ue->timeend);
    }

    public function get_name() {
        // second word in class is always enrol name, sorry, no fancy plugin names with _
        $words = explode('_', get_class($this));
        return $words[1];
    }
    public function roles_protected() {
        return true;
    }
    
    // function addcourse($courseId, $masterCourseId) {
    //     // Connect to database
    //     $servername = "localhost";
    //     $username = "root";
    //     $password = "";
    //     $dbname = "moodle";
    //     $conn = new mysqli($servername, $username, $password, $dbname);
    //     if ($conn->connect_error) {
    //       die("Connection failed: " . $conn->connect_error);
    //     }
      
    //     // Prepare SQL statement
    //     $sql = "UPDATE course SET id_mastercourse = ? WHERE id_course = ?";
    //     $stmt = $conn->prepare($sql);
    //     $stmt->bind_param("ii", $masterCourseId, $courseId);
      
    //     // Execute SQL statement
    //     if ($stmt->execute() === TRUE) {
    //       echo "Master course added successfully to course.";
    //     } else {
    //       echo "Error: " . $sql . "<br>" . $conn->error;
    //     }
      
    //     // Close database connection
    //     $conn->close();
    //   }
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

    // public function update_messages(array $messageids, $type): bool
    // {
    //     global $DB;
    //     list($ids, $params) = $DB->get_in_or_equal($messageids);
    //     return $DB->set_field_select('local_message', 'messagetype', $type, "id $ids", $params);
    // }

    /** Gets all messages that have not been read by this user
     * @param int $userid the user that we are getting messages for
     * @return array of messages
     */
    public function get_messages(int $userid): array
    {
        global $DB;
        $sql = "SELECT lm.id, lm.messagetext, lm.messagetype 
            FROM {local_message} lm 
            LEFT OUTER JOIN {local_message_read} lmr ON lm.id = lmr.messageid AND lmr.userid = :userid 
            WHERE lmr.userid IS NULL";
        $params = [
            'userid' => $userid,
        ];
        try {
            return $DB->get_records_sql($sql, $params);
        } catch (dml_exception $e) {
            // Log error here.
            return [];
        }
    }

    /** Gets all messages
     * @return array of messages
     */
    public function get_all_messages(): array {
        global $DB;
        return $DB->get_records('local_message');
    }

    /** Mark that a message was read by this user.
     * @param int $message_id the message to mark as read
     * @param int $userid the user that we are marking message read
     * @return bool true if successful
     */
    public function mark_message_read(int $message_id, int $userid): bool
    {
        global $DB;
        $read_record = new stdClass();
        $read_record->messageid = $message_id;
        $read_record->userid = $userid;
        $read_record->timeread = time();
        try {
            return $DB->insert_record('local_message_read', $read_record, false);
        } catch (dml_exception $e) {
            return false;
        }
    }

    /** Get a single message from its id.
     * @param int $messageid the message we're trying to get.
     * @return object|false message data or false if not found.
     */
    public function get_message(int $messageid)
    {
        global $DB;
        return $DB->get_record('local_message', ['id' => $messageid]);
    }

    /** Update details for a single message.
     * @param int $messageid the message we're trying to get.
     * @param string $message_text the new text for the message.
     * @param string $message_type the new type for the message.
     * @return bool message data or false if not found.
     */
    public function update_message(int $messageid, string $message_text, string $message_type): bool
    {
        global $DB;
        $object = new stdClass();
        $object->id = $messageid;
        $object->messagetext = $message_text;
        $object->messagetype = $message_type;
        return $DB->update_record('local_message', $object);
    }

    /** Update the type for an array of messages.
     * @return bool message data or false if not found.
     */
    public function update_messages(array $messageids, $type): bool
    {
        global $DB;
        list($ids, $params) = $DB->get_in_or_equal($messageids);
        return $DB->set_field_select('local_message', 'messagetype', $type, "id $ids", $params);
    }

    /** Delete a message and all the read history.
     * @param $messageid
     * @return bool
     * @throws \dml_transaction_exception
     * @throws dml_exception
     */
    public function delete_message($messageid)
    {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        $deletedMessage = $DB->delete_records('local_message', ['id' => $messageid]);
        $deletedRead = $DB->delete_records('local_message_read', ['messageid' => $messageid]);
        if ($deletedMessage && $deletedRead) {
            $DB->commit_delegated_transaction($transaction);
        }
        return true;
    }

    /** Delete all messages by id.
     * @param $messageids
     * @return bool
     */
    public function delete_messages($messageids)
    {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        list($ids, $params) = $DB->get_in_or_equal($messageids);
        $deletedMessages = $DB->delete_records_select('local_message', "id $ids", $params);
        $deletedReads = $DB->delete_records_select('local_message_read', "messageid $ids", $params);
        if ($deletedMessages && $deletedReads) {
            $DB->commit_delegated_transaction($transaction);
        }
        return true;
    }
}
