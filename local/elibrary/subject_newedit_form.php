<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot.'/innoverz/lib.php');
require_once($CFG->dirroot.'/local/elibrary/lib/forms2lib.php');

class subject_newedit_form extends moodleform2 {
    /**
     * The form definition
     */
    function definition () {
        global $CFG, $USER, $OUTPUT, $PAGE;
        $mform = $this->_form;
		
		empty_replace($_GET['id'], 0);
		
		$array = array(
			array(
				'type' => 'header',
				'name' => 'subject_information',
				'label' => get_string('subject_information', 'local_elibrary')
			),
			array(
				'type' => 'hidden',
				'name' => 'id',
				'value' => $_GET['id']
			),
			array(
				'type' => 'text',
				'name' => 'name_eng',
				'label' => get_string('subject_name_eng', 'local_elibrary'),
				'attribute' => 'size="20"',
				'required' => true
			),
			array(
				'type' => 'text',
				'name' => 'name_chi',
				'label' => get_string('subject_name_chi', 'local_elibrary'),
				'attribute' => 'size="20"',
				'required' => true
			),
			array(
				'type' => 'action_button',
				'label' => get_string((empty($_GET['id']) ? 'new_subject' : 'edit_subject'), 'local_elibrary'),
				'cancel' => false
			)
		);
		
		$this->defineFromArray($mform, $array);
		
		$renderer = $PAGE->get_renderer('theme_innoverz','core_elibrary');
		
		if(isset($this->_customdata['subject_info'])){
			$fields = array(
				'parent_id', 'name_eng', 'name_chi'
			);
			
			foreach($fields as $field){
				switch($field){
					default:
						$mform->setDefault($field, $this->_customdata['subject_info']->$field);
				}
			}
		}

    }

    /**
     * A bit of custom validation for this form
     *
     * @param array $data An assoc array of field=>value
     * @param array $files An array of files
     * @return array
     */
    function validation($data, $files) {
        global $DB, $CFG;

        $errors = parent::validation($data, $files);
		
		$this->validateColumnLength($errors, 'mdl_library_subject', $data);
		
        return $errors;
    }

}
