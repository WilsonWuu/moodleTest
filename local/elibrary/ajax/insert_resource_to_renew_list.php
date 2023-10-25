<?php

require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.$CFG->LIBRARY_BASEURL.'lib.php');

require_capability('local/elibrary:renewresource', context_system::instance());

$accessno = required_param('accessno', PARAM_RAW_TRIMMED);


$loan_status = get_copy_loan_status($accessno);

if($loan_status === false){
	echo json_encode(array(
		'status' => 'fail',
		'msg' => 'msg_resource_copy_not_found'
	));
	exit;
}

if($loan_status->isloan != 1){
	echo json_encode(array(
		'status' => 'fail',
		'msg' => 'msg_resource_copy_not_on_loan'
	));
	exit;
}

$is_loan_overdue = check_is_loan_overdue($loan_status->id);
if($is_loan_overdue){
	echo json_encode(array(
		'status' => 'fail',
		'msg' => 'msg_loan_overdue'
	));
	exit;
}

$loanid = get_loanid_by_copyid($loan_status->id);
$renew_count = get_renew_count($loanid);
if($renew_count >= LIBRARY_MAX_RENEW_TIMES){
	echo json_encode(array(
		'status' => 'fail',
		'msg' => 'msg_over_max_renew_times'
	));
	exit;
}

$resourceid = get_library_resourceid_by_copyid($loan_status->id);
if(check_is_library_resource_reserving($resourceid)){
	echo json_encode(array(
		'status' => 'fail',
		'msg' => 'msg_resource_is_reserving'
	));
	exit;
}

$copy_info = get_library_resource_copy_info_for_renew_list($loanid);

echo json_encode(array(
	'status' => 'success',
	'data' => json_encode($copy_info)
));
exit;