<?php
defined('MOODLE_INTERNAL') || die();

theme_innoverz_extend_flat_navigation($PAGE->flatnav);

/* require_once($CFG->dirroot . '/theme/moove/layout/mypublic.php'); */


global $DB;

// Get the profile userid.
$userid = optional_param('id', $USER->id, PARAM_INT);
$user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
user_preference_allow_ajax_update('sidepre-open', PARAM_ALPHA);

require_once($CFG->libdir . '/behat/lib.php');

$hasdrawertoggle = false;
$navdraweropen = false;
$draweropenright = false;

if (isloggedin()) {
    $hasdrawertoggle = true;
    $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
    $draweropenright = (get_user_preferences('sidepre-open', 'true') == 'true');
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

$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$regionmainsettingsmenu = $OUTPUT->region_main_settings_menu();
$context = context_course::instance(SITEID);
$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => $context, "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'bodyattributes' => $bodyattributes,
    'hasdrawertoggle' => $hasdrawertoggle,
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

$usercourses = \theme_innoverz\util\extras::user_courses_with_progress($user);
$templatecontext['hascourses'] = (count($usercourses)) ? true : false;
$templatecontext['courses'] = array_values($usercourses);
$templatecontext['user'] = $user;
$templatecontext['user']->profilepicture = \theme_innoverz\util\extras::get_user_picture($user, 100);

$templatecontext['importantnotices'] = $themesettings->importantnotices();

$competencyplans = \theme_innoverz\util\extras::get_user_competency_plans($user);
$templatecontext['hascompetencyplans'] = (count($competencyplans)) ? true : false;
$templatecontext['competencyplans'] = $competencyplans;

$templatecontext['headerbuttons'] = \theme_innoverz\util\extras::get_mypublic_headerbuttons($context, $user);

echo $OUTPUT->render_from_template('theme_innoverz/mypublic', $templatecontext);