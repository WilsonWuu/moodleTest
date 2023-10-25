<?php
 
// Every file should have GPL and copyright in the header - we skip it in tutorials but you should not skip it for real.
 
// This line protects the file from being accessed by a URL directly.                                                               
defined('MOODLE_INTERNAL') || die();                                                                                                

//require_once($CFG->dirroot . '/theme/moove/settings.php');

// This is used for performance, we don't need to know about these settings on every page in Moodle, only when
// we are looking at the admin settings pages.
if ($ADMIN->fulltree) {

    // Boost provides a nice setting page which splits settings onto separate tabs. We want to use it here.
    $settings = new theme_boost_admin_settingspage_tabs('themesettinginnoverz', get_string('configtitle', 'theme_innoverz'));

    /*
    * ----------------------
    * General settings tab
    * ----------------------
    */
    $page = new admin_settingpage('theme_innoverz_general', get_string('generalsettings', 'theme_innoverz'));

    // Logo file setting.
    $name = 'theme_innoverz/logo';
    $title = get_string('logo', 'theme_innoverz');
    $description = get_string('logodesc', 'theme_innoverz');
    $opts = array('accepted_types' => array('.png', '.jpg', '.gif', '.webp', '.tiff', '.svg'), 'maxfiles' => 1);
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo', 0, $opts);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Favicon setting.
    $name = 'theme_innoverz/favicon';
    $title = get_string('favicon', 'theme_innoverz');
    $description = get_string('favicondesc', 'theme_innoverz');
    $opts = array('accepted_types' => array('.ico'), 'maxfiles' => 1);
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'favicon', 0, $opts);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Preset.
    $name = 'theme_innoverz/preset';
    $title = get_string('preset', 'theme_innoverz');
    $description = get_string('preset_desc', 'theme_innoverz');
    $default = 'default.scss';

    $context = context_system::instance();
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'theme_innoverz', 'preset', 0, 'itemid, filepath, filename', false);

    $choices = [];
    foreach ($files as $file) {
        $choices[$file->get_filename()] = $file->get_filename();
    }
    // These are the built in presets.
    $choices['default.scss'] = 'default.scss';
    $choices['plain.scss'] = 'plain.scss';

    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Preset files setting.
    $name = 'theme_innoverz/presetfiles';
    $title = get_string('presetfiles', 'theme_innoverz');
    $description = get_string('presetfiles_desc', 'theme_innoverz');

    $setting = new admin_setting_configstoredfile($name, $title, $description, 'preset', 0,
        array('maxfiles' => 20, 'accepted_types' => array('.scss')));
    $page->add($setting);

    // Login page background image.
    $name = 'theme_innoverz/loginbgimg';
    $title = get_string('loginbgimg', 'theme_innoverz');
    $description = get_string('loginbgimg_desc', 'theme_innoverz');
    $opts = array('accepted_types' => array('.png', '.jpg', '.svg'));
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'loginbgimg', 0, $opts);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Variable $brand-color.
    // We use an empty default value because the default colour should come from the preset.
    $name = 'theme_innoverz/brandcolor';
    $title = get_string('brandcolor', 'theme_innoverz');
    $description = get_string('brandcolor_desc', 'theme_innoverz');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Variable $navbar-header-color.
    // We use an empty default value because the default colour should come from the preset.
    $name = 'theme_innoverz/navbarheadercolor';
    $title = get_string('navbarheadercolor', 'theme_innoverz');
    $description = get_string('navbarheadercolor_desc', 'theme_innoverz');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Variable $navbar-bg.
    // We use an empty default value because the default colour should come from the preset.
    $name = 'theme_innoverz/navbarbg';
    $title = get_string('navbarbg', 'theme_innoverz');
    $description = get_string('navbarbg_desc', 'theme_innoverz');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Variable $navbar-bg-hover.
    // We use an empty default value because the default colour should come from the preset.
    $name = 'theme_innoverz/navbarbghover';
    $title = get_string('navbarbghover', 'theme_innoverz');
    $description = get_string('navbarbghover_desc', 'theme_innoverz');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Course format option.
    $name = 'theme_innoverz/coursepresentation';
    $title = get_string('coursepresentation', 'theme_innoverz');
    $description = get_string('coursepresentationdesc', 'theme_innoverz');
    $options = [];
    $options[1] = get_string('coursedefault', 'theme_innoverz');
    $options[2] = get_string('coursecover', 'theme_innoverz');
    $setting = new admin_setting_configselect($name, $title, $description, $default, $options);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $name = 'theme_innoverz/courselistview';
    $title = get_string('courselistview', 'theme_innoverz');
    $description = get_string('courselistviewdesc', 'theme_innoverz');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $page->add($setting);

    // Must add the page after definiting all the settings!
    $settings->add($page);

    /*
    * ----------------------
    * Advanced settings tab
    * ----------------------
    */
    $page = new admin_settingpage('theme_innoverz_advanced', get_string('advancedsettings', 'theme_innoverz'));

    // Raw SCSS to include before the content.
    $setting = new admin_setting_scsscode('theme_innoverz/scsspre',
        get_string('rawscsspre', 'theme_innoverz'), get_string('rawscsspre_desc', 'theme_innoverz'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Raw SCSS to include after the content.
    $setting = new admin_setting_scsscode('theme_innoverz/scss', get_string('rawscss', 'theme_innoverz'),
        get_string('rawscss_desc', 'theme_innoverz'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Google analytics block.
    $name = 'theme_innoverz/googleanalytics';
    $title = get_string('googleanalytics', 'theme_innoverz');
    $description = get_string('googleanalyticsdesc', 'theme_innoverz');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $settings->add($page);

    /*
    * -----------------------
    * Frontpage settings tab
    * -----------------------
    */
    $page = new admin_settingpage('theme_innoverz_frontpage', get_string('frontpagesettings', 'theme_innoverz'));

    // Disable bottom footer.
    $name = 'theme_innoverz/disablefrontpageloginbox';
    $title = get_string('disablefrontpageloginbox', 'theme_innoverz');
    $description = get_string('disablefrontpageloginboxdesc', 'theme_innoverz');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $page->add($setting);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Disable teachers from cards.
    $name = 'theme_innoverz/disableteacherspic';
    $title = get_string('disableteacherspic', 'theme_innoverz');
    $description = get_string('disableteacherspicdesc', 'theme_innoverz');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $page->add($setting);

    // Headerimg file setting.
    $name = 'theme_innoverz/headerimg';
    $title = get_string('headerimg', 'theme_innoverz');
    $description = get_string('headerimgdesc', 'theme_innoverz');
    $opts = array('accepted_types' => array('.png', '.jpg', '.gif', '.webp', '.tiff', '.svg'));
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'headerimg', 0, $opts);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Bannerheading.
    $name = 'theme_innoverz/bannerheading';
    $title = get_string('bannerheading', 'theme_innoverz');
    $description = get_string('bannerheadingdesc', 'theme_innoverz');
    $default = 'Perfect Learning System';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Bannercontent.
    $name = 'theme_innoverz/bannercontent';
    $title = get_string('bannercontent', 'theme_innoverz');
    $description = get_string('bannercontentdesc', 'theme_innoverz');
    $default = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $name = 'theme_innoverz/displaymarketingbox';
    $title = get_string('displaymarketingbox', 'theme_innoverz');
    $description = get_string('displaymarketingboxdesc', 'theme_innoverz');
    $default = 1;
    $choices = array(0 => 'No', 1 => 'Yes');
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $page->add($setting);

    // Marketing1icon.
    $name = 'theme_innoverz/marketing1icon';
    $title = get_string('marketing1icon', 'theme_innoverz');
    $description = get_string('marketing1icondesc', 'theme_innoverz');
    $opts = array('accepted_types' => array('.png', '.jpg', '.gif', '.webp', '.tiff', '.svg'));
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing1icon', 0, $opts);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Marketing1heading.
    $name = 'theme_innoverz/marketing1heading';
    $title = get_string('marketing1heading', 'theme_innoverz');
    $description = get_string('marketing1headingdesc', 'theme_innoverz');
    $default = 'We host';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Marketing1subheading.
    $name = 'theme_innoverz/marketing1subheading';
    $title = get_string('marketing1subheading', 'theme_innoverz');
    $description = get_string('marketing1subheadingdesc', 'theme_innoverz');
    $default = 'your MOODLE';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Marketing1content.
    $name = 'theme_innoverz/marketing1content';
    $title = get_string('marketing1content', 'theme_innoverz');
    $description = get_string('marketing1contentdesc', 'theme_innoverz');
    $default = 'Moodle hosting in a powerful cloud infrastructure';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Marketing1url.
    $name = 'theme_innoverz/marketing1url';
    $title = get_string('marketing1url', 'theme_innoverz');
    $description = get_string('marketing1urldesc', 'theme_innoverz');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Marketing2icon.
    $name = 'theme_innoverz/marketing2icon';
    $title = get_string('marketing2icon', 'theme_innoverz');
    $description = get_string('marketing2icondesc', 'theme_innoverz');
    $opts = array('accepted_types' => array('.png', '.jpg', '.gif', '.webp', '.tiff', '.svg'));
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing2icon', 0, $opts);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Marketing2heading.
    $name = 'theme_innoverz/marketing2heading';
    $title = get_string('marketing2heading', 'theme_innoverz');
    $description = get_string('marketing2headingdesc', 'theme_innoverz');
    $default = 'Consulting';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Marketing2subheading.
    $name = 'theme_innoverz/marketing2subheading';
    $title = get_string('marketing2subheading', 'theme_innoverz');
    $description = get_string('marketing2subheadingdesc', 'theme_innoverz');
    $default = 'for your company';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Marketing2content.
    $name = 'theme_innoverz/marketing2content';
    $title = get_string('marketing2content', 'theme_innoverz');
    $description = get_string('marketing2contentdesc', 'theme_innoverz');
    $default = 'Moodle consulting and training for you';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Marketing2url.
    $name = 'theme_innoverz/marketing2url';
    $title = get_string('marketing2url', 'theme_innoverz');
    $description = get_string('marketing2urldesc', 'theme_innoverz');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Marketing3icon.
    $name = 'theme_innoverz/marketing3icon';
    $title = get_string('marketing3icon', 'theme_innoverz');
    $description = get_string('marketing3icondesc', 'theme_innoverz');
    $opts = array('accepted_types' => array('.png', '.jpg', '.gif', '.webp', '.tiff', '.svg'));
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing3icon', 0, $opts);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Marketing3heading.
    $name = 'theme_innoverz/marketing3heading';
    $title = get_string('marketing3heading', 'theme_innoverz');
    $description = get_string('marketing3headingdesc', 'theme_innoverz');
    $default = 'Development';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Marketing3subheading.
    $name = 'theme_innoverz/marketing3subheading';
    $title = get_string('marketing3subheading', 'theme_innoverz');
    $description = get_string('marketing3subheadingdesc', 'theme_innoverz');
    $default = 'themes and plugins';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Marketing3content.
    $name = 'theme_innoverz/marketing3content';
    $title = get_string('marketing3content', 'theme_innoverz');
    $description = get_string('marketing3contentdesc', 'theme_innoverz');
    $default = 'We develop themes and plugins as your desires';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Marketing3url.
    $name = 'theme_innoverz/marketing3url';
    $title = get_string('marketing3url', 'theme_innoverz');
    $description = get_string('marketing3urldesc', 'theme_innoverz');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Marketing4icon.
    $name = 'theme_innoverz/marketing4icon';
    $title = get_string('marketing4icon', 'theme_innoverz');
    $description = get_string('marketing4icondesc', 'theme_innoverz');
    $opts = array('accepted_types' => array('.png', '.jpg', '.gif', '.webp', '.tiff', '.svg'));
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing4icon', 0, $opts);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Marketing4heading.
    $name = 'theme_innoverz/marketing4heading';
    $title = get_string('marketing4heading', 'theme_innoverz');
    $description = get_string('marketing4headingdesc', 'theme_innoverz');
    $default = 'Support';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Marketing4subheading.
    $name = 'theme_innoverz/marketing4subheading';
    $title = get_string('marketing4subheading', 'theme_innoverz');
    $description = get_string('marketing4subheadingdesc', 'theme_innoverz');
    $default = 'we give you answers';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Marketing4content.
    $name = 'theme_innoverz/marketing4content';
    $title = get_string('marketing4content', 'theme_innoverz');
    $description = get_string('marketing4contentdesc', 'theme_innoverz');
    $default = 'MOODLE specialized support';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Marketing4url.
    $name = 'theme_innoverz/marketing4url';
    $title = get_string('marketing4url', 'theme_innoverz');
    $description = get_string('marketing4urldesc', 'theme_innoverz');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // 
    $name = 'theme_innoverz/numbersfrontpage';
    $title = get_string('numbersfrontpage', 'theme_innoverz');
    $description = get_string('numbersfrontpagedesc', 'theme_innoverz');
    $default = 1;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
    $page->add($setting);

    // Enable sponsors on frontpage guest page.
    $name = 'theme_innoverz/sponsorsfrontpage';
    $title = get_string('sponsorsfrontpage', 'theme_innoverz');
    $description = get_string('sponsorsfrontpagedesc', 'theme_innoverz');
    $default = 0;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
    $page->add($setting);

    $name = 'theme_innoverz/sponsorstitle';
    $title = get_string('sponsorstitle', 'theme_innoverz');
    $description = get_string('sponsorstitledesc', 'theme_innoverz');
    $default = get_string('sponsorstitledefault', 'theme_innoverz');
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_TEXT);
    $page->add($setting);

    $name = 'theme_innoverz/sponsorssubtitle';
    $title = get_string('sponsorssubtitle', 'theme_innoverz');
    $description = get_string('sponsorssubtitledesc', 'theme_innoverz');
    $default = get_string('sponsorssubtitledefault', 'theme_innoverz');
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_TEXT);
    $page->add($setting);

    $name = 'theme_innoverz/sponsorscount';
    $title = get_string('sponsorscount', 'theme_innoverz');
    $description = get_string('sponsorscountdesc', 'theme_innoverz');
    $default = 1;
    $options = array();
    for ($i = 0; $i < 5; $i++) {
        $options[$i] = $i;
    }
    $setting = new admin_setting_configselect($name, $title, $description, $default, $options);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // If we don't have an slide yet, default to the preset.
    $sponsorscount = get_config('theme_innoverz', 'sponsorscount');

    if (!$sponsorscount) {
        $sponsorscount = 1;
    }

    for ($sponsorsindex = 1; $sponsorsindex <= $sponsorscount; $sponsorsindex++) {
        $fileid = 'sponsorsimage' . $sponsorsindex;
        $name = 'theme_innoverz/sponsorsimage' . $sponsorsindex;
        $title = get_string('sponsorsimage', 'theme_innoverz');
        $description = get_string('sponsorsimagedesc', 'theme_innoverz');
        $opts = array('accepted_types' => array('.png', '.jpg', '.gif', '.webp', '.tiff', '.svg'), 'maxfiles' => 1);
        $setting = new admin_setting_configstoredfile($name, $title, $description, $fileid, 0, $opts);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        $name = 'theme_innoverz/sponsorsurl' . $sponsorsindex;
        $title = get_string('sponsorsurl', 'theme_innoverz');
        $description = get_string('sponsorsurldesc', 'theme_innoverz');
        $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_TEXT);
        $page->add($setting);
    }

    // Enable clients on frontpage guest page.
    $name = 'theme_innoverz/clientsfrontpage';
    $title = get_string('clientsfrontpage', 'theme_innoverz');
    $description = get_string('clientsfrontpagedesc', 'theme_innoverz');
    $default = 0;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
    $page->add($setting);

    $name = 'theme_innoverz/clientstitle';
    $title = get_string('clientstitle', 'theme_innoverz');
    $description = get_string('clientstitledesc', 'theme_innoverz');
    $default = get_string('clientstitledefault', 'theme_innoverz');
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_TEXT);
    $page->add($setting);

    $name = 'theme_innoverz/clientssubtitle';
    $title = get_string('clientssubtitle', 'theme_innoverz');
    $description = get_string('clientssubtitledesc', 'theme_innoverz');
    $default = get_string('clientssubtitledefault', 'theme_innoverz');
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_TEXT);
    $page->add($setting);

    $name = 'theme_innoverz/clientscount';
    $title = get_string('clientscount', 'theme_innoverz');
    $description = get_string('clientscountdesc', 'theme_innoverz');
    $default = 1;
    $options = array();
    for ($i = 0; $i < 5; $i++) {
        $options[$i] = $i;
    }
    $setting = new admin_setting_configselect($name, $title, $description, $default, $options);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // If we don't have an slide yet, default to the preset.
    $clientscount = get_config('theme_innoverz', 'clientscount');

    if (!$clientscount) {
        $clientscount = 1;
    }

    for ($clientsindex = 1; $clientsindex <= $clientscount; $clientsindex++) {
        $fileid = 'clientsimage' . $clientsindex;
        $name = 'theme_innoverz/clientsimage' . $clientsindex;
        $title = get_string('clientsimage', 'theme_innoverz');
        $description = get_string('clientsimagedesc', 'theme_innoverz');
        $opts = array('accepted_types' => array('.png', '.jpg', '.gif', '.webp', '.tiff', '.svg'), 'maxfiles' => 1);
        $setting = new admin_setting_configstoredfile($name, $title, $description, $fileid, 0, $opts);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        $name = 'theme_innoverz/clientsurl' . $clientsindex;
        $title = get_string('clientsurl', 'theme_innoverz');
        $description = get_string('clientsurldesc', 'theme_innoverz');
        $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_TEXT);
        $page->add($setting);
    }

    $settings->add($page);

    /*
    * -----------------------
    * Frontpage slider settings tab
    * -----------------------
    */
    $page = new admin_settingpage('theme_innoverz_slider', get_string('frontpageslidersettings', 'theme_innoverz'));

    // Enable or disable Slideshow settings.
    $name = 'theme_innoverz/sliderenabled';
    $title = get_string('sliderenabled', 'theme_innoverz');
    $description = get_string('sliderenableddesc', 'theme_innoverz');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $page->add($setting);

    // Enable slideshow on frontpage guest page.
    $name = 'theme_innoverz/sliderfrontpage';
    $title = get_string('sliderfrontpage', 'theme_innoverz');
    $description = get_string('sliderfrontpagedesc', 'theme_innoverz');
    $default = 0;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
    $page->add($setting);

    $name = 'theme_innoverz/slidercount';
    $title = get_string('slidercount', 'theme_innoverz');
    $description = get_string('slidercountdesc', 'theme_innoverz');
    $default = 1;
    $options = array();
    for ($i = 0; $i < 13; $i++) {
        $options[$i] = $i;
    }
    $setting = new admin_setting_configselect($name, $title, $description, $default, $options);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // If we don't have an slide yet, default to the preset.
    $slidercount = get_config('theme_innoverz', 'slidercount');

    if (!$slidercount) {
        $slidercount = 1;
    }

    for ($sliderindex = 1; $sliderindex <= $slidercount; $sliderindex++) {
        $fileid = 'sliderimage' . $sliderindex;
        $name = 'theme_innoverz/sliderimage' . $sliderindex;
        $title = get_string('sliderimage', 'theme_innoverz');
        $description = get_string('sliderimagedesc', 'theme_innoverz');
        $opts = array('accepted_types' => array('.png', '.jpg', '.gif', '.webp', '.tiff', '.svg'), 'maxfiles' => 1);
        $setting = new admin_setting_configstoredfile($name, $title, $description, $fileid, 0, $opts);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        $name = 'theme_innoverz/slidertitle' . $sliderindex;
        $title = get_string('slidertitle', 'theme_innoverz');
        $description = get_string('slidertitledesc', 'theme_innoverz');
        $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_TEXT);
        $page->add($setting);

        $name = 'theme_innoverz/slidercap' . $sliderindex;
        $title = get_string('slidercaption', 'theme_innoverz');
        $description = get_string('slidercaptiondesc', 'theme_innoverz');
        $default = '';
        $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
        $page->add($setting);
    }

    // Must add the page after definiting all the settings!
    $settings->add($page);

    /*
    * --------------------
    * Footer settings tab
    * --------------------
    */
    $page = new admin_settingpage('theme_innoverz_footer', get_string('footersettings', 'theme_innoverz'));

    $name = 'theme_innoverz/getintouchcontent';
    $title = get_string('getintouchcontent', 'theme_innoverz');
    $description = get_string('getintouchcontentdesc', 'theme_innoverz');
    $default = 'Conecti.me';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Website.
    $name = 'theme_innoverz/website';
    $title = get_string('website', 'theme_innoverz');
    $description = get_string('websitedesc', 'theme_innoverz');
    $default = 'http://conecti.me';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Mobile.
    $name = 'theme_innoverz/mobile';
    $title = get_string('mobile', 'theme_innoverz');
    $description = get_string('mobiledesc', 'theme_innoverz');
    $default = 'Mobile : +55 (98) 00123-45678';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Mail.
    $name = 'theme_innoverz/mail';
    $title = get_string('mail', 'theme_innoverz');
    $description = get_string('maildesc', 'theme_innoverz');
    $default = 'willianmano@conecti.me';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Facebook url setting.
    $name = 'theme_innoverz/facebook';
    $title = get_string('facebook', 'theme_innoverz');
    $description = get_string('facebookdesc', 'theme_innoverz');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Twitter url setting.
    $name = 'theme_innoverz/twitter';
    $title = get_string('twitter', 'theme_innoverz');
    $description = get_string('twitterdesc', 'theme_innoverz');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Linkdin url setting.
    $name = 'theme_innoverz/linkedin';
    $title = get_string('linkedin', 'theme_innoverz');
    $description = get_string('linkedindesc', 'theme_innoverz');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Youtube url setting.
    $name = 'theme_innoverz/youtube';
    $title = get_string('youtube', 'theme_innoverz');
    $description = get_string('youtubedesc', 'theme_innoverz');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Instagram url setting.
    $name = 'theme_innoverz/instagram';
    $title = get_string('instagram', 'theme_innoverz');
    $description = get_string('instagramdesc', 'theme_innoverz');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Whatsapp url setting.
    $name = 'theme_innoverz/whatsapp';
    $title = get_string('whatsapp', 'theme_innoverz');
    $description = get_string('whatsappdesc', 'theme_innoverz');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Top footer background image.
    $name = 'theme_innoverz/topfooterimg';
    $title = get_string('topfooterimg', 'theme_innoverz');
    $description = get_string('topfooterimgdesc', 'theme_innoverz');
    $opts = array('accepted_types' => array('.png', '.jpg', '.svg'));
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'topfooterimg', 0, $opts);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Disable bottom footer.
    $name = 'theme_innoverz/disablebottomfooter';
    $title = get_string('disablebottomfooter', 'theme_innoverz');
    $description = get_string('disablebottomfooterdesc', 'theme_innoverz');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $page->add($setting);
    $setting->set_updatedcallback('theme_reset_all_caches');

    $settings->add($page);

    // Forum page.
    $settingpage = new admin_settingpage('theme_innoverz_forum', get_string('forumsettings', 'theme_innoverz'));

    $settingpage->add(new admin_setting_heading('theme_innoverz_forumheading', null,
            format_text(get_string('forumsettingsdesc', 'theme_innoverz'), FORMAT_MARKDOWN)));

    // Enable custom template.
    $name = 'theme_innoverz/forumcustomtemplate';
    $title = get_string('forumcustomtemplate', 'theme_innoverz');
    $description = get_string('forumcustomtemplatedesc', 'theme_innoverz');
    $default = 0;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
    $settingpage->add($setting);

    // Header setting.
    $name = 'theme_innoverz/forumhtmlemailheader';
    $title = get_string('forumhtmlemailheader', 'theme_innoverz');
    $description = get_string('forumhtmlemailheaderdesc', 'theme_innoverz');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $settingpage->add($setting);

    // Footer setting.
    $name = 'theme_innoverz/forumhtmlemailfooter';
    $title = get_string('forumhtmlemailfooter', 'theme_innoverz');
    $description = get_string('forumhtmlemailfooterdesc', 'theme_innoverz');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $settingpage->add($setting);

    $settings->add($settingpage);
}
