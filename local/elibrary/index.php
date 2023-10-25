<?php

require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/course/lib.php');

require_login();
$PAGE->set_context(context_system::instance());
require_capability('local/elibrary:viewresource', context_system::instance());

if(isset($_GET['issubmit'])){
	redirect(new moodle_url($CFG->LIBRARY_BASEURL.'search_resource.php?' . $_SERVER['QUERY_STRING']));
}

$PAGE->set_url(new moodle_url($CFG->LIBRARY_BASEURL.'index.php?' . $_SERVER['QUERY_STRING']));
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add(get_string('library','local_elibrary'));
$PAGE->set_title(get_string('library','local_elibrary'));
$PAGE->set_heading(get_string('library','local_elibrary'));

$renderer = $PAGE->get_renderer('theme_innoverz','core_elibrary');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('library','local_elibrary'));
echo $renderer->start_layout();
echo $renderer->search_resource_search_bar($_GET);

$resource_list = get_popular_library_resources(10);
echo $renderer->popular_resources($resource_list);

echo $renderer->complete_layout();
echo $OUTPUT->footer();