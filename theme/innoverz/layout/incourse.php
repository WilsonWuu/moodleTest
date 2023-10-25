<?php
defined('MOODLE_INTERNAL') || die();

theme_innoverz_extend_flat_navigation($PAGE->flatnav);

//require_once($CFG->dirroot . '/theme/moove/layout/incourse.php');

user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
user_preference_allow_ajax_update('sidepre-open', PARAM_ALPHA);

require_once($CFG->libdir . '/behat/lib.php');

if (isloggedin()) {
    $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
    $draweropenright = (get_user_preferences('sidepre-open', 'true') == 'true');
} else {
    $navdraweropen = false;
    $draweropenright = false;
}

$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = strpos($blockshtml, 'data-block=') !== false;

$extraclasses = [];
if ($navdraweropen) {
    $extraclasses[] = 'drawer-open-left';
}

if ($draweropenright && $hasblocks) {
    $extraclasses[] = 'drawer-open-right';
}

$moduleswithnavinblocks = ['book', 'quiz'];

if (isset($PAGE->cm->modname) && in_array($PAGE->cm->modname, $moduleswithnavinblocks)) {
    $navdraweropen = false;

    $extraclasses = [];
}

$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$regionmainsettingsmenu = $OUTPUT->region_main_settings_menu();
$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'bodyattributes' => $bodyattributes,
    'hasdrawertoggle' => true,
    'navdraweropen' => $navdraweropen,
    'draweropenright' => $draweropenright,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu)
];

// Improve boost navigation.
theme_moove_extend_flat_navigation($PAGE->flatnav);

$templatecontext['flatnavigation'] = $PAGE->flatnav;

$themesettings = new \theme_innoverz\util\theme_settings();

$templatecontext = array_merge($templatecontext, $themesettings->footer_items());

$templatecontext['importantnotices'] = $themesettings->importantnotices();

if (isset($PAGE->cm->modname) && in_array($PAGE->cm->modname, $moduleswithnavinblocks)) {
    echo $OUTPUT->render_from_template('theme_moove/incourse', $templatecontext);
} else {
    echo $OUTPUT->render_from_template('theme_innoverz/columns2', $templatecontext);
}

