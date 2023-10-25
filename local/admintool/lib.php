<?php

// migrated from admin/tool/uploaduser/index_lib.php in 2.7
// to get the folder url ($_SERVER[REQUEST_URI])
// used in course/lib.php in 2.7 for executing background task (execute_background_task)


function get_folder_url()
{
    $link =  "//$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $escaped_link = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
    $escaped_link = explode("/", $escaped_link);
    unset($escaped_link[count($escaped_link) - 1]);
    $escaped_link = implode("/", $escaped_link);
    $escaped_link = 'http:' . $escaped_link;
    //$escaped_link = 'http' . (isset($_SERVER['HTTPS']) ? 's:' : ':') . $escaped_link;
    return $escaped_link;
}

// migrated from admin/tool/uploaduser/index_lib.php  in 2.7 and 
// used in admin/tool/uploaduser/index_lib.php createUploadUser in 2.7
// course/lib.php send_course_application_email  in 2.7
// login/signup_lib.php send_user_registration_confirmed_email  in 2.7
function execute_background_task($link)
{
    //send email in background
    global $CFG;
    $proc_command = "$CFG->wget $link -q -o wgetlog.txt -b";
    $proc = popen($proc_command, "r");
    pclose($proc);
}

// migrated from admin/tool/uploaduser/index_lib.php in 2.7
// used in lib/classes/task/resend_user_activation_email_task.php in 2.7 and
// admin/tool/uploaduser/index_lib_background.php in 2.7 

require_once("$CFG->dirroot/innoverz/lib/moodlelib.php");
function send_user_created_email($user)
{
    global $CFG;
    echo "send email";
    $site = get_site();
    $supportuser = core_user::get_support_user();

    $data = new \stdClass();
    $data->firstname = fullname($user);
    $data->admin     = generate_email_signoff();

    $subject = get_string('emailusercreatedsubject', 'local_admintool', format_string($site->fullname));

    $username = urlencode($user->username);
    $username = str_replace('.', '%2E', $username); // Prevent problems with trailing dots.

    // for upload user, $user->secret should be generated in function createUploadUser() in local\admintool\uploaduser\locallib.php
    if (!isset($user->secret)) $user->secret = random_string(15);
    
    $user->mailformat = 1;  // Always send HTML version as well.

    if (empty(trim($user->password)) ||  $user->password == 'to be generated') {
        $data->link  = $CFG->wwwroot . '/innoverz/login/activate_user.php?data=' . $user->secret . '/' . $username;
        $message     = get_string('emailinactiveusercreatedcontent', 'local_admintool', $data);
        $messagehtml = text_to_html(get_string('emailinactiveusercreatedcontent', 'local_admintool', $data), false, false, true);
        // Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
        return email_to_user_innoverz($user, $supportuser, $subject, $message, $messagehtml, null, null, null, null, null, null, 'upload_user_inactive');
    } else {
        $data->link  = $CFG->wwwroot . '/innoverz/login/confirm.php?data=' . $user->secret . '/' . $username;
        $data->sitename  = format_string($site->fullname);
        $message     = get_string('emailusercreatedcontent', 'local_admintool', $data);
        $messagehtml = text_to_html(get_string('emailusercreatedcontent', 'local_admintool', $data), false, false, true);
        // Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
        return email_to_user_innoverz($user, $supportuser, $subject, $message, $messagehtml, null, null, null, null, null, null, 'upload_user_active');
    }
}


// migrated from admin/tool/uploaduser/index_lib.php in 2.7
// used in that file in 2.7 as default script
// that file is used in admin/tool/uploaduser/index.php and course/lib.php
function send_deactivate_user_email($user)
{
    global $CFG;

    $site = get_site();
    $supportuser = core_user::get_support_user();

    $data = new \stdClass();
    $data->firstname = fullname($user);
    $data->admin     = generate_email_signoff();

    $subject = get_string('emailuserdeactivatesubject', 'local_admintool', format_string($site->fullname));
    $message     = get_string('emailuserdeactivatecontent', 'local_admintool', $data);
    $messagehtml = text_to_html(get_string('emailuserdeactivatecontent', 'local_admintool', $data), false, false, true);

    $user->mailformat = 1;  // Always send HTML version as well.
    // Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
    return email_to_user_innoverz($user, $supportuser, $subject, $message, $messagehtml, null, null, null, null, null, null, 'user_deactivate');
}

// migrated from admin/tool/uploaduser/index_lib.php in 2.7
// used in that file in 2.7 as default script
// that file is used in config.php(productions) in 2.7, 
// admin/tool/uploaduser/index.php and course/lib.php in 2.7
function deleteInvalidUser($delete)
{
    $creloadcsv = optional_param('reloadcsv', 0, PARAM_INT);
    $confirm = optional_param('confirm', '', PARAM_ALPHANUM);   //md5 confirmation hash
    $delete  = optional_param('delete', 0, PARAM_INT);
    $sort = optional_param('sort', 'name', PARAM_ALPHANUM);
    $suspend      = optional_param('suspend', 0, PARAM_INT);
    $unsuspend    = optional_param('unsuspend', 0, PARAM_INT);
    $sitecontext = context_system::instance();
    $site = get_site();
    $deletereturnurl = new moodle_url('/admin/tool/uploaduser/index.php', array('reloadcsv' => 1));

    //global $DB, $CFG, $sitecontext, $site, $confirm;
    if ($delete and confirm_sesskey()) {              // Delete a selected user, after confirmation
        require_capability('moodle/user:delete', $sitecontext);

        $user = $DB->get_record('user', array('id' => $delete, 'mnethostid' => $CFG->mnet_localhost_id), '*', MUST_EXIST);

        if (is_siteadmin($user->id)) {
            print_error('useradminodelete', 'error');
        }

        if ($confirm != md5($delete)) {
            $PAGE->set_url(new moodle_url('/admin/tool/uploaduser/index.php', array('delete' => $delete, 'sesskey' => sesskey())));
            $PAGE->set_context(context_system::instance());
            echo $OUTPUT->header();
            $fullname = fullname($user, true);
            echo $OUTPUT->heading(get_string('deleteuser', 'admin'));
            $optionsyes = array('delete' => $delete, 'confirm' => md5($delete), 'sesskey' => sesskey());
            echo $OUTPUT->confirm(get_string('deletecheckfull', '', "'$fullname'"), new moodle_url($deletereturnurl, $optionsyes), $deletereturnurl);
            echo $OUTPUT->footer();
            die;
        } else if (data_submitted() and !$user->deleted) {
            if (delete_user($user)) {
                send_deactivate_user_email($user);
                \core\session\manager::gc(); // Remove stale sessions.
                redirect($deletereturnurl);
            } else {
                \core\session\manager::gc(); // Remove stale sessions.
                echo $OUTPUT->header();
                echo $OUTPUT->notification($deletereturnurl, get_string('deletednot', '', fullname($user, true)));
            }
        }
    } else if ($suspend and confirm_sesskey()) {
        require_capability('moodle/user:update', $sitecontext);

        if ($user = $DB->get_record('user', array('id' => $suspend, 'mnethostid' => $CFG->mnet_localhost_id, 'deleted' => 0))) {
            if (!is_siteadmin($user) and $USER->id != $user->id and $user->suspended != 1) {
                $user->suspended = 1;
                $user->suspendedtime = time();
                // Force logout.
                \core\session\manager::kill_user_sessions($user->id);
                user_update_user($user, false);
            }
        }
        redirect($deletereturnurl);
    } else if ($unsuspend and confirm_sesskey()) {
        require_capability('moodle/user:update', $sitecontext);

        if ($user = $DB->get_record('user', array('id' => $unsuspend, 'mnethostid' => $CFG->mnet_localhost_id, 'deleted' => 0))) {
            if ($user->suspended != 0) {
                $user->suspended = 0;
                user_update_user($user, false);
            }
        }
        redirect($deletereturnurl);
    }
}

// migrated from admin\tool\uploaduser\index_lib_background.php in 2.7
// used in that file in 2.7 as default script, and
// lib/classes/task/resend_user_activation_email_task.php in 2.7
function updateUserForSentEmail($user) {
	global $DB;
	$dataobject = new \stdClass();
	$dataobject->id = $user->id;
	$dataobject->issentactivationemail = 1;
	$DB->update_record('user', $dataobject);
}

/**
 * migrated from admin\tool\uploaduser\index_lib_background.php in 2.7
 * used in that file in 2.7 as default script
 * can be for general use
 */
function writeLog($filename, $message) {
	$file = fopen($filename,"a");
	$message = date('[Y-m-d H:i:s]') . " " . $message . "\n";
	fwrite($file, $message);
	fclose($file);
}

/**
 * migrated from admin/tool/uploaduser/index_lib.php in 2.7
 * only used in admin/tool/uploaduser/index_lib.php 
 * setAllValidDBFields, getInvalidSWDUserlist
 * can be for general use
 */
function datetotime($date, $format = 'DD.MM.YYYY')
{

    switch ($format) {
        case 'YYYY-MM-DD':
            list($year, $month, $day) = explode('-', $date);
            break;
        case 'YYYY/MM/DD':
            list($year, $month, $day) = explode('/', $date);
            break;
        case 'YYYY.MM.DD':
            list($year, $month, $day) = explode('.', $date);
            break;
        case 'DD-MM-YYYY':
            list($day, $month, $year) = explode('-', $date);
            break;
        case 'DD/MM/YYYY':
            list($day, $month, $year) = explode('/', $date);
            break;
        case 'DD.MM.YYYY':
            list($day, $month, $year) = explode('.', $date);
            break;
        case 'MM-DD-YYYY';
            list($month, $day, $year) = explode('-', $date);
            break;
        case 'MM/DD/YYYY':
            list($month, $day, $year) = explode('/', $date);
            break;
        case 'MM.DD.YYYY':
            list($month, $day, $year) = explode('.', $date);
            break;
        default:
            list($year, $month, $day) = explode('-', $date);
    }

    return mktime(0, 0, 0, $month, $day, $year);
}

/**
 * migrated from admin/tool/uploaduser/index_lib.php in 2.7
 * only used in admin/tool/uploaduser/index_lib.php
 * getInvalidSWDUserlist
 * can be for general use
 */
function validateDateFormat($date)
{
    //$dt = DateTime::createFromFormat("Y-m-d", $date);
    $dt = DateTime::createFromFormat("d.m.y", $date);
    /*if($dt != false && !array_sum($dt->getLastErrors())) {
		echo "valid format $date ";
	} else {
		echo "invalid format $date ";
	}*/
    return $dt != false && !array_sum($dt->getLastErrors());
}