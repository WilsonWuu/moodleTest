<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

class local_interrai_renderer extends plugin_renderer_base {
	
	public function search_resource_search_bar($default_data = null){
		global $DB;
		
		empty_replace($default_data['category'], 0);
		
		$cats = get_sellector_all_categories();
	
		$output = '';
		
		$output .= '<div class="search_video">';
		$output .= '<form autocomplete="off" method="GET">';
		$output .= '<input type="hidden" name="issubmit" />';
		
		$output .= '<label>';
		$output .= '<span>' . get_string('category', 'local_videos') . ': </span>';
		$output .= '<select name="category" id="id_category">';
		$output .= '<option value="0">'.get_string('allcategory', 'local_interrai').'</option>';
		foreach($cats as $key=>$value){
			$output .= '<option value="' . $key . '"' . ($default_data['category'] == $key ? ' selected="selected"' : '') . '>' . $value . '</option>';
		}
		$output .= '</select>';
		$output .= '</label>';
		$output .= '</form>';
		$output .= '</div>';
		
		return $output;
	}

    public function view_resource_list($rs_list = null, $totalcount=0, $page=0, $perpage=10, $url="", $detail = false){
		global $OUTPUT, $CFG, $USER;
		$output = '';
		
		$output .= '<div class="info">';
		$output .= get_string("displayingrecords", "", $totalcount);
		$output .= '</div>';

		$output .= $OUTPUT->paging_bar($totalcount, $page, $perpage, "$url");

		$table = new html_table();
		$table->classes = array('logtable','generaltable');
		$table->align = array('center', 'left', 'right');
		$table->head = array(
				'',
				get_string('user'),
				'',			
		);	
		
		$table->data = array();
		
		$startrow = $page * $perpage;
		$endrow = $startrow + $perpage;
		$endrow = ($endrow > $totalcount) ? $totalcount : $endrow;

		$i = 0;
		foreach ($rs_list as $data) {
			$i++;
			if($i < $startrow || $i > $endrow){
				continue;
			}
			if ($data->modname == 'resource') {
				$detailhtml = '<a target="_blank" href="' . $CFG->wwwroot . $CFG->INTERRAI_BASEURL.'view.php?id=' . $data->id . '">' . $data->name . '</a>';
			} else {
				$detailhtml = '<a target="_blank" href="' . $CFG->wwwroot . '/local/folder/view.php?id=' . $data->id . '">' . $data->name . '</a>';
			}
			$detailhtml .= $data->detail;			
			
			$buttonedithtml = '';
			
			//if (is_siteadmin() || $USER->id == $data->userid) {
			if (has_capability('local/interrai:managefileresources', context_system::instance())) {
				$buttonedithtml = '<a href="' . $CFG->wwwroot . $CFG->INTERRAI_BASEURL.'editresource.php?update=' . $data->id . '">' . get_string('buttonedit', 'local_interrai') . '</a>';
				$buttonedithtml .= '&nbsp&nbsp&nbsp<a href="' . $CFG->wwwroot . $CFG->INTERRAI_BASEURL.'?delete=' . $data->id . '&sesskey='.sesskey().'">' . get_string('buttondelete', 'local_interrai') . '</a>';
			}
			
			$table->data[] = array(
					'<img src="' . $data->icon . '" alt="File icon"/>',
					$detailhtml,
					$buttonedithtml
				);
		}

		$output .= html_writer::table($table);
		$output .= $OUTPUT->paging_bar($totalcount, $page, $perpage, "$url");
        return $output;
    }
	
	public function start_layout() {
        return html_writer::start_tag('div', array('class'=>'mainresources'));
    }
	
    public function complete_layout() {
        return html_writer::end_tag('div');
    }
}