<?php

require_once("../../config.php");
require_once($CFG->dirroot.$CFG->EBOOK_BASEURL."lib.php");
require_once($CFG->dirroot.$CFG->EBOOK_BASEURL."renderer.php");

$booksubscribe = optional_param('btn_booksubscribe', null, PARAM_RAW);
$bookdisplay = optional_param('bookdisplay', 0, PARAM_INT);

require_login();

require_capability('local/ebook:view', context_system::instance());

$PAGE->set_context(context_system::instance());

$PAGE->set_url(new moodle_url($CFG->EBOOK_BASEURL.'index.php?' . $_SERVER['QUERY_STRING']));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('menuebook','local_ebook'));
$PAGE->set_heading(get_string('menuebook'));

if ($bookdisplay) {
	
}

if ($booksubscribe) {
	createBookSubscription($USER->id);
	redirect($PAGE->url, get_string('ebook_subscribe_success'));
}

$mySubscription = getMySubscription();

$renderer = new core_ebook_renderer();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('ebook_subscribe'));
echo $renderer->start_layout();

if ($mySubscription) {
	$subscribe_status_list = getSubscriptionStatusList();
	switch($subscribe_status_list[$mySubscription->status]) {
		case 'PROCESS':
			echo $renderer->user_subscribe_processing();
			break;
		case 'REJECTED':
			echo $renderer->user_subscribe_rejected();
			echo $renderer->user_subscribe();
			break;
		case 'APPROVED':
			echo $renderer->user_subscribe_approved();
			break;
	}
} else {
	echo $renderer->user_subscribe();
}
echo $renderer->complete_layout();
echo $OUTPUT->footer();