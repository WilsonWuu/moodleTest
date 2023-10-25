<?php
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../moove/lib.php');

function theme_innoverz_get_main_scss_content($theme)
{
    global $CFG;

    $scss = '';
    $filename = !empty($theme->settings->preset) ? $theme->settings->preset : null;
    $fs = get_file_storage();

    $context = context_system::instance();
    if ($filename == 'default.scss') {
        // We still load the default preset files directly from the boost theme. No sense in duplicating them.                      
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    } else if ($filename == 'plain.scss') {
        // We still load the default preset files directly from the boost theme. No sense in duplicating them.                      
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/plain.scss');
    } else if ($filename && ($presetfile = $fs->get_file($context->id, 'theme_photo', 'preset', 0, '/', $filename))) {
        // This preset file was fetched from the file area for theme_photo and not theme_boost (see the line above).                
        $scss .= $presetfile->get_content();
    } else {
        // Safety fallback - maybe new installs etc.                                                                                
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    }

    //default boost return
    //return $scss;                               

    // Moove scss.
    $moovevariables = file_get_contents($CFG->dirroot . '/theme/moove/scss/moove/_variables.scss');
    $moove = file_get_contents($CFG->dirroot . '/theme/moove/scss/moove.scss');

    // innoverz scss
    $innoverz = file_get_contents($CFG->dirroot . '/theme/innoverz/scss/innoverz.scss');
    // Combine them together.
    return $moovevariables . "\n" . $scss . "\n" . $moove . "\n" . $innoverz;
}

/**
 * Inject additional SCSS.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_innoverz_get_extra_scss($theme)
{
    $scss = $theme->settings->scss;

    $scss .= theme_moove_set_headerimg($theme);

    $scss .= theme_moove_set_topfooterimg($theme);

    $scss .= theme_moove_set_loginbgimg($theme);

    return $scss;
}

/**
 * Get SCSS to prepend.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_innoverz_get_pre_scss($theme)
{
    $scss = '';
    $configurable = [
        // Config key => [variableName, ...].
        'brandcolor' => ['brand-primary'],
        'navbarheadercolor' => 'navbar-header-color',
        'navbarbg' => 'navbar-bg',
        'navbarbghover' => 'navbar-bg-hover'
    ];

    // Prepend variables first.
    foreach ($configurable as $configkey => $targets) {
        $value = isset($theme->settings->{$configkey}) ? $theme->settings->{$configkey} : null;
        if (empty($value)) {
            continue;
        }
        array_map(function ($target) use (&$scss, $value) {
            $scss .= '$' . $target . ': ' . $value . ";\n";
        }, (array) $targets);
    }

    // Prepend pre-scss.
    if (!empty($theme->settings->scsspre)) {
        $scss .= $theme->settings->scsspre;
    }

    return $scss;
}


/**
 * array(7) {
    [0]=>
    string(6) "myhome"
    [1]=>
    string(4) "home"
    [2]=>
    string(8) "calendar"
    [3]=>
    string(11) "contentbank"
    [4]=>
    string(9) "mycourses"
    [5]=>
    string(1) "3"
    [6]=>
    string(12) "sitesettings"
    }
 * 
 * 
 * 
 */
function theme_innoverz_extend_flat_navigation(\flat_navigation $flatnav)
{
    global $USER, $DB;
    /* echo '<pre>';
    var_dump($flatnav->get_key_list());
    echo '</pre>';
    die();  */
    /**
     * Departmental Library and submenu
     */
    //Resource Kit
    $section1 = [
        'text' => get_string('resource_kits','local_resourcekits'),
        'type' => \navigation_node::TYPE_MY_CATEGORY,
        'key' => 'custom-sections',
    ];
    $flat = new \flat_navigation_node($section1, 0);
    $flat->key = 'resourcekits';
        
    //$url = new moodle_url('/local/resourcekits/index.php');
    //$rekit = navigation_node::create(get_string('new_promote_officers','local_resourcekits'), $url);
    //$flat2 = new \flat_navigation_node($rekit, 0);
    $flat2 = new \flat_navigation_node(
        navigation_node::create(
            get_string('new_promote_officers','local_resourcekits'),
            new moodle_url('/local/course/index.php?categoryid=65&viewtype')
        ),
        0
    );
    $flat2->key = 'resourcekits1';
    
    $flat->add_node($flat2);


    if (isset($flat)) {
        if (in_array('mycourses', $flatnav->get_key_list()))
            $flatnav->add($flat, 'mycourses');
        else if (in_array('sitesettings', $flatnav->get_key_list()))
            $flatnav->add($flat, 'sitesettings');
        else
            $flatnav->add($flat);
    }
    /**
     * Local courses
     */
    $section1 = [
        'text' => get_string('courses', 'local_course'),
        'type' => \navigation_node::TYPE_MY_CATEGORY,
        'key' => 'custom-sections',
    ];
    $flat = new \flat_navigation_node($section1, 0);
    $flat->key = 'courses';

    $flat2 = new \flat_navigation_node(
        navigation_node::create(
            get_string('ecourse', 'local_course'),
            new moodle_url('/local/course?categoryid=2')
        ),
        0
    );
    $flat2->key = 'courses1';

    $flat->add_node($flat2);

    $flat2 = new \flat_navigation_node(
        navigation_node::create(
            get_string('classroomcourse', 'local_course'),
            new moodle_url('/local/course?categoryid=3')
        ),
        0
    );
    $flat2->key = 'courses2';

    $flat->add_node($flat2);


    if (isset($flat)) {
        if (in_array('mycourses', $flatnav->get_key_list()))
            $flatnav->add($flat, 'mycourses');
        else if (in_array('sitesettings', $flatnav->get_key_list()))
            $flatnav->add($flat, 'sitesettings');
        else
            $flatnav->add($flat);
    }

    /**
     * Department library
     */
    if (has_capability('local/elibrary:view', context_system::instance())) {
        $section1 = [
            'text' => get_string('SWD_LIBRARY', 'local_elibrary'),
            //'shorttext' => get_string('coursesections', 'theme_moove'),
            //'icon' => new pix_icon('t/viewdetails', ''),
            'type' => \navigation_node::TYPE_MY_CATEGORY,
            'key' => 'custom-sections',
            //'parent' => $participantsitem->parent
        ];
        $flat = new \flat_navigation_node($section1, 0);
        $flat->key = 'library';

        $url = new moodle_url('/local/elibrary/search_resource.php');
        $elibrary = navigation_node::create(get_string('search_resource', 'local_elibrary'), $url);
        $flat2 = new \flat_navigation_node($elibrary, 0);
        $flat2->key = 'library1';

        $flat->add_node($flat2);

        $url = new moodle_url('/local/elibrary/user_loan_history.php');
        $elibrary = navigation_node::create(get_string('MY_LOAN_RECORDS', 'local_elibrary'), $url);
        $flat2 = new \flat_navigation_node($elibrary, 0);
        $flat2->key = 'library2';

        $flat->add_node($flat2);

        $url = new moodle_url('/local/elibrary/user_reservation_history.php');
        $elibrary = navigation_node::create(get_string('MY_RESERVATION_RECORDS', 'local_elibrary'), $url);
        $flat2 = new \flat_navigation_node($elibrary, 0);
        $flat2->key = 'library3';

        $flat->add_node($flat2);

        $url = new moodle_url('/local/pages?id=2');
        $elibrary = navigation_node::create(get_string('SWD_LIBRARY_RULES', 'local_elibrary'), $url);
        $flat2 = new \flat_navigation_node($elibrary, 0);
        $flat2->key = 'library4';

        $flat->add_node($flat2);

        if (isset($flat)) {
            if (in_array('sitesettings', $flatnav->get_key_list()))
                $flatnav->add($flat, 'sitesettings'); //before sitesettings menu
            else
                $flatnav->add($flat);   //at the end
        }
    }

    /**
     * local ebook
     */
    if (has_capability('local/ebook:view', context_system::instance())) {
        $url = new moodle_url('/local/ebook/view.php');
        $ebook = navigation_node::create(get_string('menuebook', 'local_ebook'), $url);
        $flat = new \flat_navigation_node($ebook, 0);
        $flat->key = 'ebook';

        if (isset($flat)) {
            if (in_array('sitesettings', $flatnav->get_key_list()))
                $flatnav->add($flat, 'sitesettings');
            else
                $flatnav->add($flat);
        }
    }

    /**
     * library administration
     */
    if (has_capability('local/elibrary:resourceadministration', context_system::instance())) {
        $url = new moodle_url('/local/elibrary/admin.php');
        $elibrary = navigation_node::create(get_string('elibrary:resourceadministration', 'local_elibrary'), $url);
        $flat3 = new \flat_navigation_node($elibrary, 0);
        //$flat->set_showdivider(true, 'local_elibrary');
        $flat3->key = 'library_admin';

        $flatnav->add($flat3);
    }

    /**
     * Page plugin
     */
    if (has_capability('local/pages:addpages', context_system::instance())) {
        $url = new moodle_url('/local/pages/pages.php');
        $elibrary = navigation_node::create(get_string('pagesplugin', 'local_pages'), $url);
        $flat3 = new \flat_navigation_node($elibrary, 0);
        $flat3->key = 'local_pages';
    }

    
    /**
     * Local Interrai plugin
     */
    if (has_capability('local/interrai:viewfileresources', context_system::instance())) {
        $url = new moodle_url('/local/interrai/');
        $interrai = navigation_node::create(get_string('modulename', 'local_interrai'), $url);
        $flat1 = new \flat_navigation_node($interrai, 0);
        $flat1->key = 'local_interrai_view';
    }
    if (isset($flat1)) {
        if (in_array('sitesettings', $flatnav->get_key_list()))
            $flatnav->add($flat1, 'sitesettings');
        else
            $flatnav->add($flat1);
    }
}

/**
 * Get theme setting
 *
 * @param string $setting
 * @param bool $format
 * @return string
 */
function theme_innoverz_get_setting($setting, $format = false)
{
    $theme = theme_config::load('innoverz');

    if (empty($theme->settings->$setting)) {
        return false;
    }

    if (!$format) {
        return $theme->settings->$setting;
    }

    if ($format === 'format_text') {
        return format_text($theme->settings->$setting, FORMAT_PLAIN);
    }

    if ($format === 'format_html') {
        return format_text($theme->settings->$setting, FORMAT_HTML, array('trusted' => true, 'noclean' => true));
    }

    return format_string($theme->settings->$setting);
}


/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return mixed
 */
function theme_innoverz_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array())
{
    $theme = theme_config::load('innoverz');

    if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'logo') {
        return $theme->setting_file_serve('logo', $args, $forcedownload, $options);
    }

    if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'headerimg') {
        return $theme->setting_file_serve('headerimg', $args, $forcedownload, $options);
    }

    if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'marketing1icon') {
        return $theme->setting_file_serve('marketing1icon', $args, $forcedownload, $options);
    }

    if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'marketing2icon') {
        return $theme->setting_file_serve('marketing2icon', $args, $forcedownload, $options);
    }

    if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'marketing3icon') {
        return $theme->setting_file_serve('marketing3icon', $args, $forcedownload, $options);
    }

    if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'marketing4icon') {
        return $theme->setting_file_serve('marketing4icon', $args, $forcedownload, $options);
    }

    if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'topfooterimg') {
        return $theme->setting_file_serve('topfooterimg', $args, $forcedownload, $options);
    }

    if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'loginbgimg') {
        return $theme->setting_file_serve('loginbgimg', $args, $forcedownload, $options);
    }

    if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'favicon') {
        return $theme->setting_file_serve('favicon', $args, $forcedownload, $options);
    }

    if ($context->contextlevel == CONTEXT_SYSTEM and preg_match("/^sliderimage[1-9][0-9]?$/", $filearea) !== false) {
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    }

    if ($context->contextlevel == CONTEXT_SYSTEM and preg_match("/^sponsorsimage[1-9][0-9]?$/", $filearea) !== false) {
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    }

    if ($context->contextlevel == CONTEXT_SYSTEM and preg_match("/^clientsimage[1-9][0-9]?$/", $filearea) !== false) {
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    }

    send_file_not_found();
}
