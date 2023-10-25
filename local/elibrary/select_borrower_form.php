<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot.$CFG->LIBRARY_BASEURL.'lib/forms2lib.php');

class select_borrower_form extends moodleform2 {
    /**
     * The form definition
     */
    function definition () {
        global $CFG, $USER, $OUTPUT, $PAGE;
        $mform = $this->_form;
		
		$array = array(
			array(
				'type' => 'header',
				'name' => 'select_borrower',
				'label' => get_string('select_borrower', 'local_elibrary')
			),
			array(
				'type' => 'text',
				'name' => 'borrower_username',
				'label' => get_string('scan_borrower_card', 'local_elibrary'),
				'attribute' => 'size="20"',
				'required' => false
			),
			array(
				'type' => 'text',
				'name' => 'borrower_firstname',
				'label' => get_string('scan_firstname', 'local_elibrary'),
				'attribute' => 'size="20"',
				'required' => false
			),
			array(
				'type' => 'text',
				'name' => 'borrower_lastname',
				'label' => get_string('scan_lastname', 'local_elibrary'),
				'attribute' => 'size="20"',
				'required' => false
			),
			array(
				'type' => 'text',
				'name' => 'borrower_chinesename',
				'label' => get_string('scan_chinesename', 'local_elibrary'),
				'attribute' => 'size="20"',
				'required' => false
			),
			array(
				'type' => 'action_button',
				'name' => 'btn_searchuser',
				'label' => get_string('search', 'local_elibrary'),
				'cancel' => false
			)
		);
		
		$this->defineFromArray($mform, $array);
		
		$renderer = $PAGE->get_renderer('theme_innoverz','core_elibrary');

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
		
        return $errors;
    }

}
