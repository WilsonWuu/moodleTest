<?php
require_once(dirname(__FILE__) . '/../../config.php');

require_login();

//$PAGE->set_url('/local/resourcekits/index.php');

$PAGE->set_url('local/elibrary:viewresource');
$PAGE->set_url(new moodle_url($CFG->LIBRARY_BASEURL.'index.php?' . $_SERVER['QUERY_STRING']));
#Top
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add(get_string('resource_kits','local_resourcekits'));
$PAGE->set_title('resource_kits','local_resourcekits');
$PAGE->set_heading(get_string('resource_kits','local_resourcekits'));
#---Below is full UI---
$renderer = $PAGE->get_renderer('theme_innoverz','core_elibrary');
#Title
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('resource_kits','local_resourcekits'));
echo $renderer->start_layout();
echo $renderer->search_resource_search_bar($_GET);
#echo '<iframe width="560" height="315" src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>';

#Bottom
echo $renderer->complete_layout();
echo $OUTPUT->footer();