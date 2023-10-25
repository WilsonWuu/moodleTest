<?php
defined('MOODLE_INTERNAL') || die();

theme_innoverz_extend_flat_navigation($PAGE->flatnav);

//require_once($CFG->dirroot . '/theme/moove/layout/frontpage.php');

user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
user_preference_allow_ajax_update('sidepre-open', PARAM_ALPHA);

require_once($CFG->libdir . '/behat/lib.php');

$extraclasses = [];

$themesettings = new \theme_innoverz\util\theme_settings();

if (isloggedin()) {
    $blockshtml = $OUTPUT->blocks('side-pre');
    //$frontcenterhtml = $OUTPUT->blocks('front-center');
    $frontcenterhtml = $OUTPUT->blocks_for_region('front-center');

    $hasblocks = strpos($blockshtml, 'data-block=') !== false;
    $hasfrontcenter = strpos($frontcenterhtml, 'data-block=') !== false;

    $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
    $draweropenright = (get_user_preferences('sidepre-open', 'true') == 'true');

    if ($navdraweropen) {
        $extraclasses[] = 'drawer-open-left';
    }

    if ($draweropenright && $hasblocks) {
        $extraclasses[] = 'drawer-open-right';
    }

    $sliderfrontpage = false;
    $sliderenabled = false;
    if ((theme_innoverz_get_setting('sliderenabled', true) == true) && (theme_innoverz_get_setting('sliderfrontpage', true) == true)) {
        $sliderfrontpage = true;
        $sliderenabled = true;
        $extraclasses[] = 'slideshow';
    }

    $bodyattributes = $OUTPUT->body_attributes($extraclasses);
    $regionmainsettingsmenu = $OUTPUT->region_main_settings_menu();

    $templatecontext = [
        'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
        'output' => $OUTPUT,
        'sidepreblocks' => $blockshtml,
        'frontcenterblock' => $frontcenterhtml,
        'hasblocks' => $hasblocks,
        'hasfrontcenter' => $hasfrontcenter,
        'bodyattributes' => $bodyattributes,
        'hasdrawertoggle' => true,
        'navdraweropen' => $navdraweropen,
        'draweropenright' => $draweropenright,
        'regionmainsettingsmenu' => $regionmainsettingsmenu,
        'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
        'sliderfrontpage' => $sliderfrontpage
    ];

    // Improve boost navigation.
    theme_moove_extend_flat_navigation($PAGE->flatnav);

    $templatecontext['flatnavigation'] = $PAGE->flatnav;

    $templatecontext['importantnotices'] = $themesettings->importantnotices();
    
    $templatecontext = array_merge($templatecontext, $themesettings->footer_items(), $themesettings->slideshow());

    echo $OUTPUT->render_from_template('theme_innoverz/frontpage', $templatecontext);
} else {
    $sliderfrontpage = false;
    if ((theme_moove_get_setting('sliderenabled', true) == true) && (theme_moove_get_setting('sliderfrontpage', true) == true)) {
        $sliderfrontpage = true;
        $extraclasses[] = 'slideshow';
    }

    $numbersfrontpage = false;
    if (theme_moove_get_setting('numbersfrontpage', true) == true) {
        $numbersfrontpage = true;
    }

    $sponsorsfrontpage = false;
    if (theme_moove_get_setting('sponsorsfrontpage', true) == true) {
        $sponsorsfrontpage = true;
    }

    $clientsfrontpage = false;
    if (theme_moove_get_setting('clientsfrontpage', true) == true) {
        $clientsfrontpage = true;
    }

    $bannerheading = '';
    if (!empty($PAGE->theme->settings->bannerheading)) {
        $bannerheading = theme_moove_get_setting('bannerheading', true);
    }

    $bannercontent = '';
    if (!empty($PAGE->theme->settings->bannercontent)) {
        $bannercontent = theme_moove_get_setting('bannercontent', true);
    }

    $shoulddisplaymarketing = false;
    if (theme_moove_get_setting('displaymarketingbox', true) == true) {
        $shoulddisplaymarketing = true;
    }

    $disablefrontpageloginbox = false;
    if (theme_moove_get_setting('disablefrontpageloginbox', true) == true) {
        $disablefrontpageloginbox = true;
        $extraclasses[] = 'disablefrontpageloginbox';
    }

    $bodyattributes = $OUTPUT->body_attributes($extraclasses);
    $regionmainsettingsmenu = $OUTPUT->region_main_settings_menu();

    $templatecontext = [
        'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
        'output' => $OUTPUT,
        'bodyattributes' => $bodyattributes,
        'hasdrawertoggle' => false,
        'canloginasguest' => $CFG->guestloginbutton and !isguestuser(),
        'cansignup' => $CFG->registerauth == 'email' || !empty($CFG->registerauth),
        'bannerheading' => $bannerheading,
        'bannercontent' => $bannercontent,
        'shoulddisplaymarketing' => $shoulddisplaymarketing,
        'sliderfrontpage' => $sliderfrontpage,
        'numbersfrontpage' => $numbersfrontpage,
        'sponsorsfrontpage' => $sponsorsfrontpage,
        'clientsfrontpage' => $clientsfrontpage,
        'disablefrontpageloginbox' => $disablefrontpageloginbox,
        'logintoken' => \core\session\manager::get_login_token()
    ];

    $templatecontext = array_merge($templatecontext, $themesettings->footer_items(), $themesettings->marketing_items());

    if ($sliderfrontpage) {
        $templatecontext = array_merge($templatecontext, $themesettings->slideshow());
    }

    if ($numbersfrontpage) {
        $templatecontext = array_merge($templatecontext, $themesettings->numbers());
    }

    if ($sponsorsfrontpage) {
        $templatecontext = array_merge($templatecontext, $themesettings->sponsors());
    }

    if ($clientsfrontpage) {
        $templatecontext = array_merge($templatecontext, $themesettings->clients());
    }

    echo $OUTPUT->render_from_template('theme_moove/frontpage_guest', $templatecontext);
}
