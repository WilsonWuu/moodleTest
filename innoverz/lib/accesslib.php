<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/accesslib.php');

function get_system_user_role($userid){
	$roles = get_user_roles(context_system::instance(), $userid, false);
	$roleids_user = array();
	foreach($roles as $role){
		$roleids_user[] = $role->roleid;
	}
	return $roleids_user;
}
