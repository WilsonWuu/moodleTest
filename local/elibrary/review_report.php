<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . $CFG->LIBRARY_BASEURL . 'lib.php');
require_once($CFG->libdir . '/adminlib.php');

$page = optional_param('page', 0, PARAM_INT);
$_SERVER['QUERY_STRING'] = clean_param($_SERVER['QUERY_STRING'], PARAM_TEXT);

require_login();
$PAGE->set_context(context_system::instance());
require_capability('local/elibrary:resourceadministration', context_system::instance());

$PAGE->set_url(new moodle_url($CFG->LIBRARY_BASEURL . 'review_report.php?' . $_SERVER['QUERY_STRING']));
$PAGE->set_pagelayout('standard');
//$PAGE->navbar->add(get_string('library','local_elibrary'));
//$PAGE->navbar->add(get_string('reviewreport','local_elibrary'));
$PAGE->set_title(get_string('library', 'local_elibrary'));
$PAGE->set_heading(get_string('library', 'local_elibrary'));

//admin_externalpage_setup('elibraryreviewreport');

if (isset($_GET['action'])) {
	switch ($_GET['action']) {
		case 'cancel_reserve':

			if (empty($_GET['id'])) {
				echo $OUTPUT->header();
				echo $OUTPUT->notification(get_string('msg_unknown_error', 'local_elibrary'));
				echo $OUTPUT->footer();
				exit;
			}

			$userid = get_reserve_userid($_GET['id']);
			if ($userid === false || (!has_capability('local/elibrary:viewuserreservationrecord', context_system::instance()) && $userid != $USER->id)) {
				echo $OUTPUT->header();
				echo $OUTPUT->notification(get_string('msg_unknown_error', 'local_elibrary'));
				echo $OUTPUT->footer();
				exit;
			}

			delete_reserve($_GET['id']);

			redirect($CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'user_reservation_history.php?' . base64_decode($_GET['query_string']));
			break;

		case 'hide_review':
			$result = hide_library_resource_review($_GET['reviewid']);
			if ($result['status'] == 'success') {
				redirect($CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'review_report.php');
				exit;
			} else {
				echo $OUTPUT->header();
				echo $OUTPUT->notification($result['msg']);
				echo $OUTPUT->footer();
				exit;
			}
			break;
		case 'delete_review':
			$result = delete_library_resource_review($_GET['reviewid']);
			if ($result['status'] == 'success') {
				redirect($CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'review_report.php');
				exit;
			} else {
				echo $OUTPUT->header();
				echo $OUTPUT->notification($result['msg']);
				echo $OUTPUT->footer();
				exit;
			}
			break;
	}
}

$renderer = $PAGE->get_renderer('theme_innoverz', 'core_elibrary');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('reviewreport', 'local_elibrary'));
echo $renderer->start_layout();

$review_list = get_all_review_list();

$review_count = count($review_list);
$perpage = 10;
$url = 'review_report.php?' . $_SERVER['QUERY_STRING'];

echo $renderer->review_list($review_list, $review_count, $page, $perpage, $url);

echo $renderer->complete_layout();
echo $OUTPUT->footer();
