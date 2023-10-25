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
    
    if ($component === 'resource') {
        if ($filearea === 'icon' and $context->contextlevel == CONTEXT_SYSTEM) {
            if (count($args) == 1) {
                $themename = theme_config::DEFAULT_THEME;
                $filename = array_shift($args);
            } else {
                $themename = array_shift($args);
                $filename = array_shift($args);
            }

            // fix file name automatically
            if ($filename !== 'f1' and $filename !== 'f2' and $filename !== 'f3') {
                $filename = 'f1';
            }
            $itemid = optional_param('reim', 0, PARAM_INT);
            
            if (!$file = $fs->get_file($context->id, 'resource', 'icon', $itemid, '/', $filename . '.png')) {
                if (!$file = $fs->get_file($context->id, 'resource', 'icon', $itemid, '/', $filename . '.jpg')) {
                    if ($filename === 'f3') {
                        // f3 512x512px was introduced in 2.3, there might be only the smaller version.
                        if (!$file = $fs->get_file($context->id, 'resource', 'icon', $itemid, '/', 'f1.png')) {
                            $file = $fs->get_file($context->id, 'resource', 'icon', $itemid, '/', 'f1.jpg');
                        }
                    }
                }
            }


            if (!$file) {
                // bad reference - try to prevent future retries as hard as possible!
                if ($resource = $DB->get_record('library_resource', array('id' => $context->instanceid), 'id, coverimage')) {
                    if ($resource->picture > 0) {
                        $DB->set_field('library_resource', 'coverimage', 0, array('id' => $resource->id));
                    }
                }
                // no redirect here because it is not cached
                $theme = theme_config::load($themename);
                $imagefile = $theme->resolve_image_location('u/' . $filename, 'moodle', null);
                send_file($imagefile, basename($imagefile), 60 * 60 * 24 * 14);
            }
            send_stored_file($file, 60 * 60 * 24 * 365, 0, false, $sendfileoptions); // enable long caching, there are many images on each page
        } else {
            send_file_not_found();
        }
    }
}
