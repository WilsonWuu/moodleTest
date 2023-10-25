<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

class core_ebook_renderer {
	
	public function start_layout() {
        return html_writer::start_tag('div', array('class'=>'mainebook'));
    }
	
    public function complete_layout() {
        return html_writer::end_tag('div');
    }

	public function user_subscribe($default_data = null){
		//empty_replace($default_data['author'], '');
		
		$output = '';
		
		$output .= '<div class="user_subscribe">';
		$output .= '<form method="POST">';		
		
		$output .= html_writer::tag('div', get_string('ebook_subscribe_description','local_ebook'), array('class'=>'description'));
		
		$output .= '<input type="submit" value="' . get_string('subscribe','local_ebook') . '" name="btn_booksubscribe" />';	
		
		$output .= '</form>';
		$output .= '</div>';	//contentblock
		
		return $output;
	}
	
	public function user_subscribe_processing() {
		$output = '';
		
		$output .= '<div class="user_subscribe">';
		
		$output .= html_writer::tag('div', get_string('ebook_subscribe_description_process','local_ebook'), array('class'=>'description'));
		
		$output .= '</div>';	//contentblock
		
		return $output;
	}
	
	public function user_subscribe_rejected() {
		$output = '';
		
		$output .= '<div class="user_subscribe">';
		
		$output .= html_writer::tag('div', get_string('ebook_subscribe_description_rejected','local_ebook'), array('class'=>'description'));
		
		$output .= '</div>';	//contentblock
		
		return $output;
	}
	
	public function user_subscribe_approved() {
		global $CFG;
		
		$output = '';
		
		$output .= '<div class="user_subscribe">';
		//$output .= '<form method="POST">';		
		
		$ebook_link = $CFG->wwwroot . '/ebook/index.php?bookdisplay=1';
		$output .= html_writer::tag('div', get_string('ebook_subscribe_description_approved','local_ebook', $ebook_link), array('class'=>'description'));
		
		//$output .= '<input type="submit" value="' . get_string('subscribe') . '" name="btn_booksubscribe" />';	
		
		//$output .= '</form>';
		$output .= '</div>';	//contentblock
		
		return $output;
	}
	
	public function view_subscribe_list($subscribe_list = null, $totalcount=0, $page=0, $perpage=10, $url="", $detail = false){
		global $OUTPUT, $CFG;
		$output = '';
		
		$output .= html_writer::tag('h2', get_string('search_result', 'elibrary'), array('class'=>'floatleft'));
			
		$output .= html_writer::table_paging_header($totalcount, $page, $perpage, $url);
		
		$fullnamedisplay = get_string('firstname') . ' / ' . get_string('lastname');

		$table = new html_table();
		$table->classes = array('logtable','generaltable');
		$table->align = array('left', 'left', 'center', 'center', 'center');
		$table->head = array(
			$fullnamedisplay,
			get_string('email'),		
			get_string('dateofapplication'),
			get_string('status'),
			'',
		);
		
		$table->data = array();
		
		$OUTPUT->paging_bar_data_loop($subscribe_list, $totalcount, $page, $perpage, function($data) use (&$table, $detail){
			global $CFG;
			
			$table->data[] = array(
				fullname($data, true),
				$data->email,
				date('Y-m-d',$data->subscribedate),
				$data->status,
				'<a href="' . $CFG->wwwroot . '/ebook/subscribe_management.php?approve=' . $data->id . '">' . get_string('approve') . '</a> / ' .
				'<a href="' . $CFG->wwwroot . '/ebook/subscribe_management.php?reject=' . $data->id . '">' . get_string('reject') . '</a> ' 
			);
		});

		$output .= html_writer::table($table);
		$output .= $OUTPUT->paging_bar($totalcount, $page, $perpage, "$url");
		
		$output = html_writer::tag('div', $output, array('class' => 'subscribe_list'));
		
        return $output;
    }
	
	public function view_ebook() {
		global $CFG;
		$temptimezone = date_default_timezone_get();
		date_default_timezone_set('UTC');
		$sign = "{$CFG->EBOOK_UID}\$hkshfls\$".date('YmdH:i');
		date_default_timezone_set($temptimezone);
		$sign = DESEncrypt($CFG->EBOOK_ENKEY, $sign);
		$sign = $sign;
		$errorurl =(new moodle_url($CFG->EBOOK_BASEURL.'login_fail.php'))->__toString();
		$output = '';		
		$output .= '<div class="view_ebook">';				
		$output .= '<div class="introduction publicmarginbottom2">' . get_string('ebook_view_introduction','local_ebook') . '</div>';
		$output .= "<form autocomplete=\'off\' method='GET' action='{$CFG->EBOOK_URL}' target='_blank' class='content-center maxwidth1050'>";
		$output .= "<input type='hidden' name='pid' value='sso'>";
		$output .= "<input type='hidden' name='uid' value='{$CFG->EBOOK_UID}'>";
		$output .= "<input type='hidden' name='pwd' value='" . strtoupper(md5($CFG->EBOOK_PWD)) . "'>";
		$output .= "<input type='hidden' name='sign' value='$sign'>";
		$output .= "<input type='hidden' name='errorurl' value='$errorurl'>";
		$output .= "<input type='submit' value='" . get_string('view') . "'/>";			
		$output .= '</form>';
		$output .= '</div>';		
		return $output;
	}
	
}