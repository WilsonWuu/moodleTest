<?php
defined('MOODLE_INTERNAL') || die();

// This plugin(local_folder) depends on plugin local_videos and local_resources and works together with plugin local_interrai.
// Please make sure to install plugins local_videos and local_resources first to make it working with local_interrai.

$plugin->version   = 2021101900; // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2020060900; // Requires this Moodle version
$plugin->component = 'local_folder'; // Full name of the plugin (used for diagnostics)

