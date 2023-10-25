<?php

define('EBOOK_SUBSCRIBE_PROCCESS', 'PROCCESS');
define('EBOOK_SUBSCRIBE_APPROVE', 'APPROVED');
define('EBOOK_SUBSCRIBE_REJECT', 'REJECTED');

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

function getSubscriptionStatusList() {
	global $DB;
	$status_list = array();
	$records = $DB->get_records('ebook_subscribe_status');
	foreach ($records as $row) {
		$status_list[$row->id] = $row->code;
	}
	return $status_list;
}

function getMySubscription() {
	global $DB, $USER;
	$SQL = '
		SELECT * FROM mdl_ebook_subscribe
		WHERE userid = ?
		ORDER BY id DESC
		LIMIT 1';
	return $DB->get_record_sql($SQL, array($USER->id));
}

function getSubscription($id) {
	global $DB;
	return $DB->get_record('ebook_subscribe', array('id' => $id), '*', MUST_EXIST);
}

function createBookSubscription($userid) {
	global $DB;
	$status = $DB->get_record('ebook_subscribe_status', array('code'=>EBOOK_SUBSCRIBE_PROCCESS), 'id');
	$status = $status->id;
	$table = 'ebook_subscribe';
	$data = array('userid'=>$userid, 'status'=>$status, 'subscribedate'=>time());
	return $DB->insert_record($table, $data);
}

function getSubscriptionList() {
	global $DB;
	$lang = current_language();
	$SQL = "
		SELECT es.id, subscribedate, ess.description_$lang as status, firstname, lastname, middlename, alternatename, lastnamephonetic, firstnamephonetic, email FROM mdl_ebook_subscribe es
		LEFT JOIN mdl_ebook_subscribe_status ess
		ON ess.id = es.status
		LEFT JOIN mdl_user u
		ON u.id = es.userid
		ORDER BY id
	";
	return $DB->get_records_sql($SQL);
}

function approveSubscription($id) {
	updateSubscriptionStatus(EBOOK_SUBSCRIBE_APPROVE, $id);
}

function rejectSubscription($id) {
	updateSubscriptionStatus(EBOOK_SUBSCRIBE_REJECT, $id);
}

function updateSubscriptionStatus($status_code, $id) {
	global $DB;
	$status = $DB->get_record('ebook_subscribe_status', array('code'=>$status_code), 'id');
	$status = $status->id;
	$SQL = "
		UPDATE mdl_ebook_subscribe
		SET status = ?
		WHERE id = ?
	";
	$DB->execute($SQL, array($status, $id));
}

function send_subscribe_approved_email($user) {
    global $CFG;
    $site = get_site();
    $supportuser = core_user::get_support_user();

    $data = new stdClass();
    $data->firstname = fullname($user);
    $data->admin     = generate_email_signoff();

    $subject = get_string('ebook_subscribe_approved_email_subject', '', format_string($site->fullname));

    $user->mailformat = 1;  // Always send HTML version as well.
	
	$data->link  = $CFG->wwwroot .'/ebook/';
	$message     = get_string('ebook_subscribe_approved_email_content', '', $data);
	$messagehtml = text_to_html(get_string('ebook_subscribe_approved_email_content', '', $data), false, false, true);
	// Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
	return email_to_user($user, $supportuser, $subject, $message, $messagehtml, null, null, null, null, null, null, 'ebook_subscribe_handled_email');
}

function send_subscribe_rejected_email($user) {
    global $CFG;
    $site = get_site();
    $supportuser = core_user::get_support_user();

    $data = new stdClass();
    $data->firstname = fullname($user);
    $data->admin     = generate_email_signoff();

    $subject = get_string('ebook_subscribe_rejected_email_subject', '', format_string($site->fullname));

    $user->mailformat = 1;  // Always send HTML version as well.
	
	$data->link  = $CFG->wwwroot .'/ebook/';
	$message     = get_string('ebook_subscribe_rejected_email_content', '', $data);
	$messagehtml = text_to_html(get_string('ebook_subscribe_rejected_email_content', '', $data), false, false, true);
	// Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
	return email_to_user($user, $supportuser, $subject, $message, $messagehtml, null, null, null, null, null, null, 'ebook_subscribe_handled_email');
}

require_once($CFG->dirroot."/vendor/autoload.php");
 
function DESEncrypt($key, $encrypt) // $encrypt == plain text
{
    // 根據 PKCS#7 RFC 5652 Cryptographic Message Syntax (CMS) 修正 Message 加入 Padding
    $block = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_ECB);
    $pad = $block - (strlen($encrypt) % $block);
    $encrypt .= str_repeat(chr($pad), $pad);
 
    // 不需要設定 IV 進行加密
    $passcrypt = mcrypt_encrypt(MCRYPT_DES, $key, $encrypt, MCRYPT_MODE_ECB);
    return base64_encode($passcrypt);
}

?>