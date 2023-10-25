<?php
defined('MOODLE_INTERNAL') || die();

$extraclasses = ['moove-login'];

$bodyattributes = $OUTPUT->body_attributes($extraclasses);

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'bodyattributes' => $bodyattributes,
    'logourl' => $this->image_url('swd_logo', 'theme') // added main logo 'swd' for login page
];

echo $OUTPUT->render_from_template('theme_innoverz/login', $templatecontext);
