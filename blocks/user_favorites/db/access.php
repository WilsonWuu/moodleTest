<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Block access settings.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package    block_user_favorites
 * @copyright  26-10-2018 MFreak.nl
 * @author     Luuk Verhoeven
 **/

defined('MOODLE_INTERNAL') || die;

$capabilities = [

    'block/user_favorites:myaddinstance' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'student' => CAP_ALLOW,
            'user' => CAP_ALLOW,
        ],
        'clonepermissionsfrom' => 'moodle/my:manageblocks',
    ],

    'block/user_favorites:addinstance' => [
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => [
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        ],

        'clonepermissionsfrom' => 'moodle/site:manageblocks',
    ],

    'block/user_favorites:add' => [
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => [
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'user' => CAP_ALLOW,
        ],
    ],

    'block/user_favorites:delete' => [
        'riskbitmask' => RISK_SPAM | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => [
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'user' => CAP_ALLOW,
        ],
    ],

    'block/user_favorites:view' => [
        'riskbitmask' => RISK_SPAM | RISK_XSS,

        'captype' => 'read',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => [
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'user' => CAP_ALLOW,
        ],
        'clonepermissionsfrom' => 'moodle/site:manageblocks',
    ],
];
