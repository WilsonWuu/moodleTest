<?php

require_once('../../../config.php');

$id = required_param('id', PARAM_INT);
$duration = required_param('duration', PARAM_INT);

if ($id !== 0 && !is_int($id)) {
	return;
}

$check = $DB->get_record_sql("SELECT id FROM mdl_pageview_log WHERE id='$id' AND durationtime=0");
if(!$check){
	echo json_encode(array(
		'status' => 'error'
	));
}

$dataobject = new stdClass();
$dataobject->id = $id;
$dataobject->durationtime = $duration;
$DB->update_record('pageview_log', $dataobject);