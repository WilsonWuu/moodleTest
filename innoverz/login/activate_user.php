<?php

require('../../config.php');
require_once($CFG->dirroot . '/user/editlib.php');
require_once('activate_user_lib.php');

/*if (current_language() != 'en') {
	redirect(new moodle_url('/login/activate_user.php', array('data'=>isset($_GET["data"]) ? $_GET["data"] : ' / ', 'lang'=>'en')));
}*/

// Try to prevent searching for sites that allow sign-up.
if (!isset($CFG->additionalhtmlhead)) {
    $CFG->additionalhtmlhead = '';
}
$CFG->additionalhtmlhead .= '<meta name="robots" content="noindex" />';

/** 
 * https_required is @deprecated in 3.9
 * //HTTPS is required in this page when $CFG->loginhttps enabled
 * $PAGE->https_required();
*/

$PAGE->set_url('/innoverz/login/activate_user.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('login');
/* $PAGE->requires->css(new Moodle_url('/login/styleheader.css') , true);
$PAGE->requires->css(new Moodle_url('/login/signup.css') , true); */

//get verify code from url which sent to user to activate his account
if (isset($_GET["data"])) {
	$secret_key = $_GET["data"];
	if (!$my_user = verify_access($secret_key)) {
		print_error('notlocalisederrormessage', 'error', '', get_string('activekeyerror', 'local_admintool'));
	}
	if (is_activated_user($secret_key)) {
		$stractivated = get_string('activatedaccountmsg', 'local_admintool');
		notice($stractivated, $CFG->wwwroot.'/login/index.php');
		die;
	}
	$isverified = true;
} else {
	/*for testing only, should remove this
	$secret_key = base64_encode(json_encode( array( 3, '$2y$10$Ox1SK2Np9LHwdCdaK0icWenpyUP7za0KXRrwHBSkxFW/TILv8/Gwu' ) ));
	$my_user = get_user(2);*/
	$my_user = new stdclass();
	$isverified = false;
}

$mform_signup = activateuser_form();

if ($mform_signup->is_cancelled()) {
    redirect(get_login_url());

} else if ($user = $mform_signup->get_data()) {
    $user->confirmed   = 1;
    $user->lang        = current_language();
    //$user->firstaccess = time();
    //$user->timecreated = time();
    //$user->mnethostid  = $CFG->mnet_localhost_id;
    //$user->secret      = random_string(15);
	$user->isapproved = 1;
	$user->isactivate = 1;
    // Initialize alternate name fields to empty strings.
    $user->email2 = '';
    activateUser($user); 
	$stractivated = get_string('activatedaccountmsg', 'local_admintool');
	notice($stractivated, $CFG->wwwroot.'/login/index.php');
    die; // Never reached.
}

if (!$isverified && !confirm_sesskey()) {
	redirect(get_login_url());
}

/* // make sure we really are on the https page when https login required
$PAGE->verify_https_required(); */


$activate = get_string('activateaccount', 'local_admintool');
$login      = get_string('login');

//$PAGE->navbar->add($login);
//$PAGE->navbar->add($activate);

$PAGE->set_title($activate);
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($activate);
$mform_signup->display();
echo $OUTPUT->footer();
