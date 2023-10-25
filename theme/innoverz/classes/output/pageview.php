<?php
// Added by Tai in lib\classes\pageview.php in 2.7
namespace theme_innoverz\output;

class pageview {

	private static $page;

	public static function log() {
		global $CFG, $DB, $USER, $COURSE;
		
		$dataobject = new \stdClass();
		$dataobject->userid = $USER->id;
		$dataobject->time = time();
		$dataobject->ip = $_SERVER['REMOTE_ADDR'];
		$dataobject->page = $CFG->urlpath;
		$dataobject->querystring = clean_param($_SERVER['QUERY_STRING'], PARAM_TEXT);
		$dataobject->courseid = $COURSE->id;
		
		$object = self::get_object_name();
		$dataobject->object = $object->object;
		$dataobject->objectid = $object->objectid;
		$dataobject->objectkey = $object->objectkey;

		return $DB->insert_record('pageview_log', $dataobject);
	}
	
	private static function get_object_name(){
		global $CFG, $DB, $PAGE;
	
		$dataobject = new \stdClass();
		$dataobject->object = '';
		$dataobject->objectid = 0;
		$dataobject->objectkey = '';
	
		//Parse by URL
		if(strpos($CFG->urlpath, '/elibrary/') === 0){
			$dataobject->object = 'elibrary';
			if($CFG->urlpath == '/elibrary/view_resource_detail.php'){
				$resourceid = required_param('id', PARAM_INT);
				$dataobject->objectid = $resourceid;
			}
		}elseif(strpos($CFG->urlpath, '/km/') === 0){
			$dataobject->object = 'km';
			if($CFG->urlpath == '/km/view.php'){
				$branch =  required_param('branch', PARAM_ALPHAEXT);
				$dataobject->objectkey = $branch;
			}
		//Parse by context
		}elseif(isset($PAGE->context->contextlevel)){
			switch($PAGE->context->contextlevel){
				case CONTEXT_SYSTEM:
					$dataobject->object = 'system';
					$dataobject->objectid = $PAGE->context->instanceid;
					break;
				case CONTEXT_USER:
					$dataobject->object = 'user';
					$dataobject->objectid = $PAGE->context->instanceid;
					break;
				case CONTEXT_COURSECAT:
					$dataobject->object = 'coursecat';
					$dataobject->objectid = $PAGE->context->instanceid;
					break;
				case CONTEXT_COURSE:
					if($PAGE->context->instanceid == 1){
						$dataobject->object = 'system';
						$dataobject->objectid = 0;
					}else{
						$dataobject->object = 'course';
						$dataobject->objectid = $PAGE->context->instanceid;
					}
					break;
				case CONTEXT_MODULE:
					$cm = $DB->get_record_sql("
						SELECT cm.id, m.name, cm.instance
						FROM mdl_course_modules cm
						LEFT JOIN mdl_modules m ON cm.module=m.id
						WHERE cm.id=?
					", array($PAGE->context->instanceid));
					$dataobject->object = $cm->name;
					$dataobject->objectid = $cm->instance;
					break;
				case CONTEXT_BLOCK:
					$dataobject->object = 'block';
					$dataobject->objectid = $PAGE->context->instanceid;
					break;
			}
		}
		
		return $dataobject;
	}

}
