<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.'/innoverz/lib.php');
require_once($CFG->dirroot.'/local/elibrary/lib/forms2lib.php');

class resource_newedit_form extends moodleform2 {
    /**
     * The form definition
     */
    function definition () {
        global $CFG, $USER, $OUTPUT, $PAGE;
        $mform = $this->_form;
		$filemanageroptions = $this->_customdata['filemanageroptions'];
		
		empty_replace($_GET['id'], 0);
		
		$array = array(
			array(
				'type' => 'header',
				'name' => 'resource_information',
				'label' => get_string('resource_information', 'local_elibrary')
			),
			array(
				'type' => 'hidden',
				'name' => 'id',
				'value' => $_GET['id']
			),
			array(
				'type' => 'text',
				'name' => 'title',
				'label' => get_string('title', 'local_elibrary'),
				'attribute' => 'size="20"',
				'required' => true
			),
			array(
				'type' => 'static',
				'name' => 'currentpicture',
				'label' => get_string('currentpicture'),
				'value' => ''
			),
			array(
				'type' => 'filemanager',
				'name' => 'coverimage',
				'label' => get_string('cover_image','local_elibrary'),
				'attribute' => '',
				'options' => $filemanageroptions
			),
			array(
				'type' => 'select',
				'name' => 'classid',
				'label' => get_string('class', 'local_elibrary'),
				'options' => get_library_selector_data('class'),
				'attribute' => '',
				'required' => true
			),
			array(
				'type' => 'text',
				'name' => 'series',
				'label' => get_string('series', 'local_elibrary'),
				'attribute' => 'size="20"'
			),
			array(
				'type' => 'select',
				'name' => 'subjectid',
				'label' => get_string('subject', 'local_elibrary'),
				'options' => get_library_selector_data('subject'),
				'attribute' => '',
				'required' => true
			),
			array(
				 'type' => 'text',
				 'name' => 'publisher',
				 'label' => get_string('publisher', 'local_elibrary'),
				 'attribute' => 'size="20"'
			),
			array(
				'type' => 'text',
				'name' => 'publishyear',
				'label' => get_string('year_of_publication', 'local_elibrary'),
				'attribute' => 'size="20"'
			),
			array(
				'type' => 'text',
				'name' => 'publishcountry',
				'label' => get_string('publish_country', 'local_elibrary'),
				'attribute' => 'size="20"'
			),
			array(
				'type' => 'text',
				'name' => 'publishtype',
				'label' => get_string('publish_type', 'local_elibrary'),
				'attribute' => 'size="20"'
			),
			array(
				'type' => 'text',
				'name' => 'author',
				'label' => get_string('author', 'local_elibrary'),
				'attribute' => 'size="20"'
			),
			array(
				'type' => 'text',
				'name' => 'edition',
				'label' => get_string('edition', 'local_elibrary'),
				'attribute' => 'size="20"'
			),
			// array(
				// 'type' => 'text',
				// 'name' => 'supply',
				// 'label' => get_string('supply', 'local_elibrary'),
				// 'attribute' => 'size="20"'
			// ),
			array(
				'type' => 'text',
				'name' => 'isbn',
				'label' => get_string('isbn', 'local_elibrary'),
				'attribute' => 'size="20"'
			),
			array(
				'type' => 'editor',
				'name' => 'description',
				'label' => get_string('simple_description', 'local_elibrary'),
			),
			array(
				'type' => 'textarea',
				'name' => 'remark',
				'label' => get_string('remark', 'local_elibrary'),
				'attribute' => 'rows="5" cols="50"'
			),
			// array(
				// 'type' => 'text',
				// 'name' => 'frequency',
				// 'label' => get_string('frequency', 'local_elibrary'),
				// 'attribute' => 'size="20"'
			// ),
			array(
				'type' => 'text',
				'name' => 'language',
				'label' => get_string('language', 'local_elibrary'),
				'attribute' => 'size="20"'
			),
			array(
				'type' => 'text',
				'name' => 'nopage',
				'label' => get_string('no_page', 'local_elibrary'),
				'attribute' => 'size="20"'
			),
			array(
				'type' => 'select',
				'name' => 'currencyid',
				'label' => get_string('currency', 'local_elibrary'),
				'options' => get_library_selector_data('currency'),
				'attribute' => '',
				'required' => true
			),
			array(
				'type' => 'text',
				'name' => 'cost',
				'label' => get_string('cost', 'local_elibrary'),
				'attribute' => 'size="5"'
			),
			/* Hide By Jimmy
			array( 
				'type' => 'static',
				'name' => 'costhk',
				'label' => get_string('cost_hkd', 'local_elibrary'),
				'value' => '$0'
			),
			*/
			array(
				'type' => 'action_button',
				'label' => get_string((empty($_GET['id']) ? 'new_resource' : 'edit_resource'), 'local_elibrary'),
				'cancel' => false
			),
			array(
				'type' => 'html',
				'content' => '<a name="copy"></a>'
			)
		);
		
		if(!empty($_GET['id'])){
			$array[] = array(
				'type' => 'header',
				'name' => 'manage_copy',
				'label' => get_string('manage_copy', 'local_elibrary')
			);
		}
		
		$this->defineFromArray($mform, $array);
		
		$renderer = $PAGE->get_renderer('theme_innoverz','core_elibrary');
		
		if(!empty($_GET['id']) && isset($this->_customdata['resource_copy_list'])){
			$mform->addElement('html', $renderer->view_resource_copy_list($this->_customdata['resource_copy_list']));
		}
		
		if(isset($this->_customdata['resource_info'])){
			$fields = array(
				'title', 'classid', 'series', 'subjectid', 'coverimage',
				'publisher', 'publishyear', 'publishcountry', 'publishtype',
				'author', 'edition', 'supply', 'isbn',
				'description', 'remark', 'frequency', 'language', 'nopage', 'currencyid', 'cost', 'costhk'
			);
			
			foreach($fields as $field){
				switch($field){
					case 'description':
						$mform->setDefault($field, array('text' => $this->_customdata['resource_info']->$field));
						break;
					case 'costhk':
						$mform->setDefault($field, '$' . $this->_customdata['resource_info']->$field);
						break;
					case 'coverimage':
						$draftitemid = file_get_submitted_draft_itemid('coverimage');
						//file_prepare_draft_area($draftitemid, $context->id, 'mod_glossary', 'attachment', $entry->id, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 50));
					default:
						$mform->setDefault($field, $this->_customdata['resource_info']->$field);
				}
			}
		}else{
			$mform->setDefault('cost', 0);
		}

    }
	
    function definition_after_data(){
		global $DB, $CFG;
        $mform = $this->_form;
		
		if ($resourceid = $mform->getElementValue('id')) {
            $resource = $DB->get_record('library_resource', array('id' => $resourceid));
        } else {
            $resource = false;
        }
		 // Print picture.
         if ($resource) {
             $context = context_system::instance();
             $fs = get_file_storage();
             $hasuploadedpicture = ($fs->file_exists($context->id, 'resource', 'icon', $resource->id, '/', 'f2.png') || $fs->file_exists($context->id, 'resource', 'icon', $resource->id, '/', 'f2.jpg'));
			 if (!empty($resource->coverimage) && $hasuploadedpicture) {
				$moodleurl = get_library_resource_image_url($resource->id, $resource->coverimage);
                $imagevalue = '<img class="coverimage" alt="Cover image" src="'.$moodleurl.'">';
             } else {
                 $imagevalue = '<img class="coverimage" alt="Cover image" src="'.new moodle_url($CFG->LIBRARY_BASEURL.'coverimage.gif').'">';
             }
         } else {
                $imagevalue = '<img class="coverimage" alt="Cover image" src="'.new moodle_url($CFG->LIBRARY_BASEURL.'coverimage.gif').'">';
         }
         $imageelement = $mform->getElement('currentpicture');
         $imageelement->setValue($imagevalue);

		 /*
         if ($resource && $mform->elementExists('deletepicture') && !$hasuploadedpicture) {
             $mform->removeElement('deletepicture');
         }
		 */
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
		
		$data['description'] = $data['description']['text'];
		
		$this->validateColumnLength($errors, 'mdl_library_resource', $data);
		
        return $errors;
    }

}
