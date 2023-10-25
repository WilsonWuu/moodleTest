<?php

require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.$CFG->LIBRARY_BASEURL.'lib.php');

require_capability('local/elibrary:loanresource', context_system::instance());

$userid = required_param('userid', PARAM_INT);
$accessno = required_param('accessno', PARAM_RAW_TRIMMED);
$borrower = optional_param('borrower', '', PARAM_RAW);

$is_ngo = check_is_ngo_user($userid);
if(isset($is_ngo['status']) && $is_ngo['status'] == 'fail'){
	echo json_encode($is_ngo);
	exit;
}

if (!empty($borrower)) {
	$borrower = json_decode(base64_decode($borrower));
	$user_number_of_loan = get_user_number_of_loan_by_borrowerid($borrower->borrowerid);
	$loan_quota = LIBRARY_LIBRARIAN_LOAN_QUOTA;
} else {
	$user_number_of_loan = get_user_number_of_loan($userid);

	$loan_quota = LIBRARY_SWD_GL_LOAN_QUOTA;
	if(check_is_unlimited_loan_quota($userid)){
		$loan_quota = LIBRARY_UNLIMITED_LOAN_QUOTA;
	}elseif($is_ngo){
		$loan_quota = LIBRARY_NGO_GL_LOAN_QUOTA;
	}
}

if($user_number_of_loan >= $loan_quota){
	echo json_encode(array(
		'status' => 'fail',
		'msg' => 'msg_over_loan_quota'
	));
	exit;
}

$loan_status = get_copy_loan_status($accessno);

if($loan_status === false){
	echo json_encode(array(
		'status' => 'fail',
		'msg' => 'msg_resource_copy_not_found'
	));
	exit;
}

if($loan_status->isloan == 1){
	echo json_encode(array(
		'status' => 'fail',
		'msg' => 'msg_resource_copy_on_loan'
	));
	exit;
}

$reserved_userids = check_library_resource_is_reserved($accessno);
if(is_array($reserved_userids) && count($reserved_userids) > 0){
	$resourceid = get_library_resourceid_by_accessno($accessno);
	if(!in_array($userid, $reserved_userids) || get_user_reserve_rank_in_queue($resourceid, $userid) > count_available_copy_by_resourceid($resourceid)){
		echo json_encode(array(
			'status' => 'fail',
			'msg' => 'msg_resource_reserved_by_other'
		));
		exit;
	}
}

$copyid = $loan_status->id;
$copy_info = get_library_resource_copy_info_for_loan_list($copyid);
$copy_info->id = $copyid;


echo json_encode(array(
	'status' => 'success',
	'data' => json_encode($copy_info)
));
exit;