<?php

// including config.php in which including /lib/setup.php for moodle initialization
require_once(__DIR__ . '/../../../config.php');

// including for displaying search result
require_once('renderer.php');

// including for fetching data from database
require_once('lib.php');

// including for displaying search filter form
require_once(__DIR__ . '/report_search_form.php');

// get route parameter to decide whether to export file
$isdownload = optional_param('download', 0, PARAM_INT);

// identifier name
$reportname = 'visitsprofilereport';

// context in moodle make the situation possible, a user can be a teacher of one course and a student in another course
// the system is the overall context
$context = context_system::instance();

// create page url
$pagesuburl = '/local/reports/visitsprofile/index.php';
$url = new moodle_url($pagesuburl);

// $PAGE the moodle page global for setting up the page

// set page url
$PAGE->set_url($url);

// set page layout to the base theme page layout 'report' which used for reports within moodle, special layout designed to handle horizontal scrolling in a nice way.
$PAGE->set_pagelayout('report');

// set the context for the page
$PAGE->set_context($context);

// checks that the current user is logged in and has the required privileges
require_login();

// tests has_capability() and displays an error if the user does not have that capability
require_capability('moodle/site:viewreports', $context);

// instantiate for displaying search result
$renderer = new reportvisitsprofile_renderer();

// instantiate for displaying search filter form
$form = new report_search_form(null, null, 'post', '', array('class' => 'searchform'));

// declare a variable for search result
$reportcontent = '';

// returns a localized string
$heading = get_string($reportname, 'local_reports');

// export report file
if ($isdownload && confirm_sesskey()) {
    $data = new stdClass;
    $data->reporttype = required_param('reporttype', PARAM_INT);
    $data->startdate = required_param('startdate', PARAM_INT);
    $data->enddate = required_param('enddate', PARAM_INT);
    $data->userrole = required_param('userrole', PARAM_INT);
    $records = reportvisitsprofile_get_records($data);
    $records['roleid'] = $data->userrole;
    // download report file
    $renderer->download_report($data, $records, $reportname);
}

// form submitted handler
if ($data = $form->get_data()) {
    $records = reportvisitsprofile_get_records($data);

    $records['roleid'] = $data->userrole;

    // display 2 download buttons
    $reportcontent .= $renderer->download_actions($pagesuburl, (array)$data);

    // display data result in html table
    $reportcontent .= $renderer->report_list($data, $records);
}

// ????
require_once($CFG->libdir . '/adminlib.php');
admin_externalpage_setup($reportname);

// including js
$PAGE->requires->js('/local/reports/index.js');

// set page title
$PAGE->set_title($heading);

// set page heading
$PAGE->set_heading($heading);

// $OUTPUT for displaying HTML

// display moodle header
echo $OUTPUT->header();

// display page heading
echo $OUTPUT->heading($heading);

// set custom css class name
echo $renderer->start_layout();

// display search filter form
$form->display();

// display search result
echo $reportcontent;

// set custom css class name
echo $renderer->end_layout();

// display moodle footer
echo $OUTPUT->footer();
