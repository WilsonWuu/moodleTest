<?php
//referred from admin/tool/uploaduser/index_lib_background.php in 2.7 by Innoverz

require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/csvlib.class.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/group/lib.php');
require_once($CFG->dirroot.'/cohort/lib.php');
require_once("$CFG->dirroot/local/admintool/lib.php");
require_once("$CFG->dirroot/local/admintool/uploaduser/locallib.php");

if (isset($_GET['data'])) {
	$user = json_decode(base64_decode($_GET['data']));
	if (is_object($user)) {
		$user->lastnamephonetic = '';
		$user->firstnamephonetic = '';
		$user->middlename = '';
		$user->alternatename = '';
		$PAGE->set_context(context_system::instance());
		writeLog('wgetresults.txt', "chinese name = " . $user->profile_field_chiname. ", email = {$user->email}");
		if (send_user_created_email($user)) {
			 updateUserForSentEmail($user);
		}
	} else {
		writeLog('wgetresults.txt', "data = {$_GET['data']}");
	}	
} 