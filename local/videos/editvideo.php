<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
* Adds or updates modules in a course using new formslib
*
* @package    moodlecore
* @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

require_once("../../config.php");
require_once($CFG->dirroot."/innoverz/course/lib.php");
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->libdir.'/completionlib.php');
//require_once($CFG->libdir.'/conditionlib.php');
require_once($CFG->libdir.'/plagiarismlib.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . $CFG->VIDEOS_BASEURL.'lib.php');
require_once($CFG->dirroot . $CFG->VIDEOS_BASEURL.'mod_resource_mod_form_innoverz.php');

$MAX_UPDATED_FILES = 3;

$add    = optional_param('add', '', PARAM_ALPHA);     // module name
$update = optional_param('update', 0, PARAM_INT);
$return = optional_param('return', 0, PARAM_BOOL);    //return to course/view.php if false or mod/modname/view.php if true
$type   = optional_param('type', '', PARAM_ALPHANUM); //TODO: hopefully will be removed in 2.0
$sectionreturn = optional_param('sr', null, PARAM_INT);
$catdefault = 0;

$url = new moodle_url($CFG->VIDEOS_BASEURL.'editvideo.php');
$url->param('sr', $sectionreturn);
if (!empty($return)) {
    $url->param('return', $return);
}

if (!empty($add)) {
    $section = optional_param('section', 1, PARAM_INT);
    $course  = optional_param('course', SITEID, PARAM_INT);

    $url->param('add', $add);
    $url->param('section', $section);
    $url->param('course', $course);
    $PAGE->set_url($url);

    $course = $DB->get_record('course', array('id'=>$course), '*', MUST_EXIST);
    require_login($course);

    // There is no page for this in the navigation. The closest we'll have is the course section.
    // If the course section isn't displayed on the navigation this will fall back to the course which
    // will be the closest match we have.
    navigation_node::override_active_url(course_get_url($course, $section));

	$context = context_system::instance();
	require_capability('local/videos:managevideoresources', $context);
	$cw = get_fast_modinfo($course)->get_section_info($section);
	$module = $DB->get_record('modules', array('name'=>$add), '*', MUST_EXIST);

    $cm = null;

    $data = new stdClass();
    $data->section          = $section;  // The section number itself - relative!!! (section column in course_sections)
    $data->visible          = $cw->visible;
    $data->course           = $course->id;
    $data->module           = $module->id;
    $data->modulename       = $module->name;
    $data->groupmode        = $course->groupmode;
    $data->groupingid       = $course->defaultgroupingid;
    $data->groupmembersonly = 0;
    $data->id               = '';
    $data->instance         = '';
    $data->coursemodule     = '';
    $data->add              = $add;
    $data->return           = 0; //must be false if this is an add, go back to course view on cancel
    $data->sr               = $sectionreturn;
    $data->video_ordering   = getMostRecentVideoOrdering();

    if (plugin_supports('mod', $data->modulename, FEATURE_MOD_INTRO, true)) {
        $draftid_editor = file_get_submitted_draft_itemid('introeditor');
        file_prepare_draft_area($draftid_editor, null, null, null, null, array('subdirs'=>true));
        $data->introeditor = array('text'=>'', 'format'=>FORMAT_HTML, 'itemid'=>$draftid_editor); // TODO: add better default
    }

    if (plugin_supports('mod', $data->modulename, FEATURE_ADVANCED_GRADING, false)
            and has_capability('moodle/grade:managegradingforms', $context)) {
        require_once($CFG->dirroot.'/grade/grading/lib.php');

        $data->_advancedgradingdata['methods'] = grading_manager::available_methods();
        $areas = grading_manager::available_areas('mod_'.$module->name);

        foreach ($areas as $areaname => $areatitle) {
            $data->_advancedgradingdata['areas'][$areaname] = array(
                'title'  => $areatitle,
                'method' => '',
            );
            $formfield = 'advancedgradingmethod_'.$areaname;
            $data->{$formfield} = '';
        }
    }

    if (!empty($type)) { //TODO: hopefully will be removed in 2.0
        $data->type = $type;
    }

    $sectionname = get_section_name($course, $cw);
    $fullmodulename = get_string('video', 'local_videos');

    if ($data->section && $course->format != 'site') {
        $heading = new stdClass();
        $heading->what = $fullmodulename;
        $heading->to   = $sectionname;
        $pageheading = get_string('addinganewto', 'local_videos', $heading);
    } else {
        $pageheading = get_string('addinganew', 'local_videos', $fullmodulename);
    }
    $navbaraddition = $pageheading;

} else if (!empty($update)) {

    $url->param('update', $update);
    $PAGE->set_url($url);

    // Select the "Edit settings" from navigation.
    navigation_node::override_active_url(new moodle_url($CFG->VIDEOS_BASEURL.'editvideo.php', array('update'=>$update, 'return'=>1)));

    // Check the course module exists.
    $cm = get_coursemodule_from_id('', $update, 0, false, MUST_EXIST);

    // Check the course exists.
    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

    // require_login
    require_login($course, false, $cm); // needed to setup proper $COURSE

    list($cm, $context, $module, $data, $cw) = videos_can_update_moduleinfo($cm);

    $data->coursemodule       = $cm->id;
    $data->section            = $cw->section;  // The section number itself - relative!!! (section column in course_sections)
    $data->visible            = $cm->visible; //??  $cw->visible ? $cm->visible : 0; // section hiding overrides
    $data->cmidnumber         = $cm->idnumber;          // The cm IDnumber
    $data->groupmode          = groups_get_activity_groupmode($cm); // locked later if forced
    $data->groupingid         = $cm->groupingid;
    $data->groupmembersonly   = $cm->groupmembersonly;
    $data->course             = $course->id;
    $data->module             = $module->id;
    $data->modulename         = $module->name;
    $data->instance           = $cm->instance;
    $data->return             = $return;
    $data->sr                 = $sectionreturn;
    $data->update             = $update;
    $data->completion         = $cm->completion;
    $data->completionview     = $cm->completionview;
    $data->completionexpected = $cm->completionexpected;
    $data->completionusegrade = is_null($cm->completiongradeitemnumber) ? 0 : 1;
    $data->showdescription    = $cm->showdescription;
	$data->quota_maxlearnersubmission = get_coursemodule_quota($cm->id, 'maxlearnersubmission');	  //max learner submission

    if (!empty($CFG->enableavailability)) {
        $data->availabilityconditionsjson = $cm->availability;
    }

    if (plugin_supports('mod', $data->modulename, FEATURE_MOD_INTRO, true)) {
        $draftid_editor = file_get_submitted_draft_itemid('introeditor');
        $currentintro = file_prepare_draft_area($draftid_editor, $context->id, 'mod_'.$data->modulename, 'intro', 0, array('subdirs'=>true), $data->intro);
        $data->introeditor = array('text'=>$currentintro, 'format'=>$data->introformat, 'itemid'=>$draftid_editor);
    }

    if (plugin_supports('mod', $data->modulename, FEATURE_ADVANCED_GRADING, false)
            and has_capability('moodle/grade:managegradingforms', $context)) {
        require_once($CFG->dirroot.'/grade/grading/lib.php');
        $gradingman = get_grading_manager($context, 'mod_'.$data->modulename);
        $data->_advancedgradingdata['methods'] = $gradingman->get_available_methods();
        $areas = $gradingman->get_available_areas();

        foreach ($areas as $areaname => $areatitle) {
            $gradingman->set_area($areaname);
            $method = $gradingman->get_active_method();
            $data->_advancedgradingdata['areas'][$areaname] = array(
                'title'  => $areatitle,
                'method' => $method,
            );
            $formfield = 'advancedgradingmethod_'.$areaname;
            $data->{$formfield} = $method;
        }
    }

    if ($items = grade_item::fetch_all(array('itemtype'=>'mod', 'itemmodule'=>$data->modulename,
                                             'iteminstance'=>$data->instance, 'courseid'=>$course->id))) {
        // add existing outcomes
        foreach ($items as $item) {
            if (!empty($item->outcomeid)) {
                $data->{'outcome_'.$item->outcomeid} = 1;
            }
        }

        // set category if present
        $gradecat = false;
        foreach ($items as $item) {
            if ($gradecat === false) {
                $gradecat = $item->categoryid;
                continue;
            }
            if ($gradecat != $item->categoryid) {
                //mixed categories
                $gradecat = false;
                break;
            }
        }
        if ($gradecat !== false) {
            // do not set if mixed categories present
            $data->gradecat = $gradecat;
        }
    }
	
	//added by Felix
	$catdefault = get_module_category($cm->id);

    $sectionname = get_section_name($course, $cw);
    $fullmodulename = get_string('video', 'local_videos');

    if ($data->section && $course->format != 'site') {
        $heading = new stdClass();
        $heading->what = $fullmodulename;
        $heading->in   = $sectionname;
        $pageheading = get_string('updatingain', 'local_videos', $heading);
    } else {
        $pageheading = get_string('updatinga', 'local_videos', $fullmodulename);
    }
    $navbaraddition = null;
	
} else {
    require_login();
    print_error('invalidaction');
}

$pagepath = 'mod-' . $module->name . '-';
if (!empty($type)) { //TODO: hopefully will be removed in 2.0
    $pagepath .= $type;
} else {
    $pagepath .= 'mod';
}
$PAGE->set_pagetype($pagepath);
$PAGE->set_pagelayout('admin');
$PAGE->add_body_class('local_editvideo'); //innoverz

$modmoodleform = "$CFG->dirroot/mod/$module->name/mod_form.php";
if (file_exists($modmoodleform)) {
    require_once($modmoodleform);
} else {
    print_error('noformdesc');
}

$mformclassname = 'mod_resource_mod_form_innoverz';
$btnset = null;
if ($course->id == SITEID) {
	$btnset = array(true, get_string('savechangesanddisplay'), get_string('savereturnvideo', 'local_videos'));
}
$mform = new $mformclassname($data, $cw->section, $cm, $course, 'editvideo.php', $btnset, video_get_file_types(), $MAX_UPDATED_FILES, get_sellector_all_categories(), $catdefault, true, true, true);
$mform->set_data($data);

if ($mform->is_cancelled()) {
    if ($return && !empty($cm->id)) {
        redirect("$CFG->wwwroot/local/videos/view.php?id=$cm->id");
    } else if($course->id != SITEID) {
		redirect(course_get_url($course, $cw->section, array('sr' => $sectionreturn)));
	} else {
        redirect(new Moodle_url($CFG->VIDEOS_BASEURL.'searchvideo.php'));
    }
} else if ($fromform = $mform->get_data()) {
	$fromform->showdescription = 1;
    if (!empty($fromform->update)) {
        list($cm, $fromform) = update_moduleinfo($cm, $fromform, $course, $mform);
        
        if (isset($fromform->hdvideofile)) {
            save_hdvideofile($fromform->coursemodule, $fromform->hdvideofile);
        }

		update_course_module_category($fromform);
		video_set_otherinfo($fromform);
		create_video_thumbnail($fromform->coursemodule);
    } else if (!empty($fromform->add)) {
        $fromform = add_moduleinfo($fromform, $course, $mform);
		
        if (isset($fromform->hdvideofile)) {
            save_hdvideofile($fromform->coursemodule, $fromform->hdvideofile);
        }
        
		update_course_module_category($fromform);
		video_set_otherinfo($fromform);
		set_isvideo($fromform);
		create_video_thumbnail($fromform->coursemodule);
    } else {
        print_error('invaliddata');
    }
    if (isset($fromform->submitbutton)) {
        if (empty($fromform->showgradingmanagement)) {
            $returnurl = new moodle_url("/local/videos/view.php", array('id' => $fromform->coursemodule));
            redirect($returnurl);
            //redirect("$CFG->wwwroot/local/videos/view.php?id=$fromform->coursemodule");
        } else {
            $returnurl = new moodle_url("/local/videos/view.php", array('id' => $fromform->coursemodule));
            redirect($fromform->gradingman->get_management_url($returnurl));
        }
    } else {
        if($course->id != SITEID) {
			redirect(course_get_url($course, $cw->section, array('sr' => $sectionreturn)));
		} else {
			redirect(new Moodle_url($CFG->VIDEOS_BASEURL.'searchvideo.php'));
		}
    }
    exit;

} else {

    $streditinga = get_string('editinga', 'moodle', $fullmodulename);
    $strmodulenameplural = get_string('modulenameplural', $module->name);

    if (!empty($cm->id)) {
        $context = context_module::instance($cm->id);
    } else {
        $context = context_course::instance($course->id);
    }

    $PAGE->set_heading($course->fullname);
    $PAGE->set_title($streditinga);
    $PAGE->set_cacheable(false);

    if (isset($navbaraddition)) {
        $PAGE->navbar->add($navbaraddition);
    }

    echo $OUTPUT->header();
	
	/*echo $OUTPUT->heading($pageheading);

    if (get_string_manager()->string_exists('modulename_help', $module->name)) {
        echo $OUTPUT->heading_with_help($pageheading, 'modulename', $module->name, 'icon');
    } else {*/
        echo $OUTPUT->heading_with_help($pageheading, '', $module->name, 'icon');
   // }

    $mform->display();

    echo $OUTPUT->footer();
}
