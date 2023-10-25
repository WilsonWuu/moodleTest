<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'local/interrai:viewfileresources' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_PREVENT,
            'student' => CAP_PREVENT
        )
    ),
    'local/interrai:managefileresources' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_PREVENT,
            'student' => CAP_PREVENT
        )
    ),
);
