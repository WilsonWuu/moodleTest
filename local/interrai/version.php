<?php
defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2021121000; // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2020060900; // Requires this Moodle version
$plugin->component = 'local_interrai'; // Full name of the plugin (used for diagnostics)
/* $plugin->dependencies = array(
    'local_resources' => 2021101600,
    'local_videos' => 2021101600
); */

// This plugin(local_interrai) depends on plugins local_videos and local_resources.
// Please make sure to install plugins local_videos and local_resources first to make it working