<?php
require_once($CFG->dirroot . '/local/resources/lib.php');

$EDITRESOURCEPATH = 'interrai/editresource.php';

function get_resource_types_interrai()
{
    $acceptedtypes = array(
        '.doc', '.docx', '.tex', '.txt', '.csv', '.pps', '.ppt', '.pptx', '.xls', '.xlsx', '.xlsm', '.pdf', '.bmp', '.gif', '.jpg', 'jpeg', '.png', '.tif', '.tiff', '.gtar', '.tgz', '.gz', '.gzip', '.tar', '.zip', '.mp4', '.m4v', '.f4v'
    );
    return $acceptedtypes;
}

//referred from resource_cm_control() in local/resources/lib.php
function resource_cm_control_interrai($course, $section, $sectionreturn = null, $displayoptions = array())
{
    global $CFG, $DB, $PAGE, $OUTPUT, $USER;

    $vertical = !empty($displayoptions['inblock']);

    // check to see if user can add menus and there are modules to add
    if (
        !has_capability('local/interrai:managefileresources', context_system::instance())
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

    //customize url instead of using the above default ways
    $filelink = new moodle_url($CFG->INTERRAI_BASEURL . 'editresource.php', array('add' => 'resource', 'section' => 1));
    $folderlink = new moodle_url($CFG->INTERRAI_BASEURL . 'editresource.php', array('add' => 'folder', 'section' => 1));
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

    return $output;
}

//referred from get_resource_list() in local/resources/lib.php
function get_interrai_list($course, $section, $catid = 0){
    
    global $DB, $USER;

    
    $modinfo = get_fast_modinfo($course);

    $role_filter='';
    if (user_has_role_assignment($USER->id, $DB->get_field('role', 'id', array('shortname' => 'ir_assessor')))) {
        // user has role InterRAI Assessor
        $role_filter = ' and is_visible_to_interrai_assessor = 1 ';
    }elseif (user_has_role_assignment($USER->id, $DB->get_field('role', 'id', array('shortname' => 'ir_trainee')))) {
        // user has role InterRAI Trainee
        $role_filter = ' and is_visible_to_interrai_trainee = 1 ';
    }

    $sql = '
    SELECT cm.id, f.name, m.name "modname", f.timemodified FROM mdl_course_modules cm
    left join mdl_modules m on m.id = cm.module
    left join mdl_folder f on f.id = cm.instance
    where m.name = "folder" and cm.course = 1 and f.isinterrai = 1 '.$role_filter.'
    UNION
    SELECT cm.id, r.name, m.name "modname", r.timemodified FROM mdl_course_modules cm
    left join mdl_modules m on m.id = cm.module
    left join mdl_resource r on r.id = cm.instance
    where m.name = "resource" and cm.course = 1 and r.isinterrai = 1 and r.isvideo = 0 '.$role_filter;

    $result = $DB->get_records_sql($sql);
    
    $list = array();
    $timemodified = array();
    foreach ($result as $row){
        $mod = $modinfo->cms[$row->id];
        
        $timemodified[] = $row->timemodified;

        $data = new stdclass();
        $data->id = $row->id;
        $data->name = $row->name;
        $data->detail = '';
        $data->icon = $mod->get_icon_url();
        $data->modname = $row->modname;

        $list[] = $data;
    }
    array_multisort($timemodified, SORT_DESC, $list);

    return $list;
}