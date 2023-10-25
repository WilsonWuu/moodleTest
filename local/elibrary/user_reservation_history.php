<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . $CFG->LIBRARY_BASEURL . 'lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/adminlib.php');

$searchhistory = optional_param('btn_searchhistory', null, PARAM_RAW);
$userid = optional_param('userid', $USER->id, PARAM_INT); //$user_id = 0:own, -1:all
$page = optional_param('page', 0, PARAM_INT);
$_SERVER['QUERY_STRING'] = clean_param($_SERVER['QUERY_STRING'], PARAM_TEXT);

require_login();
$PAGE->set_context(context_system::instance());

$PAGE->set_url(new moodle_url($CFG->LIBRARY_BASEURL . 'user_reservation_history.php?' . $_SERVER['QUERY_STRING']));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('library', 'local_elibrary'));
$PAGE->set_heading(get_string('library', 'local_elibrary'));
$PAGE->requires->jquery_plugin('user_loan_history', 'local_elibrary');

if (isset($userid) && $userid < 0 && has_capability('moodle/site:config', context_system::instance())) {
	//admin_externalpage_setup('elibraryreservationreport');
} else {
	$PAGE->navbar->add(get_string('library', 'local_elibrary'));
	$PAGE->navbar->add(get_string('user_reservation_records', 'local_elibrary'));
}

if (!(has_capability('local/elibrary:viewuserreservationrecord', context_system::instance()) || ($userid == $USER->id && has_capability('local/elibrary:viewownreservationrecord', context_system::instance())))) {
	echo $OUTPUT->header();
	echo $OUTPUT->notification(get_string('msg_unknown_error', 'local_elibrary'));
	echo $OUTPUT->footer();
	exit;
}

if (isset($_GET['action'])) {
	switch ($_GET['action']) {
		case 'success_reserve':
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

			$loan_status = get_copy_loan_status($_GET['firstaccessno']);

			$return_date = time() + (3600 * 24 * LIBRARY_ON_LOAN_DAYS);
			$return_date = date('Y-m-d', $return_date) . ' 00:00:00';
			$return_date = strtotime($return_date) - 1;

			$is_ngo = check_is_ngo_user($userid);

			add_loan_record($userid, $_GET['resource_id'], $loan_status->id, $return_date, $is_ngo);

			redirect($CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'user_loan_history.php?' . base64_decode($_GET['query_string']));
			exit;
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
			exit;
	}
}

if (isset($_GET['borrower_username']) && confirm_sesskey()) {
	$userid = get_userid_by_username($_GET['borrower_username']);
	if ($userid === false) {
		redirect($CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'user_reservation_history.php', get_string('msg_user_not_found', 'local_elibrary'));
		exit;
	}
	$userid = $userid->id;
	redirect($CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'user_reservation_history.php?userid=' . $userid);
	exit;
}

if (has_capability('local/elibrary:viewuserreservationrecord', context_system::instance()) && $userid < 0) {	//all
	$username = '';
} else {
	$username = get_username_by_userid($userid);
	if ($username === false) {
		redirect($CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'user_reservation_history.php', get_string('msg_user_not_found', 'local_elibrary'));
		exit;
	}
	$username = $username->username;
}

$renderer = $PAGE->get_renderer('theme_innoverz', 'core_elibrary');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('user_reservation_records', 'local_elibrary'));
echo $renderer->start_layout();

$user_current_reservation_list = get_user_current_reservation_list($userid);
if ($searchhistory) {
	$title = required_param('title', PARAM_TEXT);
	$operation = required_param('title_op', PARAM_INT);
	$accessno = optional_param('accessno', null, PARAM_ALPHANUM);
	$user_reservation_history = get_user_reservation_history($userid, $title, $operation, $accessno);
} else {
	$user_reservation_history = get_user_reservation_history($userid);
}

$loan_count = count($user_reservation_history);
$perpage = 10;
$url = 'user_reservation_history.php?' . $_SERVER['QUERY_STRING'];

if (has_capability('local/elibrary:viewuserreservationrecord', context_system::instance())) {
	echo $renderer->select_borrower('reserve', $username, true);
}
echo $renderer->user_current_reservation_list($user_current_reservation_list);

echo '<h3 style="text-align:left">' . get_string('reservation_history', 'local_elibrary') . '</h3>';

if (count($user_reservation_history) || $searchhistory) {
	echo $renderer->filter_resource_history($userid);
}

echo $renderer->user_reservation_history($user_reservation_history, $loan_count, $page, $perpage, $url);

echo $renderer->complete_layout();
echo $OUTPUT->footer();
