<?php

require_once("$CFG->dirroot/$CFG->admin/tool/uploaduser/user_form.php");

/**
 * refer from admin\tool\uploaduser\user_form.php admin_uploaduser_form1
 * used in local\admintool\uploaduser\index.php
 */
class admin_uploaduser_form1_innoverz extends moodleform
{
    function definition()
    {
        $mform = $this->_form;

        $mform->addElement('header', 'settingsheader', get_string('upload'));

        $url = new moodle_url('example.csv');
        $link = html_writer::link($url, 'example.csv');
        $mform->addElement('static', 'examplecsv', get_string('examplecsv', 'tool_uploaduser'), $link);
        $mform->addHelpButton('examplecsv', 'examplecsv', 'tool_uploaduser');

        $mform->addElement('filepicker', 'userfile', get_string('file'));
        $mform->addRule('userfile', null, 'required');

        $choices = csv_import_reader::get_delimiter_list();
        $mform->addElement('select', 'delimiter_name', get_string('csvdelimiter', 'tool_uploaduser'), $choices);
        if (array_key_exists('cfg', $choices)) {
            $mform->setDefault('delimiter_name', 'cfg');
        } else if (get_string('listsep', 'langconfig') == ';') {
            $mform->setDefault('delimiter_name', 'semicolon');
        } else {
            $mform->setDefault('delimiter_name', 'comma');
        }

        $choices = core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'tool_uploaduser'), $choices);
        $mform->setDefault('encoding', 'UTF-8');

        if (isset($this->_customdata['insertrows'])) {
            $mform->addElement('hidden', 'insertrows', $this->_customdata['insertrows']);
            $mform->setType('insertrows', PARAM_INT);
        } else {
            $choices = array('10' => 10, '20' => 20, '100' => 100, '1000' => 1000, '100000' => 100000);
            $mform->addElement('select', 'previewrows', get_string('rowpreviewnum', 'tool_uploaduser'), $choices);
            $mform->setType('previewrows', PARAM_INT);
        }

        $this->add_action_buttons(false, get_string('uploadusers', 'tool_uploaduser'));
    }
}
