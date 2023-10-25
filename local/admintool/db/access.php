<?php
// referred from \admin\tool\uploaduser\db\access.php

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    // Allows the user to upload user pictures.
    'local/admintool:uploaduserpictures' => array(
        'riskbitmask' => RISK_SPAM,
        'hide' => true,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' =>  'moodle/site:uploadusers',
    ),
);
