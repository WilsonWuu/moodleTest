<?php

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.$CFG->LIBRARY_BASEURL.'lib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();
$PAGE->set_context(context_system::instance());
require_capability('local/elibrary:renewresource', context_system::instance());

$PAGE->set_url(new moodle_url($CFG->LIBRARY_BASEURL.'renew_resource.php?' . $_SERVER['QUERY_STRING']));
$PAGE->set_pagelayout('standard');
//$PAGE->navbar->add(get_string('library','local_elibrary'));
//$PAGE->navbar->add(get_string('renew_resource','local_elibrary'));
$PAGE->set_title(get_string('library','local_elibrary'));
$PAGE->set_heading(get_string('library','local_elibrary'));
//admin_externalpage_setup('elibraryrenewresource');

if(isset($_POST['renew_accessno'])){
	if(!isset($_POST['renew_accessno']) || !is_array($_POST['renew_accessno']) || count($_POST['renew_accessno']) == 0){
		echo $OUTPUT->header();
		echo $OUTPUT->notification(get_string('msg_unknown_error', 'local_elibrary'));
		echo $OUTPUT->footer();
		exit;
	}
	
	foreach($_POST['renew_accessno'] as $accessno){
		$loan_status = get_copy_loan_status($accessno);

		if($loan_status === false){
			echo $OUTPUT->header();
			echo $OUTPUT->notification(get_string('msg_resource_copy_not_found', 'local_elibrary'));
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
	}
	redirect($PAGE->url, get_string('msg_success_renew', 'local_elibrary'));
	exit;
}

$string_for_js = array('msg_resource_copy_not_found', 'msg_resource_copy_not_on_loan', 'msg_success_to_input_resource', 'msg_fail_to_input_resource', 'msg_resource_already_entered', 'msg_renew_list_empty', 'msg_loan_overdue', 'msg_over_max_renew_times', 'msg_resource_is_reserving');
foreach($string_for_js as $string){
	$PAGE->requires->string_for_js($string, 'local_elibrary');
}
$PAGE->requires->js_init_call('M.local_elibrary.init_renew_resource');

$renderer = $PAGE->get_renderer('theme_innoverz','core_elibrary');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('renew_resource','local_elibrary'));
echo $renderer->start_layout();
echo $renderer->renew_resource_scan_resource_barcode();
echo $renderer->renew_resource_loan_list();

echo $renderer->complete_layout();
echo $OUTPUT->footer();