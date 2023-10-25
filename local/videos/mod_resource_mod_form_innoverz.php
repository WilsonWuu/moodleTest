<?php
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/innoverz/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/resource/locallib.php');
require_once($CFG->libdir . '/filelib.php');


class mod_resource_mod_form_innoverz extends moodleform_mod_innoverz
{

    var $accepted_types;
    var $button_setting;
    var $cat_list; //discard to use, only check whether is showing cat list
    var $cat_default;
    var $maxfiles;
    var $enablelink;
    var $defaultshowdesc;
    var $enablenonconformance;
    var $formaction;

    function __construct(
        $data,
        $section,
        $cm,
        $course,
        $action = 'modedit.php',
        $button_setting = null,
        $accepted_types = '*',
        $maxfiles = -1,
        $cat_list = array(),
        $cat_default = 0,
        $enablelink = false,
        $defaultshowdesc = false,
        $enablenonconformance = false
    ) {
        $this->accepted_types = $accepted_types;
        $this->button_setting = $button_setting;
        $this->cat_list = $cat_list;
        $this->cat_default = $cat_default;
        $this->maxfiles = $maxfiles;
        $this->enablelink = $enablelink;
        $this->defaultshowdesc = $defaultshowdesc;
        $this->enablenonconformance = $enablenonconformance;
        $this->formaction = $action;
        $this->_modname = 'resource';
        parent::__construct($data, $section, $cm, $course, $action);
    }

    function definition()
    {
        global $CFG, $DB;
        $mform = &$this->_form;

        $config = get_config('resource');

        if ($this->current->instance and $this->current->tobemigrated) {
            // resource not migrated yet
            $resource_old = $DB->get_record('resource_old', array('oldid' => $this->current->instance));
            $mform->addElement('static', 'warning', '', get_string('notmigrated', 'resource', $resource_old->type));
            $mform->addElement('cancel');
            $this->standard_hidden_coursemodule_elements();
            return;
        }

        //-------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size' => '48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        //added by felix
        if (count($this->cat_list) > 0) {
            $displaylist = core_course_category::make_categories_list('moodle/course:create', 3, " / ", 2);
            $options = array(get_string('pleaseselect', 'local_videos')) + $displaylist;
            $mform->addElement('select', 'category', get_string('category'), $options);
            $mform->addRule('category', null, 'required', null, 'server');

            if ($this->cat_default) {
                $mform->setDefault('category', $this->cat_default);
            }
        }

        //deprecated: $this->add_intro_editor($config->requiremodintro, null, $this->defaultshowdesc);
        $this->standard_intro_elements();

        $mform->addElement('text', 'video_ordering', get_string('video_ordering', 'local_videos'), 'size="7"');
        $mform->setType('video_ordering', PARAM_INT);

        //-------------------------------------------------------
        $mform->addElement('header', 'contentsection', get_string('contentheader', 'resource'));
        $mform->setExpanded('contentsection');

        $filemanager_options = array();
        $filemanager_options['accepted_types'] = $this->accepted_types;
        $filemanager_options['maxbytes'] = 0;
        $filemanager_options['maxfiles'] = $this->maxfiles;
        $filemanager_options['mainfile'] = true;

        //added by felix by adding external ref link
        if ($this->enablelink) {
            $mform->addElement('static', 'policyvideoupload', '', get_string('policyvideoupload', 'local_videos'));
            $mform->addElement('text', 'link', get_string('navigation_link', 'local_videos'), 'size="40"');
            $mform->setType('link', PARAM_RAW);
            $mform->setDefault('link', 'http://');
            $mform->addElement('filemanager', 'files', get_string('selectfiles'), null, $filemanager_options);
        } else {
            $mform->addElement('filemanager', 'files', get_string('selectfiles'), null, $filemanager_options);
        }

        //added by felix by enable show the non-conformance logo instead of WCAG
        if ($this->enablenonconformance) {
            $checkboxarray = array();
            $checkboxarray[] = &$mform->createElement('checkbox', 'isnonconformance', '', get_string('isnonconformance', 'local_videos'), 1);
            $mform->addGroup($checkboxarray, 'isnonconformance', '', '', false);
        }

        // add legacy files flag only if used
        if (isset($this->current->legacyfiles) and $this->current->legacyfiles != RESOURCELIB_LEGACYFILES_NO) {
            $options = array(
                RESOURCELIB_LEGACYFILES_DONE   => get_string('legacyfilesdone', 'resource'),
                RESOURCELIB_LEGACYFILES_ACTIVE => get_string('legacyfilesactive', 'resource')
            );
            $mform->addElement('select', 'legacyfiles', get_string('legacyfiles', 'resource'), $options);
        }

        //-------------------------------------------------------
        //HD video upload
        if ($this->formaction == 'editvideo.php') {
            if (!function_exists('video_get_video_file_types')) {
                require_once($CFG->dirroot . $CFG->VIDEOS_BASEURL . 'lib.php');
            }
            $mform->addElement('header', 'contentsection2', get_string('hdversion', 'local_videos'));

            $filemanager_options = array();
            $filemanager_options['accepted_types'] = video_get_video_file_types();
            $filemanager_options['maxbytes'] = 0;
            $filemanager_options['maxfiles'] = 1;
            $filemanager_options['mainfile'] = true;
            $mform->addElement('filemanager', 'hdvideofile', get_string('selectfiles'), null, $filemanager_options);
        }

        //-------------------------------------------------------
        $mform->addElement('header', 'optionssection', get_string('appearance'));

        if ($this->current->instance) {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions), $this->current->display);
        } else {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions));
        }

        if (count($options) == 1) {
            $mform->addElement('hidden', 'display');
            $mform->setType('display', PARAM_INT);
            reset($options);
            $mform->setDefault('display', key($options));
        } else {
            $mform->addElement('select', 'display', get_string('displayselect', 'resource'), $options);
            $mform->setDefault('display', $config->display);
            $mform->addHelpButton('display', 'displayselect', 'resource');
        }

        $mform->addElement('checkbox', 'showsize', get_string('showsize', 'resource'));
        $mform->setDefault('showsize', $config->showsize);
        $mform->addHelpButton('showsize', 'showsize', 'resource');
        $mform->addElement('checkbox', 'showtype', get_string('showtype', 'resource'));
        $mform->setDefault('showtype', $config->showtype);
        $mform->addHelpButton('showtype', 'showtype', 'resource');

        if (array_key_exists(RESOURCELIB_DISPLAY_POPUP, $options)) {
            $mform->addElement('text', 'popupwidth', get_string('popupwidth', 'resource'), array('size' => 3));
            if (count($options) > 1) {
                $mform->disabledIf('popupwidth', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupwidth', PARAM_INT);
            $mform->setDefault('popupwidth', $config->popupwidth);
            $mform->setAdvanced('popupwidth', true);

            $mform->addElement('text', 'popupheight', get_string('popupheight', 'resource'), array('size' => 3));
            if (count($options) > 1) {
                $mform->disabledIf('popupheight', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupheight', PARAM_INT);
            $mform->setDefault('popupheight', $config->popupheight);
            $mform->setAdvanced('popupheight', true);
        }

        if (
            array_key_exists(RESOURCELIB_DISPLAY_AUTO, $options) or
            array_key_exists(RESOURCELIB_DISPLAY_EMBED, $options) or
            array_key_exists(RESOURCELIB_DISPLAY_FRAME, $options)
        ) {
            $mform->addElement('checkbox', 'printintro', get_string('printintro', 'resource'));
            $mform->disabledIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_POPUP);
            $mform->disabledIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_DOWNLOAD);
            $mform->disabledIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_OPEN);
            $mform->disabledIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_NEW);
            $mform->setDefault('printintro', $config->printintro);
        }

        $options = array('0' => get_string('none'), '1' => get_string('allfiles'), '2' => get_string('htmlfilesonly'));
        $mform->addElement('select', 'filterfiles', get_string('filterfiles', 'resource'), $options);
        $mform->setDefault('filterfiles', $config->filterfiles);
        $mform->setAdvanced('filterfiles', true);

        //-------------------------------------------------------
        $this->standard_coursemodule_elements();

        //-------------------------------------------------------
        $btnset = $this->button_setting;
        if ($btnset) {
            $this->add_action_buttons($btnset[0], $btnset[1], $btnset[2]);
        } else {
            $this->add_action_buttons();
        }

        //-------------------------------------------------------
        $mform->addElement('hidden', 'revision');
        $mform->setType('revision', PARAM_INT);
        $mform->setDefault('revision', 1);
    }

    function data_preprocessing(&$default_values)
    {
        if ($this->current->instance and !$this->current->tobemigrated) {
            $draftitemid = file_get_submitted_draft_itemid('files');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_resource', 'content', 0, array('subdirs' => true));
            $default_values['files'] = $draftitemid;

            $draftitemid = file_get_submitted_draft_itemid('hdvideofile');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_resource', 'hdvideo', 0, array('subdirs' => true));
            $default_values['hdvideofile'] = $draftitemid;

        }
        if (!empty($default_values['displayoptions'])) {
            $displayoptions = unserialize($default_values['displayoptions']);
            if (isset($displayoptions['printintro'])) {
                $default_values['printintro'] = $displayoptions['printintro'];
            }
            if (!empty($displayoptions['popupwidth'])) {
                $default_values['popupwidth'] = $displayoptions['popupwidth'];
            }
            if (!empty($displayoptions['popupheight'])) {
                $default_values['popupheight'] = $displayoptions['popupheight'];
            }
            if (!empty($displayoptions['showsize'])) {
                $default_values['showsize'] = $displayoptions['showsize'];
            } else {
                // Must set explicitly to 0 here otherwise it will use system
                // default which may be 1.
                $default_values['showsize'] = 0;
            }
            if (!empty($displayoptions['showtype'])) {
                $default_values['showtype'] = $displayoptions['showtype'];
            } else {
                $default_values['showtype'] = 0;
            }
        }
    }

    function definition_after_data()
    {
        if ($this->current->instance and $this->current->tobemigrated) {
            // resource not migrated yet
            return;
        }

        parent::definition_after_data();
    }

    function validation($data, $files)
    {

        $errors = parent::validation($data, $files);

        //added by felix
        if (isset($data['category']) && $data['category'] == 0) {
            $errors['category'] = get_string('selectcategoryerror', 'local_videos');
        }

        if ($this->enablelink) {
            $link = trim($data['link']);
            if (!empty($link) && $link != "http://") {
                if (!filter_var($link, FILTER_VALIDATE_URL)) {
                    $errors['link'] = get_string('errorbookmarklink');
                }
            } else {
                $this->validate_uploaded_files($data, $files, $errors);
            }
        } else {
            $this->validate_uploaded_files($data, $files, $errors);
        }
        return $errors;
    }

    function validate_uploaded_files($data, $files, &$errors)
    {
        global $USER;
        $usercontext = context_user::instance($USER->id);
        $fs = get_file_storage();
        if (!$files = $fs->get_area_files($usercontext->id, 'user', 'draft', $data['files'], 'sortorder, id', false)) {
            $errors['files'] = get_string('required');
            return $errors;
        }
        if ($this->formaction == 'editvideo.php') {
            //check number of video uploaded
            $numofvideo = 0;
            foreach ($files as $file) {
                if ($file->get_mimetype() == 'video/mp4') {
                    $numofvideo++;
                }
            }
            if ($numofvideo == 0 || $numofvideo > 1) {
                $errors['files'] = get_string('error_uploadonevideo');
            }
            //check number of image uploaded
            $numofimage = 0;
            $imagemimetypes = array('image/png', 'image/jpeg');
            foreach ($files as $file) {
                if (in_array($file->get_mimetype(), $imagemimetypes)) {
                    $numofimage++;
                }
            }
            if ($numofimage > 1) {
                $errors['files'] = get_string('error_uploadoneimage');
            }
        }

        if (count($files) == 1) {
            // no need to select main file if only one picked
            return $errors;
        } else if (count($files) > 1) {
            $mainfile = false;
            foreach ($files as $file) {
                if ($file->get_sortorder() == 1) {
                    $mainfile = true;
                    break;
                }
            }
            // set a default main file
            if (!$mainfile) {
                $file = reset($files);
                file_set_sortorder(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename(),
                    1
                );
            }
        }
    }
}
