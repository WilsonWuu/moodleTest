<?php

require_once("../../config.php");
require_once($CFG->dirroot.$CFG->EBOOK_BASEURL.'lib.php');
require_once($CFG->dirroot.$CFG->EBOOK_BASEURL.'renderer.php');

$approve = optional_param('approve', 0, PARAM_INT);
$reject = optional_param('reject', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);

$_SERVER['QUERY_STRING'] = clean_param( $_SERVER['QUERY_STRING'], PARAM_TEXT);

$base_url = new moodle_url($CFG->EBOOK_BASEURL.'subscribe_management.php');

require_login();
$PAGE->set_context(context_system::instance());

$PAGE->set_url(new moodle_url($CFG->EBOOK_BASEURL.'subscribe_management.php?' . $_SERVER['QUERY_STRING']));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('menuebook'));
$PAGE->set_heading(get_string('menuebook'));

if ($approve) {
	$subscription = getSubscription($approve);
	approveSubscription($approve);
	send_subscribe_approved_email(core_user::get_user($subscription->userid));
	redirect($base_url, get_string('ebook_subscribe_approve_success'));
}

if ($reject) {
	$subscription = getSubscription($reject);
	rejectSubscription($reject);
	send_subscribe_rejected_email(core_user::get_user($subscription->userid));
	redirect($base_url, get_string('ebook_subscribe_reject_success'));
}

$subscribe_list = getSubscriptionList();

$renderer = new core_ebook_renderer();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('ebook_subscribe_management'));
echo $renderer->start_layout();

$totalcount = count($subscribe_list);
$perpage = 10;
$url = 'subscride_management.php?' . $_SERVER['QUERY_STRING'];

echo $renderer->view_subscribe_list($subscribe_list, $totalcount, $page, $perpage, $url);

echo $renderer->complete_layout();
echo $OUTPUT->footer();