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
		
		$this->add_action_buttons(false, get_string('search'));
		//$mform->addElement('submit', 'reportsearchformsubmit', get_string('search'));
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array the errors that were found
     */
    function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

       /* // Add field validation check for duplicate shortname.
        if ($course = $DB->get_record('course', array('shortname' => $data['shortname']), '*', IGNORE_MULTIPLE)) {
            if (empty($data['id']) || $course->id != $data['id']) {
                $errors['shortname'] = get_string('shortnametaken', '', $course->fullname);
            }
        }

        // Add field validation check for duplicate idnumber.
        if (!empty($data['idnumber']) && (empty($data['id']) || $this->course->idnumber != $data['idnumber'])) {
            if ($course = $DB->get_record('course', array('idnumber' => $data['idnumber']), '*', IGNORE_MULTIPLE)) {
                if (empty($data['id']) || $course->id != $data['id']) {
                    $errors['idnumber'] = get_string('courseidnumbertaken', 'error', $course->fullname);
                }
            }
        }

        $errors = array_merge($errors, enrol_course_edit_validation($data, $this->context));

        $courseformat = course_get_format((object)array('format' => $data['format']));
        $formaterrors = $courseformat->edit_form_validation($data, $files, $errors);
        if (!empty($formaterrors) && is_array($formaterrors)) {
            $errors = array_merge($errors, $formaterrors);
        }*/

        return $errors;
    }
	
	function get_data() {
		$isprintfriendly = optional_param('printfriendlyreport', 0, PARAM_INT);
		if ($isprintfriendly) {
			global $PAGE;
			$PAGE->requires->css(new moodle_url('/theme/innoverz/style/print.css'));
			$PAGE->requires->css(new moodle_url('/theme/innoverz/style/printreport.css'));
            $PAGE->requires->js(new moodle_url('/local/reportlearningresources/print.js'));
			$data = new stdclass;
			$data->reporttype = required_param('reporttype', PARAM_INT);
			$data->startdate = required_param('startdate', PARAM_INT);
			$data->enddate = required_param('enddate', PARAM_INT);
			return $data;
		} else {
			return parent::get_data();		
		}
	}
}

