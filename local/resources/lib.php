<?php

require_once($CFG->dirroot . '/innoverz/lib/outputcomponents.php');

$EDITRESOURCEPATH = 'resources/editresource.php';
function get_resource_types()
{
    $acceptedtypes = array(
        '.doc', '.docx', '.tex', '.txt', '.csv', '.pps', '.ppt', '.pptx', '.xls', '.xlsx', '.xlsm', '.pdf', '.bmp', '.gif', '.jpg', 'jpeg', '.png', '.tif', '.tiff', '.gtar', '.tgz', '.gz', '.gzip', '.tar', '.zip'
    );
    return $acceptedtypes;
}
function course_modchooser_module_types($modules)
{
    $return = '';
    foreach ($modules as $module) {
        if (!isset($module->types)) {
            $return .= course_modchooser_module($module);
        } else {
            $return .= course_modchooser_module($module, array('nonoption'));
            foreach ($module->types as $type) {
                $return .= course_modchooser_module($type, array('option', 'subtype'));
            }
        }
    }
    return $return;
}

/**
 * Return the HTML for the specified module adding any required classes
 *
 * @param object $module An object containing the title, and link. An
 * icon, and help text may optionally be specified. If the module
 * contains subtypes in the types option, then these will also be
 * displayed.
 * @param array $classes Additional classes to add to the encompassing
 * div element
 * @return string The composed HTML for the module
 */
function course_modchooser_module($module, $classes = array('option'))
{
    $output = '';
    $output .= html_writer::start_tag('div', array('class' => implode(' ', $classes)));
    $output .= html_writer::start_tag('label', array('for' => 'module_' . $module->name));
    if (!isset($module->types)) {
        $output .= html_writer::tag('input', '', array(
            'type' => 'radio',
            'name' => 'jumplink', 'id' => 'module_' . $module->name, 'value' => $module->link
        ));
    }

    $output .= html_writer::start_tag('span', array('class' => 'modicon'));
    if (isset($module->icon)) {
        // Add an icon if we have one
        $output .= $module->icon;
    }
    $output .= html_writer::end_tag('span');

    $output .= html_writer::tag('span', $module->title, array('class' => 'typename'));
    if (!isset($module->help)) {
        // Add help if found
        $module->help = get_string('nohelpforactivityorresource', 'moodle');
    }

    // Format the help text using markdown with the following options
    $options = new stdClass();
    $options->trusted = false;
    $options->noclean = false;
    $options->smiley = false;
    $options->filter = false;
    $options->para = true;
    $options->newlines = false;
    $options->overflowdiv = false;
    $module->help = format_text($module->help, FORMAT_MARKDOWN, $options);
    $output .= html_writer::tag('span', $module->help, array('class' => 'typesummary'));
    $output .= html_writer::end_tag('label');
    $output .= html_writer::end_tag('div');

    return $output;
}

function course_modchooser_title($title, $identifier = null)
{
    $module = new stdClass();
    $module->name = $title;
    $module->types = array();
    $module->title = get_string($title, $identifier);
    $module->help = '';
    return course_modchooser_module($module, array('moduletypetitle'));
}

function course_modchooser($modules, $course)
{
    global $PAGE, $CFG;
    static $isdisplayed = false;
    if ($isdisplayed) {
        return '';
    }
    $isdisplayed = true;

    // Add the header
    $header = html_writer::tag(
        'div',
        get_string('addresourceoractivity', 'moodle'),
        array('class' => 'hd choosertitle')
    );

    $formcontent = html_writer::start_tag('form', array(
        'action' => new moodle_url($CFG->RESOURCES_BASEURL . 'jumpto.php'),
        'id' => 'chooserform', 'method' => 'post'
    ));
    $formcontent .= html_writer::start_tag('div', array('id' => 'typeformdiv'));
    $formcontent .= html_writer::tag('input', '', array(
        'type' => 'hidden', 'id' => 'course',
        'name' => 'course', 'value' => $course->id
    ));
    $formcontent .= html_writer::tag('input', '', array(
        'type' => 'hidden', 'name' => 'sesskey',
        'value' => sesskey()
    ));
    $formcontent .= html_writer::end_tag('div');

    // Put everything into one tag 'options'
    $formcontent .= html_writer::start_tag('div', array('class' => 'options'));
    $formcontent .= html_writer::tag(
        'div',
        get_string('selectmoduletoviewhelp', 'moodle'),
        array('class' => 'instruction')
    );
    // Put all options into one tag 'alloptions' to allow us to handle scrolling
    $formcontent .= html_writer::start_tag('div', array('class' => 'alloptions'));

    // Activities
    $activities = array_filter($modules, function ($mod) {
        return ($mod->archetype !== MOD_ARCHETYPE_RESOURCE && $mod->archetype !== MOD_ARCHETYPE_SYSTEM);
    });
    if (count($activities)) {
        $formcontent .= course_modchooser_title('activities');
        $formcontent .= course_modchooser_module_types($activities);
    }

    // Resources
    $resources = array_filter($modules, function ($mod) {
        return ($mod->archetype === MOD_ARCHETYPE_RESOURCE);
    });
    if (count($resources)) {
        $formcontent .= course_modchooser_title('resources');
        $formcontent .= course_modchooser_module_types($resources);
    }

    $formcontent .= html_writer::end_tag('div'); // modoptions
    $formcontent .= html_writer::end_tag('div'); // types

    $formcontent .= html_writer::start_tag('div', array('class' => 'submitbuttons'));
    $formcontent .= html_writer::tag(
        'input',
        '',
        array('type' => 'submit', 'name' => 'submitbutton', 'class' => 'submitbutton', 'value' => get_string('add'))
    );
    $formcontent .= html_writer::tag(
        'input',
        '',
        array('type' => 'submit', 'name' => 'addcancel', 'class' => 'addcancel', 'value' => get_string('cancel'))
    );
    $formcontent .= html_writer::end_tag('div');
    $formcontent .= html_writer::end_tag('form');

    // Wrap the whole form in a div
    $formcontent = html_writer::tag('div', $formcontent, array('id' => 'chooseform'));

    // Put all of the content together
    $content = $formcontent;

    $content = html_writer::tag('div', $content, array('class' => 'choosercontainer'));
    return $header . html_writer::tag('div', $content, array('class' => 'chooserdialoguebody'));
}


/**
 * Retrieve all metadata for the requested modules
 * referred from lib\deprecatedlib.php
 * 
 * @param object $course The Course
 * @param array $modnames An array containing the list of modules and their
 * names
 * @param int $sectionreturn The section to return to
 * @return array A list of stdClass objects containing metadata about each
 * module
 */
function get_module_metadata_innoverz($course, $modnames, $sectionreturn = null)
{
    global $OUTPUT;

    // get_module_metadata will be called once per section on the page and courses may show
    // different modules to one another
    static $modlist = array();
    if (!isset($modlist[$course->id])) {
        $modlist[$course->id] = array();
    }

    $return = array();
    $urlbase = new moodle_url('/course/mod.php', array('id' => $course->id, 'sesskey' => sesskey()));
    if ($sectionreturn !== null) {
        $urlbase->param('sr', $sectionreturn);
    }
    foreach ($modnames as $modname => $modnamestr) {
        if (!course_allowed_module($course, $modname)) {
            continue;
        }
        if (isset($modlist[$course->id][$modname])) {
            // This module is already cached
            $return += $modlist[$course->id][$modname];
            continue;
        }
        $modlist[$course->id][$modname] = array();

        // Create an object for a default representation of this module type in the activity chooser. It will be used
        // if module does not implement callback get_shortcuts() and it will also be passed to the callback if it exists.
        $defaultmodule = new stdClass();
        $defaultmodule->title = $modnamestr;
        $defaultmodule->name = $modname;
        $defaultmodule->link = new moodle_url($urlbase, array('add' => $modname));
        $defaultmodule->icon = $OUTPUT->pix_icon('icon', '', $defaultmodule->name, array('class' => 'icon'));
        $sm = get_string_manager();
        if ($sm->string_exists('modulename_help', $modname)) {
            $defaultmodule->help = get_string('modulename_help', $modname);
            if ($sm->string_exists('modulename_link', $modname)) {  // Link to further info in Moodle docs.
                $link = get_string('modulename_link', $modname);
                $linktext = get_string('morehelp');
                $defaultmodule->help .= html_writer::tag(
                    'div',
                    $OUTPUT->doc_link($link, $linktext, true),
                    array('class' => 'helpdoclink')
                );
            }
        }
        $defaultmodule->archetype = plugin_supports('mod', $modname, FEATURE_MOD_ARCHETYPE, MOD_ARCHETYPE_OTHER);

        // Each module can implement callback modulename_get_shortcuts() in its lib.php and return the list
        // of elements to be added to activity chooser.
        $items = component_callback($modname, 'get_shortcuts', array($defaultmodule), null);
        if ($items !== null) {
            foreach ($items as $item) {
                // Add all items to the return array. All items must have different links, use them as a key in the return array.
                if (!isset($item->archetype)) {
                    $item->archetype = $defaultmodule->archetype;
                }
                if (!isset($item->icon)) {
                    $item->icon = $defaultmodule->icon;
                }
                // If plugin returned the only one item with the same link as default item - cache it as $modname,
                // otherwise append the link url to the module name.
                $item->name = (count($items) == 1 &&
                    $item->link->out() === $defaultmodule->link->out()) ? $modname : $modname . ':' . $item->link;

                // If the module provides the helptext property, append it to the help text to match the look and feel
                // of the default course modules.
                if (isset($item->help) && isset($item->helplink)) {
                    $linktext = get_string('morehelp');
                    $item->help .= html_writer::tag(
                        'div',
                        $OUTPUT->doc_link($item->helplink, $linktext, true),
                        array('class' => 'helpdoclink')
                    );
                }
                $modlist[$course->id][$modname][$item->name] = $item;
            }
            $return += $modlist[$course->id][$modname];
            // If get_shortcuts() callback is defined, the default module action is not added.
            // It is a responsibility of the callback to add it to the return value unless it is not needed.
            continue;
        }

        // The callback get_shortcuts() was not found, use the default item for the activity chooser.
        $modlist[$course->id][$modname][$modname] = $defaultmodule;
        $return[$modname] = $defaultmodule;
    }

    core_collator::asort_objects_by_property($return, 'title');
    return $return;
}


/**
 * Renders HTML for the menus to add activities and resources to the current course
 *
 * Note, if theme overwrites this function and it does not use modchooser,
 * see also {@link core_course_renderer::add_modchoosertoggle()}
 *
 * @param stdClass $course
 * @param int $section relative section number (field course_sections.section)
 * @param int $sectionreturn The section to link back to
 * @param array $displayoptions additional display options, for example blocks add
 *     option 'inblock' => true, suggesting to display controls vertically
 * @return string
 */
function resource_cm_control($course, $section, $sectionreturn = null, $displayoptions = array())
{
    global $CFG, $DB, $PAGE, $OUTPUT, $USER;

    $vertical = !empty($displayoptions['inblock']);

    // check to see if user can add menus and there are modules to add
    if (
        !has_capability('local/resources:managefileresources', context_system::instance())
        //|| !$PAGE->user_is_editing()
        || !($modnames = get_module_types_names()) || empty($modnames)
    ) {
        return '';
    }

    $modnames =  array(
        "resource" => "File",
        "folder" => "Folder"
    );

    // Retrieve all modules with associated metadata
    $modules = get_module_metadata_innoverz($course, $modnames, $sectionreturn);
    $urlparams = array('section' => $section);

    // We'll sort resources and activities into two lists
    $activities = array(MOD_CLASS_ACTIVITY => array(), MOD_CLASS_RESOURCE => array());

    /* foreach ($modules as $module) {
        if (isset($module->types)) {
            // This module has a subtype
            // NOTE: this is legacy stuff, module subtypes are very strongly discouraged!!
            $subtypes = array();
            foreach ($module->types as $subtype) {
                $link = $subtype->link->out(true, $urlparams);
                $subtypes[$link] = $subtype->title;
            }

            // Sort module subtypes into the list
            $activityclass = MOD_CLASS_ACTIVITY;
            if ($module->archetype == MOD_CLASS_RESOURCE) {
                $activityclass = MOD_CLASS_RESOURCE;
            }
            if (!empty($module->title)) {
                // This grouping has a name
                $activities[$activityclass][] = array($module->title => $subtypes);
            } else {
                // This grouping does not have a name
                $activities[$activityclass] = array_merge($activities[$activityclass], $subtypes);
            }
        } else {
            // This module has no subtypes
            $activityclass = MOD_CLASS_ACTIVITY;
            if ($module->archetype == MOD_ARCHETYPE_RESOURCE) {
                $activityclass = MOD_CLASS_RESOURCE;
            } else if ($module->archetype === MOD_ARCHETYPE_SYSTEM) {
                // System modules cannot be added by user, do not add to dropdown
                continue;
            }
            $link = $module->link->out(true, $urlparams);
            $activities[$activityclass][$link] = $module->title;
        }
    } */

    //customize url instead of using the above default ways
    $filelink = new moodle_url($CFG->RESOURCES_BASEURL . 'editresource.php', array('add' => 'resource', 'section' => 1));
    $folderlink = new moodle_url($CFG->RESOURCES_BASEURL . 'editresource.php', array('add' => 'folder', 'section' => 1));
    $activities[MOD_CLASS_RESOURCE] = array(
        $filelink->__toString() => 'File',
        $folderlink->__toString() => 'Folder'
    );


    $straddactivity = get_string('addactivity');
    $straddresource = get_string('addresource');
    $sectionname = get_section_name($course, $section);
    $strresourcelabel = get_string('addresourcetosection', null, $sectionname);
    $stractivitylabel = get_string('addactivitytosection', null, $sectionname);

    $output = html_writer::start_tag('div', array('class' => 'section_add_menus', 'id' => 'add_menus-section-' . $section));

    if (!$vertical) {
        $output .= html_writer::start_tag('div', array('class' => 'horizontal'));
    }

    if (!empty($activities[MOD_CLASS_RESOURCE])) {
        $select = new url_select($activities[MOD_CLASS_RESOURCE], '', array('' => $straddresource), "ressection$section");
        $select->set_help_icon('resources');
        $select->set_label($strresourcelabel, array('class' => 'accesshide'));
        $output .= $OUTPUT->render($select);
    }

    if (!empty($activities[MOD_CLASS_ACTIVITY])) {
        $select = new url_select($activities[MOD_CLASS_ACTIVITY], '', array('' => $straddactivity), "section$section");
        $select->set_help_icon('activities');
        $select->set_label($stractivitylabel, array('class' => 'accesshide'));
        $output .= $OUTPUT->render($select);
    }

    if (!$vertical) {
        $output .= html_writer::end_tag('div');
    }

    $output .= html_writer::end_tag('div');

    /* if (has_capability('local/resources:managefileresources', context_system::instance())) {
        $url = new moodle_url($CFG->RESOURCES_BASEURL.'editresource.php', array('add' => 'resource', 'section' => 1));
        $output .= html_writer_innoverz::new_record_widget($url, get_string('addfile', 'local_resources'));

        $url = new moodle_url($CFG->RESOURCES_BASEURL.'editresource.php', array('add' => 'folder', 'section' => 1));
        $output .= html_writer_innoverz::new_record_widget($url, get_string('addfolder', 'local_resources'));
    } */

    //if (course_ajax_enabled($course) && $course->id == $PAGE->course->id) {
    // modchooser can be added only for the current course set on the page!
    /* $straddeither = get_string('addresource', 'local_resources');
    // The module chooser link
    $modchooser = html_writer::start_tag('div', array('class' => 'mdl-right'));
    $modchooser .= html_writer::start_tag('div', array('class' => 'section-modchooser'));
    $icon = html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('add_btn', 'theme'), 'alt' => "", 'title' => "", 'class' => 'addimage'));
    $span = html_writer::tag('span', $straddeither, array('class' => 'section-modchooser-text'));
    $modchooser .= html_writer::tag('span', $icon . $span, array('class' => 'section-modchooser-link'));
    $modchooser .= html_writer::end_tag('div');
    $modchooser .= html_writer::end_tag('div'); */
    /* 
    // Wrap the normal output in a noscript div
    $usemodchooser = get_user_preferences('usemodchooser', $CFG->modchooserdefault);
    if ($usemodchooser) {
        $output = html_writer::tag('div', $output, array('class' => 'hiddenifjs addresourcedropdown'));
        $modchooser = html_writer::tag('div', $modchooser, array('class' => 'visibleifjs addresourcemodchooser'));
    } else {
        // If the module chooser is disabled, we need to ensure that the dropdowns are shown even if javascript is disabled
        $output = html_writer::tag('div', $output, array('class' => 'show addresourcedropdown'));
        $modchooser = html_writer::tag('div', $modchooser, array('class' => 'hide addresourcemodchooser'));
    }
    $courserenderer = $PAGE->get_renderer('core', 'course');
    $output = course_modchooser($modules, $course) . $modchooser . $output; */
    //}

    return $output;
}

function get_resource_list($course, $section, $catid = 0)
{

    global $PAGE, $DB;
    $modinfo = get_fast_modinfo($course);
    if (is_object($section)) {
        $section = $modinfo->get_section_info($section->section);
    } else {
        $section = $modinfo->get_section_info($section);
    }
    $completioninfo = new completion_info($course);

    // check if we are currently in the process of moving a module with JavaScript disabled
    $ismoving = $PAGE->user_is_editing() && ismoving($course->id);
    if ($ismoving) {
        $movingpix = new pix_icon('movehere', get_string('movehere'), 'moodle', array('class' => 'movetarget'));
        $strmovefull = strip_tags(get_string("movefull", "", "'$USER->activitycopyname'"));
    }

    // Get the list of modules visible to user (excluding the module being moved if there is one)
    $list = array();
    $courserenderer = $PAGE->get_renderer('core', 'course');

    $modnames = array();

    if (!empty($modinfo->sections[$section->section])) {
        foreach ($modinfo->sections[$section->section] as $modnumber) {
            $mod = $modinfo->cms[$modnumber];

            if ($mod->modname == "resource" || $mod->modname == "folder") {

                if ($catid && !$DB->record_exists("course_modules", array("id" => $mod->id, "category" => $catid))) {
                    continue;
                }

                //skip local video or local interrai items
                if (
                    ($mod->modname == "resource" && $DB->record_exists("resource", array("id" => $mod->instance, "isvideo" => 1))) ||
                    ($mod->modname == "resource" && $DB->record_exists("resource", array("id" => $mod->instance, "isinterrai" => 1))) ||
                    ($mod->modname == "folder" && $DB->record_exists("folder", array("id" => $mod->instance, "isinterrai" => 1)))
                ) {
                    continue;
                }

                $data = new stdclass();
                $data->id = $mod->id;
                $data->name = $mod->name;
                $data->detail = $courserenderer->course_section_cm_text($mod);
                $data->icon = $mod->get_icon_url();
                $data->modname = $mod->modname;
                $list[] = $data;
                $modnames[] = $mod->name;
            }
        }
    }
    array_multisort($modnames, SORT_ASC, $list);
    return $list;
}

function resources_resource_display_embed($resource, $cm, $course, $file)
{
    global $CFG, $PAGE, $OUTPUT, $EDITRESOURCEPATH;

    $clicktoopen = resource_get_clicktoopen($file, $resource->revision);

    $context = context_module::instance($cm->id);
    $path = '/' . $context->id . '/mod_resource/content/' . $resource->revision . $file->get_filepath() . $file->get_filename();
    $fullurl = file_encode_url($CFG->wwwroot . '/pluginfile.php', $path, false);
    $moodleurl = new moodle_url('/pluginfile.php' . $path);

    $mimetype = $file->get_mimetype();
    $title    = $resource->name;

    $extension = resourcelib_get_extension($file->get_filename());

    $mediarenderer = $PAGE->get_renderer('core', 'media');
    $embedoptions = array(
        core_media::OPTION_TRUSTED => true,
        core_media::OPTION_BLOCK => true,
    );

    if (file_mimetype_in_typegroup($mimetype, 'web_image')) {  // It's an image
        $code = resourcelib_embed_image($fullurl, $title);
    } else if ($mimetype === 'application/pdf') {
        // PDF document
        $code = resourcelib_embed_pdf($fullurl, $title, $clicktoopen);
    } else if ($mediarenderer->can_embed_url($moodleurl, $embedoptions)) {
        // Media (audio/video) file.
        $code = $mediarenderer->embed_url($moodleurl, $title, 0, 0, $embedoptions);
    } else {
        // anything else - just try object tag enlarged as much as possible
        $code = resourcelib_embed_general($fullurl, $title, $clicktoopen, $mimetype);
    }

    videos_resource_print_header($resource, $cm, $course, $EDITRESOURCEPATH);
    resource_print_heading($resource, $cm, $course);

    echo $code;

    resource_print_intro($resource, $cm, $course);

    echo $OUTPUT->footer();
    die;
}

function resources_resource_display_frame($resource, $cm, $course, $file)
{
    global $PAGE, $OUTPUT, $CFG, $EDITRESOURCEPATH;

    $frame = optional_param('frameset', 'main', PARAM_ALPHA);

    if ($frame === 'top') {
        $PAGE->set_pagelayout('frametop');
        videos_resource_print_header($resource, $cm, $course, $EDITRESOURCEPATH);
        resource_print_heading($resource, $cm, $course);
        resource_print_intro($resource, $cm, $course);
        echo $OUTPUT->footer();
        die;
    } else {
        $config = get_config('resource');
        $context = context_module::instance($cm->id);
        $path = '/' . $context->id . '/mod_resource/content/' . $resource->revision . $file->get_filepath() . $file->get_filename();
        $fileurl = file_encode_url($CFG->wwwroot . '/pluginfile.php', $path, false);
        $navurl = "$CFG->wwwroot/mod/resource/view.php?id=$cm->id&amp;frameset=top";
        $title = strip_tags(format_string($course->shortname . ': ' . $resource->name));
        $framesize = $config->framesize;
        $contentframetitle = format_string($resource->name);
        $modulename = s(get_string('modulename', 'resource'));
        $dir = get_string('thisdirection', 'langconfig');

        $file = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html dir="$dir">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>$title</title>
  </head>
  <frameset rows="$framesize,*">
    <frame src="$navurl" title="$modulename" />
    <frame src="$fileurl" title="$contentframetitle" />
  </frameset>
</html>
EOF;

        @header('Content-Type: text/html; charset=utf-8');
        echo $file;
        die;
    }
}

function resources_resource_print_workaround($resource, $cm, $course, $file)
{
    global $CFG, $OUTPUT, $EDITRESOURCEPATH;

    videos_resource_print_header($resource, $cm, $course, $EDITRESOURCEPATH);
    resource_print_heading($resource, $cm, $course, true);
    resource_print_intro($resource, $cm, $course, true);

    $resource->mainfile = $file->get_filename();
    echo '<div class="resourceworkaround">';
    switch (resource_get_final_display_type($resource)) {
        case RESOURCELIB_DISPLAY_POPUP:
            $path = '/' . $file->get_contextid() . '/mod_resource/content/' . $resource->revision . $file->get_filepath() . $file->get_filename();
            $fullurl = file_encode_url($CFG->wwwroot . '/pluginfile.php', $path, false);
            $options = empty($resource->displayoptions) ? array() : unserialize($resource->displayoptions);
            $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
            $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
            $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
            $extra = "onclick=\"window.open('$fullurl', '', '$wh'); return false;\"";
            echo resource_get_clicktoopen($file, $resource->revision, $extra);
            break;

        case RESOURCELIB_DISPLAY_NEW:
            $extra = 'onclick="this.target=\'_blank\'"';
            echo resource_get_clicktoopen($file, $resource->revision, $extra);
            break;

        case RESOURCELIB_DISPLAY_DOWNLOAD:
            echo resource_get_clicktodownload($file, $resource->revision);
            break;

        case RESOURCELIB_DISPLAY_OPEN:
        default:
            echo resource_get_clicktoopen($file, $resource->revision);
            break;
    }
    echo '</div>';

    echo $OUTPUT->footer();
    die;
}
