<?php

//  Display the elibrary page.

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.$CFG->LIBRARY_BASEURL.'subject_newedit_form.php');
require_once($CFG->dirroot.$CFG->LIBRARY_BASEURL.'lib.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();
$PAGE->set_context(context_system::instance());
require_capability('local/elibrary:resourceadministration', context_system::instance());

$PAGE->set_url(new moodle_url($CFG->LIBRARY_BASEURL.'subject_edit.php?' . $_SERVER['QUERY_STRING']));
$PAGE->set_pagelayout('standard');
//$PAGE->navbar->add(get_string('library','local_elibrary'));
//$PAGE->navbar->add(get_string('edit_subject','local_elibrary'));
$PAGE->set_title(get_string('library','local_elibrary'));
$PAGE->set_heading(get_string('library','local_elibrary'));

//admin_externalpage_setup('elibrarymaintainsubject');

$_GET['id'] = isset($_POST['id']) ? $_POST['id'] : $_GET['id'];

$custom_data = array(
	'subject_info' => get_subject_info($_GET['id'])
);

$mform = new subject_newedit_form(null, $custom_data);
$data = $mform->get_data();
if($data){
	$result = update_subject($data);
	if($result['status'] == 'success'){
		redirect($CFG->wwwroot.$CFG->LIBRARY_BASEURL.'subject_list.php');
		exit;
	}else{
		echo $OUTPUT->header();
		echo $OUTPUT->notification($result['msg']);
		echo $OUTPUT->footer();
		exit;
	}
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('edit_subject','local_elibrary'));
$mform->display();
echo $OUTPUT->footer();