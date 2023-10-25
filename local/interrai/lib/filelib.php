<?php

require_once('../../lib/filelib.php');

/**
 * This function delegates file serving to individual plugins
 *
 * @param string $relativepath
 * @param bool $forcedownload
 * @param null|string $preview the preview mode, defaults to serving the original file
 * @param boolean $offline If offline is requested - don't serve a redirect to an external file, return a file suitable for viewing
 *                         offline (e.g. mobile app).
 * @param bool $embed Whether this file will be served embed into an iframe.
 * @todo MDL-31088 file serving improments
 */
function file_pluginfile_innoverz($relativepath, $forcedownload, $preview = null, $offline = false, $embed = false)
{
    global $DB, $CFG, $USER;
    // relative path must start with '/'
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

    $fs = get_file_storage();

    $sendfileoptions = ['preview' => $preview, 'offline' => $offline, 'embed' => $embed];

    /**
     * here is the if statement referred from filelib.php
     */
    $modname = substr($component, 4);
    require_once($CFG->dirroot . $CFG->VIDEOS_BASEURL . 'lib.php');

    if ($context->contextlevel == CONTEXT_MODULE) {
        if ($cm->modname !== $modname) {
            // somebody tries to gain illegal access, cm type must match the component!
            send_file_not_found();
        }
    }

    if ($filearea === 'intro') {
        if (!plugin_supports('mod', $modname, FEATURE_MOD_INTRO, true)) {
            send_file_not_found();
        }

        // Require login to the course first (without login to the module).
        require_course_login($course, true);

        // Now check if module is available OR it is restricted but the intro is shown on the course page.
        $cminfo = cm_info::create($cm);
        if (!$cminfo->uservisible) {
            if (!$cm->showdescription || !$cminfo->is_visible_on_course_page()) {
                // Module intro is not visible on the course page and module is not available, show access error.
                require_course_login($course, true, $cminfo);
            }
        }

        // all users may access it
        $filename = array_pop($args);
        $filepath = $args ? '/' . implode('/', $args) . '/' : '/';
        if (!$file = $fs->get_file($context->id, 'mod_' . $modname, 'intro', 0, $filepath, $filename) or $file->is_directory()) {
            send_file_not_found();
        }

        // finally send the file
        // innoverz: we send it inline instead
        send_stored_file($file, 0, 0, false, $options);
    }
    video_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $sendfileoptions);

    send_file_not_found();
}
