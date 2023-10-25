<?php

require_once("../../config.php");
require_once($CFG->dirroot.$CFG->EBOOK_BASEURL."lib.php");
require_once($CFG->dirroot.$CFG->EBOOK_BASEURL."renderer.php");

require_login();

require_capability('local/ebook:view', context_system::instance());

$PAGE->set_context(context_system::instance());

$PAGE->set_url(new moodle_url($CFG->EBOOK_BASEURL.'view.php?' . $_SERVER['QUERY_STRING']));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('menuebook','local_ebook'));
$PAGE->set_heading(get_string('menuebook','local_ebook'));

$renderer = new core_ebook_renderer();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('menuebook','local_ebook'));
echo $renderer->start_layout();
echo $renderer->view_ebook();
echo $renderer->complete_layout();
echo $OUTPUT->footer();