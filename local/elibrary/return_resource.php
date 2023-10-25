<?php

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.$CFG->LIBRARY_BASEURL.'lib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();
$PAGE->set_context(context_system::instance());
require_capability('local/elibrary:returnresource', context_system::instance());

$PAGE->set_url(new moodle_url($CFG->LIBRARY_BASEURL.'return_resource.php?' . $_SERVER['QUERY_STRING']));
$PAGE->set_pagelayout('standard');
//$PAGE->navbar->add(get_string('library','local_elibrary'));
//$PAGE->navbar->add(get_string('return_resource','local_elibrary'));
$PAGE->set_title(get_string('library','local_elibrary'));
$PAGE->set_heading(get_string('library','local_elibrary'));
//admin_externalpage_setup('elibraryreturnresource');

if(isset($_POST['return_accessno'])){
	if(!isset($_POST['return_accessno']) || !is_array($_POST['return_accessno']) || count($_POST['return_accessno']) == 0){
		echo $OUTPUT->header();
		echo $OUTPUT->notification(get_string('msg_unknown_error', 'local_elibrary'));
		echo $OUTPUT->footer();
		exit;
	}
	
	foreach($_POST['return_accessno'] as $accessno){
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
		
		add_return_record($loan_status->id);
	}
	
	redirect($PAGE->url, get_string('msg_success_return', 'local_elibrary'));
	exit;
}

$string_for_js = array('msg_resource_copy_not_found', 'msg_resource_copy_not_on_loan', 'msg_success_to_input_resource', 'msg_fail_to_input_resource', 'msg_resource_already_entered', 'msg_return_list_empty', 'days');
foreach($string_for_js as $string){
	$PAGE->requires->string_for_js($string, 'local_elibrary');
}
$PAGE->requires->js_init_call('M.local_elibrary.init_return_resource');

$renderer = $PAGE->get_renderer('theme_innoverz','core_elibrary');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('return_resource','local_elibrary'));
echo $renderer->start_layout();
echo $renderer->return_resource_scan_resource_barcode();
echo $renderer->return_resource_loan_list();

echo $renderer->complete_layout();
echo $OUTPUT->footer();