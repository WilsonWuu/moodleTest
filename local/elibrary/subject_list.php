<?php

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.$CFG->LIBRARY_BASEURL.'lib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir.'/adminlib.php');

$page = optional_param('page', 0, PARAM_INT);
$_SERVER['QUERY_STRING'] = clean_param( $_SERVER['QUERY_STRING'], PARAM_TEXT);

require_login();
$PAGE->set_context(context_system::instance());
require_capability('local/elibrary:resourceadministration', context_system::instance());

$PAGE->set_url(new moodle_url($CFG->LIBRARY_BASEURL.'subject_list.php?' . $_SERVER['QUERY_STRING']));
$PAGE->set_pagelayout('standard');
//$PAGE->navbar->add(get_string('library','local_elibrary'), new moodle_url($CFG->LIBRARY_BASEURL.''));
//$PAGE->navbar->add(get_string('list_subject','local_elibrary'));
$PAGE->set_title(get_string('library','local_elibrary'));
$PAGE->set_heading(get_string('library','local_elibrary'));

//admin_externalpage_setup('elibrarymaintainsubject');

if(isset($_GET['action'])){
	switch($_GET['action']){
		case 'delete_subject':
			$result = delete_subject($_GET['subjectid']);
			if($result['status'] == 'success'){
				redirect($CFG->wwwroot.$CFG->LIBRARY_BASEURL.'subject_list.php?' . base64_decode($_GET['query_string']));
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

$renderer = $PAGE->get_renderer('theme_innoverz','core_elibrary');

echo $OUTPUT->header();
//echo $OUTPUT->heading(get_string('library','local_elibrary'));
echo $renderer->start_layout();

$subject_list = get_subject_list($_GET);
$subject_count = count($subject_list);
$perpage = 10;
$url = 'subject_list.php?' . $_SERVER['QUERY_STRING'];

echo $renderer->view_subject_list($subject_list, $subject_count, $page, $perpage, $url);

echo $renderer->complete_layout();
echo $OUTPUT->footer();