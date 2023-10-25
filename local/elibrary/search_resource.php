<?php

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.$CFG->LIBRARY_BASEURL.'lib.php');
require_once($CFG->dirroot.'/course/lib.php');

$page = optional_param('page', 0, PARAM_INT);
$_SERVER['QUERY_STRING'] = clean_param( $_SERVER['QUERY_STRING'], PARAM_TEXT);

require_login();
$PAGE->set_context(context_system::instance());
require_capability('local/elibrary:viewresource', context_system::instance());

$PAGE->set_url(new moodle_url($CFG->LIBRARY_BASEURL.'search_resource.php?' . $_SERVER['QUERY_STRING']));
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add(get_string('library','local_elibrary'), new moodle_url($CFG->LIBRARY_BASEURL.''));
$PAGE->navbar->add(get_string('search_resource','local_elibrary'));
$PAGE->set_title(get_string('search_resource','local_elibrary') . ' - ' . get_string('library','local_elibrary'));
$PAGE->set_heading(get_string('library','local_elibrary'));

$renderer = $PAGE->get_renderer('theme_innoverz','core_elibrary');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('library','local_elibrary'));
echo $renderer->start_layout();
echo $renderer->search_resource_search_bar($_GET);

$resource_list = get_library_resource_list($_GET);
$resource_count = count($resource_list);
$perpage = 10;
$url = 'search_resource.php?' . $_SERVER['QUERY_STRING'];

echo $renderer->view_resource_list($resource_list, $resource_count, $page, $perpage, $url);

echo $renderer->complete_layout();
echo $OUTPUT->footer();