<?php

require_once("../../config.php");

require_login();
$PAGE->set_context(context_system::instance());

$PAGE->set_url(new moodle_url($CFG->EBOOK_BASEURL.'/login_fail.php?'));
$PAGE->set_pagelayout('login');
$PAGE->set_title(get_string('menuebook','local_ebook'));
$PAGE->set_heading(get_string('menuebook','local_ebook'));
notice(get_string('ebook_login_fail','local_ebook'), '/');

?>