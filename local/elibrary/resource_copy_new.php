<?php

//  Display the elibrary page.

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.$CFG->LIBRARY_BASEURL.'resource_copy_newedit_form.php');
require_once($CFG->dirroot.$CFG->LIBRARY_BASEURL.'lib.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();
$PAGE->set_context(context_system::instance());
require_capability('local/elibrary:resourceadministration', context_system::instance());

$PAGE->set_url(new moodle_url($CFG->LIBRARY_BASEURL.'resource_copy_new.php?' . $_SERVER['QUERY_STRING']));
$PAGE->set_pagelayout('standard');
//$PAGE->navbar->add(get_string('library','local_elibrary'));
//$PAGE->navbar->add(get_string('new_resource_copy','local_elibrary'));
$PAGE->set_title(get_string('library','local_elibrary'));
$PAGE->set_heading(get_string('library','local_elibrary'));

//admin_externalpage_setup('elibrarymaintainresource');

$resourceid = required_param('resourceid', PARAM_INT);

// $next_accessno = get_copy_last_accessno();
// $next_accessno = $next_accessno->last_accessno + 1;
$custom_data = array(
	'resource_copy_info' => (object)array(
		//'accessno' => $next_accessno,
		'resourceid' => $resourceid
	)
);
$mform = new resource_copy_newedit_form(null, $custom_data);
$data = $mform->get_data();
if($data){
	$result = add_library_resource_copy($data);
	if($result['status'] == 'success'){
		redirect($CFG->wwwroot.$CFG->LIBRARY_BASEURL.'resource_edit.php?id=' . $data->resourceid . '#copy');
		exit;
	}else{
		echo $OUTPUT->header();
		echo $OUTPUT->notification($result['msg']);
		echo $OUTPUT->footer();
		exit;
	}
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('new_resource_copy','local_elibrary'));
$mform->display();
echo $OUTPUT->footer();