<?php

require('../../config.php');
require_once($CFG->dirroot.$CFG->VIDEOS_BASEURL.'lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID

//get video thumbnail
if ($id && confirm_sesskey()) {
	$filepaths = get_filepath_by_course_module($id);
	$img = $filepaths ? $filepaths->image : $CFG->dirroot . '/theme/innoverz/pix/video_default.png';
	$getInfo = getimagesize($img);
	header('Content-type: ' . $getInfo['mime']);
	readfile($img);
}
?>