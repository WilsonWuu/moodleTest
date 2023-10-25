<?php

/**
 * This form is for user import
 * 
 * */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

class moodleform2 extends moodleform
{
	function definition()
	{
		// Should declare in child-class
	}

	function defineFromArray(&$mform, $form_items)
	{
		foreach ($form_items as $item) {
			switch ($item['type']) {
				case 'header':
				case 'editor':
					$mform->addElement($item['type'], $item['name'], $item['label']);
					break;
				case 'hidden':
					$mform->addElement($item['type'], $item['name'], $item['value']);
					break;
				case 'filemanager':
					$mform->addElement($item['type'], $item['name'], $item['label'], $item['attribute'], $item['options']);
					break;
				case 'select':
					$mform->addElement($item['type'], $item['name'], $item['label'], $item['options'], $item['attribute']);
					break;
				case 'static':
					$mform->addElement($item['type'], $item['name'], $item['label'], $item['value']);
					break;
				case 'html':
					$mform->addElement($item['type'], $item['content']);
					break;
				case 'action_button':
					$this->add_action_buttons($item['cancel'], $item['label']);
					break;
				case 'checkbox':
					$mform->addElement($item['type'], $item['name'], $item['label']);
					break;
				case 'text':
				case 'textarea':
				default:
					$mform->addElement($item['type'], $item['name'], $item['label'], $item['attribute']);
			}
			switch ($item['type']) {
				case 'hidden':
				case 'text':
				case 'textarea':
				case 'editor':
					$mform->setType($item['name'], PARAM_RAW);
					break;
			}
			if (isset($item['required']) && $item['required'] === true) {
				$mform->addRule($item['name'], get_string('required'), 'required');
			}
			if (isset($item['default'])) {
				$mform->setDefault($item['name'], $item['default']);
			}
		}
	}

	/**
	 * for elibrary 
	 * */
	function validateColumnLength(&$errors, $table_name, $data)
	{
		global $DB;

		$limits = $DB->get_records_sql("SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH FROM information_schema.columns WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table_name'");
		foreach ($data as $name => $value) {
			if (isset($limits[$name]) && ctype_digit($limits[$name]->character_maximum_length) && strlen($value) > $limits[$name]->character_maximum_length) {
				$errors[$name] = get_string('msg_string_over_length', 'elibrary', $limits[$name]->character_maximum_length);
			}
			if (isset($limits[$name]) && in_array(strtolower($limits[$name]->data_type), array('integer', 'int', 'smallint', 'tinyint', 'mediumint', 'bigint')) && !ctype_digit($value)) {
				$errors[$name] = get_string('msg_must_integer', 'elibrary');
			}
			if (isset($limits[$name]) && in_array(strtolower($limits[$name]->data_type), array('decimal', 'numeric', 'float', 'double'))) {
				if (!is_numeric($value)) {
					$errors[$name] = get_string('msg_must_numeric', 'elibrary');
				} else {
				}
			}
		}
	}
}
