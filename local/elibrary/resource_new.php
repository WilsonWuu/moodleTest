<?php

//  Display the elibrary page.

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.$CFG->LIBRARY_BASEURL.'resource_newedit_form.php');
require_once($CFG->dirroot.$CFG->LIBRARY_BASEURL.'lib.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();
$PAGE->set_context(context_system::instance());
require_capability('local/elibrary:resourceadministration', context_system::instance());

$PAGE->set_url(new moodle_url($CFG->LIBRARY_BASEURL.'resource_new.php?' . $_SERVER['QUERY_STRING']));
$PAGE->set_pagelayout('standard');
//$PAGE->navbar->add(get_string('library','local_elibrary'));
//$PAGE->navbar->add(get_string('new_resource','local_elibrary'));
$PAGE->set_title(get_string('library','local_elibrary'));
$PAGE->set_heading(get_string('library','local_elibrary'));

//admin_externalpage_setup('elibrarymaintainresource');

$filemanageroptions = array('maxbytes' => $CFG->maxbytes,
							'subdirs' => 0,
							'maxfiles' => 1,
							'accepted_types' => array('.png', '.gif', '.bmp', '.jpg', '.jpeg'), 'maxfiles' => 1);
$mform = new resource_newedit_form(null, array('filemanageroptions' => $filemanageroptions));
$data = $mform->get_data();
if($data){
	$result = add_library_resource($data);
	$data->id = $result['id'];
	update_library_resource_coverimage($data, $mform, $filemanageroptions);
	if($result['status'] == 'success'){
		redirect($CFG->wwwroot.$CFG->LIBRARY_BASEURL.'resource_copy_new.php?resourceid=' . $result['id'] . '#copy');
		exit;
	}else{
		echo $OUTPUT->header();
		echo $OUTPUT->notification($result['msg']);
		echo $OUTPUT->footer();
		exit;
	}
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('new_resource','local_elibrary'));
$mform->display();
echo $OUTPUT->footer();