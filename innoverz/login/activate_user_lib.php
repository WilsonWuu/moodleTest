<?php
	
	//temp function for testing
	function get_user($id) {
		global $DB;
		$table = 'user';
		$result = $DB->get_record($table,array('id' => $id));
		return count($result) ? $result : 0;
	}
	
	function verify_access($secret_key) {
		global $DB;
		$verdata = explode('/', $secret_key);
		$table = 'user';
		$select = "secret = {$verdata[0]} AND username = '{$verdata[1]}'"; //is put into the where clause
		$result = $DB->get_record($table,array('secret' => $verdata[0] , 'username' => $verdata[1]));

		if (empty($result)){
			return 0;
		} else{
			return $result;
		}

		return 0;
	}
	
	function is_activated_user($secret_key) {
		global $DB;
		$verdata = explode('/', $secret_key);
		$table = 'user';
		return $DB->record_exists($table,array('secret' => $verdata[0] , 'username' => $verdata[1], 'isactivate'=>1));
	}

	 function activateuser_form() {
        global $CFG;

        require_once($CFG->dirroot.'/innoverz/login/activate_user_form.php');
        return new Activate_user_form(null, null, 'post', '', array('autocomplete'=>'on'));
	}

	function get_security_question_list($list = array()) {
		global $DB;
		$table = "security_question";
		$result = $DB->get_records($table);
		//$list = array();
		foreach($result as $value) {
			$list[$value->id] = $value->question;
		}
		return $list;
	}
	
	function activateUser($user) {
		global $DB;
		$table = "user";
		$user->password = hash_internal_user_password($user->password);
		return $DB->update_record($table, $user);
	}
	
	function getServiceSettings() {
		$servicesettings = array(get_string('servicesettingmss', 'local_admintool'), get_string('servicesettingos', 'local_admintool'), get_string('servicesettingfcws', 'local_admintool'),
			get_string('servicesettinges', 'local_admintool'), get_string('servicesettingrs', 'local_admintool'), get_string('servicesettingcygws', 'local_admintool'),
			get_string('servicesettingsss', 'local_admintool'), get_string('servicesettingm', 'local_admintool'), get_string('servicesettinga', 'local_admintool'),
			get_string('servicesettingss', 'local_admintool'), get_string('servicesettingo', 'local_admintool'));
		return $servicesettings;
	}

?>