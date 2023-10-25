<?php

require_once(__DIR__.'/../../../config.php');
require_once('renderer.php');
require_once('lib.php');
require_once(__DIR__.'/../report_search_form.php');

$isdownload = optional_param('download', 0, PARAM_INT);

$reportname = 'coursepagehitsreport';

$context = context_system::instance();
$pagesuburl = '/local/reports/coursepagehits/index.php';
$url = new moodle_url($pagesuburl);
$PAGE->set_url($url);
$PAGE->set_pagelayout('report');
$PAGE->set_context($context);
// Check permissions
require_login();

require_capability('moodle/site:viewreports', $context);

$renderer = new reportcoursepagehits_renderer();
$form = new report_search_form(null, null, 'post', '', array('class'=>'searchform'));
$reportcontent = '';
$heading = get_string($reportname, 'local_reports');

if ($isdownload && confirm_sesskey()) {
	$data = new stdclass;
	$data->reporttype = required_param('reporttype', PARAM_INT);
	$data->startdate = required_param('startdate', PARAM_INT);
	$data->enddate = required_param('enddate', PARAM_INT);
	$records = reportcoursepagehits_get_records($data);
	$renderer->download_report($data, $records, $reportname);
}

if ($data = $form->get_data()) {
	$records = reportcoursepagehits_get_records($data);
	$reportcontent .= $renderer->download_actions($pagesuburl, (array)$data);
	$reportcontent .= $renderer->report_list($data, $records);
}

require_once($CFG->libdir.'/adminlib.php');
admin_externalpage_setup($reportname);

$PAGE->requires->js('/local/reports/index.js');

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