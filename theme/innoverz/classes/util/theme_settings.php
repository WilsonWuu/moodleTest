<?php

namespace theme_innoverz\util;

use theme_config;
use html_writer;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

//require_once($CFG->dirroot . '/theme/innoverz/classes/util/theme_settings.php');

class theme_settings
{

    /**
     * Get config theme footer itens
     *
     * @return array
     */
    public function footer_items()
    {
        $theme = theme_config::load('innoverz');

        $templatecontext = [];

        $footersettings = [
            'facebook', 'twitter', 'whatsapp', 'linkedin', 'youtube', 'instagram', 'getintouchcontent',
            'website', 'mobile', 'mail'
        ];

        foreach ($footersettings as $setting) {
            if (!empty($theme->settings->$setting)) {
                $templatecontext[$setting] = $theme->settings->$setting;
            }
        }

        $templatecontext['disablebottomfooter'] = false;
        if (!empty($theme->settings->disablebottomfooter)) {
            $templatecontext['disablebottomfooter'] = true;
        }

        return $templatecontext;
    }

    /**
     * Get config theme slideshow
     *
     * @return array
     */
    public function slideshow()
    {
        global $OUTPUT;

        $theme = theme_config::load('innoverz');

        $templatecontext['sliderenabled'] = $theme->settings->sliderenabled;

        if (empty($templatecontext['sliderenabled'])) {
            return $templatecontext;
        }

        $slidercount = $theme->settings->slidercount;

        for ($i = 1, $j = 0; $i <= $slidercount; $i++, $j++) {
            $sliderimage = "sliderimage{$i}";
            $slidertitle = "slidertitle{$i}";
            $slidercap = "slidercap{$i}";

            $templatecontext['slides'][$j]['key'] = $j;
            $templatecontext['slides'][$j]['active'] = false;

            $image = $theme->setting_file_url($sliderimage, $sliderimage);
            if (empty($image)) {
                $image = $OUTPUT->image_url('slide_default', 'theme');
            }
            $templatecontext['slides'][$j]['image'] = $image;
            $templatecontext['slides'][$j]['title'] = $theme->settings->$slidertitle;
            $templatecontext['slides'][$j]['caption'] = $theme->settings->$slidercap;

            if ($i === 1) {
                $templatecontext['slides'][$j]['active'] = true;
            }
        }

        return $templatecontext;
    }

    /**
     * Get config theme marketing itens
     *
     * @return array
     */
    public function marketing_items()
    {
        global $OUTPUT;

        $theme = theme_config::load('innoverz');

        $templatecontext = [];

        for ($i = 1; $i < 5; $i++) {
            $marketingicon = 'marketing' . $i . 'icon';
            $marketingheading = 'marketing' . $i . 'heading';
            $marketingsubheading = 'marketing' . $i . 'subheading';
            $marketingcontent = 'marketing' . $i . 'content';
            $marketingurl = 'marketing' . $i . 'url';

            $templatecontext[$marketingicon] = $OUTPUT->image_url('icon_default', 'theme');
            if (!empty($theme->settings->$marketingicon)) {
                $templatecontext[$marketingicon] = $theme->setting_file_url($marketingicon, $marketingicon);
            }

            $templatecontext[$marketingheading] = '';
            if (!empty($theme->settings->$marketingheading)) {
                $templatecontext[$marketingheading] = theme_innoverz_get_setting($marketingheading, true);
            }

            $templatecontext[$marketingsubheading] = '';
            if (!empty($theme->settings->$marketingsubheading)) {
                $templatecontext[$marketingsubheading] = theme_innoverz_get_setting($marketingsubheading, true);
            }

            $templatecontext[$marketingcontent] = '';
            if (!empty($theme->settings->$marketingcontent)) {
                $templatecontext[$marketingcontent] = theme_innoverz_get_setting($marketingcontent, true);
            }

            $templatecontext[$marketingurl] = '';
            if (!empty($theme->settings->$marketingurl)) {
                $templatecontext[$marketingurl] = $theme->settings->$marketingurl;
            }
        }

        return $templatecontext;
    }

    /**
     * Get the frontpage numbers
     *
     * @return array
     */
    public function numbers()
    {
        global $DB;

        $templatecontext['numberusers'] = $DB->count_records('user', array('deleted' => 0, 'suspended' => 0)) - 1;
        $templatecontext['numbercourses'] = $DB->count_records('course', array('visible' => 1)) - 1;
        $templatecontext['numberactivities'] = $DB->count_records('course_modules');

        return $templatecontext;
    }

    /**
     * Get config theme sponsors logos and urls
     *
     * @return array
     */
    public function sponsors()
    {
        $theme = theme_config::load('innoverz');

        $templatecontext['sponsorstitle'] = $theme->settings->sponsorstitle;
        $templatecontext['sponsorssubtitle'] = $theme->settings->sponsorssubtitle;

        $sponsorscount = $theme->settings->sponsorscount;

        for ($i = 1, $j = 0; $i <= $sponsorscount; $i++, $j++) {
            $sponsorsimage = "sponsorsimage{$i}";
            $sponsorsurl = "sponsorsurl{$i}";

            $image = $theme->setting_file_url($sponsorsimage, $sponsorsimage);
            if (empty($image)) {
                continue;
            }

            $templatecontext['sponsors'][$j]['image'] = $image;
            $templatecontext['sponsors'][$j]['url'] = $theme->settings->$sponsorsurl;
        }

        return $templatecontext;
    }

    /**
     * Get config theme clients logos and urls
     *
     * @return array
     */
    public function clients()
    {
        $theme = theme_config::load('innoverz');

        $templatecontext['clientstitle'] = $theme->settings->clientstitle;
        $templatecontext['clientssubtitle'] = $theme->settings->clientssubtitle;

        $clientscount = $theme->settings->clientscount;

        for ($i = 1, $j = 0; $i <= $clientscount; $i++, $j++) {
            $clientsimage = "clientsimage{$i}";
            $clientsurl = "clientsurl{$i}";

            $image = $theme->setting_file_url($clientsimage, $clientsimage);
            if (empty($image)) {
                continue;
            }

            $templatecontext['clients'][$j]['image'] = $image;
            $templatecontext['clients'][$j]['url'] = $theme->settings->$clientsurl;
        }

        return $templatecontext;
    }

    public function importantnotices()
    {
        //return html_writer::link(new moodle_url('/local/pages/important-notices-' . current_language()), get_string('important-notices', 'theme_innoverz'));
        return html_writer::link(new moodle_url('/local/pages?id=1'), get_string('important-notices', 'theme_innoverz'));
    }
}
