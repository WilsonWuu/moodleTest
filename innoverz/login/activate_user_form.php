<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot . '/user/editlib.php');

class Activate_user_form extends moodleform {
    function definition() {
        global $USER, $CFG, $my_user;
		
		empty_replace($my_user->firstname, '');
		empty_replace($my_user->username, '');
		empty_replace($my_user->id, 0);
		empty_replace($my_user->fullname, '');

        $mform = $this->_form;
		
		$mform->setDisableShortforms(true);

        $mform->addElement('header', 'acinfo', get_string('acinfo','local_admintool'), '');
		
		$mform->addElement('text', 'fullname', get_string('fullname'), 'maxlength="100" size="30" readonly="readonly"');
        $mform->setType('fullname', PARAM_RAW);
        //$mform->addRule('username', get_string('missingusername'), 'required', null, 'server');
		$mform->setDefault('fullname', $my_user->fullname);
		
		$mform->addElement('static', 'loginidpolicy', '', get_string('loginidpolicy','local_admintool'));

        $mform->addElement('text', 'username', get_string('username'), 'maxlength="100" size="12" readonly="readonly"');
        $mform->setType('username', PARAM_NOTAGS);
        //$mform->addRule('username', get_string('missingusername'), 'required', null, 'server');
		$mform->setDefault('username', $my_user->username);

        if (!empty($CFG->passwordpolicy)){
            $mform->addElement('static', 'passwordpolicyinfo', '', print_password_policy());
        }
        $mform->addElement('passwordunmask', 'password', get_string('password'), 'maxlength="32" size="12"');
        $mform->setType('password', PARAM_RAW);
        $mform->addRule('password', get_string('missingpassword'), 'required', null, 'server');
		
		$mform->addElement('static', 'activateuseremailpolicy', '', get_string('activateuseremailpolicy','local_admintool'));

        $mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="25"');
        $mform->setType('email', PARAM_NOTAGS);
        $mform->addRule('email', get_string('missingemail'), 'required', null, 'server');

        $mform->addElement('text', 'email2', get_string('emailagain'), 'maxlength="100" size="25"');
        $mform->setType('email2', PARAM_NOTAGS);
        $mform->addRule('email2', get_string('missingemail'), 'required', null, 'server');
		
		$radioarray=array();
		$servicesettings = getServiceSettings();
		for ($i=0; $i<count($servicesettings); $i++) {
			$radioarray[] =& $mform->createElement('radio', 'servicesetting', '', $servicesettings[$i], $i);
		}
		$mform->addGroup($radioarray, 'servicesetting', get_string('servicesetting','local_admintool'), '<br />', false);
        $mform->setDefault('servicesetting', -1);
		$mform->addRule('servicesetting', get_string('servicesettingerror','local_admintool'), 'required', null, 'server');
		/*$namefields = useredit_get_required_name_fields();
        foreach ($namefields as $field) {
            $mform->addElement('text', $field, get_string($field), 'maxlength="100" size="30"');
            $mform->setType($field, PARAM_TEXT);
            $stringid = 'missing' . $field;
            if (!get_string_manager()->string_exists($stringid, 'moodle')) {
                $stringid = 'required';
            }
            $mform->addRule($field, get_string($stringid), 'required', null, 'server');
        }*/
		
		/* $question[''] = get_string('selectsecurityquestion');
        $question = get_security_question_list($question);  */
        
        /*$default_question[''] = get_string('selectsecurityquestion');
        $question = array_merge($default_question, $question); */

        /* 
        $mform->addElement('select', 'securityquestionid', get_string('securityquestion'), $question);
		$mform->addRule('securityquestionid', get_string('missingselectquestion'), 'required', null, 'server');

        if( !empty($CFG->question) ){
            $mform->setDefault('securityquestionid', $CFG->question);
        }else{
            $mform->setDefault('securityquestionid', '');
        }
		
		$mform->addElement('text', 'securityanswer', get_string('securityanswer'), 'maxlength="100" size="25"');
        $mform->setType('securityanswer', PARAM_NOTAGS);
        $mform->addRule('securityanswer', get_string('missingsecurityanswer'), 'required', null, 'server'); */

        /*(if ($this->signup_captcha_enabled()) {
            $mform->addElement('recaptcha', 'recaptcha_element', get_string('recaptcha', 'auth'), array('https' => $CFG->loginhttps));
            $mform->addHelpButton('recaptcha_element', 'recaptcha', 'auth');
        }*/

        //profile_signup_fields($mform);

        if (!empty($CFG->sitepolicy)) {
            $mform->addElement('header', 'policyagreement', get_string('policyagreement'), '');
            $mform->setExpanded('policyagreement');
            $mform->addElement('static', 'policylink', '', '<a href="'.$CFG->sitepolicy.'" onclick="this.target=\'_blank\'">'.get_String('policyagreementclick').'</a>');
            $mform->addElement('checkbox', 'policyagreed', get_string('policyaccept'));
            $mform->addRule('policyagreed', get_string('policyagree'), 'required', null, 'server');
        }
		
		$mform->addElement('hidden','id',$my_user->id);
		$mform->setType('id', PARAM_INT);

        // buttons
        $this->add_action_buttons(true, get_string('activateaccount','local_admintool'));

    }

    function definition_after_data(){
        $mform = $this->_form;
        $mform->applyFilter('username', 'trim');
    }

    function validation($data, $files) {
        global $CFG, $DB;
        $errors = parent::validation($data, $files);

        $authplugin = get_auth_plugin($CFG->registerauth);

        //check if user exists in external db
        //TODO: maybe we should check all enabled plugins instead
        if ($authplugin->user_exists($data['username'])) {
            $errors['username'] = get_string('usernameexists');
        }


        if (! validate_email($data['email'])) {
            $errors['email'] = get_string('invalidemail');

        } else if ($DB->record_exists('user', array('email'=>$data['email']))) {
            $errors['email'] = get_string('emailexists').' <a href="forgot_password.php">'.get_string('newpassword').'?</a>';
        }
        if (empty($data['email2'])) {
            $errors['email2'] = get_string('missingemail');

        } else if ($data['email2'] != $data['email']) {
            $errors['email2'] = get_string('invalidemail');
        }
        if (!isset($errors['email'])) {
            if ($err = email_is_not_allowed($data['email'])) {
                $errors['email'] = $err;
            }

        }

        $errmsg = '';
        if (!check_password_policy($data['password'], $errmsg)) {
            $errors['password'] = $errmsg;
        }

        return $errors;

    }

    /**
     * Returns whether or not the captcha element is enabled, and the admin settings fulfil its requirements.
     * @return bool
     */
    /* function signup_captcha_enabled() {
        global $CFG;
        return !empty($CFG->recaptchapublickey) && !empty($CFG->recaptchaprivatekey) && get_config('auth/email', 'recaptcha');
    } */

}
