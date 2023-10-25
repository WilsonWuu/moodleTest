<?php

require_once(dirname(__FILE__).'/../../config.php');
require_once('lib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once('searchvideo_form.php');
require_once('renderer.php');

$sectionreturn = optional_param('sr', null, PARAM_INT);
$delete        = optional_param('delete', 0, PARAM_INT);
$confirm       = optional_param('confirm', 0, PARAM_BOOL);
$page = optional_param('page', 0, PARAM_INT); // page number
$perpage = 10;
if (isset($_GET['issubmit']) && confirm_sesskey()) {
	$category = required_param('category', PARAM_INT); // Category ID
} else {
	$category = optional_param('category', 0, PARAM_INT); // Category ID
}

require_course_login(get_site());
$PAGE->set_url(new moodle_url($CFG->VIDEOS_BASEURL.'searchvideo.php?' . $_SERVER['QUERY_STRING']));

if (!empty($delete)) {
    $cm     = get_coursemodule_from_id('', $delete, 0, true, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

    require_login($course, false, $cm);
    $modcontext = context_module::instance($cm->id);
	
	$userid = get_resource_owner_by_cm($cm->id);
	
	if (!$userid == $USER->id) {
		require_capability('local/videos:managevideoresources', $modcontext);
	}

    $return = new Moodle_url($CFG->VIDEOS_BASEURL.'searchvideo.php');

    if (!$confirm or !confirm_sesskey()) {
        $fullmodulename = get_string('modulename', $cm->modname);

        $optionsyes = array('confirm'=>1, 'delete'=>$cm->id, 'sesskey'=>sesskey(), 'sr' => $sectionreturn);

        $strdeletecheck = get_string('deletecheck', '', $fullmodulename);
        $strdeletecheckfull = get_string('deletecheckfull', '', "$fullmodulename '$cm->name'");

        $PAGE->set_pagetype('mod-' . $cm->modname . '-delete');
        $PAGE->set_title($strdeletecheck);
        $PAGE->set_heading($course->fullname);
        $PAGE->navbar->add($strdeletecheck);

        echo $OUTPUT->header();
        echo $OUTPUT->box_start('noticebox');
        $formcontinue = new single_button(new moodle_url("$CFG->wwwroot/local/videos/searchvideo.php", $optionsyes), get_string('yes'));
        $formcancel = new single_button($return, get_string('no'), 'get');
        echo $OUTPUT->confirm($strdeletecheckfull, $formcontinue, $formcancel);
        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();

        exit;
    }
	
    // Delete the module.
	//remove_video_file($modcontext);
	if ($file = get_file_by_course_module($cm->id)) {
		remove_video_thumbnail(get_file_by_course_module($cm->id));
	}
    course_delete_module($cm->id);

    redirect($return);
}

$PAGE->requires->js(new moodle_url($CFG->VIDEOS_BASEURL.'module.js'));

$PAGE->set_pagelayout('standard');

$PAGE->navbar->add(get_string('videos','local_videos'));
//$PAGE->navbar->add(get_string('videolist','local_videos'));
$PAGE->set_title(get_string('videos','local_videos'));
$PAGE->set_heading(get_string('videos','local_videos'));

$renderer = new core_videos_renderer();


echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('videolist','local_videos'));

echo $renderer->start_layout();

echo $renderer->videos_top_navigation($_GET);

$video_list = get_video_list($category);
$video_count = count($video_list);

$url = 'searchvideo.php?' . $_SERVER['QUERY_STRING'];

echo $renderer->view_video_list($video_list, $video_count, $page, $perpage, $url);

echo $renderer->complete_layout();

echo $OUTPUT->footer();
