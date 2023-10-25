<?php

require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.$CFG->LIBRARY_BASEURL.'lib.php');

require_capability('local/elibrary:returnresource', context_system::instance());

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

$copyid = $loan_status->id;
$copy_info = get_library_resource_copy_info_for_return_list($copyid);
$copy_info->id = $copyid;


echo json_encode(array(
	'status' => 'success',
	'data' => json_encode($copy_info)
));
exit;