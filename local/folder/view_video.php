<?php
require('../../config.php');
require_once($CFG->dirroot . $CFG->VIDEOS_BASEURL . 'lib.php');

if (empty($relativepath)) {
    $relativepath = get_file_argument();
}

if (!$relativepath) {
    print_error('invalidargorconf');
} else if ($relativepath[0] != '/') {
    print_error('pathdoesnotstartslash');
}

// extract relative path components
$args = explode('/', ltrim($relativepath, '/'));

if (count($args) < 3) { // always at least context, component and filearea
    print_error('invalidarguments');
}

$contextid = (int)array_shift($args);
$component = clean_param(array_shift($args), PARAM_COMPONENT);
$filearea  = clean_param(array_shift($args), PARAM_AREA);

list($context, $course, $cm) = get_context_info_array($contextid);

$file = getVideoFile($course, $cm, $context, $filearea, $args);

$mediamanager = core_media_manager::instance($PAGE);
$embedoptions = array(
    core_media_manager::OPTION_TRUSTED => true,
    core_media_manager::OPTION_BLOCK => true,
);

$PAGE->set_url("/mod/folder/view_video.php/{$relativepath}");
$PAGE->navbar->add(get_string('modulename','local_interrai'), new moodle_url('/local/interrai'));

$PAGE->set_title($file->get_filename());

$PAGE->set_heading($file->get_filename());

$output = $PAGE->get_renderer('local_folder');

echo $output->header();

echo $mediamanager->embed_url(
    new moodle_url($CFG->VIDEOS_BASEURL . "pluginfile.php/{$relativepath}"),
    $file->get_filename(),
    0,
    0,
    $embedoptions
);

echo $output->footer();
