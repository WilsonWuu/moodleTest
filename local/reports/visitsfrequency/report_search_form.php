<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');

/**
 * The form for handling editing a course.
 */
class report_search_form extends moodleform {
    protected $course;
    protected $context;

    /**
     * Form definition.
     */
    function definition() {
        global $CFG, $PAGE;

        $mform    = $this->_form;
		$mform->setDisableShortforms(true);

       // $categories      = $this->_customdata['categories'];
	   $mform->addElement('header', 'searchreport', get_string('searchreport','local_reports'), '');

	   $radioarray=array();
	   $reporttypes = array(get_string('daily','local_reports'), get_string('monthly','local_reports'), get_string('yearly','local_reports'));
	   
		for ($i=0; $i<count($reporttypes); $i++) {
			$radioarray[] =& $mform->createElement('radio', 'reporttype', '', $reporttypes[$i], $i);
		}
		$mform->addGroup($radioarray, 'reporttype', get_string('reporttype','local_reports'), ' ', false);
		$mform->setDefault('reporttype', -1);
		$mform->addRule('reporttype', get_string('missingreporttype','local_reports'), 'required', null, 'client');

        $mform->addElement('date_selector', 'startdate', get_string('from'));
        $mform->setDefault('startdate', time() + 3600 * 24);

        $mform->addElement('date_selector', 'enddate', get_string('to'));
        $mform->setDefault('enddate', time() + 3600 * 24 * 30);
		
		$roles = get_all_roles();
		$selvalues = array(0=>get_string('missingreporttype','local_reports'),'anonymous'=>get_string('anonymous','local_reports'));
		foreach ($roles as $role) {
			$selvalues[$role->id] = $role->name;
		}
		$mform->addElement('select', 'userrole', get_string('userrole','local_reports'), $selvalues);
		$mform->addRule('userrole',null, 'required', null, 'server');
		
		$this->add_action_buttons(false, get_string('search'));
		//$mform->addElement('submit', 'reportsearchformsubmit', get_string('search'));
    }

    function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);
		
		if (!$data['userrole']) {
			$errors['userrole'] = get_string('missinguserrole','local_reports');
		}

        return $errors;
    }
	
	function get_data() {
		$isprintfriendly = optional_param('printfriendlyreport', 0, PARAM_INT);
		if ($isprintfriendly) {
			global $PAGE;
			$PAGE->requires->css(new moodle_url('/theme/clean/style/print.css'));
			$PAGE->requires->css(new moodle_url('/theme/clean/style/printreport.css'));
			$data = new stdclass;
			$data->reporttype = required_param('reporttype', PARAM_INT);
			$data->startdate = required_param('startdate', PARAM_INT);
			$data->enddate = required_param('enddate', PARAM_INT);
			$data->userrole = required_param('userrole', PARAM_INT);
			return $data;
		} else {
			return parent::get_data();		
		}
	}
}

