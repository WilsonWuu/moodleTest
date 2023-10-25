<?php
// copied from lib/moodlelib.php in moodle 3.9, referring the same file location in 2.7 which had been hardcoded

require_once("$CFG->dirroot/innoverz/lib.php");

/**
 * Send an email to a specified user
 *
 * @param stdClass $user  A {@link $USER} object
 * @param stdClass $from A {@link $USER} object
 * @param string $subject plain text subject line of the email
 * @param string $messagetext plain text version of the message
 * @param string $messagehtml complete html version of the message (optional)
 * @param string $attachment a file on the filesystem, relative to $CFG->dataroot
 * @param string $attachname the name of the file (extension indicates MIME)
 * @param bool $usetrueaddress determines whether $from email address should
 *          be sent out. Will be overruled by user profile setting for maildisplay
 * @param string $replyto Email address to reply to
 * @param string $replytoname Name of reply to recipient
 * @param int $wordwrapwidth custom word wrap width, default 79
 * @parma string $triggername name of email trigger
 * @parma object $extra
 *                       object attribute : send_from_user (boolean) (default: false)
 *                                          contextid (int)
 *                                          recipientid (int)
 *                                          recipient_cc (string)
 *                                          recipient_bcc (string)
 *                       
 * @return bool Returns true if mail was sent OK and false if there was an error.
 */
function email_to_user_innoverz($user, $from, $subject, $messagetext, $messagehtml = '', $attachment = '', $attachname = '',
                       $usetrueaddress = true, $replyto = '', $replytoname = '', $wordwrapwidth = 79, $triggername = 'default', $extra = null) {

    global $CFG, $USER, $DB;

    if (empty($user) or empty($user->id)) {
        debugging('Can not send email to null user', DEBUG_DEVELOPER);
        return false;
    }

    if (empty($user->email)) {
        debugging('Can not send email to user without email: '.$user->id, DEBUG_DEVELOPER);
        return false;
    }

    if (!empty($user->deleted)) {
        debugging('Can not send email to deleted user: '.$user->id, DEBUG_DEVELOPER);
        return false;
    }

    if (defined('BEHAT_SITE_RUNNING')) {
        // Fake email sending in behat.
        return true;
    }

    if (!empty($CFG->noemailever)) {
        // Hidden setting for development sites, set in config.php if needed.
        debugging('Not sending email due to $CFG->noemailever config setting', DEBUG_NORMAL);
        return true;
    }

    if (!empty($CFG->divertallemailsto)) {
        $subject = "[DIVERTED {$user->email}] $subject";
        $user = clone($user);
        $user->email = $CFG->divertallemailsto;
    }

    // Skip mail to suspended users.
    if ((isset($user->auth) && $user->auth=='nologin') or (isset($user->suspended) && $user->suspended)) {
        return true;
    }

    if (!validate_email($user->email)) {
        // We can not send emails to invalid addresses - it might create security issue or confuse the mailer.
        $invalidemail = "User $user->id (".fullname($user).") email ($user->email) is invalid! Not sending.";
        error_log($invalidemail);
        if (CLI_SCRIPT) {
            mtrace('Error: lib/moodlelib.php email_to_user(): '.$invalidemail);
        }
        return false;
    }

    if (over_bounce_threshold($user)) {
        $bouncemsg = "User $user->id (".fullname($user).") is over bounce threshold! Not sending.";
        error_log($bouncemsg);
        if (CLI_SCRIPT) {
            mtrace('Error: lib/moodlelib.php email_to_user(): '.$bouncemsg);
        }
        return false;
    }

    // If the user is a remote mnet user, parse the email text for URL to the
    // wwwroot and modify the url to direct the user's browser to login at their
    // home site (identity provider - idp) before hitting the link itself.
    if (is_mnet_remote_user($user)) {
        require_once($CFG->dirroot.'/mnet/lib.php');

        $jumpurl = mnet_get_idp_jump_url($user);
        $callback = partial('mnet_sso_apply_indirection', $jumpurl);

        $messagetext = preg_replace_callback("%($CFG->wwwroot[^[:space:]]*)%",
                $callback,
                $messagetext);
        $messagehtml = preg_replace_callback("%href=[\"'`]($CFG->wwwroot[\w_:\?=#&@/;.~-]*)[\"'`]%",
                $callback,
                $messagehtml);
    }
    $mail = get_mailer();

    if (!empty($mail->SMTPDebug)) {
        echo '<pre>' . "\n";
    }

    $temprecipients = array();
    $tempreplyto = array();

    $supportuser = core_user::get_support_user();

    // Make up an email address for handling bounces.
    if (!empty($CFG->handlebounces)) {
        $modargs = 'B'.base64_encode(pack('V', $user->id)).substr(md5($user->email), 0, 16);
        $mail->Sender = generate_email_processing_address(0, $modargs);
    } else {
        $mail->Sender = $supportuser->email;
    }

    if (!empty($CFG->emailonlyfromnoreplyaddress)) {
        $usetrueaddress = false;
        if (empty($replyto) && $from->maildisplay) {
            $replyto = $from->email;
            $replytoname = fullname($from);
        }
    }

    if (is_string($from)) { // So we can pass whatever we want if there is need.
        $mail->From     = $CFG->noreplyaddress;
        $mail->FromName = $from;
    } else if ($usetrueaddress and $from->maildisplay) {
        $mail->From     = $from->email;
        $mail->FromName = fullname($from);
    } else {
        $mail->From     = $CFG->noreplyaddress;
        $mail->FromName = fullname($from);
        if (empty($replyto)) {
            $tempreplyto[] = array($CFG->noreplyaddress, get_string('noreplyname'));
        }
    }

    if (!empty($replyto)) {
        $tempreplyto[] = array($replyto, $replytoname);
    }

    $mail->Subject = substr($subject, 0, 900);

    $temprecipients[] = array($user->email, fullname($user));

    // Set word wrap.
    $mail->WordWrap = $wordwrapwidth;

    if (!empty($from->customheaders)) {
        // Add custom headers.
        if (is_array($from->customheaders)) {
            foreach ($from->customheaders as $customheader) {
                $mail->addCustomHeader($customheader);
            }
        } else {
            $mail->addCustomHeader($from->customheaders);
        }
    }

    if (!empty($from->priority)) {
        $mail->Priority = $from->priority;
    }

    if ($messagehtml && !empty($user->mailformat) && $user->mailformat == 1) {
        // Don't ever send HTML to users who don't want it.
        $mail->isHTML(true);
        $mail->Encoding = 'quoted-printable';
        $mail->Body    =  $messagehtml;
        $mail->AltBody =  "\n$messagetext\n";
    } else {
        $mail->IsHTML(false);
        $mail->Body =  "\n$messagetext\n";
    }

    if ($attachment && $attachname) {
        if (preg_match( "~\\.\\.~" , $attachment )) {
            // Security check for ".." in dir path.
            $temprecipients[] = array($supportuser->email, fullname($supportuser, true));
            $mail->addStringAttachment('Error in attachment.  User attempted to attach a filename with a unsafe name.', 'error.txt', '8bit', 'text/plain');
        } else {
            require_once($CFG->libdir.'/filelib.php');
            $mimetype = mimeinfo('type', $attachname);
            $mail->addAttachment($CFG->dataroot .'/'. $attachment, $attachname, 'base64', $mimetype);
        }
    }

    // Check if the email should be sent in an other charset then the default UTF-8.
    if ((!empty($CFG->sitemailcharset) || !empty($CFG->allowusermailcharset))) {

        // Use the defined site mail charset or eventually the one preferred by the recipient.
        $charset = $CFG->sitemailcharset;
        if (!empty($CFG->allowusermailcharset)) {
            if ($useremailcharset = get_user_preferences('mailcharset', '0', $user->id)) {
                $charset = $useremailcharset;
            }
        }

        // Convert all the necessary strings if the charset is supported.
        $charsets = get_list_of_charsets();
        unset($charsets['UTF-8']);
        if (in_array($charset, $charsets)) {
            $mail->CharSet  = $charset;
            $mail->FromName = core_text::convert($mail->FromName, 'utf-8', strtolower($charset));
            $mail->Subject  = core_text::convert($mail->Subject, 'utf-8', strtolower($charset));
            $mail->Body     = core_text::convert($mail->Body, 'utf-8', strtolower($charset));
            $mail->AltBody  = core_text::convert($mail->AltBody, 'utf-8', strtolower($charset));

            foreach ($temprecipients as $key => $values) {
                $temprecipients[$key][1] = core_text::convert($values[1], 'utf-8', strtolower($charset));
            }
            foreach ($tempreplyto as $key => $values) {
                $tempreplyto[$key][1] = core_text::convert($values[1], 'utf-8', strtolower($charset));
            }
        }
    }

    foreach ($temprecipients as $values) {
        $mail->addAddress($values[0], $values[1]);
    }
    foreach ($tempreplyto as $values) {
        $mail->addReplyTo($values[0], $values[1]);
    }

	$mail_send_result = false;
	$blocked_by_email_trigger = false;

	$email_triggers = $DB->get_records_sql("SELECT code, enable FROM mdl_email_trigger ORDER BY id");
	foreach($email_triggers as &$row){
		$row = ($row->enable == 1) ? true : false;
	}

	if(array_key_exists($triggername, $email_triggers) && $email_triggers[$triggername] === false){
		$blocked_by_email_trigger = true;
		$mail->ErrorInfo = 'blocked by email trigger';
	}else{
		if ($mail->send()) {
			set_send_count($user);
			if (!empty($mail->SMTPDebug)) {
				echo '</pre>';
			}
			$mail_send_result = true;
		} else {
			// Trigger event for failing to send email.
			$event = \core\event\email_failed::create(array(
				'context' => context_system::instance(),
				'userid' => $from->id,
				'relateduserid' => $user->id,
				'other' => array(
					'subject' => $subject,
					'message' => $messagetext,
					'errorinfo' => $mail->ErrorInfo
				)
			));
			$event->trigger();
			if (CLI_SCRIPT) {
				mtrace('Error: lib/moodlelib.php email_to_user(): '.$mail->ErrorInfo);
			}
			if (!empty($mail->SMTPDebug)) {
				echo '</pre>';
			}
		}
    }

	$object = new \stdClass();
	$object->senderid = ($extra != null && isset($extra->send_from_user) && $extra->send_from_user) ? $USER->id : 0;
	$object->recipientid = ($extra != null && isset($extra->recipientid)) ? $extra->recipientid : 0;
	$object->title = $subject;
	$object->content = $messagetext;
	$object->attachment = $attachment;
	$object->recipient = $user->email;
	$object->recipient_cc = ($extra != null && isset($extra->recipient_cc)) ? $extra->recipient_cc : '';
	$object->recipient_bcc = ($extra != null && isset($extra->recipient_bcc)) ? $extra->recipient_bcc : '';
	$object->triggername = $triggername;
	$object->contextid = ($extra != null && isset($extra->contextid)) ? $extra->contextid : 0;
	$object->contextids = ($extra != null && isset($extra->contextids)) ? $extra->contextids : '';
	$object->result = ($mail_send_result) ? 'success' : $mail->ErrorInfo;
	
	record_email_log($object);
	$mail_send_result = $blocked_by_email_trigger ? true : $mail_send_result;
	return $mail_send_result;
}

function record_email_log($object){
	global $DB;
	
	$object = filter_object_key($object, array('senderid', 'recipientid', 'title', 'content', 'attachment', 'recipient', 'recipient_cc', 'recipient_bcc', 'triggername', 'contextid', 'contextids', 'result'));
	empty_replace_array($object, '');
	$object->addtime = time();
	$DB->insert_record('email_log', $object);
}


/**
 * Send email to specified user with confirmation text and activation link.
 *
 * @param stdClass $user A {@link $USER} object
 * @param string $confirmationurl user confirmation URL
 * @return bool Returns true if mail was sent OK and false if there was an error.
 */
function send_confirmation_email_innoverz($user, $confirmationurl = null) {
    global $CFG;

    $site = get_site();
    $supportuser = core_user::get_support_user();

    $data = new \stdClass();
    $data->firstname = fullname($user);
    $data->sitename  = format_string($site->fullname);
    $data->admin     = generate_email_signoff();

    $subject = get_string('emailconfirmationsubject', '', format_string($site->fullname));

    if (empty($confirmationurl)) {
        $confirmationurl = '/login/confirm.php';
    }

    $confirmationurl = new moodle_url($confirmationurl);
    // Remove data parameter just in case it was included in the confirmation so we can add it manually later.
    $confirmationurl->remove_params('data');
    $confirmationpath = $confirmationurl->out(false);

    // We need to custom encode the username to include trailing dots in the link.
    // Because of this custom encoding we can't use moodle_url directly.
    // Determine if a query string is present in the confirmation url.
    $hasquerystring = strpos($confirmationpath, '?') !== false;
    // Perform normal url encoding of the username first.
    $username = urlencode($user->username);
    // Prevent problems with trailing dots not being included as part of link in some mail clients.
    $username = str_replace('.', '%2E', $username);

    $data->link = $confirmationpath . ( $hasquerystring ? '&' : '?') . 'data='. $user->secret .'/'. $username;

    $message     = get_string('emailconfirmation', '', $data);
    $messagehtml = text_to_html(get_string('emailconfirmation', '', $data), false, false, true);

    // Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
    return email_to_user_innoverz($user, $supportuser, $subject, $message, $messagehtml, null, null, null, null, null, null, 'register_user_email_confirm');
}

/**
 * Sends a password change confirmation email.
 *
 * @param stdClass $user A {@link $USER} object
 * @param stdClass $resetrecord An object tracking metadata regarding password reset request
 * @return bool Returns true if mail was sent OK and false if there was an error.
 */
function send_password_change_confirmation_email_innoverz($user, $resetrecord) {
    global $CFG;

    $site = get_site();
    $supportuser = core_user::get_support_user();
    $pwresetmins = isset($CFG->pwresettime) ? floor($CFG->pwresettime / MINSECS) : 30;

    $data = new \stdClass();
    $data->firstname = $user->firstname;
    $data->lastname  = $user->lastname;
    $data->username  = $user->username;
    $data->sitename  = format_string($site->fullname);
    $data->link      = $CFG->wwwroot .'/login/forgot_password.php?token='. $resetrecord->token;
    $data->admin     = generate_email_signoff();
    $data->resetminutes = $pwresetmins;

    $message = get_string('emailresetconfirmation', '', $data);
    $subject = get_string('emailresetconfirmationsubject', '', format_string($site->fullname));

    // Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
    return email_to_user_innoverz($user, $supportuser, $subject, $message, null, null, null, null, null, null, null, 'reset_password_confirm');

}

function empty_replace_array(&$array, $replace){
	foreach($array as &$each){
		empty_replace($each, '');
	}
}

function filter_array_key($array, $filter_keys){
	return array_intersect_key($array, array_flip($filter_keys));
}

function filter_object_key($object, $filter_keys){
	return (object)filter_array_key(get_object_vars($object), $filter_keys);
}