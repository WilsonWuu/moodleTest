<?php
defined('MOODLE_INTERNAL') || die();

theme_innoverz_extend_flat_navigation($PAGE->flatnav);

/* require_once($CFG->dirroot . '/theme/moove/layout/mydashboard.php'); */


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
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'canviewadmininfos' => false
];

$themesettings = new \theme_innoverz\util\theme_settings();

$templatecontext = array_merge($templatecontext, $themesettings->footer_items());

if (is_siteadmin() && $PAGE->pagetype == 'my-index') {
    $adminifos = new \theme_innoverz\util\admininfos();

    $templatecontext['totalusage'] = $adminifos->get_totaldiskusage();
    $templatecontext['totalactiveusers'] = $adminifos->get_totalactiveusers();
    $templatecontext['totalsuspendedusers'] = $adminifos->get_suspendedusers();
    $templatecontext['totalcourses'] = $adminifos->get_totalcourses();
    $templatecontext['onlineusers'] = $adminifos->get_totalonlineusers();

    $templatecontext['canviewadmininfos'] = true;
}

// Improve boost navigation.
theme_moove_extend_flat_navigation($PAGE->flatnav);

$templatecontext['flatnavigation'] = $PAGE->flatnav;

$templatecontext['importantnotices'] = $themesettings->importantnotices();

echo $OUTPUT->render_from_template('theme_innoverz/mydashboard', $templatecontext);

