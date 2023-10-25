<?php

require_once('../../config.php');
require_once($CFG->dirroot.$CFG->LIBRARY_BASEURL.'lib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir.'/adminlib.php');

$searchhistory = optional_param('btn_searchhistory', null, PARAM_RAW);
$userid = optional_param('userid', $USER->id, PARAM_INT); //$user_id = 0:own, -1:all, -2:librarian
$page = optional_param('page', 0, PARAM_INT);
$_SERVER['QUERY_STRING'] = clean_param( $_SERVER['QUERY_STRING'], PARAM_TEXT);

require_login();
$PAGE->set_context(context_system::instance());

$PAGE->set_url(new moodle_url($CFG->LIBRARY_BASEURL.'user_loan_history.php?' . $_SERVER['QUERY_STRING']));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('library','local_elibrary'));
$PAGE->set_heading(get_string('library','local_elibrary'));
$PAGE->requires->jquery_plugin('user_loan_history','local_elibrary');

if(isset($userid) && $userid < 0 && has_capability('moodle/site:config', context_system::instance())){
	//admin_externalpage_setup('elibraryloanreport');
}else{
	$PAGE->navbar->add(get_string('library','local_elibrary'));
	$PAGE->navbar->add(get_string('user_loan_records','local_elibrary'));
}

$_GET['status'] = isset($_GET['status']) ? $_GET['status'] : 0;

if(!(has_capability('local/elibrary:viewuserloanrecord', context_system::instance()) || ($userid == $USER->id && has_capability('local/elibrary:viewownloanrecord', context_system::instance())))){
	echo $OUTPUT->header();
	echo $OUTPUT->notification(get_string('msg_unknown_error', 'local_elibrary'));
	echo $OUTPUT->footer();
	exit;
}

if(isset($_GET['action'])){
	switch($_GET['action']){
		case 'renew_resource':
		
			if(empty($_GET['accessno'])){
				echo $OUTPUT->header();
				echo $OUTPUT->notification(get_string('msg_unknown_error', 'local_elibrary'));
				echo $OUTPUT->footer();
				exit;
			}
			
			$loan_status = get_copy_loan_status($_GET['accessno']);

			if($loan_status === false){
				echo $OUTPUT->header();
				echo $OUTPUT->notification(get_string('msg_resource_copy_not_found', 'local_elibrary'));
				echo $OUTPUT->footer();
				exit;
			}
			
			$userid = get_loan_userid_by_copyid($loan_status->id);
			if($userid === false || (!has_capability('local/elibrary:renewresource', context_system::instance()) && $userid != $USER->id)){
				echo $OUTPUT->header();
				echo $OUTPUT->notification(get_string('msg_unknown_error', 'local_elibrary'));
				echo $OUTPUT->footer();
				exit;
			}

			if($loan_status->isloan != 1){
				echo $OUTPUT->header();
				echo $OUTPUT->notification(get_string('msg_resource_copy_not_on_loan', 'local_elibrary'));
				echo $OUTPUT->footer();
				exit;
			}
			
			$is_loan_overdue = check_is_loan_overdue($loan_status->id);
			if($is_loan_overdue){
				echo $OUTPUT->header();
				echo $OUTPUT->notification(get_string('msg_loan_overdue', 'local_elibrary'));
				echo $OUTPUT->footer();
				exit;
			}
			
			$loanid = get_loanid_by_copyid($loan_status->id);
			$renew_count = get_renew_count($loanid);
			if($renew_count >= LIBRARY_MAX_RENEW_TIMES){
				echo $OUTPUT->header();
				echo $OUTPUT->notification(get_string('msg_over_max_renew_times', 'local_elibrary'));
				echo $OUTPUT->footer();
				exit;
			}
			
			$resourceid = get_library_resourceid_by_copyid($loan_status->id);
			if(check_is_library_resource_reserving($resourceid)){
				echo $OUTPUT->header();
				echo $OUTPUT->notification(get_string('msg_resource_is_reserving', 'local_elibrary'));
				echo $OUTPUT->footer();
				exit;
			}
			
			$new_return_date = time() + (3600 * 24 * LIBRARY_RENEW_DAYS);
			$new_return_date = date('Y-m-d', $new_return_date) . ' 00:00:00';
			$new_return_date = strtotime($new_return_date) - 1;
			add_renew_record($loanid, $new_return_date);
			
			redirect($CFG->wwwroot.$CFG->LIBRARY_BASEURL.'user_loan_history.php?' . base64_decode($_GET['query_string']));
			exit;
	}
}

if(isset($_GET['borrower_username'])){
	$userid = get_userid_by_username($_GET['borrower_username']);
	if($userid === false){
		redirect($CFG->wwwroot.$CFG->LIBRARY_BASEURL.'user_loan_history.php', get_string('msg_user_not_found', 'local_elibrary'));
		exit;
	}
	$userid = $userid->id;
	redirect($CFG->wwwroot.$CFG->LIBRARY_BASEURL.'user_loan_history.php?userid=' . $userid);
	exit;
}

if(has_capability('local/elibrary:viewuserloanrecord', context_system::instance()) && $userid < 0){	//all / librarian
	$username = '';
}else{
	$username = get_username_by_userid($userid);
	if($username === false){
		redirect($CFG->wwwroot.$CFG->LIBRARY_BASEURL.'user_loan_history.php', get_string('msg_user_not_found', 'local_elibrary'));
		exit;
	}
	$username = $username->username;
}

$renderer = $PAGE->get_renderer('theme_innoverz','core_elibrary');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('user_loan_records','local_elibrary'));
echo $renderer->start_layout();

$user_current_loan_list = get_user_current_loan_list($userid);
if ($searchhistory) {
	$title = required_param('title', PARAM_TEXT);
	$operation = required_param('title_op', PARAM_INT);
	$accessno = optional_param('accessno', null, PARAM_ALPHANUM);
	$user_loan_history = get_user_loan_history($userid, $title, $operation, $accessno);
} else {
	$user_loan_history = get_user_loan_history($userid);
}

$loan_count = count($user_loan_history);
$perpage = 10;
$url = 'user_loan_history.php?' . $_SERVER['QUERY_STRING'];

if(has_capability('local/elibrary:viewuserloanrecord', context_system::instance())){
	echo $renderer->select_borrower('loan', $username, true);
}
echo $renderer->user_current_loan_list($user_current_loan_list, $_GET['status']);

echo '<h3 style="text-align:left">' . get_string('loan_history', 'local_elibrary') . '</h3>';

if (count($user_loan_history) || $searchhistory) {
	echo $renderer->filter_resource_history($userid);
}
echo $renderer->user_loan_history($user_loan_history, $loan_count, $page, $perpage, $url);

echo $renderer->complete_layout();
echo $OUTPUT->footer();