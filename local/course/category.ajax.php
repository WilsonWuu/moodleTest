<?php
define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../config.php');

if ($CFG->forcelogin) {
    require_login();
}

$PAGE->set_context(context_system::instance());
$courserenderer = $PAGE->get_renderer('local_course');

echo json_encode($courserenderer->coursecat_ajax());
