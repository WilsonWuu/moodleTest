<?php

require_once($CFG->dirroot .'/local/videos/lib.php');

class block_localvideoresources extends block_base {

	var $LIMIT_ROW;
	
    public function init() {
		global $PAGE;
		$this->LIMIT_ROW = 3;
		//$PAGE->requires->js(new moodle_url('/blocks/resources/module.js'));
		$this->title   = get_string('videoresources', 'block_localvideoresources');
    }
    // The PHP tag and the curly bracket for the class definition 
    // will only be closed after there is another function added in the next section.
	public function get_content() {
		global $DB;
    if ($this->content !== null) {
      return $this->content;
    }
    $this->content         =  new stdClass();
	$this->content->text = $this->get_list_view();
	$this->content->footer = html_writer::link(new moodle_url('/local/videos/searchvideo.php'),get_string('morevideos','block_localvideoresources'));
    return $this->content;
  }
	private function get_list_view() {
		global $DB;
		$list = $this->get_video_list();
		$output = html_writer::start_tag('div');
		$output .= html_writer::start_tag('ul', array("class"=>"block_tree list")); 
		foreach ( $list as $row ) {		
			// get video thumbnail
			if ($file = get_file_by_course_module($row->id, 'image')) {
				$resource = $DB->get_record('resource', array('id'=>$row->instance), '*', MUST_EXIST);
				$context = context_module::instance($row->id); 
				$path = '/'.$context->id.'/mod_resource/content/'.$resource->revision.$file->get_filepath().$file->get_filename();
				$videoimageurl = moodle_url::make_file_url('/pluginfile.php', $path, false);
			} else {
				$videoimageurl = new moodle_url("/local/videos/readimagefile.php?id={$row->id}&sesskey=".sesskey());
			}		
			$output .= html_writer::start_tag('li', array('class'=>"block-navli"));
			$output .= html_writer::start_tag('div', array('class'=>"video-unit"));
			$output .= html_writer::start_tag('div', array('class'=>"video_img"));
			$output .= html_writer::link(new moodle_url('/local/videos/view.php', array('id'=>$row->id)),
				html_writer::tag('img', '', array('src'=>$videoimageurl, 'alt'=>$row->name, 'title'=>$row->name, 'style'=>'max-width: 100%;')),
				array('target'=>(empty($row->link) ? '_self' : '_blank')));
			$output .= html_writer::end_tag('div');
			$output .= html_writer::start_tag('div', array('class'=>"video_title"));
			$output .= html_writer::link(new moodle_url('/local/videos/view.php', array("id"=>$row->id)),$row->name, array('target'=>(empty($row->link) ? '_self' : '_blank')));
			$output .= html_writer::end_tag('div');
			$output .= html_writer::end_tag('li'); 
			$output .= html_writer::start_tag('li'); 
			$output .= html_writer::empty_tag('hr'); 
			$output .= html_writer::end_tag('li'); 
		}
		$output .= html_writer::end_tag('ul'); 
		$output .= html_writer::end_tag('div'); 
		return $output;
	}
	
	function get_video_list() {
		global $DB;
		$CONTEXT_MODULE = CONTEXT_MODULE;
		$SQL = "
			SELECT cm.id, cm.instance, r.name, r.link
			FROM mdl_course_modules cm, mdl_resource r, mdl_context c
			WHERE cm.instance = r.id
			AND cm.id = c.instanceid
			AND c.contextlevel = $CONTEXT_MODULE
			AND cm.visible = 1
			AND cm.course = 1
			AND r.isvideo = 1			
			ORDER BY video_ordering desc
			limit {$this->LIMIT_ROW};
		";  
		$list = $DB->get_records_sql($SQL);
		return $list;
	}
}
?>