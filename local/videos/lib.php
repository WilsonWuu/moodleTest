<?php

if (!defined('MOODLE_INTERNAL')) {
	die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

$mp4exion = array('mp4', 'm4v', 'f4v');

function video_get_file_types()
{
	return array('.mp4', '.m4v', '.f4v', '.txt', '.jpeg', '.jpg', '.png', '.bmp');
}

function video_get_video_file_types()
{
	return array('.mp4', '.m4v', '.f4v');
}

function get_sellector_all_categories()
{
	/*global $DB;
	$table = "course_categories";
	$conditions = null;
	$sort = "sortorder";
	$sdcats = $DB->get_records($table, $conditions, $sort);		
	$cats = array();
	foreach($sdcats as $obj) {
		$cats[$obj->id] = $obj->name;
	}*/
	$cats = core_course_category::make_categories_list('moodle/course:create', 3, " / ", 2);
	return $cats;
}

function videos_can_update_moduleinfo($cm, $resourcename = 'video')
{
	global $DB, $USER;

	// Check the $USER has the right capability.
	$context = context_module::instance($cm->id);

	$userid = get_resource_owner_by_cm($cm->id);

	if ($userid != $USER->id) {
		if ($resourcename == 'video') {
			require_capability('local/videos:managevideoresources', $context);
		} else if ($resourcename == 'interrai'){
			require_capability('local/interrai:managefileresources', $context);
		}else{
			require_capability('local/resources:managefileresources', $context);
		}
	}

	// Check module exists.
	$module = $DB->get_record('modules', array('id' => $cm->module), '*', MUST_EXIST);

	// Check the moduleinfo exists.
	$data = $DB->get_record($module->name, array('id' => $cm->instance), '*', MUST_EXIST);

	// Check the course section exists.
	$cw = $DB->get_record('course_sections', array('id' => $cm->section), '*', MUST_EXIST);

	return array($cm, $context, $module, $data, $cw);
}

function update_videos_resource_button($cmid, $ignored, $string, $path)
{
	global $DB, $CFG, $OUTPUT, $USER;

	// debugging('update_module_button() has been deprecated. Please change your code to use $OUTPUT->update_module_button().');

	//NOTE: DO NOT call new output method because it needs the module name we do not have here!

	$record = $DB->get_record('context', array('instanceid' => $cmid, 'contextlevel' => CONTEXT_MODULE));
	$userid = get_resource_owner($record->path);

	if (has_capability('moodle/course:manageactivities', context_module::instance($cmid)) || $userid == $USER->id) {
		$string = get_string('updatethis', '', $string);

		$url = new moodle_url($path, array('update' => $cmid, 'return' => true, 'sesskey' => sesskey()));
		return $OUTPUT->single_button($url, $string);
	} else {
		return '';
	}
}

function videos_resource_print_header($resource, $cm, $course, $path)
{
	global $PAGE, $OUTPUT;

	$PAGE->set_title($course->shortname . ': ' . $resource->name);
	$PAGE->set_heading($course->fullname);
	$PAGE->set_activity_record($resource);
	$PAGE->set_button(update_videos_resource_button($cm->id, '', get_string('modulename', 'resource'), $path));
	echo $OUTPUT->header();
}

/**
 * @deprecated in 3.9, because videos display from 2.7 is not working, 
 * use resource_video_display_embed() instead
 */
/* function stream_video($resource, $cm, $course, $file, $hashdvideo, $ishdvideo = 0)
{
	global $CFG, $PAGE, $OUTPUT, $RTMP_URL, $HTTP_URL, $mp4exion, $FILE_DRIVE;

	$clicktoopen = resource_get_clicktoopen($file, $resource->revision);

	$context = context_module::instance($cm->id);
	$path = '/' . $context->id . '/mod_resource/content/' . $resource->revision . $file->get_filepath() . $file->get_filename();
	$fullurl = file_encode_url($CFG->wwwroot . '/pluginfile.php', $path, false);
	$moodleurl = new moodle_url('/pluginfile.php' . $path);

	$mimetype = $file->get_mimetype();
	$title    = $resource->name;

	$extension = resourcelib_get_extension($file->get_filename());
	$contenthash = $file->get_contenthash();
	$filepath = substr($contenthash, 0, 2) . '/' . substr($contenthash, 2, 2) . '/' . $contenthash;
	if (in_array($extension, $mp4exion)) {
		$rtmpsubpath = 'mp4:' . $filepath;
	} else {
		if (file_exists($CFG->filedir . '/' . $filepath))
			copy($CFG->filedir . '/' . $filepath, $CFG->filedir . '/' . $filepath . '.' . $extension);
	}

	$urls = array();
	//reference to external_file which stored in FTP/Video folder
	if ($file && $file->is_external_file()) {
		$reference = $file->get_reference();
		if (!empty(explode('/', $reference)[0]))
			$reference = '/' . $reference;
		$subpath = 'repository' . $reference;
		$urls[] = $CFG->STREAM_HTTP_URL . $subpath;
		$subpath = 'mp4:repository' . $reference;
		$urls[] = $CFG->STREAM_HTTP_URL . $subpath;
	} else {
		$urls[] = $CFG->STREAM_HTTP_URL . $filepath;
		$urls[] = $CFG->STREAM_HTTP_URL . $rtmpsubpath;
	}

	//$url = $RTMP_URL . $subpath;	
	//$url = 'rtmp://192.168.1.251/vod/35/6b/sample';


	$mediarenderer = new core_videos_renderer();
	$code = $mediarenderer->get_video_player($urls);

	videos_resource_print_header($resource, $cm, $course, $CFG->VIDEOS_BASEURL . 'editvideo.php');
	resource_print_heading($resource, $cm, $course);

	echo "<div class='publicmarginbottom'>" . get_string('video_recommend', 'local_videos') . "</div>";

	echo $code;

	if ($hashdvideo) {
		echo $mediarenderer->video_version_control($ishdvideo);
	}

	echo "<br><hr>";
	resource_print_intro($resource, $cm, $course);

	if (get_file_by_course_module($cm->id, $mimetype = 'text/plain')) {
		echo $mediarenderer->download_transcript($cm->id);
	}

	echo $OUTPUT->footer();
	die;
}
 */

/**
 * Referred from mod\resource\locallib.php
 * Display embedded resource file and, if necessary, display "download transcript" button.
 * @param object $resource
 * @param object $cm
 * @param object $course
 * @param stored_file $file main file
 * @return does not return
 */
function resource_video_display_embed($resource, $cm, $course, $file, $hashdvideo = false, $ishdvideo = 0)
{
	global $CFG, $PAGE, $OUTPUT;

	$clicktoopen = resource_get_clicktoopen($file, $resource->revision);

	$context = context_module::instance($cm->id);

	// we use custom pluginfile to ensure cleaniness
	/*  $moodleurl = moodle_url::make_pluginfile_url(
		$context->id,
		'mod_resource',
		$ishdvideo ?  'hdvideo' : 'content',
		$resource->revision,
		$file->get_filepath(),
		$file->get_filename()
	); */

	$filearea = $ishdvideo ?  'hdvideo' : 'content';
	$moodleurl = new moodle_url($CFG->VIDEOS_BASEURL . "pluginfile.php/{$context->id}/mod_resource/{$filearea}/{$resource->revision}/{$file->get_filename()}");

	$mimetype = $file->get_mimetype();
	$title    = $resource->name;

	$extension = resourcelib_get_extension($file->get_filename());

	$mediamanager = core_media_manager::instance($PAGE);
	$embedoptions = array(
		core_media_manager::OPTION_TRUSTED => true,
		core_media_manager::OPTION_BLOCK => true,
	);

	if (file_mimetype_in_typegroup($mimetype, 'web_image')) {  // It's an image
		$code = resourcelib_embed_image($moodleurl->out(), $title);
	} else if ($mimetype === 'application/pdf') {
		// PDF document
		$code = resourcelib_embed_pdf($moodleurl->out(), $title, $clicktoopen);
	} else if ($mediamanager->can_embed_url($moodleurl, $embedoptions)) {
		// Media (audio/video) file.
		$code = $mediamanager->embed_url($moodleurl, $title, 0, 0, $embedoptions);
	} else {
		// We need a way to discover if we are loading remote docs inside an iframe.
		$moodleurl->param('embed', 1);

		// anything else - just try object tag enlarged as much as possible
		$code = resourcelib_embed_general($moodleurl, $title, $clicktoopen, $mimetype);
	}

	resource_print_header($resource, $cm, $course);
	resource_print_heading($resource, $cm, $course);

	echo $code;

	$mediarenderer = new core_videos_renderer();
	if ($hashdvideo) {
		echo $mediarenderer->video_version_control($ishdvideo);
	}

	echo "<br><hr>";
	resource_print_intro($resource, $cm, $course);

	if (get_file_by_course_module($cm->id, $mimetype = 'text/plain')) {
		echo $mediarenderer->download_transcript($cm->id);
	}

	echo $OUTPUT->footer();
	die;
}

function update_course_module_category($data)
{
	global $DB;
	$record = new stdclass();
	$record->id = $data->coursemodule;
	$record->category = $data->category;

	$DB->update_record("course_modules", $record);
	return true;
}

function get_module_category($cmid)
{
	global $DB;
	$record = $DB->get_record("course_modules", array('id' => $cmid), 'category');
	return $record->category;
}

function set_isvideo($data)
{
	global $DB;
	$record = new stdclass();
	$record->id = $data->instance;
	$record->isvideo = 1;

	$DB->update_record("resource", $record);
}

function get_video_list($catid = 0, $limit = null)
{
	global $DB, $SETION_VIDEO;
	$CONTEXT_MODULE = CONTEXT_MODULE;
	$SQL = "
		SELECT cm.id, cm.instance, cm.showdescription, r.name, r.intro, r.introformat, r.displayoptions, r.link, c.path, c.depth
		FROM mdl_course_modules cm, mdl_resource r, mdl_context c
		WHERE cm.instance = r.id
		AND cm.id = c.instanceid
		AND c.contextlevel = $CONTEXT_MODULE
		AND cm.visible = 1
		AND cm.course = 1
		AND r.isvideo = 1	
		AND r.isinterrai = 0			
	";
	if ($catid) {
		$SQL .= "AND cm.category = $catid";
	}
	$SQL .= " ORDER BY r.video_ordering DESC"; // use custom video ordering field to sort
	if (!is_null($limit)) {
		$SQL .= " LIMIT {$limit}";
	}
	$list = $DB->get_records_sql($SQL);
	foreach ($list as $row) {
		$row->userid = get_resource_owner($row->path);
	}
	return $list;
}

function get_resource_owner($path)
{
	global $DB;
	$temp = explode('/', $path);
	$contextid = end($temp);
	$record = $DB->get_records('files', array('contextid' => $contextid, 'component' => 'mod_resource'), '', 'userid', 0, 1);
	return key($record);
}

function get_resource_owner_by_cm($cmid)
{
	global $DB;
	$record = $DB->get_record('context', array('instanceid' => $cmid, 'contextlevel' => CONTEXT_MODULE));
	return get_resource_owner($record->path);
}

function create_video_thumbnail($cmid)
{
	global $CFG;
	require_once('ffmpeg_lib.php');
	if ($filepaths = get_filepath_by_course_module($cmid)) {
		$imagefilename = $filepaths->image;
		if (!file_exists($imagefilename)) {
			if (!createImageFromVideo($filepaths->video, $filepaths->image)) {
				echo 'cannot create image file';
				exit();
			}
		}
	}
}

function remove_video_thumbnail($file)
{
	global $DB;
	$table = 'files';
	$select = 'contenthash = ? AND component != ? AND filearea != ? AND filename = ?';
	$params = array($file->get_contenthash(), 'user', 'draft', $file->get_filename());
	//$conditions = array('contenthash'=>$file->get_contenthash(), 'component !='=>'user', 'filearea !=','draft');
	if ($DB->count_records_select($table, $select, $params) <= 1) {
		$filepaths = get_filepath_by_file($file);
		$imagefilename = $filepaths->image;
		if (file_exists($imagefilename)) {
			unlink($imagefilename);
		}
	}
}

function get_file_by_course_module($cmid, $mimetype = 'video/mp4', $filearea = 'content')
{
	$context = context_module::instance($cmid);
	$fs = get_file_storage();
	$files = $fs->get_area_files($context->id, 'mod_resource', $filearea, 0, 'sortorder DESC, id ASC', false); // TODO: this is not very efficient!!
	if (count($files) < 1) {
		//resource_print_filenotfound($resource, $cm, $course);
		return false;
	} else {
		$file = null;
		foreach ($files as $thisfile) {
			if (strpos($thisfile->get_mimetype(), $mimetype) !== false) {
				$file = $thisfile;
				break;
			}
		}
		unset($files);
		return $file;
	}
}

//get the completed file path
function get_filepath_by_course_module($cmid)
{
	if ($file = get_file_by_course_module($cmid)) {
		$filepaths = get_filepath_by_file($file);
		return $filepaths;
	} else {
		return false;
	}
}

function get_filepath_by_file($file)
{
	global $CFG;
	$contenthash = $file->get_contenthash();
	$filepaths = new stdclass();
	if ($file->is_external_file()) {
		$videofolder = $CFG->filedir . "/video_images";
		if (!is_dir($videofolder)) {
			mkdir($videofolder);
		}
		$reference = $file->get_reference();
		if (!empty(explode('/', $reference)[0]))
			$reference = '/' . $reference;
		$filepaths->video = $CFG->repositorydir . '/video' . $reference;
		$filepaths->image = $videofolder . $reference . $CFG->thumbnailfiletype;
		return $filepaths;
	} else {
		$filepath = $CFG->filedir . '/' . substr($contenthash, 0, 2) . '/' . substr($contenthash, 2, 2) . '/' . $contenthash;
		$filepaths->video = $filepath;
		$filepaths->image = $filepath . $CFG->thumbnailfiletype;
		return $filepaths;
	}
}

function remove_dir_file($dir)
{
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dir . "/" . $object) == "dir")
					rrmdir($dir . "/" . $object);
				else unlink($dir . "/" . $object);
			}
		}
		reset($objects);
		rmdir($dir);
	}
}

//not use
function remove_video_file($context)
{
	$fs = get_file_storage();
	$files = $fs->get_area_files($context->id, 'mod_resource', 'content', 0, 'sortorder DESC, id ASC', false); // TODO: this is not very efficient!!
	print_r($files);
	exit();
	if (count($files) < 1) {
		resource_print_filenotfound($resource, $cm, $course);
		die;
	} else {
		$file = reset($files);
		unset($files);
	}
	$contenthash = $file->get_contenthash();
	$subpath = substr($contenthash, 0, 2);
	remove_dir_file();
}

function video_set_otherinfo($data)
{
	global $DB;
	$record = new stdclass();
	$record->id = $data->instance;
	$record->link = ($data->link == "http://" || empty($data->link)) ? null : $data->link;
	if (!isset($data->isnonconformance)) {
		$record->isnonconformance = 0;
	}
	/**
	 * add video_ordering field value
	 */
	$record->video_ordering = $data->video_ordering;

	$DB->update_record("resource", $record);
}

function save_hdvideofile($cmid, $hdvideofile)
{
	global $CFG;
	require_once("$CFG->libdir/filelib.php");

	$context = context_module::instance($cmid);
	// added by felix - handle uploaded HD video
	$draftitemid = $hdvideofile;
	file_save_draft_area_files($draftitemid, $context->id, 'mod_resource', 'hdvideo', 0, array('subdirs' => true));
}


/**
 * referred from mod\resource\lib.php function resource_pluginfile(..)
 * Serves the resource files.
 *
 * @package  mod_resource
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function video_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload = false, array $options = array())
{
	global $CFG, $DB;
	$file = getVideoFile($course, $cm, $context, $filearea, $args, $forcedownload, $options);

	// should we apply filters?
	$mimetype = $file->get_mimetype();
	if ($mimetype === 'text/html' or $mimetype === 'text/plain' or $mimetype === 'application/xhtml+xml') {
		$filter = $DB->get_field('resource', 'filterfiles', array('id' => $cm->instance));
		$CFG->embeddedsoforcelinktarget = true;
	} else {
		$filter = 0;
	}

	// finally send the file
	send_stored_file($file, null, $filter, $forcedownload, $options);
}

function getVideoFile($course, $cm, $context, $filearea, $args, $forcedownload = false, array $options = array()){
	global $CFG, $DB;
	require_once("$CFG->libdir/resourcelib.php");

	if ($context->contextlevel != CONTEXT_MODULE) {
		return false;
	}

	require_course_login($course, true, $cm);
	if (!has_capability('mod/resource:view', $context)) {
		return false;
	}

	array_shift($args); // ignore revision - designed to prevent caching problems only

	$fs = get_file_storage();
	$relativepath = implode('/', $args);
	$fullpath = rtrim("/$context->id/mod_$cm->modname/$filearea/0/$relativepath", '/');
	do {
		if (!$file = $fs->get_file_by_hash(sha1($fullpath))) {
			if ($fs->get_file_by_hash(sha1("$fullpath/."))) {
				if ($file = $fs->get_file_by_hash(sha1("$fullpath/index.htm"))) {
					break;
				}
				if ($file = $fs->get_file_by_hash(sha1("$fullpath/index.html"))) {
					break;
				}
				if ($file = $fs->get_file_by_hash(sha1("$fullpath/Default.htm"))) {
					break;
				}
			}
			$resource = $DB->get_record('resource', array('id' => $cm->instance), 'id, legacyfiles', MUST_EXIST);
			if ($resource->legacyfiles != RESOURCELIB_LEGACYFILES_ACTIVE) {
				return false;
			}
			if (!$file = resourcelib_try_file_migration('/' . $relativepath, $cm->id, $cm->course, 'mod_resource', 'content', 0)) {
				return false;
			}
			// file migrate - update flag
			$resource->legacyfileslast = time();
			$DB->update_record('resource', $resource);
		}
	} while (false);

	return $file;

}

function getMostRecentVideoOrdering()
{
	global $DB;
	$value = $DB->get_field_sql("SELECT video_ordering FROM mdl_resource order by video_ordering desc limit 1");
	if ($value != null)
		return intval($value) + 10;
	return 10; //default init ordering value
}