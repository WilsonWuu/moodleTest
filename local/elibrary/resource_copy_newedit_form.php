<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot.'/innoverz/lib.php');
require_once($CFG->dirroot.'/local/elibrary/lib/forms2lib.php');

class resource_copy_newedit_form extends moodleform2 {
    /**
     * The form definition
     */
    function definition () {
        global $CFG, $USER, $OUTPUT, $PAGE;
        $mform = $this->_form;
		
		empty_replace($_GET['id'], 0);
		
		$this->defineFromArray($mform, array(
			array(
				'type' => 'header',
				'name' => 'manage_copy',
				'label' => get_string('manage_copy', 'local_elibrary')
			),
			array(
				'type' => 'hidden',
				'name' => 'resourceid',
				'value' => 0
			),
			array(
				'type' => 'hidden',
				'name' => 'id',
				'value' => $_GET['id']
			),
			array(
				'type' => 'hidden',
				'name' => 'oldaccessno',
				'value' => 0
			),
			array(
				'type' => 'text',
				'name' => 'accessno',
				'label' => get_string('accession_number', 'local_elibrary'),
				'attribute' => 'size="10"',
				'required' => true
			),
			array(
				'type' => 'text',
				'name' => 'callno',
				'label' => get_string('call_number', 'local_elibrary'),
				'attribute' => 'size="10"',
				'required' => false
			),
			array(
				'type' => 'select',
				'name' => 'locateid',
				'label' => get_string('locate', 'local_elibrary'),
				'options' => get_library_selector_data('locate'),
				'attribute' => '',
				'required' => true
			),
			array(
				'type' => 'textarea',
				'name' => 'remark',
				'label' => get_string('remark', 'local_elibrary'),
				'attribute' => 'rows="5" cols="50"'
			),
			array(
				'type' => 'action_button',
				'label' => get_string((empty($_GET['id']) ? 'new_resource_copy' : 'edit_resource_copy'), 'local_elibrary'),
				'cancel' => false
			)
		));

		$renderer = $PAGE->get_renderer('theme_innoverz','core_elibrary');
	
		if(isset($this->_customdata['resource_copy_info'])){
			$fields = array('accessno', 'oldaccessno', 'resourceid', 'callno', 'locateid', 'remark');
			
			foreach($fields as $field){
				if(isset($this->_customdata['resource_copy_info']->$field)){
					$mform->setDefault($field, $this->_customdata['resource_copy_info']->$field);
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
		
		$this->validateColumnLength($errors, 'mdl_library_copy', $data);
		
		if($data['accessno'] != $data['oldaccessno'] && check_copy_accessno_exist($data['accessno'])){
			$errors['accessno'] = get_string('msg_accessno_already_used', 'local_elibrary');
		}
		
        return $errors;
    }

}
