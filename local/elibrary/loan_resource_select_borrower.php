<?php

//  Display the elibrary page.

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.$CFG->LIBRARY_BASEURL.'select_borrower_form.php');
require_once($CFG->dirroot.$CFG->LIBRARY_BASEURL.'select_non_borrower_form.php');
require_once($CFG->dirroot.$CFG->LIBRARY_BASEURL.'lib.php');
require_once($CFG->libdir.'/adminlib.php');

$page = optional_param('page', 0, PARAM_INT);
$_SERVER['QUERY_STRING'] = clean_param( $_SERVER['QUERY_STRING'], PARAM_TEXT);

require_login();
$PAGE->set_context(context_system::instance());
require_capability('local/elibrary:loanresource', context_system::instance());

$PAGE->set_url(new moodle_url($CFG->LIBRARY_BASEURL.'loan_resource_select_borrower.php?' . $_SERVER['QUERY_STRING']));
$PAGE->set_pagelayout('standard');
//$PAGE->navbar->add(get_string('library','local_elibrary'));
//$PAGE->navbar->add(get_string('loan_resource','local_elibrary'));
$PAGE->set_title(get_string('library','local_elibrary'));
$PAGE->set_heading(get_string('library','local_elibrary'));
//admin_externalpage_setup('elibraryloanresource');

$mform = new select_borrower_form(null, null, 'get');
$mform_non_borrower = new select_non_borrower_form(null, null, 'get');

$data = $mform->get_data();
$data_non_borrower = $mform_non_borrower->get_data();

if(!empty($data_non_borrower->contact_person) || !empty($data_non_borrower->contact_number) || !empty($data_non_borrower->contact_email) || !empty($data_non_borrower->borrower_id)){	//non e-learning centre user+
	if(!empty($data_non_borrower->borrower_id)){
		$user_number_of_loan = get_user_number_of_loan_by_borrowerid($data_non_borrower->borrower_id);
		if ($user_number_of_loan >= LIBRARY_LIBRARIAN_LOAN_QUOTA) {
			redirect($PAGE->url, get_string('msg_over_loan_quota', 'local_elibrary'));
			exit;
		}
	}
	$borrower = new stdClass();
	$borrower->contactperson = $data_non_borrower->contact_person;
	$borrower->contactnumber = $data_non_borrower->contact_number;
	$borrower->contactemail = $data_non_borrower->contact_email;
	$borrower->borrowerid = $data_non_borrower->borrower_id;
	$borrower = base64_encode(json_encode($borrower));
	redirect($CFG->wwwroot.$CFG->LIBRARY_BASEURL.'loan_resource.php?borrower=' . $borrower);
	exit;
	
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('loan_resource','local_elibrary'));
echo '<div class="loan_resource_select_borrower">';
$mform->display();



echo '<h3 style="text-align:left">' . get_string('borrower_list', 'local_elibrary') . '</h3>';

$username = isset($data->borrower_username) ? $data->borrower_username : '';
$firstname = isset($data->borrower_firstname) ? $data->borrower_firstname : '';
$lastname = isset($data->borrower_lastname) ? $data->borrower_lastname : '';
$chinesename = isset($data->borrower_chinesename) ? $data->borrower_chinesename : '';

if ($username == '' && $firstname == '' && $lastname == '' && $chinesename == '') {
	$user_data = array();
} else {
	$user_data = get_userdata($username, $firstname, $lastname, $chinesename);
}		
$renderer = $PAGE->get_renderer('theme_innoverz','core_elibrary');


foreach ($user_data as &$data) {
	$is_ngo = check_is_ngo_user($data->id);
	$user_number_of_loan = get_user_number_of_loan($data->id);
	$loan_quota = LIBRARY_SWD_GL_LOAN_QUOTA;
	if($is_ngo){
		$loan_quota = LIBRARY_NGO_GL_LOAN_QUOTA;
	}
	if($user_number_of_loan >= $loan_quota){
		$data->overloan = 'YES';
	}
	else{
		$data->overloan = 'NO';
	}
}

//print_r ($user_data);
	
$user_count = count($user_data);
$perpage = 10;
$url = 'loan_resource_select_borrower.php?' . $_SERVER['QUERY_STRING'];
		
echo $renderer->user_loan_resource_select_borrower($user_data, $user_count, $page, $perpage, $url);
	
	
/*
$result = get_userid_by_username($data->borrower_username);
if($result !== false){
	$is_ngo = check_is_ngo_user($result->id);
	$user_number_of_loan = get_user_number_of_loan($result->id);
	$loan_quota = LIBRARY_SWD_GL_LOAN_QUOTA;
	if($is_ngo){
		$loan_quota = LIBRARY_NGO_GL_LOAN_QUOTA;
	}
	if($user_number_of_loan >= $loan_quota){
		redirect($PAGE->url, get_string('msg_over_loan_quota', 'local_elibrary'));
			exit;
	}
	redirect($CFG->wwwroot.$CFG->LIBRARY_BASEURL.'loan_resource.php?userid=' . $result->id);
	exit;
}else{
	redirect($PAGE->url, get_string('msg_user_not_found', 'local_elibrary'));
	exit;
*/

/* echo "<br><hr><br>";
$mform_non_borrower->display(); */
echo '</div>';
echo $OUTPUT->footer();