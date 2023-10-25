<?php

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.$CFG->LIBRARY_BASEURL.'lib.php');
require_once($CFG->dirroot.'/course/lib.php');


$resourceid = required_param('id', PARAM_INT);

require_login();
$PAGE->set_context(context_system::instance());
require_capability('local/elibrary:viewresource', context_system::instance());

$PAGE->set_url(new moodle_url($CFG->LIBRARY_BASEURL.'view_resource_detail.php?' . $_SERVER['QUERY_STRING']));
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add(get_string('library','local_elibrary'), new moodle_url($CFG->LIBRARY_BASEURL.''));
$PAGE->navbar->add(get_string('resource_detail','local_elibrary'));
$PAGE->set_heading(get_string('library','local_elibrary'));

if(isset($_POST['action'])){
	switch($_POST['action']){
		case 'post_review':
			$result = add_library_resource_review($_POST['review_content'], $_POST['resource_id']);
			if($result['status'] == 'success'){
				redirect($CFG->wwwroot.$CFG->LIBRARY_BASEURL.'view_resource_detail.php?id=' . $_POST['resource_id']);
				exit;
			}else{
				echo $OUTPUT->header();
				echo $OUTPUT->notification($result['msg']);
				echo $OUTPUT->footer();
				exit;
			}
			break;
		case 'reserve_resource':
			$user_own_reserve_info = get_user_own_reserve_info($_POST['resource_id']);

			if($user_own_reserve_info != null){
				echo $OUTPUT->header();
				echo $OUTPUT->notification(get_string('msg_already_reserved_resource', 'local_elibrary'));
				echo $OUTPUT->footer();
				exit;
			}
			$result = add_reserve($_POST['resource_id']);
			if($result['status'] == 'success'){
				redirect($PAGE->url, get_string('msg_success_reserve', 'local_elibrary'));
				echo $OUTPUT->header();
				echo $OUTPUT->notification(get_string('msg_success_reserve', 'local_elibrary'));
				echo $OUTPUT->footer();
				exit;
			}else{
				echo $OUTPUT->header();
				echo $OUTPUT->notification($result['msg']);
				echo $OUTPUT->footer();
				exit;
			}
			break;
		case 'cancel_reserve_resource':
			$user_own_reserve_info = get_user_own_reserve_info($_POST['resource_id']);
			if($user_own_reserve_info == null){
				echo $OUTPUT->header();
				echo $OUTPUT->notification(get_string('msg_not_already_reserved_resource', 'local_elibrary'));
				echo $OUTPUT->footer();
				exit;
			}
			$result = delete_own_reserve($_POST['resource_id']);
			if($result['status'] == 'success'){
				redirect($PAGE->url, get_string('msg_success_cancel_reserve', 'local_elibrary'));
				echo $OUTPUT->header();
				echo $OUTPUT->notification(get_string('msg_success_cancel_reserve', 'local_elibrary'));
				echo $OUTPUT->footer();
				exit;
			}else{
				echo $OUTPUT->header();
				echo $OUTPUT->notification($result['msg']);
				echo $OUTPUT->footer();
				exit;
			}
			break;
		case 'share_resource':
			if(empty($_POST['email_address']) || !filter_var($_POST['email_address'], FILTER_VALIDATE_EMAIL)){
				redirect($CFG->wwwroot.$CFG->LIBRARY_BASEURL.'view_resource_detail.php?id=' . $resourceid, get_string('msg_enter_correct_email_address', 'local_elibrary'));
				exit;
			}
			
			$resource_data = get_library_resource_info($_POST['resource_id']);
			if(!$resource_data){
				redirect($CFG->wwwroot.$CFG->LIBRARY_BASEURL.'view_resource_detail.php?id=' . $resourceid, get_string('msg_unknown_error', 'local_elibrary'));
				exit;
			}
			
			$obj = new stdClass();
			$obj->recipient_name = $_POST['recipient_name'];
			$obj->message = nl2br($_POST['message']);
			$obj->firstname = $USER->firstname;
			$obj->lastname = $USER->lastname;
			$obj->url = html_writer::link(new moodle_url($CFG->LIBRARY_BASEURL.'view_resource_detail.php', array('id' => $resourceid)), new moodle_url($CFG->LIBRARY_BASEURL.'view_resource_detail.php', array('id' => $resourceid)));
			$obj->title = $resource_data->title;
			$obj->author = $resource_data->author;
			if(!empty($resource_data->coverimage)){
				$context = context_system::instance();
				$fs = get_file_storage();
				$hasuploadedpicture = ($fs->file_exists($context->id, 'resource', 'icon', $resource_data->id, '/', 'f2.png') || $fs->file_exists($context->id, 'resource', 'icon', $resource_data->id, '/', 'f2.jpg'));
				if (!empty($resource_data->coverimage) && $hasuploadedpicture) {
					$resource_data->coverimage = get_library_resource_image_url($resource_data->id, $resource_data->coverimage);
				} else {
					$resource_data->coverimage = new moodle_url($CFG->LIBRARY_BASEURL.'coverimage.gif');
				}
			}else{
				$resource_data->coverimage = new moodle_url($CFG->LIBRARY_BASEURL.'coverimage.gif');
			}
			$obj->coverimage = html_writer::tag('img', null, array('src' => $resource_data->coverimage));
			
			$subject = get_string('email_subject_share_resource', 'local_elibrary', $obj);
			$message = get_string('email_content_share_resource', 'local_elibrary', $obj);
			$supportuser = core_user::get_support_user();
			$mail = get_mailer();
			if (!empty($mail->SMTPDebug)) {
				echo '<pre>' . "\n";
			}
			$mail->Sender = $CFG->smtpuser;
			$mail->From     = $supportuser->email;
			$mail->FromName = fullname($supportuser);
			$mail->Subject = substr($subject, 0, 900);
			$mail->WordWrap = 79;
			$mail->isHTML(true);
			$mail->Encoding = 'quoted-printable';
			$mail->Body    =  $message;
			$mail->AltBody =  "\n" . strip_tags($message) . "\n";
			$mail->addAddress($_POST['email_address'], $_POST['recipient_name']);
			$mail_send_result = $mail->send();
			if (!empty($mail->SMTPDebug)) {
				echo '</pre>';
			}
			
			redirect($CFG->wwwroot.$CFG->LIBRARY_BASEURL.'view_resource_detail.php?id=' . $resourceid, get_string('msg_success_to_share_resource', 'local_elibrary'));
			break;
	}

}

if(isset($_GET['action'])){
	switch($_GET['action']){
		case 'hide_review':
			$result = hide_library_resource_review($_GET['reviewid']);
			if($result['status'] == 'success'){
				redirect($CFG->wwwroot.$CFG->LIBRARY_BASEURL.'view_resource_detail.php?id=' . $resourceid);
				exit;
			}else{
				echo $OUTPUT->header();
				echo $OUTPUT->notification($result['msg']);
				echo $OUTPUT->footer();
				exit;
			}
			break;
		case 'delete_review':
			$result = delete_library_resource_review($_GET['reviewid']);
			if($result['status'] == 'success'){
				redirect($CFG->wwwroot.$CFG->LIBRARY_BASEURL.'view_resource_detail.php?id=' . $resourceid);
				exit;
			}else{
				echo $OUTPUT->header();
				echo $OUTPUT->notification($result['msg']);
				echo $OUTPUT->footer();
				exit;
			}
			break;
	}
}


$resource_info = get_library_resource_info($resourceid);

if(!$resource_info){
	redirect(new moodle_url($CFG->LIBRARY_BASEURL.'search_resource.php'), get_string('msg_resource_not_found', 'local_elibrary'));
}

$resource_copy_list = get_library_resource_copy_list($resourceid, $resource_info);
$resource_review_list = get_library_resource_review_list($resourceid);
$reserve_queue_count = get_reserve_queue_count($resourceid);

$PAGE->set_title(get_string('title_view_resource_detail','local_elibrary', $resource_info->title) . ' - ' . get_string('library','local_elibrary'));
$renderer = $PAGE->get_renderer('theme_innoverz','core_elibrary');

echo $OUTPUT->header();
//echo $OUTPUT->heading(get_string('resource_detail','local_elibrary'));
echo $renderer->start_layout();

echo $renderer->view_resource_detail_info($resource_info);
echo $renderer->view_resource_detail_copy_list($resource_copy_list);
if($USER->id > 1){
	echo $renderer->view_resource_detail_reserve($resourceid, $reserve_queue_count, $resource_copy_list);
}
echo $renderer->view_resource_detail_review_list($resource_review_list);
echo $renderer->view_resource_detail_share($resourceid);

echo $renderer->complete_layout();
echo $OUTPUT->footer();