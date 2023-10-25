<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once('lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . $CFG->VIDEOS_BASEURL . 'lib.php');
require_once('searchresource_form.php');
require_once('renderer.php');

$sectionreturn = optional_param('sr', null, PARAM_INT);
$delete        = optional_param('delete', 0, PARAM_INT);
$confirm       = optional_param('confirm', 0, PARAM_BOOL);
$category = optional_param('category', 0, PARAM_INT); // Category ID
$page = optional_param('page', 0, PARAM_INT); // page number
$perpage = 10;

$_SERVER['QUERY_STRING'] = clean_param($_SERVER['QUERY_STRING'], PARAM_TEXT);

require_course_login(get_site());
$context = context_system::instance();
//require_capability('local/resources:viewfileresources', $context);
$PAGE->set_url(new moodle_url($CFG->INTERRAI_BASEURL . '?' . $_SERVER['QUERY_STRING']));

if (!empty($delete)) {
    $cm     = get_coursemodule_from_id('', $delete, 0, true, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

    require_login($course, false, $cm);
    $modcontext = context_module::instance($cm->id);

    $userid = get_resource_owner_by_cm($cm->id);

    if (!$userid == $USER->id) {
        require_capability('local/interrai:managefileresources', $modcontext);
    }

    $return = new Moodle_url($CFG->INTERRAI_BASEURL . 'index.php');

    if (!$confirm or !confirm_sesskey()) {
        $fullmodulename = get_string('modulename', $cm->modname);

        $optionsyes = array('confirm' => 1, 'delete' => $cm->id, 'sesskey' => sesskey(), 'sr' => $sectionreturn);

        $strdeletecheck = get_string('deletecheck', '', $fullmodulename);
        $strdeletecheckfull = get_string('deletecheckfull', '', "$fullmodulename '$cm->name'");

        $PAGE->set_pagetype('mod-' . $cm->modname . '-delete');
        $PAGE->set_title($strdeletecheck);
        $PAGE->set_heading($course->fullname);
        $PAGE->navbar->add($strdeletecheck);

        echo $OUTPUT->header();
        echo $OUTPUT->box_start('noticebox');
        $formcontinue = new single_button(new moodle_url("$CFG->wwwroot/local/interrai/", $optionsyes), get_string('yes'));
        $formcancel = new single_button($return, get_string('no'), 'get');
        echo $OUTPUT->confirm($strdeletecheckfull, $formcontinue, $formcancel);
        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();

        exit;
    }

    // Delete the module.
    course_delete_module($cm->id);

    redirect($return);
}

$PAGE->requires->js(new moodle_url($CFG->INTERRAI_BASEURL . 'module.js'));

$PAGE->set_pagelayout('standard');

$PAGE->navbar->add(get_string('modulename', 'local_interrai'));
$PAGE->set_title(get_string('modulename', 'local_interrai'));
$PAGE->set_heading(get_string('modulename', 'local_interrai'));

//$renderer = new core_resources_renderer();
$renderer = $PAGE->get_renderer('local_interrai');


echo $OUTPUT->header();

$editing = $PAGE->user_is_editing();

/// Print Section or custom info
$siteformatoptions = course_get_format($SITE)->get_format_options();
$modinfo = get_fast_modinfo($SITE);
$modnames = get_module_types_names();
$modnamesplural = get_module_types_names(true);
$modnamesused = $modinfo->get_used_module_names();
$mods = $modinfo->get_cms();

if ($editing) {
    // make sure section with number 1 exists
    course_create_sections_if_missing($SITE, 1);
    // re-request modinfo in case section was created
    $modinfo = get_fast_modinfo($SITE);
}
$section = $modinfo->get_section_info(1);

if (($section && (!empty($modinfo->sections[1]) or !empty($section->summary))) or $editing) {
    echo $OUTPUT->box_start('generalbox sitetopic');

    /// If currently moving a file then show the current clipboard
    if (ismoving($SITE->id)) {
        $stractivityclipboard = strip_tags(get_string('activityclipboard', '', $USER->activitycopyname));
        echo '<p><font size="2">';
        echo "$stractivityclipboard&nbsp;&nbsp;(<a href=\"course/mod.php?cancelcopy=true&amp;sesskey=" . sesskey() . "\">" . get_string('cancel') . '</a>)';
        echo '</font></p>';
    }

    $context = context_course::instance(SITEID);
    $summarytext = file_rewrite_pluginfile_urls($section->summary, 'pluginfile.php', $context->id, 'course', 'section', $section->id);
    $summaryformatoptions = new stdClass();
    $summaryformatoptions->noclean = true;
    $summaryformatoptions->overflowdiv = true;

    echo format_text($summarytext, $section->summaryformat, $summaryformatoptions);

    /*if ($editing && has_capability('moodle/course:update', $context)) {
        $streditsummary = get_string('editsummary');
        echo "<a title=\"$streditsummary\" ".
                " href=\"course/editsection.php?id=$section->id\"><img src=\"" . $OUTPUT->pix_url('t/edit') . "\" ".
                " class=\"iconsmall\" alt=\"$streditsummary\" /></a><br /><br />";
    }*/

    echo $renderer->start_layout();

    echo resource_cm_control_interrai($SITE, $section->section);

    //echo $renderer->search_resource_search_bar($_GET);

    $rs_list = get_interrai_list($SITE, $section, $category);
    $rs_count = count($rs_list);

    $url = 'index.php?' . $_SERVER['QUERY_STRING'];

    echo $renderer->view_resource_list($rs_list, $rs_count, $page, $perpage, $url);

    echo $renderer->complete_layout();

    echo $OUTPUT->box_end();
}

echo $OUTPUT->footer();

//$PAGE->requires->js_function_init_call('M.course.init_chooser');
