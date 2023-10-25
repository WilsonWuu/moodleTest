<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/user/editlib.php');

class searchvideo_form extends moodleform {

	var $defaultdata;

	public function __construct($defaultdata) {
		parent::__construct();
		$this->defaultdata = $defaultdata;
	}
	
    function definition() {
		$mform = $this->_form;
        $default_data = $this->defaultdata;
		
		//empty_replace($default_data['title'], '');
		empty_replace($default_data['category'], '');
		$cats = get_sellector_all_categories();	
		
		$mform->addElement('header', 'headerchoosecat', get_string('headerchoosecat', 'local_videos'), '');
        $mform->addElement('select', 'category', get_string('category', 'local_videos'), $cats);
		$mform->setType('select', PARAM_RAW);
		if (!empty($default_data['category'])) {
			 $mform->setSelected();
		}

    }

    function definition_after_data(){
        $mform = $this->_form;
        //$mform->applyFilter('username', 'trim');
    }

    function validation($data, $files) {
        global $CFG, $DB;
        $errors = parent::validation($data, $files);
        return $errors;
    }

}
