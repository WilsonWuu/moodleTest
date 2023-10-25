
<?php

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.$CFG->LIBRARY_BASEURL.'lib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();
$PAGE->set_context(context_system::instance());
require_capability('local/elibrary:loanresource', context_system::instance());

$PAGE->set_url(new moodle_url($CFG->LIBRARY_BASEURL.'loan_resource.php?' . $_SERVER['QUERY_STRING']));
$PAGE->set_pagelayout('standard');
//$PAGE->navbar->add(get_string('library','local_elibrary'));
//$PAGE->navbar->add(get_string('loan_resource','local_elibrary'));
$PAGE->set_title(get_string('library','local_elibrary'));
$PAGE->set_heading(get_string('library','local_elibrary'));
//admin_externalpage_setup('elibraryloanresource');

$user_id = optional_param('user_id', 0, PARAM_INT);

if(isset($_POST['loan_accessno'])){
	if(!isset($user_id) || empty($user_id)
	|| !isset($_POST['loan_accessno']) || !is_array($_POST['loan_accessno']) || count($_POST['loan_accessno']) == 0
	|| (isset($_POST['borrower']) && !empty($_POST['borrower']) && !validate_borrower_encoded_string($_POST['borrower']))){
		echo $OUTPUT->header();
		echo $OUTPUT->notification(get_string('msg_unknown_error', 'local_elibrary'));
		echo $OUTPUT->footer();
		exit;
	}
	
	$is_ngo = check_is_ngo_user($user_id);
	if(isset($is_ngo['status']) && $is_ngo['status'] == 'fail'){
		echo $OUTPUT->header();
		echo $OUTPUT->notification(get_string($is_ngo['msg'], 'local_elibrary'));
		echo $OUTPUT->footer();
		exit;
	}
	
	if ($user_id) {
		$user_number_of_loan = get_user_number_of_loan($user_id);

		$loan_quota = LIBRARY_SWD_GL_LOAN_QUOTA;
		if(check_is_unlimited_loan_quota($user_id)){
			$loan_quota = LIBRARY_UNLIMITED_LOAN_QUOTA;
		}elseif($is_ngo){
			$loan_quota = LIBRARY_NGO_GL_LOAN_QUOTA;
		}
	}else if(isset($_POST['borrower']) && !empty($_POST['borrower'])){
		$borrower = json_decode(base64_decode($_POST['borrower']));
		$user_number_of_loan = get_user_number_of_loan_by_borrowerid($borrower->borrowerid);
		$loan_quota = LIBRARY_LIBRARIAN_LOAN_QUOTA;
	} else {
		// this statement must not be never executed
		redirect($CFG->wwwroot.$CFG->LIBRARY_BASEURL.'loan_resource_select_borrower.php');
		exit;
	}
	
	if(($user_number_of_loan + count($_POST['loan_accessno'])) > $loan_quota){
		echo $OUTPUT->header();
		echo $OUTPUT->notification(get_string('msg_over_loan_quota', 'local_elibrary'));
		echo $OUTPUT->footer();
		exit;
	}
	
	foreach($_POST['loan_accessno'] as $accessno){
		$loan_status = get_copy_loan_status($accessno);

		if($loan_status === false){
			echo $OUTPUT->header();
			echo $OUTPUT->notification(get_string('msg_resource_copy_not_found', 'local_elibrary'));
			echo $OUTPUT->footer();
			exit;
		}

		if($loan_status->isloan == 1){
			echo $OUTPUT->header();
			echo $OUTPUT->notification(get_string('msg_resource_copy_on_loan', 'local_elibrary'));
			echo $OUTPUT->footer();
			exit;
		}
		
		$resourceid = get_library_resourceid_by_accessno($accessno);
		$reserved_userids = check_library_resource_is_reserved($accessno);
		
		if(is_array($reserved_userids) && count($reserved_userids) > 0){
			if(!in_array($user_id, $reserved_userids) || get_user_reserve_rank_in_queue($resourceid, $user_id) > count_available_copy_by_resourceid($resourceid)){
				echo $OUTPUT->header();
				echo $OUTPUT->notification(get_string('msg_resource_reserved_by_other', 'local_elibrary'));
				echo $OUTPUT->footer();
				exit;
			}
		}
		
		$return_date = time() + (3600 * 24 * LIBRARY_ON_LOAN_DAYS);
		$return_date = date('Y-m-d', $return_date) . ' 00:00:00';
		$return_date = strtotime($return_date) - 1;
		
		$borrower = new stdClass();
		$borrower->contactperson = '';
		$borrower->contactnumber = '';
		$borrower->contactemail = '';
		$borrower->borrowerid = '';
		if(isset($_POST['borrower']) && !empty($_POST['borrower'])){
			$borrower = json_decode(base64_decode($_POST['borrower']));
		}
		if (is_object($borrower)) {
			add_loan_record($user_id, $resourceid, $loan_status->id, $return_date, $is_ngo, $borrower->contactperson, $borrower->contactnumber, $borrower->contactemail, $borrower->borrowerid);
		}		
	}
	
	redirect(new moodle_url($CFG->LIBRARY_BASEURL.'loan_resource_select_borrower.php'), get_string('msg_success_loan', 'local_elibrary'));
	exit;
}

if((!isset($_GET['userid']) || empty($_GET['userid'])) && (!isset($_GET['borrower']) || empty($_GET['borrower']))){
	redirect($CFG->wwwroot.$CFG->LIBRARY_BASEURL.'loan_resource_select_borrower.php');
	exit;
}
$userid = optional_param('userid', 0, PARAM_INT);
$borrower = optional_param('borrower', '', PARAM_RAW);

if(!empty($userid)){	//select borrower
	$is_ngo = check_is_ngo_user($userid);

	if(isset($is_ngo['status']) && $is_ngo['status'] == 'fail'){	//validate userid
		echo $OUTPUT->header();
		echo $OUTPUT->notification(get_string($is_ngo['msg'], 'local_elibrary'));
		echo $OUTPUT->footer();
		exit;
	}
	
	if ($is_ngo) {
		require_once('external_borrower_form.php');
		$ngouserform = new external_borrower_form(null, array('userid'=>$userid), 'GET');
		if ($data = $ngouserform->get_data()) {
			$borrower = new stdClass();
			$borrower->contactperson = $data->contact_person;
			$borrower->contactnumber = $data->contact_number;
			$borrower->contactemail = $data->contact_email;
			$borrower->borrowerid = '';
			$borrower = base64_encode(json_encode($borrower));
		} else {
			echo $OUTPUT->header();
			echo $OUTPUT->heading(get_string('loan_resource','local_elibrary'));
			echo $ngouserform->display();
			echo $OUTPUT->footer();
			exit();
		}
	}
	
	$user_number_of_loan = get_user_number_of_loan($userid);

	if($is_ngo){
		$loan_quota_available = LIBRARY_NGO_GL_LOAN_QUOTA - $user_number_of_loan;
	}else{
		$loan_quota_available = LIBRARY_SWD_GL_LOAN_QUOTA - $user_number_of_loan;
	}
}elseif(!empty($borrower)){	//non e-learning centre user
	if(!validate_borrower_encoded_string($borrower)){	//validate borrower
		redirect($CFG->wwwroot.$CFG->LIBRARY_BASEURL.'loan_resource_select_borrower.php');
		exit;
	}
	$userid = $USER->id;
	$borrowerobj = json_decode(base64_decode($borrower));
	
	if(!empty($borrowerobj->borrowerid)){
		$user_number_of_loan = get_user_number_of_loan_by_borrowerid($borrowerobj->borrowerid);
		$loan_quota_available = LIBRARY_LIBRARIAN_LOAN_QUOTA - $user_number_of_loan;
	} else {
		redirect($CFG->wwwroot.$CFG->LIBRARY_BASEURL.'loan_resource_select_borrower.php');
		exit;
	}
	//$loan_quota_available = LIBRARY_UNLIMITED_LOAN_QUOTA;
}


$string_for_js = array('msg_resource_copy_not_found', 'msg_resource_copy_on_loan', 'msg_over_loan_quota', 'msg_success_to_input_resource', 'msg_fail_to_input_resource', 'msg_resource_already_entered', 'msg_loan_list_empty', 'msg_resource_reserved_by_other');
foreach($string_for_js as $string){
	$PAGE->requires->string_for_js($string, 'local_elibrary');
}
$PAGE->requires->js_init_call('M.local_elibrary.init_loan_resource', array('loan_quota_available' => $loan_quota_available));
//$PAGE->requires->js(new moodle_url($CFG->LIBRARY_BASEURL.'module.js'));

$renderer = $PAGE->get_renderer('theme_innoverz','core_elibrary');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('loan_resource','local_elibrary'));
echo $renderer->start_layout();
echo $renderer->loan_resource_scan_resource_barcode();
echo $renderer->loan_resource_loan_list($userid, $borrower);

echo $renderer->complete_layout();
echo $OUTPUT->footer();