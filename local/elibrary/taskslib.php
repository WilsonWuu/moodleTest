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


require_once(dirname(__FILE__) . '/../config.php');
require_once($CFG->dirroot.$CFG->LIBRARY_BASEURL.'lib.php');
require_once($CFG->dirroot.'/user/profile_lib.php');

class library_cancel_reservation_tasks{
	
	public function execute(){
		global $DB;
		$time = time();
		$time_before_10days = $time - (3600*24*10);
		
		$site = get_site();
		$sitename = format_string($site->fullname);
		$supportuser = core_user::get_support_user();
		$admin = generate_email_signoff();
		
		//Get 1st reserve of the queue, which is 
		$reserves = $DB->get_records_sql("
			SELECT *
			FROM mdl_library_reserve
			WHERE availabletime <= $time_before_10days AND availabletime IS NOT NULL AND isdone=0
            GROUP BY resourceid 
            HAVING MIN(requestdate)
		");
		foreach($reserves as $reserve){
			$reserve->isdone = "-1";
			$DB->update_record('library_reserve', $reserve);
			
			$user = $DB->get_record_sql("SELECT * FROM mdl_user WHERE id={$reserve->userid}");
			$resource = $DB->get_record_sql("SELECT title FROM mdl_library_resource WHERE id={$reserve->resourceid}");
			
			$data = new stdClass();
			$data->firstname = $user->firstname;
			$data->resourcename = $resource->title;
			$data->resourceurl = (new moodle_url($CFG->LIBRARY_BASEURL.'view_resource_detail.php', array('id'=>$reserve->resourceid)))->__toString();
			$data->sitename = $sitename;
			$data->admin = $admin;
				
			$subject = get_string('system_cancel_reservation_email_subject', 'local_elibrary', $data);
			$messagetext = get_string('system_cancel_reservation_email_message', 'local_elibrary', $data);
			$messagehtml = text_to_html($messagetext, false, false, true);
				
			//Send email
			$user->mailformat = 1;  // Always send HTML version as well.
			email_to_user($user, $supportuser, $subject, $messagetext, $messagehtml, null, null, null, null, null, null, 'system_cancel_reservation_email');
		}
		
		//Get again the 1st reserve of the queue, after cancel some reservation
		$reserves = $DB->get_records_sql("
			SELECT *
			FROM mdl_library_reserve
			WHERE (availabletime=0 OR availabletime IS NULL) AND isdone=0
            GROUP BY resourceid 
            HAVING MIN(requestdate);
		");
		foreach($reserves as $reserve){
			$reserve->availabletime = $time;
			$DB->update_record('library_reserve', $reserve);
			
			send_reserve_email($reserve);
		}
	}
	
}

class library_return_resource_reminder_tasks{
	
	public function execute(){
		global $DB, $CFG;
		
		$triggername = 'library_return_resource_reminder';
		$blocked_by_email_trigger = false;
		
		$email_triggers = $DB->get_records_sql("SELECT code, enable FROM mdl_email_trigger ORDER BY id");
		foreach($email_triggers as &$row){
			$row = ($row->enable == 1) ? true : false;
		}

		if(array_key_exists($triggername, $email_triggers) && $email_triggers[$triggername] === false){
			$blocked_by_email_trigger = true;
		}
		
		$time = time();
		$curdate = date('Y-m-d');
		$nextdate = date('Y-m-d', strtotime('+1 day'));
		
		// $curdate = '2016-06-06';
		// $nextdate = '2016-06-20';
		
		$site = get_site();
		$sitename = format_string($site->fullname);
		$supportuser = core_user::get_support_user();
		$admin = generate_email_signoff();
		
		//Get 1st reserve of the queue, which is 
		$loans = $DB->get_records_sql("
			SELECT loandate, returndate, contactperson, contactnumber, contactemail, islibrarian, mdl_user.id as userid, email as useremail, username, firstname, lastname, lastnamephonetic, firstnamephonetic, middlename, alternatename, title, mdl_library_resource.id as resourceid
			FROM mdl_library_loan
			LEFT JOIN mdl_user ON mdl_user.id = mdl_library_loan.userid
			LEFT JOIN mdl_library_copy ON mdl_library_copy.id = mdl_library_loan.copyid
			LEFT JOIN mdl_library_resource ON mdl_library_resource.id = mdl_library_copy.resourceid
			WHERE SUBDATE(FROM_UNIXTIME(returndate), INTERVAL 3 DAY) 
			BETWEEN STR_TO_DATE('$curdate', '%Y-%m-%d') AND STR_TO_DATE('$nextdate', '%Y-%m-%d')
			AND actualreturndate = 0
		");
		foreach($loans as $loan){
			
			if (!$loan->contactperson) {
				$loan->contactperson = 'Sir / Madam';
			}
			
			$data = new stdClass();
			if ($loan->islibrarian) {
				$data->firstname = $loan->contactperson ? $loan->contactperson : '';
			} else {
				$data->firstname = $loan->firstname;
			}		
			$data->resourcename = $loan->title;
			$data->resourceurl = (new moodle_url($CFG->LIBRARY_BASEURL.'user_loan_history.php'))->__toString();
			$data->sitename = $sitename;
			$data->admin = $admin;
			$data->duedate = date('Y-m-d', $loan->returndate);
			$data->loandate = date('Y-m-d', $loan->loandate);
				
			$subject = get_string('return_resource_reminder_email_subject', 'local_elibrary', $data);
			$messagetext = get_string('return_resource_reminder_email_message', 'local_elibrary', $data);
			$messagehtml = text_to_html($messagetext, false, false, true);
			
			$mail = get_mailer();
			$mail->Sender = $CFG->smtpuser;
			$mail->From     = $supportuser->email;
			$mail->FromName = fullname($supportuser);
			$mail->Subject = $subject;
			$mail->WordWrap = 79;
			$mail->isHTML(true);
			$mail->Encoding = 'quoted-printable';
			$mail->Body    =  $messagehtml;
			$mail->AltBody =  "\n" . strip_tags($messagehtml) . "\n";
			if ($loan->islibrarian) {
				$mail->addAddress($loan->contactemail, $loan->contactperson);
			} else {
				$mail->addAddress($loan->useremail, fullname($loan));
			}				
			$sas = get_role_users(get_user_role_id('sa'), context_system::instance());
			$recipient_cc = array();
			foreach ($sas as $sa){	
				$mail->AddBCC($sa->email, fullname($sa));
				$recipient_cc[] = $sa->email;
			}
			
			if ($blocked_by_email_trigger) {
				$mail_send_result = false;
				$mail->ErrorInfo = 'blocked by email trigger';		
			} else {
				$mail_send_result = $mail->send();
			}	
			
			$object = new stdClass();
			$object->senderid = 0;
			$object->recipientid = (!$loan->islibrarian) ? $loan->userid : 0;
			$object->title = $subject;
			$object->content = $messagetext;
			$object->attachment = '';
			$object->recipient = (!$loan->islibrarian) ? $loan->useremail : $loan->contactemail;
			$object->recipient_cc = '';
			$object->recipient_bcc = (count($recipient_cc)) ? implode(',', $recipient_cc) : '';
			$object->triggername = $triggername;
			$object->contextid = 0;
			$object->contextids =  '';
			$object->result = ($mail_send_result) ? 'success' : $mail->ErrorInfo;
			
			record_email_log($object);
				
		}
		
	}
	
}