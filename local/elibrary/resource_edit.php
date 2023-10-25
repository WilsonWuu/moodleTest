<?php

//  Display the elibrary page.

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.$CFG->LIBRARY_BASEURL.'resource_newedit_form.php');
require_once($CFG->dirroot.$CFG->LIBRARY_BASEURL.'lib.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();
$PAGE->set_context(context_system::instance());
require_capability('local/elibrary:resourceadministration', context_system::instance());

$PAGE->set_url(new moodle_url($CFG->LIBRARY_BASEURL.'resource_edit.php?' . $_SERVER['QUERY_STRING']));
$PAGE->set_pagelayout('standard');
//$PAGE->navbar->add(get_string('library','local_elibrary'));
//$PAGE->navbar->add(get_string('edit_resource','local_elibrary'));
$PAGE->set_title(get_string('library','local_elibrary'));
$PAGE->set_heading(get_string('library','local_elibrary'));

//admin_externalpage_setup('elibrarymaintainresource');

if(isset($_GET['action'])){
	switch($_GET['action']){
		case 'delete_resource_copy':
			$result = delete_library_resource_copy($_GET['copyid']);
			if($result['status'] == 'success'){
				redirect($CFG->wwwroot.$CFG->LIBRARY_BASEURL.'resource_edit.php?id=' . $_GET['id'] . '#copy');
				exit;
			}else{
				echo $OUTPUT->header();
				echo $OUTPUT->notification($result['msg']);
				echo $OUTPUT->footer();
				exit;
			}
			break;
	}
}

$_GET['id'] = isset($_POST['id']) ? $_POST['id'] : $_GET['id'];


$filemanageroptions = array('maxbytes' => $CFG->maximagefilesize,
							'subdirs' => 0,
							'maxfiles' => 1,
							'accepted_types' => array('.png', '.gif', '.bmp', '.jpg', '.jpeg'), 'maxfiles' => 1);
$custom_data = array(
	'resource_copy_list' => get_library_resource_copy_list($_GET['id']),
	'resource_info' => get_library_resource_info($_GET['id']),
	'filemanageroptions' => $filemanageroptions
);
$mform = new resource_newedit_form(null, $custom_data);
$data = $mform->get_data();
if($data){
	$result = update_library_resource($data);
	update_library_resource_coverimage($data, $mform, $filemanageroptions);
	if($result['status'] == 'success'){
		redirect($CFG->wwwroot.$CFG->LIBRARY_BASEURL.'resource_list.php');
		exit;
	}else{
		echo $OUTPUT->header();
		echo $OUTPUT->notification($result['msg']);
		echo $OUTPUT->footer();
		exit;
	}
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('edit_resource','local_elibrary'));
$mform->display();
echo $OUTPUT->footer();