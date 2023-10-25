<?php
require_once(dirname(__FILE__).'/../../../config.php');
require_once('renderer.php');
require_once('lib.php');
require_once($CFG->dirroot.'/local/reports/report_search_form.php');

$isdownload = optional_param('download', 0, PARAM_INT);

$context = context_system::instance();
$pagesuburl = $CFG->LIBRARY_BASEURL.'userborrowstat/index.php';
$url = new moodle_url($pagesuburl);
$PAGE->set_url($url);
$PAGE->set_pagelayout('report');
$PAGE->set_context($context);
// Check permissions
require_login();
require_capability('local/elibrary:resourceadministration', $context);

$renderer = new reportuserborrow_renderer();
$form = new report_search_form(null, null, 'post', '', array('class'=>'searchform'));
$reportcontent = '';
$heading = get_string('userborrowstatisticsreport', 'local_elibrary');

if ($isdownload && confirm_sesskey()) {
	$data = new stdclass;
	$data->reporttype = required_param('reporttype', PARAM_INT);
	$data->startdate = required_param('startdate', PARAM_INT);
	$data->enddate = required_param('enddate', PARAM_INT);
	$records = reportuserborrow_get_resources($data);
	$renderer->download_report($data, $records);
}

if ($data = $form->get_data()) {
	$records = reportuserborrow_get_resources($data);
	$reportcontent .= $renderer->download_actions($pagesuburl, (array)$data);
	$reportcontent .= $renderer->report_list($data, $records);
}

require_once($CFG->libdir.'/adminlib.php');
//admin_externalpage_setup('elibraryuserborrowstatisticsreport');

$PAGE->requires->js($CFG->LIBRARY_BASEURL.'userborrowstat/index.js');

$PAGE->set_title($heading);
$PAGE->set_heading($heading);
	
echo $OUTPUT->header();	
echo $OUTPUT->heading($heading);
echo $renderer->start_layout();
$form->display();
echo $reportcontent;
echo $renderer->end_layout();
echo $OUTPUT->footer();

?>