<?php
defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2020091401; // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2020060900; // Requires this Moodle version
$plugin->component = 'local_resources'; // Full name of the plugin (used for diagnostics)

// Plugins local_videos and local_resources depend on each others.
// Please make to install both plugins to make them working