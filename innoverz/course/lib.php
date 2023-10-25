<?php

require_once($CFG->dirroot . '/course/lib.php');

define('COURSE_TYPE_NORMAL', 1);
define('COURSE_TYPE_CLASSROOM', 2);
define('COURSE_TYPE_TEMPLATE', 3);
define('COURSE_STATUS_ACTIVE', 1);
define('COURSE_STATUS_EXPIRED', 2);
define('COURSE_STATUS_ARCHIVE', 3);

function set_coursemodule_quota($id, $quotaname, $quota, $renewsecond = 0) {
    global $DB;
	
	$object = new stdClass();
	$object->coursemoduleid = $id;
	$object->quotaname = $quotaname;
	$object->quota = $quota;
	$object->renewsecond = $renewsecond;
	
	$existcheck = $DB->get_record_sql("SELECT id FROM mdl_course_modules_quota WHERE coursemoduleid=$id AND quotaname='$quotaname'");
	if($existcheck === false){
		$DB->insert_record('course_modules_quota', $object);
	}else{
		$object->id = $existcheck->id;
		$DB->update_record('course_modules_quota', $object);
	}
}

function get_coursemodule_quota($id, $quotaname){
    global $DB;
	
	$quota = $DB->get_record_sql("SELECT quota FROM mdl_course_modules_quota WHERE coursemoduleid=$id AND quotaname='$quotaname'");
	return ($quota) ? $quota->quota : 0;
}


/**
 * Archive a course.
 *
 * @param int $courseid The course to change.
 * @return bool
 */
function course_archive($courseid){
    global $DB;
	$course = new stdClass;
    $course->id = $courseid;
    $course->isarchive = 1;
    update_course($course);
	
	//Set all courseware expired
	$course_modules = $DB->get_records_sql("
		SELECT cm.id, m.name module, cm.instance
		FROM mdl_course_modules cm
		LEFT JOIN mdl_modules m ON cm.module=m.id
		WHERE cm.course='$courseid' AND m.name IN ('scorm', 'assign', 'quiz', 'choice', 'questionnaire')
	");
	foreach($course_modules as $cm){
		$table = '';
		$field_enddate = '';
		switch($cm->module){
			case 'scorm':
				$table = 'mdl_scorm';
				$field_enddate = 'timeclose';
				break;
			case 'assign':
				$table = 'mdl_assign';
				$field_enddate = 'cutoffdate';
				break;
			case 'quiz':
				$table = 'mdl_quiz';
				$field_enddate = 'timeclose';
				break;
			case 'choice':
				$table = 'mdl_choice';
				$field_enddate = 'timeclose';
				break;
			case 'questionnaire':
				$table = 'mdl_questionnaire';
				$field_enddate = 'closedate';
				break;
		}
		$newenddate = strtotime(date('Y-m-d') . ' 00:00:00');
		$DB->execute("UPDATE $table SET $field_enddate='$newenddate' WHERE id='{$cm->instance}'");
	}
	
	
    return true;
}

/**
 * Unarchive a course.
 *
 * @param int $courseid The course to change.
 * @return bool
 */
function course_unarchive($courseid){
    $course = new stdClass;
    $course->id = $courseid;
    $course->isarchive = 0;
    update_course($course);
    return true;
}


function filter_course_type($courses, $course_type){	//COURSE_TYPE_NORMAL, COURSE_TYPE_CLASSROOM, COURSE_TYPE_TEMPLATE
	global $DB;

	if(!is_array($courses) || count($courses) == 0){
		return $courses;
	}
	
	$courses_id = array_keys($courses);
	$courses_id = implode(',', $courses_id);
	$SQL = "SELECT id FROM mdl_course WHERE id IN ($courses_id) AND ";
	switch($course_type){
		case COURSE_TYPE_NORMAL:
			$SQL .= "isclassroom=0 AND istemplate=0";
			break;
		case COURSE_TYPE_CLASSROOM:
			$SQL .= "isclassroom=1 AND istemplate=0";
			break;
		case COURSE_TYPE_TEMPLATE:
			$SQL .= "isclassroom=0 AND istemplate=1";
			break;
	}
	$courses_id = $DB->get_records_sql($SQL);
	$courses_id = array_keys($courses_id);
	foreach($courses as $key=>$course){
		if(!in_array($key, $courses_id)){
			unset($courses[$key]);
		}
	}
	
	return $courses;
}

function filter_course_status($courses, $course_status){	//COURSE_STATUS_ACTIVE, COURSE_STATUS_EXPIRED, COURSE_STATUS_ARCHIVE
	global $DB;

	if(!is_array($courses) || count($courses) == 0){
		return $courses;
	}
	
	$courses_id = array_keys($courses);
	$courses_id = implode(',', $courses_id);
	$SQL = "SELECT id FROM mdl_course WHERE id IN ($courses_id) AND ";
	$time = time();
	switch($course_status){
		case COURSE_STATUS_ACTIVE:
			$SQL .= "isarchive=0 AND (enddate=0 OR enddate>$time)";
			break;
		case COURSE_STATUS_EXPIRED:
			$SQL .= "isarchive=0 AND enddate!=0 AND enddate<=$time";
			break;
		case COURSE_STATUS_ARCHIVE:
			$SQL .= "isarchive=1";
			break;
	}
	$courses_id = $DB->get_records_sql($SQL);
	$courses_id = array_keys($courses_id);
	foreach($courses as $key=>$course){
		if(!in_array($key, $courses_id)){
			unset($courses[$key]);
		}
	}
	
	return $courses;
}