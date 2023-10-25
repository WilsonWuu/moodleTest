<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot.$CFG->LIBRARY_BASEURL.'lib/forms2lib.php');

class external_borrower_form extends moodleform2 {
    /**
     * The form definition
     */
    function definition () {
        global $CFG, $USER, $OUTPUT, $PAGE;
        $mform = $this->_form;
		
		$array = array(
			array(
				'type' => 'header',
				'name' => 'contactinfoforngo',
				'label' => get_string('contactinfoforngo', 'local_elibrary')
			),
			array(
				'type' => 'text',
				'name' => 'contact_person',
				'label' => get_string('contact_person', 'local_elibrary'),
				'attribute' => 'size="20"',
				'required' => false
			),
			array(
				'type' => 'text',
				'name' => 'contact_number',
				'label' => get_string('contact_number', 'local_elibrary'),
				'attribute' => 'size="20"',
				'required' => false
			),
			array(
				'type' => 'text',
				'name' => 'contact_email',
				'label' => get_string('contact_email', 'local_elibrary'),
				'attribute' => 'size="20"',
				'required' => false
			),
			array(
				'type' => 'action_button',
				'label' => get_string('button_next', 'local_elibrary'),
				'cancel' => false
			),
			array(
				'type' => 'hidden',
				'name' => 'userid',
				'value' => $this->_customdata['userid']
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
