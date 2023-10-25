<?php

namespace theme_innoverz\output\elibrary;

if (!defined('MOODLE_INTERNAL')) {
	die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

use moodle_url;
use context_system;
use html_table;
use html_table_cell;
use html_table_row;

require_once($CFG->dirroot . '/innoverz/lib.php');
require_once($CFG->dirroot . '/innoverz/lib/outputcomponents.php');


class renderer extends \plugin_renderer_base
{

	public function search_resource_search_bar($default_data = null, $by_accessno = false)
	{

		empty_replace($default_data['title'], '');
		empty_replace($default_data['author'], '');
		empty_replace($default_data['publisher'], '');
		empty_replace($default_data['subject'], 0);
		empty_replace($default_data['class'], 0);
		empty_replace($default_data['accessno'], '');

		$default_data['title'] = clean_param($default_data['title'], PARAM_TEXT);
		$default_data['author'] = clean_param($default_data['author'], PARAM_TEXT);
		$default_data['publisher'] = clean_param($default_data['publisher'], PARAM_TEXT);
		$default_data['subject'] = clean_param($default_data['subject'], PARAM_INT);
		$default_data['class'] = clean_param($default_data['class'], PARAM_INT);
		$default_data['accessno'] = clean_param($default_data['accessno'], PARAM_TEXT);

		$subjects = get_library_selector_data('subject');
		$classes = get_library_selector_data('class');
		$subjects_chi = get_library_selector_data('subject_chi');
		$classes_chi = get_library_selector_data('class_chi');

		$output = '';

		$output .= '<div class="search_resource contentblock">';
		//$output .= '<form method="GET" action="' . new moodle_url($CFG->LIBRARY_BASEURL.'search_resource.php') . '">';
		$output .= '<form method="GET">';
		$output .= '<input type="hidden" name="issubmit" />';
		$output .= '<input type="hidden" name="sesskey" value="' . sesskey() . '"/>';

		$output .= '<div class="contentsubbox">';

		$output .= \html_writer_innoverz::tag('h2', get_string('search_resource', 'local_elibrary'));

		$output .= '<div class="search_fields">';

		$output .= '<div class="search_fields_item">';
		$output .= \html_writer_innoverz::tag('label', get_string('title', 'local_elibrary'), array('for' => 'id_title'));
		$output .= '<input type="text" name="title" id="id_title" value="' . $default_data['title'] . '" />';
		$output .= '</div>';	//search_fields_item

		$output .= \html_writer_innoverz::tag('div', '', array('class' => 'search_fields_padding'));

		$output .= '<div class="search_fields_item">';
		$output .= \html_writer_innoverz::tag('label', get_string('subject', 'local_elibrary'), array('for' => 'id_subject'));
		$output .= '<select name="subject" id="id_subject">';
		$output .= '<option value="0">' . get_string('any_subject', 'local_elibrary') . '</option>';
		if (current_language() == 'en') {
			foreach ($subjects as $key => $value) {
				$output .= '<option value="' . $key . '"' . ($default_data['subject'] == $key ? ' selected="selected"' : '') . '>' . mb_convert_case($value, MB_CASE_TITLE, "UTF-8") . '</option>';
			}
		} else {
			foreach ($subjects_chi as $key => $value) {
				$output .= '<option value="' . $key . '"' . ($default_data['subject'] == $key ? ' selected="selected"' : '') . '>' . $value . '</option>';
			}
		}

		$output .= '</select>';
		$output .= '</div>';	//search_fields_item

		$output .= \html_writer_innoverz::tag('div', '', array('class' => 'clear'));

		$output .= '<div class="search_fields_item">';
		$output .= \html_writer_innoverz::tag('label', get_string('author', 'local_elibrary'), array('for' => 'id_author'));
		$output .= '<input type="text" name="author" id="id_author" value="' . $default_data['author'] . '" />';
		$output .= '</div>';	//search_fields_item

		$output .= \html_writer_innoverz::tag('div', '', array('class' => 'search_fields_padding'));

		$output .= '<div class="search_fields_item">';
		$output .= \html_writer_innoverz::tag('label', get_string('class', 'local_elibrary'), array('for' => 'id_class'));
		$output .= '<select name="class" id="id_class">';
		$output .= '<option value="0">' . get_string('any_class', 'local_elibrary') . '</option>';
		if (current_language() == 'en') {
			foreach ($classes as $key => $value) {
				$output .= '<option value="' . $key . '"' . ($default_data['class'] == $key ? ' selected="selected"' : '') . '>' . ucfirst($value) . '</option>';
			}
		} else {
			foreach ($classes_chi as $key => $value) {
				$output .= '<option value="' . $key . '"' . ($default_data['class'] == $key ? ' selected="selected"' : '') . '>' . $value . '</option>';
			}
		}

		$output .= '</select>';
		$output .= '</div>';	//search_fields_item

		$output .= \html_writer_innoverz::tag('div', '', array('class' => 'clear'));

		$output .= '<div class="search_fields_item">';
		$output .= \html_writer_innoverz::tag('label', get_string('publisher', 'local_elibrary'), array('for' => 'id_publisher'));
		$output .= '<input type="text" name="publisher" id="id_publisher" value="' . $default_data['publisher'] . '" />';
		$output .= '</div>';	//search_fields_item

		if ($by_accessno) {
			$output .= \html_writer_innoverz::tag('div', '', array('class' => 'search_fields_padding'));
			$output .= '<div class="search_fields_item">';
			$output .= \html_writer_innoverz::tag('label', get_string('accession_number', 'local_elibrary'), array('for' => 'id_accessno'));
			$output .= '<input type="text" name="accessno" id="id_accessno" value="' . $default_data['accessno'] . '" />';
			$output .= '</div>';	//search_fields_item
		}

		$output .= \html_writer_innoverz::tag('div', '', array('class' => 'clear'));

		$output .= '</div>';	//search_fields

		$output .= '</div>';	//contentsubbox

		$output .= '<div class="contentbottom">';
		$output .= '<input type="submit" value="' . get_string('search', 'local_elibrary') . '" />';
		$output .= '</div>';	//contentbottom



		$output .= '</form>';
		$output .= '</div>';	//contentblock

		return $output;
	}

	public function view_resource_list($resource_list = null, $totalcount = 0, $page = 0, $perpage = 10, $url = "", $detail = false)
	{
		global $OUTPUT, $CFG;
		$output = '';

		$output .= \html_writer_innoverz::tag('h2', get_string('search_result', 'local_elibrary'), array('class' => 'floatleft'));
		if ($detail && has_capability('local/elibrary:resourceadministration', context_system::instance())) {
			$output .= \html_writer_innoverz::new_record_widget(new moodle_url($CFG->LIBRARY_BASEURL . 'resource_new.php'), get_string('button_new_resource', 'local_elibrary'));
		}

		$output .= \html_writer_innoverz::table_paging_header($totalcount, $page, $perpage, $url);

		$table = new html_table();
		$table->classes = array('logtable', 'generaltable');
		$table->align = array('left', 'left', 'center', 'center', 'center', 'center', 'center', 'center');
		if (!$detail) {
			$table->head = array(
				get_string('title', 'local_elibrary'),
				get_string('author', 'local_elibrary'),
				get_string('publisher', 'local_elibrary'),
				get_string('subject', 'local_elibrary'),
				get_string('class', 'local_elibrary'),
				'',
			);
		} else {
			$table->head = array(
				get_string('title', 'local_elibrary'),
				get_string('class', 'local_elibrary'),
				get_string('subject', 'local_elibrary'),
				get_string('author', 'local_elibrary'),
				get_string('publisher', 'local_elibrary'),
				get_string('edition', 'local_elibrary'),
				//get_string('language', 'local_elibrary'),
				get_string('loan_status', 'local_elibrary'),
				get_string('number_of_reserve', 'local_elibrary'),
				'',
			);
		}

		$table->data = array();

		$OUTPUT->paging_bar_data_loop($resource_list, $totalcount, $page, $perpage, function ($data) use (&$table, $detail) {
			global $CFG;

			if (!$detail) {
				if (current_language() == 'en') {
					$table->data[] = array(
						mb_convert_case($data->title, MB_CASE_TITLE, "UTF-8"),
						mb_convert_case($data->author, MB_CASE_TITLE, "UTF-8"),
						mb_convert_case($data->publisher, MB_CASE_TITLE, "UTF-8"),
						mb_convert_case($data->subject_eng, MB_CASE_TITLE, "UTF-8"),
						mb_convert_case($data->class_eng, MB_CASE_TITLE, "UTF-8"),
						'<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'view_resource_detail.php?id=' . $data->id . '">' . get_string('button_view_resource', 'local_elibrary') . '</a>'
					);
				} else {
					$table->data[] = array(
						mb_convert_case($data->title, MB_CASE_TITLE, "UTF-8"),
						mb_convert_case($data->author, MB_CASE_TITLE, "UTF-8"),
						mb_convert_case($data->publisher, MB_CASE_TITLE, "UTF-8"),
						$data->subject_chi,
						$data->class_chi,
						'<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'view_resource_detail.php?id=' . $data->id . '">' . get_string('button_view_resource', 'local_elibrary') . '</a>'
					);
				}
			} else {
				if (current_language() == 'en') {
					$table->data[] = array(
						mb_convert_case($data->title, MB_CASE_TITLE, "UTF-8"),
						mb_convert_case($data->class_eng, MB_CASE_TITLE, "UTF-8"),
						mb_convert_case($data->subject_eng, MB_CASE_TITLE, "UTF-8"),
						$data->author,
						$data->publisher,
						$data->edition,
						//$data->language,
						(($data->availablecopy > 0) ? get_string('available', 'local_elibrary') : get_string('not_available', 'local_elibrary')),
						$data->reserving,
						'<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'resource_edit.php?id=' . $data->id . '">' . get_string('button_edit_resource', 'local_elibrary') . '</a> ' .
							'<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'resource_list.php?action=delete_resource&resourceid=' . $data->id . '&query_string=' . base64_encode($_SERVER['QUERY_STRING']) . '" onclick="return confirm(\'' . get_string('msg_confirm_delete_resource', 'local_elibrary') . '\')">' . get_string('button_delete_resource', 'local_elibrary') . '</a>'
					);
				} else {
					$table->data[] = array(
						mb_convert_case($data->title, MB_CASE_TITLE, "UTF-8"),
						$data->class_chi,
						$data->subject_chi,
						$data->author,
						$data->publisher,
						$data->edition,
						//$data->language,
						(($data->availablecopy > 0) ? get_string('available', 'local_elibrary') : get_string('not_available', 'local_elibrary')),
						$data->reserving,
						'<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'resource_edit.php?id=' . $data->id . '">' . get_string('button_edit_resource', 'local_elibrary') . '</a> ' .
							'<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'resource_list.php?action=delete_resource&resourceid=' . $data->id . '&query_string=' . base64_encode($_SERVER['QUERY_STRING']) . '" onclick="return confirm(\'' . get_string('msg_confirm_delete_resource', 'local_elibrary') . '\')">' . get_string('button_delete_resource', 'local_elibrary') . '</a>'
					);
				}
			}
		});

		$output .= '<div class="table_wrap">' . \html_writer_innoverz::table($table) . '</div>';
		$output .= $OUTPUT->paging_bar($totalcount, $page, $perpage, "$url");

		$output = \html_writer_innoverz::tag('div', $output, array('class' => 'resource_list'));

		return $output;
	}

	public function view_resource_removed_list($resource_list = null, $totalcount = 0, $page = 0, $perpage = 10, $url = "")
	{
		global $OUTPUT, $CFG;
		$output = '';

		$output .= \html_writer_innoverz::table_paging_header($totalcount, $page, $perpage, $url);

		$table = new html_table();
		$table->classes = array('logtable', 'generaltable');
		$table->align = array('left', 'center', 'center', 'center');
		$table->head = array(
			get_string('title', 'local_elibrary'),
			get_string('accession_number', 'local_elibrary'),
			get_string('deletedate', 'local_elibrary'),
			'',
		);

		$table->data = array();

		$OUTPUT->paging_bar_data_loop($resource_list, $totalcount, $page, $perpage, function ($data) use (&$table) {
			global $CFG;

			$table->data[] = array(
				$data->title,
				$data->accessnos,
				date('Y-m-d H:i', $data->deletetime),
				'<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'resource_removed_list.php?action=recovery_resource&resourceid=' . $data->id . '" onclick="return confirm(\'Do you confirm to recovery the removed item?\')">' . get_string('button_recovery_resource', 'local_elibrary') . '</a>'
			);
		});

		$output .= '<div class="table_wrap">' . \html_writer_innoverz::table($table) . '</div>';
		$output .= $OUTPUT->paging_bar($totalcount, $page, $perpage, "$url");

		$output = \html_writer_innoverz::tag('div', $output, array('class' => 'resource_list'));

		return $output;
	}

	public function view_resource_detail_info($resource_data)
	{
		global $OUTPUT, $CFG;

		if (!empty($resource_data->coverimage)) {
			$context = context_system::instance();
			$fs = get_file_storage();
			$hasuploadedpicture = ($fs->file_exists($context->id, 'resource', 'icon', $resource_data->id, '/', 'f2.png') || $fs->file_exists($context->id, 'resource', 'icon', $resource_data->id, '/', 'f2.jpg'));
			if (!empty($resource_data->coverimage) && $hasuploadedpicture) {
				$resource_data->coverimage = get_library_resource_image_url($resource_data->id, $resource_data->coverimage, 'f3');
			} else {
				$resource_data->coverimage = new moodle_url($CFG->LIBRARY_BASEURL . 'coverimage.gif');
			}
		} else {
			$resource_data->coverimage = new moodle_url($CFG->LIBRARY_BASEURL . 'coverimage.gif');
		}
		array_walk($resource_data, 'empty_replace', '&nbsp;');

		$output = '';

		$output .= \html_writer_innoverz::tag('h2', get_string('resource_detail', 'local_elibrary'));

		$output .= '<div class="contentblock">';
		$output .= '<div class="clearfix contentsubbox">';

		$output .= '<div class="coverimage tablecell">';
		$output .= '<img width="192" src="' . $resource_data->coverimage . '" alt="' . $resource_data->title . '" />';
		$output .= '</div>';

		$output .= '<div class="descriptionbox tablecell">';

		$output .= \html_writer_innoverz::tag('h3', mb_convert_case($resource_data->title, MB_CASE_TITLE, "UTF-8"));
		$output .= '<div class="userprofile">';
		$output .= \html_writer_innoverz::start_tag('dl', array('class' => 'list'));

		$output .= \html_writer_innoverz::tag('dt', get_string('author', 'local_elibrary'));
		$output .= \html_writer_innoverz::tag('dd', mb_convert_case($resource_data->author, MB_CASE_TITLE, "UTF-8"));

		$output .= \html_writer_innoverz::tag('dt', get_string('publisher', 'local_elibrary'));
		$output .= \html_writer_innoverz::tag('dd', mb_convert_case($resource_data->publisher, MB_CASE_TITLE, "UTF-8"));

		$output .= \html_writer_innoverz::tag('dt', get_string('subject', 'local_elibrary'));
		$output .= \html_writer_innoverz::tag('dd', mb_convert_case($resource_data->subject_eng, MB_CASE_TITLE, "UTF-8"));

		$output .= \html_writer_innoverz::tag('dt', get_string('class', 'local_elibrary'));
		$output .= \html_writer_innoverz::tag('dd', mb_convert_case($resource_data->class_eng, MB_CASE_TITLE, "UTF-8"));

		$output .= \html_writer_innoverz::tag('dt', get_string('series', 'local_elibrary'));
		$output .= \html_writer_innoverz::tag('dd', mb_convert_case($resource_data->series, MB_CASE_TITLE, "UTF-8"));

		$output .= \html_writer_innoverz::tag('dt', get_string('year_of_publication', 'local_elibrary'));
		$output .= \html_writer_innoverz::tag('dd', $resource_data->publishyear);

		//$output .= \html_writer_innoverz::tag('dt', get_string('publish_country', 'local_elibrary'));
		//$output .= \html_writer_innoverz::tag('dd', mb_convert_case($resource_data->publishcountry, MB_CASE_TITLE, "UTF-8"));

		//$output .= \html_writer_innoverz::tag('dt', get_string('publish_type', 'local_elibrary'));
		//$output .= \html_writer_innoverz::tag('dd', $resource_data->publishtype);

		$output .= \html_writer_innoverz::tag('dt', get_string('edition', 'local_elibrary'));
		$output .= \html_writer_innoverz::tag('dd', $resource_data->edition);

		$output .= \html_writer_innoverz::tag('dt', get_string('isbn', 'local_elibrary'));
		$output .= \html_writer_innoverz::tag('dd', $resource_data->isbn);

		//$output .= \html_writer_innoverz::tag('dt', get_string('simple_description', 'local_elibrary'));
		//$output .= \html_writer_innoverz::tag('dd', $resource_data->description);

		$output .= \html_writer_innoverz::tag('dt', get_string('remark', 'local_elibrary'));
		$output .= \html_writer_innoverz::tag('dd', $resource_data->remark);

		//$output .= \html_writer_innoverz::tag('dt', get_string('frequency', 'local_elibrary'));
		//$output .= \html_writer_innoverz::tag('dd', $resource_data->frequency);

		//$output .= \html_writer_innoverz::tag('dt', get_string('language', 'local_elibrary'));
		//$output .= \html_writer_innoverz::tag('dd', $resource_data->language);

		$output .= \html_writer_innoverz::end_tag('dl');

		$output .= "</div>"; //userprofile
		$output .= "</div>"; //descriptionbox
		$output .= "</div>"; //contentsubbox.
		$output .= "</div>"; //contentblock.

		$output = \html_writer_innoverz::tag('div', $output, array('class' => 'resource_detail_info'));

		return $output;
	}

	public function view_resource_detail_copy_list($resource_copy_list = null)
	{
		global $OUTPUT, $CFG;
		$output = '';

		$output .= \html_writer_innoverz::tag('h2', get_string('resource_copy', 'local_elibrary'));

		$output .= '<div class="contentblock">';
		$output .= '<div class="clearfix contentsubbox">';

		$mobilelayout = '<div class="mobile-table hidden">';

		$table = new html_table();
		$table->classes = array('logtable', 'generaltable');
		$table->align = array('left', 'center', 'center', 'center', 'center', 'center');
		$table->head = array(
			get_string('title', 'local_elibrary'),
			get_string('accession_number', 'local_elibrary'),
			get_string('call_number', 'local_elibrary'),
			get_string('locate', 'local_elibrary'),
			get_string('status', 'local_elibrary'),
			get_string('first_return_date', 'local_elibrary')
		);

		$table->data = array();

		foreach ($resource_copy_list as $data) {
			$HTML_status = get_string('available', 'local_elibrary');
			if (!empty($data->reserve_id)) {
				$HTML_status = get_string('reserved', 'local_elibrary');
			} elseif ($data->isloan == 1) {
				$HTML_status = get_string('on_loan', 'local_elibrary');
			}

			if (($data->returndate) == '') {
				$returndate = get_string('no_loan_record', 'local_elibrary');
				$reservation_time = 0;
			} else {
				$returndate = date('Y-m-d', $data->returndate);
				$reservation_time = (($data->returndate));
			}

			$table->data[] = array(
				mb_convert_case($data->title, MB_CASE_TITLE, "UTF-8"),
				$data->accessno,
				$data->callno,
				"{$data->locate_code} - {$data->locate_description}",
				$HTML_status,
				$returndate  //JIMMY
			);

			$mobilelayout .= '
				<div class="mobile-table-row">
					<div>
					<div class="mobile-table-cell half label">' . get_string('title', 'local_elibrary') . '</div>' .
				'<div class="mobile-table-cell half content">' . mb_convert_case($data->title, MB_CASE_TITLE, "UTF-8") . '</div>' .
				'</div>' .
				'<div>' .
				'<div class="mobile-table-cell half label">' . get_string('accession_number', 'local_elibrary') . '</div>' .
				'<div class="mobile-table-cell half content">' . $data->accessno . '</div>' .
				'</div>' .
				'<div>' .
				'<div class="mobile-table-cell half label">' . get_string('call_number', 'local_elibrary') . '</div>' .
				'<div class="mobile-table-cell half content">' . $data->callno . '</div>' .
				'</div>' .
				'<div>' .
				'<div class="mobile-table-cell half label">' . get_string('locate', 'local_elibrary') . '</div>' .
				'<div class="mobile-table-cell half content">' . "{$data->locate_code} - {$data->locate_description}" . '</div>' .
				'</div>' .
				'<div>' .
				'<div class="mobile-table-cell half label">' . get_string('status', 'local_elibrary') . '</div>' .
				'<div class="mobile-table-cell half content">' . $HTML_status . '</div>' .
				'</div>' .
				'<div>' .
				'<div class="mobile-table-cell half label">' . get_string('first_return_date', 'local_elibrary') . '</div>' .
				'<div class="mobile-table-cell half content">' . $returndate . '</div>' .
				'</div>
				</div>
			';
		}

		$output .= '<div class="table_wrap">' . \html_writer_innoverz::table($table) . '</div>';
		$output .= $mobilelayout . '</div>';
		$output .= '</div>';	//contentsubbox
		$output .= '</div>';	//contentblock

		$output = \html_writer_innoverz::tag('div', $output, array('class' => 'resource_detail_copy_list'));

		return $output;
	}

	public function view_resource_detail_reserve($resourceid, $reserve_queue_count = 0, $resource_copy_list = null)
	{
		global $OUTPUT, $USER;
		$output = '';

		$output .= \html_writer_innoverz::tag('h2', get_string('availableforloan', 'local_elibrary'));

		$output .= '<div class="contentblock">';
		$output .= '<form method="POST">';

		$user_own_reserve_info = get_user_own_reserve_info($resourceid);

		$output .= '<input type="hidden" name="resource_id" value="' . $resourceid . '" />';
		if ($user_own_reserve_info != null) {
			$output .= '<div class="clearfix contentsubbox">';
			$st_nd_rd = array('', 'st', 'nd', 'rd', 'th');
			$output .= get_string('you_reserving_this_resource', 'local_elibrary', date('Y-m-d', $user_own_reserve_info->requestdate)) . '<br>';
			$reserve_rank_in_queue = get_user_reserve_rank_in_queue($resourceid, $USER->id);
			$output .= get_string('your_reservation_queue_position', 'local_elibrary', ($reserve_rank_in_queue . $st_nd_rd[(($reserve_rank_in_queue > 4) ? 4 : $reserve_rank_in_queue)])) . '<br>';

			$copy_available_count = 0;
			if (is_array($resource_copy_list)) {
				foreach ($resource_copy_list as $copy) {
					if ($copy->isloan == 0) {
						$copy_available_count++;
					} else {
						$recent_return_date[] = $copy->returndate;
					}
				}
				if (isset($recent_return_date) && count($recent_return_date) >= 1) {
					$recent_return_date = min($recent_return_date);
				}
			}

			if (!is_array($resource_copy_list) || $copy_available_count === 0) {
				$output .= get_string('have_not_copy_available', 'local_elibrary', $copy_available_count) . '<br>';
			} else if ($reserve_rank_in_queue <= $copy_available_count) {
				$output .= get_string('have_copy_available', 'local_elibrary', $copy_available_count) . '<br>';
			} else if ($reserve_rank_in_queue == 1) {
				$output .= get_string('all_copy_loaned_out', 'local_elibrary', date('Y-m-d', $recent_return_date)) . '<br>';
			}
			$output .= '</div>';	//contentsubbox
			$output .= '<div class="contentbottom">';
			$output .= '<input type="hidden" name="action" value="cancel_reserve_resource" />';
			$output .= '<input type="submit" value="' . get_string('cancel_reserve_resource', 'local_elibrary') . '" onclick="return confirm(\'' . get_string('msg_confirm_cancel_reserve_resource', 'local_elibrary') . '\')" />';
			$output .= '</div>';	//contentbottom
		} else {
			$output .= '<div class="clearfix contentsubbox">';
			if ($reserve_queue_count <= 0) {
				$output .= get_string('is_not_reserving', 'local_elibrary');
			} elseif ($reserve_queue_count == 1) {
				$output .= get_string('reserving_one', 'local_elibrary', $reserve_queue_count);
			} else {
				$output .= get_string('reserving_more_than_one', 'local_elibrary', $reserve_queue_count);
			}
			$output .= '</div>';	//contentsubbox
			$output .= '<div class="contentbottom">';
			$output .= '<input type="hidden" name="action" value="reserve_resource" />';
			$output .= '<input type="submit" value="' . get_string('reserve_resource', 'local_elibrary') . '" onclick="return confirm(\'' . get_string('msg_confirm_reserve_resource', 'local_elibrary') . '\')" />';
			$output .= '</div>';	//contentbottom
		}
		$output .= '</form>';
		$output .= '</div>';	//contentblock
		$output = \html_writer_innoverz::tag('div', $output, array('class' => 'resource_detail_reserve'));

		return $output;
	}

	public function view_resource_detail_review_list($resource_review_list = null)
	{
		global $OUTPUT, $CFG;

		$resourceid = required_param('id', PARAM_INT);

		$output = '';

		$output .= \html_writer_innoverz::tag('h2', get_string('resource_review', 'local_elibrary'));
		$output .= '<div class="contentblock">';
		$output .= '<div class="clearfix contentsubbox">';

		if ($resource_review_list) {

			$mobilelayout = '<div class="mobile-table hidden">';

			$table = new html_table();
			$table->attributes['class'] = 'logtable generaltable';
			$table->align = array('left', 'center', 'center', 'center');
			$table->head = array(
				get_string('review', 'local_elibrary'),
				get_string('user', 'local_elibrary'),
				get_string('date', 'local_elibrary')
			);
			if (has_capability('local/elibrary:manageresourcereview', context_system::instance())) {
				$table->head[] = '';
			}

			$table->data = array();

			foreach ($resource_review_list as $data) {
				if (!has_capability('local/elibrary:manageresourcereview', context_system::instance())) {
					if ($data->ishide == 1) {
						continue;
					}
				}
				$tablerows = array(
					nl2br($data->message),
					'<a href="' . $CFG->wwwroot . '/user/profile.php?id=' . $data->userid . '" target="blank">' . fullname($data) . '</a>',
					date('Y-m-d H:i', $data->adddate)
				);
				$mobilelayout .= '
					<div class="mobile-table-row">
						<div>
						<div class="mobile-table-cell half label">' . get_string('review', 'local_elibrary') . '</div>' .
					'<div class="mobile-table-cell half content">' . nl2br($data->message) . '</div>' .
					'</div>' .
					'<div>' .
					'<div class="mobile-table-cell half label">' . get_string('user', 'local_elibrary') . '</div>' .
					'<div class="mobile-table-cell half content">' . '<a href="' . $CFG->wwwroot . '/user/profile.php?id=' . $data->userid . '" target="blank">' . fullname($data) . '</a>' . '</div>' .
					'</div>' .
					'<div>' .
					'<div class="mobile-table-cell half label">' . get_string('date', 'local_elibrary') . '</div>' .
					'<div class="mobile-table-cell half content">' . date('Y-m-d H:i', $data->adddate) . '</div>' .
					'</div>
				';

				if (has_capability('local/elibrary:manageresourcereview', context_system::instance())) {
					$tablerows[] = '<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'view_resource_detail.php?id=' . $resourceid . '&action=hide_review&reviewid=' . $data->id . '" onclick="return confirm(\'' . get_string('msg_confirm_hide_review', 'local_elibrary') . '\')">' . get_string((($data->ishide == 0) ? 'button_hide_review' : 'button_show_review'), 'local_elibrary') . '</a> ' .
						'<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'view_resource_detail.php?id=' . $resourceid . '&action=delete_review&reviewid=' . $data->id . '" onclick="return confirm(\'' . get_string('msg_confirm_delete_review', 'local_elibrary') . '\')">' . get_string('button_delete_review', 'local_elibrary') . '</a>';
					$mobilelayout .= '
						<div>
						<div class="mobile-table-cell">' . '<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'view_resource_detail.php?id=' . $resourceid . '&action=hide_review&reviewid=' . $data->id . '" onclick="return confirm(\'' . get_string('msg_confirm_hide_review', 'local_elibrary') . '\')">' . get_string((($data->ishide == 0) ? 'button_hide_review' : 'button_show_review'), 'local_elibrary') . '</a> ' .
						'<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'view_resource_detail.php?id=' . $resourceid . '&action=delete_review&reviewid=' . $data->id . '" onclick="return confirm(\'' . get_string('msg_confirm_delete_review', 'local_elibrary') . '\')">' . get_string('button_delete_review', 'local_elibrary') . '</a>' . '</div>' .
						'</div>
					';
				}
				$table->data[] = $tablerows;
			}

			$output .= '<div class="table_wrap">' . \html_writer_innoverz::table($table) . '</div>';

			$output .= $mobilelayout . '</div></div>';

			$output .= \html_writer_innoverz::empty_tag('hr');
		}

		$output .= '<form method="post">';
		$output .= '<input type="hidden" name="action" value="post_review">';
		$output .= '<input type="hidden" name="resource_id" value="' . $resourceid . '">';
		$table = new html_table();
		$table->attributes['class'] = 'generaltable visible';
		$table->id = 'post_review_table';
		$table->data = array();
		$table->data[] = array(
			\html_writer_innoverz::tag('label', get_string('review_content', 'local_elibrary'), array('for' => 'review_content')),
			'<textarea id="review_content" name="review_content"></textarea>'
		);
		$table->data[] = array(
			'',
			'<input type="submit" value="' . get_string('post_review', 'local_elibrary') . '">'
		);
		$output .= '<div class="table_wrap">' . \html_writer_innoverz::table($table) . '</div>';
		$output .= '</form>';
		$output .= '</div>';	//contentsubbox
		$output .= '</div>';	//contentblock

		$output = \html_writer_innoverz::tag('div', $output, array('class' => 'resource_detail_review_list'));

		return $output;
	}

	public function view_resource_detail_share($resourceid)
	{
		global $OUTPUT;
		$output = '';

		$output .= \html_writer_innoverz::tag('h2', get_string('share_resource', 'local_elibrary'));

		$output .= '<div class="contentblock">';
		$output .= '<form method="POST">';
		$output .= '<input type="hidden" name="resource_id" value="' . $resourceid . '" />';
		$output .= '<input type="hidden" name="action" value="share_resource" />';

		$output .= '<div class="clearfix contentsubbox">';

		$output .= '<table class="generaltable visible responsive">';
		$output .= '<caption class="accesshide">Summary</caption>';
		$output .= '<tbody>';

		$output .= '<tr>';

		$output .= '<th class="header">';
		$output .= '<label for="recipient_name">' . get_string('recipient_name', 'local_elibrary') . '</label>';
		$output .= '</th>';

		$output .= '<td>';
		$output .= '<input type="text" name="recipient_name" id="recipient_name">';
		$output .= '</td>';

		$output .= '</tr>';

		$output .= '<tr>';

		$output .= '<th class="header">';
		$output .= '<label for="email_address">' . get_string('email_address', 'local_elibrary') . '</label>';
		$output .= '</th>';

		$output .= '<td>';
		$output .= '<input type="text" name="email_address" id="email_address">';
		$output .= '</td>';

		$output .= '</tr>';

		$output .= '<tr>';

		$output .= '<th class="header">';
		$output .= '<label for="message">' . get_string('message', 'local_elibrary') . '</label>';
		$output .= '</th>';

		$output .= '<td>';
		$output .= '<textarea name="message" id="message"></textarea>';
		$output .= '</td>';

		$output .= '</tr>';

		$output .= '</tbody>';
		$output .= '</table>';
		$output .= '</div>';	//contentsubbox

		$output .= '<div class="contentbottom">';
		$output .= '<input type="submit" value="' . get_string('share_resource', 'local_elibrary') . '" />';
		$output .= '</div>';	//contentbottom

		$output .= '</form>';

		$output .= '</div>';	//contentblock

		$output = \html_writer_innoverz::tag('div', $output, array('class' => 'resource_detail_share'));

		return $output;
	}

	public function view_resource_copy_list($resource_copy_list = null)
	{
		global $OUTPUT, $CFG;
		$output = '';

		$table = new html_table();
		$table->classes = array('logtable', 'generaltable');
		$table->align = array('center', 'center', 'center', 'left', 'center');

		$table->head = array(
			get_string('accession_number', 'local_elibrary'),
			get_string('call_number', 'local_elibrary'),
			get_string('locate', 'local_elibrary'),
			get_string('status', 'local_elibrary'),
			get_string('remark', 'local_elibrary'),
			'',
		);

		$table->data = array();

		foreach ($resource_copy_list as $data) {
			$table->data[] = array(
				$data->accessno,
				$data->callno,
				"{$data->locate_code} - {$data->locate_description}",
				($data->isloan == 1) ? get_string('not_available', 'local_elibrary') : get_string('available', 'local_elibrary'),
				nl2br($data->remark),
				'<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'resource_copy_edit.php?id=' . $data->id . '">' . get_string('button_edit_resource_copy', 'local_elibrary') . '</a> ' .
					'<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'resource_edit.php?action=delete_resource_copy&copyid=' . $data->id . '&id=' . $_GET['id'] . '" onclick="return confirm(\'' . get_string('msg_confirm_delete_resource_copy', 'local_elibrary') . '\')">' . get_string('button_delete_resource_copy', 'local_elibrary') . '</a>'
			);
		}

		$output .= '<div class="table_wrap">' . \html_writer_innoverz::table($table) . '</div>';

		$output .= \html_writer_innoverz::new_record_widget(new moodle_url($CFG->LIBRARY_BASEURL . 'resource_copy_new.php', array('resourceid' => $_GET['id'])), get_string('button_new_resource_copy', 'local_elibrary'));
		//$output .= '<input type="button" value="' . get_string('button_new_resource_copy', 'local_elibrary') . '" onclick="window.location=\'' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL .'resource_copy_new.php?resourceid=' . $_GET['id'] . '\'">';

		return $output;
	}

	public function start_layout()
	{
		return \html_writer_innoverz::start_tag('div', array('class' => 'mainlibrary'));
	}

	public function complete_layout()
	{
		return \html_writer_innoverz::end_tag('div');
	}

	public function view_class_list($class_list = null, $totalcount = 0, $page = 0, $perpage = 10, $url = "")
	{
		global $OUTPUT, $CFG;
		$output = '';

		$output .= \html_writer_innoverz::tag('h2', get_string('list_class', 'local_elibrary'), array('class' => 'floatleft'));
		$output .= \html_writer_innoverz::new_record_widget(new moodle_url($CFG->LIBRARY_BASEURL . 'class_new.php'), get_string('button_new_class', 'local_elibrary'));
		$output .= \html_writer_innoverz::table_paging_header($totalcount, $page, $perpage, $url);

		$table = new html_table();
		$table->classes = array('logtable', 'generaltable');
		$table->align = array('left', 'center');

		$table->head = array(
			get_string('class', 'local_elibrary'),
			'',
		);

		$table->data = array();

		$OUTPUT->paging_bar_data_loop($class_list, $totalcount, $page, $perpage, function ($data) use (&$table) {
			global $CFG;

			$table->data[] = array(
				$data->description_eng,
				'<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'class_edit.php?id=' . $data->id . '">' . get_string('button_edit_class', 'local_elibrary') . '</a> ' .
					'<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'class_list.php?action=delete_class&classid=' . $data->id . '&query_string=' . base64_encode($_SERVER['QUERY_STRING']) . '" onclick="return confirm(\'' . get_string('msg_confirm_delete_class', 'local_elibrary') . '\')">' . get_string('button_delete_class', 'local_elibrary') . '</a>'
			);
		});

		$output .= '<div class="table_wrap">' . \html_writer_innoverz::table($table) . '</div>';
		$output .= $OUTPUT->paging_bar($totalcount, $page, $perpage, "$url");

		$output = \html_writer_innoverz::tag('div', $output, array('class' => 'class_list'));

		return $output;
	}

	public function view_currency_list($currency_list = null, $totalcount = 0, $page = 0, $perpage = 10, $url = "")
	{
		global $OUTPUT, $CFG;
		$output = '';

		$output .= \html_writer_innoverz::tag('h2', get_string('list_currency', 'local_elibrary'), array('class' => 'floatleft'));
		$output .= \html_writer_innoverz::new_record_widget(new moodle_url($CFG->LIBRARY_BASEURL . 'currency_new.php'), get_string('button_new_currency', 'local_elibrary'));
		$output .= \html_writer_innoverz::table_paging_header($totalcount, $page, $perpage, $url);

		$table = new html_table();
		$table->classes = array('logtable', 'generaltable');
		$table->align = array('left', 'center');

		$table->head = array(
			get_string('currency_code', 'local_elibrary'),
			get_string('currency_rate', 'local_elibrary'),
			'',
		);

		$table->data = array();

		$OUTPUT->paging_bar_data_loop($currency_list, $totalcount, $page, $perpage, function ($data) use (&$table) {
			global $CFG;

			$table->data[] = array(
				$data->code,
				$data->rate,
				'<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'currency_edit.php?id=' . $data->id . '">' . get_string('button_edit_currency', 'local_elibrary') . '</a> ' .
					'<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'currency_list.php?action=delete_currency&currencyid=' . $data->id . '&query_string=' . base64_encode($_SERVER['QUERY_STRING']) . '" onclick="return confirm(\'' . get_string('msg_confirm_delete_currency', 'local_elibrary') . '\')">' . get_string('button_delete_currency', 'local_elibrary') . '</a>'
			);
		});

		$output .= '<div class="table_wrap">' . \html_writer_innoverz::table($table) . '</div>';
		$output .= $OUTPUT->paging_bar($totalcount, $page, $perpage, "$url");

		$output = \html_writer_innoverz::tag('div', $output, array('class' => 'currency_list'));
		return $output;
	}

	public function view_locate_list($locate_list = null, $totalcount = 0, $page = 0, $perpage = 10, $url = "")
	{
		global $OUTPUT, $CFG;
		$output = '';

		$output .= \html_writer_innoverz::tag('h2', get_string('list_locate', 'local_elibrary'), array('class' => 'floatleft'));
		$output .= \html_writer_innoverz::new_record_widget(new moodle_url($CFG->LIBRARY_BASEURL . 'locate_new.php'), get_string('button_new_locate', 'local_elibrary'));
		$output .= \html_writer_innoverz::table_paging_header($totalcount, $page, $perpage, $url);

		$table = new html_table();
		$table->classes = array('logtable', 'generaltable');
		$table->align = array('left', 'center');

		$table->head = array(
			get_string('locate_code', 'local_elibrary'),
			get_string('locate_description', 'local_elibrary'),
			'',
		);

		$table->data = array();

		$OUTPUT->paging_bar_data_loop($locate_list, $totalcount, $page, $perpage, function ($data) use (&$table) {
			global $CFG;

			$table->data[] = array(
				$data->code,
				$data->description,
				'<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'locate_edit.php?id=' . $data->id . '">' . get_string('button_edit_locate', 'local_elibrary') . '</a> ' .
					'<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'locate_list.php?action=delete_locate&locateid=' . $data->id . '&query_string=' . base64_encode($_SERVER['QUERY_STRING']) . '" onclick="return confirm(\'' . get_string('msg_confirm_delete_locate', 'local_elibrary') . '\')">' . get_string('button_delete_locate', 'local_elibrary') . '</a>'
			);
		});

		$output .= '<div class="table_wrap">' . \html_writer_innoverz::table($table) . '</div>';
		$output .= $OUTPUT->paging_bar($totalcount, $page, $perpage, "$url");

		$output = \html_writer_innoverz::tag('div', $output, array('class' => 'locate_list'));
		return $output;
	}

	public function view_subject_list($subject_list = null, $totalcount = 0, $page = 0, $perpage = 10, $url = "")
	{
		global $OUTPUT, $CFG;
		$output = '';

		$output .= \html_writer_innoverz::tag('h2', get_string('list_subject', 'local_elibrary'), array('class' => 'floatleft'));
		$output .= \html_writer_innoverz::new_record_widget(new moodle_url($CFG->LIBRARY_BASEURL . 'subject_new.php'), get_string('button_new_subject', 'local_elibrary'));
		$output .= \html_writer_innoverz::table_paging_header($totalcount, $page, $perpage, $url);

		$table = new html_table();
		$table->classes = array('logtable', 'generaltable');
		$table->align = array('left', 'center');

		$table->head = array(
			get_string('subject_name', 'local_elibrary'),
			''
		);

		$table->data = array();

		$OUTPUT->paging_bar_data_loop($subject_list, $totalcount, $page, $perpage, function ($data) use (&$table) {
			global $CFG;
			$table->data[] = array(
				$data->name_eng,
				'<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'subject_edit.php?id=' . $data->id . '">' . get_string('button_edit_subject', 'local_elibrary') . '</a> ' .
					'<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'subject_list.php?action=delete_subject&subjectid=' . $data->id . '&query_string=' . base64_encode($_SERVER['QUERY_STRING']) . '" onclick="return confirm(\'' . get_string('msg_confirm_delete_subject', 'local_elibrary') . '\')">' . get_string('button_delete_subject', 'local_elibrary') . '</a>'
			);
		});

		$output .= '<div class="table_wrap">' . \html_writer_innoverz::table($table) . '</div>';
		$output .= $OUTPUT->paging_bar($totalcount, $page, $perpage, "$url");

		$output = \html_writer_innoverz::tag('div', $output, array('class' => 'subject_list'));
		return $output;
	}

	public function loan_resource_scan_resource_barcode()
	{
		$output = '';

		$output .= '<div class="enter_resource_barcode">';
		$output .= '<form id="enter_resource_barcode_form">';

		$table = new html_table();
		$table->classes = array('logtable', 'generaltable');
		$table->align = array('left');

		$table->head = array(
			get_string('scan_resource_barcode', 'local_elibrary')
		);

		$table_data = '<label>';
		$table_data .= '<span>' . get_string('resource_accession_number', 'local_elibrary') . '</span>';
		$table_data .= '<input type="text" name="resource_barcode" id="resource_barcode" />';
		$table_data .= '</label>';
		$table->data = array(array($table_data));

		$table_data = ' <input type="button" value="' . get_string('enter_resource', 'local_elibrary') . '" id="button_enter_resource_barcode" />';
		$table_data .= '<span id="barcode_enter_result"></span>';
		$table->data[] = array($table_data);

		$output .= '<div class="table_wrap">' . \html_writer_innoverz::table($table) . '</div>';

		$output .= '</form>';
		$output .= '</div>';

		return $output;
	}

	public function loan_resource_loan_list($user_id, $borrower)
	{
		$output = '';

		$output .= '<form method="POST" id="loan_list_form">';
		$output .= '<input type="hidden" name="user_id" id="user_id" value="' . $user_id . '" />';
		$output .= '<input type="hidden" name="borrower" id="borrower" value="' . $borrower . '" />';

		$table = new html_table();
		$table->id = 'loan_list';
		$table->classes = array('logtable', 'generaltable');
		$table->width = '100%';
		$table->size = array('15%', '45%', '15%', '15%', '10%');
		$table->align = array('center', 'left', 'center', 'center', 'center');
		$table->head = array(
			get_string('accession_number', 'local_elibrary'),
			get_string('title', 'local_elibrary'),
			get_string('author', 'local_elibrary'),
			get_string('publisher', 'local_elibrary'),
			''
		);

		$cells = array();
		for ($i = 0; $i <= 4; $i++) {
			$cells[$i] = new html_table_cell();
			$cells[$i]->text = '';
		}
		$cells[4] = '<a href="javascript:" ref="" class="button_delete_from_loan_list">' . get_string('button_delete_from_loan_list', 'local_elibrary') . '</a>';
		$hidden_row = new html_table_row();
		$hidden_row->cells = $cells;
		$hidden_row->style = 'display:none';

		$table->data = array(
			$hidden_row
		);

		$output .= '<div class="table_wrap">' . \html_writer_innoverz::table($table) . '</div>';

		$output .= '<input type="submit" value="' . get_string('button_confirm_loan', 'local_elibrary') . '" class="form-submit" />';

		$output .= '</form>';

		return $output;
	}

	public function return_resource_scan_resource_barcode()
	{
		$output = '';

		$output .= '<div class="enter_resource_barcode">';
		$output .= '<form id="enter_resource_barcode_form">';

		$table = new html_table();
		$table->classes = array('logtable', 'generaltable');
		$table->align = array('left');

		$table->head = array(
			get_string('scan_resource_barcode', 'local_elibrary')
		);

		$table_data = '<label>';
		$table_data .= '<span>' . get_string('resource_accession_number', 'local_elibrary') . '</span>';
		$table_data .= '<input type="text" name="resource_barcode" id="resource_barcode" />';
		$table_data .= '</label>';
		$table->data = array(array($table_data));

		$table_data = ' <input type="button" value="' . get_string('enter_resource', 'local_elibrary') . '" id="button_enter_resource_barcode" />';
		$table_data .= '<span id="barcode_enter_result"></span>';
		$table->data[] = array($table_data);

		$output .= '<div class="table_wrap">' . \html_writer_innoverz::table($table) . '</div>';

		$output .= '</form>';
		$output .= '</div>';

		return $output;
	}

	public function return_resource_loan_list()
	{
		$output = '';

		$output .= '<form method="POST" id="return_list_form">';

		$table = new html_table();
		$table->id = 'return_list';
		$table->classes = array('logtable', 'generaltable');
		$table->width = '100%';
		$table->size = array('15%', '45%', '15%', '15%', '10%');
		$table->align = array('center', 'left', 'center', 'center', 'center');
		$table->head = array(
			get_string('accession_number', 'local_elibrary'),
			get_string('title', 'local_elibrary'),
			get_string('return_date', 'local_elibrary'),
			get_string('delay', 'local_elibrary'),
			''
		);

		$cells = array();
		for ($i = 0; $i <= 4; $i++) {
			$cells[$i] = new html_table_cell();
			$cells[$i]->text = '';
		}
		$cells[4] = '<a href="javascript:" ref="" class="button_delete_from_return_list">' . get_string('button_delete_from_return_list', 'local_elibrary') . '</a>';
		$hidden_row = new html_table_row();
		$hidden_row->cells = $cells;
		$hidden_row->style = 'display:none';

		$table->data = array(
			$hidden_row
		);

		$output .= '<div class="table_wrap">' . \html_writer_innoverz::table($table) . '</div>';

		$output .= '<input type="submit" value="' . get_string('button_confirm_return', 'local_elibrary') . '" />';

		$output .= '</form>';

		return $output;
	}

	public function renew_resource_scan_resource_barcode()
	{
		$output = '';

		$output .= '<div class="enter_resource_barcode">';
		$output .= '<form id="enter_resource_barcode_form">';

		$table = new html_table();
		$table->classes = array('logtable', 'generaltable');
		$table->align = array('left');

		$table->head = array(
			get_string('scan_resource_barcode', 'local_elibrary')
		);

		$table_data = '<label>';
		$table_data .= '<span>' . get_string('resource_accession_number', 'local_elibrary') . '</span>';
		$table_data .= '<input type="text" name="resource_barcode" id="resource_barcode" />';
		$table_data .= '</label>';
		$table->data = array(array($table_data));

		$table_data = ' <input type="button" value="' . get_string('enter_resource', 'local_elibrary') . '" id="button_enter_resource_barcode" />';
		$table_data .= '<span id="barcode_enter_result"></span>';
		$table->data[] = array($table_data);

		$output .= '<div class="table_wrap">' . \html_writer_innoverz::table($table) . '</div>';

		$output .= '</form>';
		$output .= '</div>';

		return $output;
	}

	public function renew_resource_loan_list()
	{
		$output = '';

		$output .= '<form method="POST" id="renew_list_form">';

		$table = new html_table();
		$table->id = 'renew_list';
		$table->classes = array('logtable', 'generaltable');
		$table->width = '100%';
		$table->size = array('15%', '45%', '15%', '15%', '10%');
		$table->align = array('center', 'left', 'center', 'center', 'center');
		$table->head = array(
			get_string('accession_number', 'local_elibrary'),
			get_string('title', 'local_elibrary'),
			get_string('old_return_date', 'local_elibrary'),
			get_string('new_return_date', 'local_elibrary'),
			''
		);

		$cells = array();
		for ($i = 0; $i <= 4; $i++) {
			$cells[$i] = new html_table_cell();
			$cells[$i]->text = '';
		}
		$cells[4] = '<a href="javascript:" ref="" class="button_delete_from_renew_list">' . get_string('button_delete_from_renew_list', 'local_elibrary') . '</a>';
		$hidden_row = new html_table_row();
		$hidden_row->cells = $cells;
		$hidden_row->style = 'display:none';

		$table->data = array(
			$hidden_row
		);

		$output .= '<div class="table_wrap">' . \html_writer_innoverz::table($table) . '</div>';

		$output .= '<input type="submit" value="' . get_string('button_confirm_renew', 'local_elibrary') . '" class="form-submit" />';

		$output .= '</form>';

		return $output;
	}

	public function select_borrower($for = 'loan', $default_username, $allow_select_all = false)
	{	//$for: loan/reserve
		global $CFG;

		$output = '';

		$output .= '<div class="select_borrower">';
		$output .= '<form id="select_borrower_form">';
		$output .= '<input type="hidden" name="sesskey" value="' . sesskey() . '">';

		$table = new html_table();
		$table->classes = array('logtable', 'generaltable');
		$table->align = array('left');

		$table->head = array(
			get_string('select_borrower', 'local_elibrary')
		);

		$table_data = '<label>';
		$table_data .= '<span>' . get_string('scan_borrower_card', 'local_elibrary') . '</span>';
		$table_data .= '<input type="text" name="borrower_username" id="borrower_username" value="' . $default_username . '" />';
		$table_data .= '</label>';
		$table->data = array(array($table_data));

		$table_data = '';
		$table_data .= ' <input type="submit" value="' . get_string('search_borrower', 'local_elibrary') . '" class="form-submit" />';
		if ($allow_select_all) {
			if ($for == 'loan') {
				$select_all_link = new moodle_url($CFG->LIBRARY_BASEURL . 'user_loan_history.php', array('userid' => -1));
			} elseif ($for == 'reserve') {
				$select_all_link = new moodle_url($CFG->LIBRARY_BASEURL . 'user_reservation_history.php', array('userid' => -1));
			}
			$table_data .= ' <input type="button" value="' . get_string('display_all_borrower', 'local_elibrary') . '" onclick="window.location=\'' . $select_all_link . '\'" />';

			// seems to be no use at all for 2.7 and 3.9, because there are no existing records with islibrarian = 1 in table mdl_library_loan
			/* if($for == 'loan'){
				$table_data .= ' <input type="button" value="' . get_string('display_librarian', 'local_elibrary') . '" onclick="window.location=\'' . new moodle_url($CFG->LIBRARY_BASEURL.'user_loan_history.php', array('userid'=>-2)) . '\'" />';
			} */
		}
		$table->data[] = array($table_data);

		$output .= '<div class="table_wrap">' . \html_writer_innoverz::table($table) . '</div>';

		$output .= '</form>';

		$output .= '<br>';

		$output .= '</div>';

		return $output;
	}

	public function user_current_loan_list($loan_list = null, $status = 0)
	{
		global $OUTPUT, $CFG;
		$output = '';
		$output .= '<h3 style="text-align:left">' . get_string('current_on_loan', 'local_elibrary') . '</h3>';

		$table = new html_table();
		$table->classes = array('logtable', 'generaltable');
		$table->align = array('left', 'center', 'center', 'center', 'center', 'center', 'center', 'center');

		$HTML_status_selector = '<label for="status_selector" class="accesshide">Status selector</label>';
		$HTML_status_selector .= '<select id="status_selector">';
		$HTML_status_selector .= '<option value="0">' . /* get_string('status_select', 'local_elibrary') . */ get_string('all') . '</option>';
		$HTML_status_selector .= '<option value="1">' . /* get_string('status_select', 'local_elibrary') . */ get_string('on_loan', 'local_elibrary') . '</option>';
		$HTML_status_selector .= '<option value="2">' . /* get_string('status_select', 'local_elibrary') . */ get_string('overdue', 'local_elibrary') . '</option>';
		$HTML_status_selector .= '<option value="3">' . /* get_string('status_select', 'local_elibrary') . */ get_string('reserved', 'local_elibrary') . '</option>';
		$HTML_status_selector .= '<option value="4">' . /* get_string('status_select', 'local_elibrary') . */ get_string('on_loan_renewed', 'local_elibrary') . '</option>';
		$HTML_status_selector .= '</select>';

		$table->head = array(
			\html_writer_innoverz::tag('span', get_string('status', 'local_elibrary'), array('style' => 'display:none')) . $HTML_status_selector,
			'',
			get_string('loan_date', 'local_elibrary'),
			get_string('due_date', 'local_elibrary'),
			get_string('title', 'local_elibrary'),
			get_string('accession_number', 'local_elibrary'),
			get_string('call_number', 'local_elibrary'),
			get_string('author', 'local_elibrary'),
		);

		$have_username = is_object(reset($loan_list)) && property_exists(reset($loan_list), 'username');
		if ($have_username) {
			array_unshift($table->head, get_string(((reset($loan_list)->islibrarian == 1) ? 'librarian' : 'username'), 'local_elibrary'));
			array_unshift($table->align, 'center');
		}
		$have_contactperson = is_object(reset($loan_list)) && @property_exists(reset($loan_list), "contactperson");
		if ($have_contactperson) {
			array_push($table->head, get_string('borrower_id', 'local_elibrary'));
			array_push($table->align, 'center');
			array_push($table->head, get_string('contact_person', 'local_elibrary'));
			array_push($table->align, 'center');
			array_push($table->head, get_string('contact_email', 'local_elibrary'));
			array_push($table->align, 'center');
			array_push($table->head, get_string('contact_number', 'local_elibrary'));
			array_push($table->align, 'center');
		}

		$table->data = array();

		foreach ($loan_list as $data) {
			$HTML_renew = '';
			$HTML_status = get_string('on_loan', 'local_elibrary');
			if (time() >= $data->returndate) {
				if ($status != 0 && $status != 2) {
					continue;
				}
				$HTML_status = get_string('overdue', 'local_elibrary');
			} elseif (!empty($data->reserve_id)) {
				if ($status != 0 && $status != 3) {
					continue;
				}
				$HTML_status = get_string('reserved', 'local_elibrary');
			} elseif (!empty($data->renew_id)) {
				if ($status != 0 && $status != 4) {
					continue;
				}
				$HTML_status = get_string('on_loan_renewed', 'local_elibrary');
			} else {
				if ($status != 0 && $status != 1) {
					continue;
				}
				$HTML_renew = '<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'user_loan_history.php?action=renew_resource&accessno=' . $data->accessno . '&query_string=' . base64_encode($_SERVER['QUERY_STRING']) . '" onclick="return confirm(\'' . get_string('msg_confirm_renew_resource', 'local_elibrary') . '\')">' . get_string('button_renew_resource', 'local_elibrary') . '</a>';
			}

			$table_row = array(
				$HTML_status,
				$HTML_renew,
				date('Y-m-d', $data->loandate),
				date('Y-m-d', $data->returndate),
				$data->title,
				$data->accessno,
				$data->callno,
				$data->author
			);
			if ($have_username) {
				array_unshift($table_row, $data->username);
			}
			if ($have_contactperson) {
				array_push($table_row, $data->borrowerid);
				array_push($table_row, $data->contactperson);
				array_push($table_row, $data->contactemail);
				array_push($table_row, $data->contactnumber);
			}
			$table->data[] = $table_row;
		}

		$output .= '<div class="table_wrap">' . \html_writer_innoverz::table($table) . '</div>';
		return $output;
	}

	public function user_loan_history($loan_list = null, $totalcount = 0, $page = 0, $perpage = 10, $url = "")
	{
		global $OUTPUT, $CFG;
		$output = '';

		//$output .= '<h3 style="text-align:left">' . get_string('loan_history', 'local_elibrary') . '</h3>';

		$output .= $OUTPUT->paging_bar($totalcount, $page, $perpage, "$url");

		$table = new html_table();
		$table->classes = array('logtable', 'generaltable');
		$table->align = array('left', 'center', 'center', 'center', 'center', 'center');

		$table->head = array(
			get_string('title', 'local_elibrary'),
			get_string('author', 'local_elibrary'),
			get_string('accession_number', 'local_elibrary'),
			get_string('call_number', 'local_elibrary'),
			get_string('loan_date', 'local_elibrary'),
			get_string('return_date', 'local_elibrary')
		);
		$have_username = is_object(reset($loan_list)) && property_exists(reset($loan_list), 'username');

		if ($have_username) {
			array_unshift($table->head, get_string(((reset($loan_list)->islibrarian == 1) ? 'librarian' : 'username'), 'local_elibrary'));
			array_unshift($table->align, 'center');
		}

		$have_contactperson = @property_exists(reset($loan_list), "contactperson");
		if ($have_contactperson) {
			array_unshift($table->head, get_string('borrower_id', 'local_elibrary'));
			array_unshift($table->align, 'center');
			array_unshift($table->head, get_string('contact_email', 'local_elibrary'));
			array_unshift($table->align, 'center');
			array_unshift($table->head, get_string('contact_number', 'local_elibrary'));
			array_unshift($table->align, 'center');
			array_unshift($table->head, get_string('contact_person', 'local_elibrary'));
			array_unshift($table->align, 'center');
		}

		$table->data = array();

		$startrow = $page * $perpage;
		$endrow = $startrow + $perpage;
		$endrow = ($endrow > $totalcount) ? $totalcount : $endrow;

		$i = 0;
		foreach ($loan_list as $data) {
			$i++;
			if ($i < $startrow || $i > $endrow) {
				continue;
			}

			$table_row = array(
				$data->title,
				$data->author,
				$data->accessno,
				$data->callno,
				date('Y-m-d', $data->loandate),
				date('Y-m-d', $data->actualreturndate)
			);
			if ($have_username) {
				array_unshift($table_row, $data->username);
			}
			if ($have_contactperson) {
				array_unshift($table_row, $data->borrowerid);
				array_unshift($table_row, $data->contactemail);
				array_unshift($table_row, $data->contactnumber);
				array_unshift($table_row, $data->contactperson);
			}
			$table->data[] = $table_row;
		}

		$output .= '<div class="table_wrap">' . \html_writer_innoverz::table($table) . '</div>';
		$output .= $OUTPUT->paging_bar($totalcount, $page, $perpage, "$url");
		return $output;
	}

	public function user_loan_resource_select_borrower($user_list = null, $totalcount = 0, $page = 0, $perpage = 10, $url = "")
	{
		global $OUTPUT, $CFG;
		$output = '';

		$output .= $OUTPUT->paging_bar($totalcount, $page, $perpage, "$url");

		$table = new html_table();
		$table->classes = array('logtable', 'generaltable');
		$table->align = array('left', 'center', 'center', 'center', 'center', 'center', 'center', 'center', /* 'center', */ 'center');

		$table->head = array(
			get_string('username', 'local_elibrary'),
			get_string('firstname', 'local_elibrary'),
			get_string('lastname', 'local_elibrary'),
			get_string('chinesename', 'local_elibrary'),
			get_string('phone1', 'local_elibrary'),
			get_string('phone2', 'local_elibrary'),
			get_string('email_address', 'local_elibrary'),
			get_string('organization', 'local_elibrary'),
			//get_string('organization_chi', 'local_elibrary'), //no such profile fields defined in 3.9 anymore

			get_string('select_borrower', 'local_elibrary')
		);

		$table->data = array();

		$startrow = $page * $perpage;
		$endrow = $startrow + $perpage;
		$endrow = ($endrow > $totalcount) ? $totalcount : $endrow;

		$i = 0;

		foreach ($user_list as $data) {
			$i++;
			if ($i < $startrow || $i > $endrow) {
				continue;
			}

			if (($data->overloan) == 'NO') {
				$link = '<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'loan_resource.php?userid=' . $data->id . '">' . get_string('button_select', 'local_elibrary') . '</a>';
			} else {
				$link = strtoupper(get_string('overloan', 'local_elibrary'));
			}

			$table_row = array(
				$data->username,
				$data->firstname,
				$data->lastname,
				$data->chiname,
				$data->phone1,
				$data->phone2,
				$data->email,
				$data->orgname,
				//$data->orgnamechi,
				$link
			);
			$table->data[] = $table_row;
		}
		$output .= '<div class="table_wrap">' . \html_writer_innoverz::table($table) . '</div>';
		$output .= $OUTPUT->paging_bar($totalcount, $page, $perpage, "$url");
		return $output;
	}

	public function user_current_reservation_list($reservation_list = null)
	{
		global $OUTPUT, $CFG;

		$islibadmin = has_capability('local/elibrary:viewuserreservationrecord', context_system::instance());
		$output = '';

		$output .= '<h3 style="text-align:left">' . get_string('current_reserving', 'local_elibrary') . '</h3>';

		$table = new html_table();
		$table->classes = array('logtable', 'generaltable');
		$table->align = array('left', 'center', 'center', 'center', 'center', 'center', 'center', 'center');

		$table->head = array(
			get_string('title', 'local_elibrary'),
			get_string('author', 'local_elibrary'),
			$islibadmin ? get_string('accession_number_available', 'local_elibrary') : get_string('isbn', 'local_elibrary'),
			get_string('call_number', 'local_elibrary'),
			get_string('reservation_status', 'local_elibrary'),
			get_string('reservation_date', 'local_elibrary'),
			''
		);
		$have_username = is_object(reset($reservation_list)) && property_exists(reset($reservation_list), 'username');
		if ($have_username) {
			array_unshift($table->head, get_string('username', 'local_elibrary'));
			array_unshift($table->align, 'center');
		}

		$table->data = array();

		foreach ($reservation_list as $data) {
			$st_nd_rd = array('', 'st', 'nd', 'rd', 'th');

			$HTML_resource_status = '';
			// if($data->reserve_rank_in_queue == 1){
			// if(!$data->isloan){
			// $HTML_resource_status = get_string('have_copy_available', 'local_elibrary');
			// }else{
			// $HTML_resource_status = get_string('all_copy_loaned_out', 'local_elibrary', date('Y-m-d', $data->recent_return_date));
			// }
			// }else{
			// $HTML_resource_status = get_string('your_reservation_queue_position', 'local_elibrary', ($data->reserve_rank_in_queue . $st_nd_rd[(($data->reserve_rank_in_queue > 4) ? 4 : $data->reserve_rank_in_queue)]));
			// }

			$HTML_resource_status = get_string('your_reservation_queue_position', 'local_elibrary', ($data->reserve_rank_in_queue . $st_nd_rd[(($data->reserve_rank_in_queue > 4) ? 4 : $data->reserve_rank_in_queue)]));

			if (!$data->isloan) {
				$HTML_resource_status .= '<br>' . get_string('have_copy_available', 'local_elibrary');
			} else if ($data->recent_return_date) {
				$HTML_resource_status .= '<br>' . get_string('all_copy_loaned_out', 'local_elibrary', date('Y-m-d', $data->recent_return_date));
			}

			$accessnos = get_available_copy_by_resourceid($data->resource_id);

			$accessnostr = array();
			$callnostr = array();
			foreach ($accessnos as $row) {
				$accessnostr[] = $row->accessno;
				$callnostr[] = $row->callno;
			}
			
			$firstaccessno = null;
			if (isset($accessnostr[0])) $firstaccessno = $accessnostr[0];

			$accessnostr = implode('<br>', $accessnostr);
			$callnostr = implode('<br>', $callnostr);

			$button_html = '';
			if ($firstaccessno != null && has_capability('local/elibrary:resourceadministration', context_system::instance())) {
				$button_html = '<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'user_reservation_history.php?action=success_reserve&resource_id=' . $data->resource_id . '&firstaccessno=' . $firstaccessno . '&id=' . $data->id . '&query_string=' . base64_encode($_SERVER['QUERY_STRING']) . '" onclick="return confirm(\'' . get_string('msg_confirm_success_reserve', 'local_elibrary') . '\')">' . get_string('button_success_reserve_resource', 'local_elibrary') . '</a><br /><br />';
			}
			$button_html .= '<a href="' . $CFG->wwwroot . $CFG->LIBRARY_BASEURL . 'user_reservation_history.php?action=cancel_reserve&id=' . $data->id . '&query_string=' . base64_encode($_SERVER['QUERY_STRING']) . '" onclick="return confirm(\'' . get_string('msg_confirm_cancel_reserve', 'local_elibrary') . '\')">' . get_string('button_cancel_reserve_resource', 'local_elibrary') . '</a>';

			$table_row = array(
				$data->title,
				$data->author,
				$islibadmin ? $accessnostr : $data->isbn,
				$callnostr,
				$HTML_resource_status,
				date('Y-m-d', $data->requestdate),
				$button_html
			);
			if ($have_username) {
				array_unshift($table_row, $data->username);
			}
			$table->data[] = $table_row;
		}

		$output .= '<div class="table_wrap">' . \html_writer_innoverz::table($table) . '</div>';
		return $output;
	}

	public function user_reservation_history($reservation_list = null, $totalcount = 0, $page = 0, $perpage = 10, $url = "")
	{
		global $OUTPUT, $CFG;
		$output = '';

		//$output .= '<h3 style="text-align:left">' . get_string('reservation_history', 'local_elibrary') . '</h3>';

		$output .= $OUTPUT->paging_bar($totalcount, $page, $perpage, "$url");

		$table = new html_table();
		$table->classes = array('logtable', 'generaltable');
		$table->align = array('left', 'center', 'center', 'center', 'center', 'center');

		$table->head = array(
			get_string('title', 'local_elibrary'),
			get_string('author', 'local_elibrary'),
			get_string('isbn', 'local_elibrary'),
			get_string('reservation_date', 'local_elibrary')
		);
		$have_username = is_object(reset($reservation_list)) && property_exists(reset($reservation_list), 'username');
		if ($have_username) {
			array_unshift($table->head, get_string('username', 'local_elibrary'));
			array_unshift($table->align, 'center');
		}

		$table->data = array();

		$startrow = $page * $perpage;
		$endrow = $startrow + $perpage;
		$endrow = ($endrow > $totalcount) ? $totalcount : $endrow;

		$i = 0;
		foreach ($reservation_list as $data) {
			$i++;
			if ($i < $startrow || $i > $endrow) {
				continue;
			}

			$table_row = array(
				$data->title,
				$data->author,
				$data->isbn,
				date('Y-m-d', $data->requestdate)
			);
			if ($have_username) {
				array_unshift($table_row, $data->username);
			}
			$table->data[] = $table_row;
		}

		$output .= '<div class="table_wrap">' . \html_writer_innoverz::table($table) . '</div>';
		$output .= $OUTPUT->paging_bar($totalcount, $page, $perpage, "$url");
		return $output;
	}

	public function review_list($review_list = null, $totalcount = 0, $page = 0, $perpage = 10, $url = "")
	{
		global $OUTPUT;
		$output = '';

		$output .= '<h3 style="text-align:left">' . get_string('reviewreport', 'local_elibrary') . '</h3>';

		$output .= \html_writer_innoverz::table_paging_header($totalcount, $page, $perpage, $url);

		$table = new html_table();
		$table->classes = array('logtable', 'generaltable');
		$table->align = array('left', 'center', 'center', 'center', 'center', 'center');

		$table->head = array(
			get_string('review', 'local_elibrary'),
			get_string('resource', 'local_elibrary'),
			get_string('user', 'local_elibrary'),
			get_string('date', 'local_elibrary'),
			''
		);

		$table->data = array();

		$OUTPUT->paging_bar_data_loop($review_list, $totalcount, $page, $perpage, function ($data) use (&$table) {
			global $CFG;
			$table->data[] = array(
				$data->message,
				\html_writer_innoverz::link(new moodle_url($CFG->LIBRARY_BASEURL . 'view_resource_detail.php', array('id' => $data->resourceid)), $data->title, array('target' => '_blank')),
				\html_writer_innoverz::link(new moodle_url('/user/profile.php', array('id' => $data->userid)), $data->username, array('target' => '_blank')),
				date('Y-m-d H:i', $data->adddate),
				\html_writer_innoverz::link(new moodle_url($CFG->LIBRARY_BASEURL . 'review_report.php', array('action' => 'hide_review', 'reviewid' => $data->id)), get_string((($data->ishide == 0) ? 'button_hide_review' : 'button_show_review'), 'local_elibrary'))
					. ' ' .
					\html_writer_innoverz::link(new moodle_url($CFG->LIBRARY_BASEURL . 'review_report.php', array('action' => 'delete_review', 'reviewid' => $data->id)), get_string('button_delete_review', 'local_elibrary'))
			);
		});

		$output .= '<div class="table_wrap">' . \html_writer_innoverz::table($table) . '</div>';
		$output .= $OUTPUT->paging_bar($totalcount, $page, $perpage, "$url");
		return $output;
	}

	public function popular_resources($resource_list = null)
	{
		global $CFG;
		$output = '';

		$output .= \html_writer_innoverz::tag('h2', get_string('top_10_resource', 'local_elibrary'));

		$output .= '<div class="popular_resources contentblock">';

		$output .= '<div class="contentsubbox">';

		$i = 1;
		foreach ($resource_list as $resource) {
			$resource->coverimage = empty($resource->coverimage) ? new moodle_url($CFG->LIBRARY_BASEURL . 'coverimage.gif') : $resource->coverimage;

			$output .= '<div class="book">';
			$output .= '<div class="book_img">';
			$output .= '<a href="' . new moodle_url($CFG->LIBRARY_BASEURL . 'view_resource_detail.php', array('id' => $resource->id)) . '">';
			if (!empty($resource->coverimage)) {
				$context = context_system::instance();
				$fs = get_file_storage();
				$hasuploadedpicture = ($fs->file_exists($context->id, 'resource', 'icon', $resource->id, '/', 'f2.png') || $fs->file_exists($context->id, 'resource', 'icon', $resource->id, '/', 'f2.jpg'));
				if (!empty($resource->coverimage) && $hasuploadedpicture) {
					$resource->coverimage = get_library_resource_image_url($resource->id, $resource->coverimage);
				} else {
					$resource->coverimage = new moodle_url($CFG->LIBRARY_BASEURL . 'coverimage.gif');
				}
			} else {
				$resource->coverimage = new moodle_url($CFG->LIBRARY_BASEURL . 'coverimage.gif');
			}
			$output .= '<img src="' . $resource->coverimage . '" alt="' . 'Library book' . '" title="' . $resource->title . '" />';
			$output .= '</a>';
			$output .= '</div>';
			$output .= '<div class="book_title">';
			$output .= '<a href="' . new moodle_url($CFG->LIBRARY_BASEURL . 'view_resource_detail.php', array('id' => $resource->id)) . '" title="' . $resource->title . '">';
			$output .= '<span style="font-weight:bold">' . $i . '. </span>' . mb_convert_case(substr($resource->title, 0, 50), MB_CASE_TITLE, "UTF-8") . ((strlen($resource->title) > 50) ? '...' : '');
			$output .= '</a>';
			$output .= '</div>';
			$output .= '</div>';
			$i++;
		}

		$output .= '</div>';	//contentsubbox

		$output .= '</div>';	//contentblock

		return $output;
	}


	public function filter_resource_history($userid, $for = "loan")
	{
		global $CFG;

		$sqlfilteroptions = array(
			get_string('filtercontains', 'local_elibrary'), get_string('filternocontain', 'local_elibrary'),
			get_string('filterisequal', 'local_elibrary'), get_string('filterstartswith', 'local_elibrary'), get_string('filterendswith', 'local_elibrary')
		);

		$output = '';

		$output .= '<div class="select_borrower">';
		$output .= '<form id="fiter_resource_history_form">';
		$output .= '<input type="hidden" name="userid" value="' . $userid . '">';
		$table = new html_table();
		$table->classes = array('logtable', 'generaltable');
		$table->align = array('left');

		$table->head = array(
			get_string("filter{$for}history", 'local_elibrary')
		);

		$table_data = '<label>';
		$table_data .= '<span>' . get_string('title', 'local_elibrary') . '</span>';
		$table_data .= \html_writer_innoverz::start_tag('select', array('id' => 'id_resourcetitle_op', 'name' => 'title_op', 'class' => 'select publicmarginbottom publicmargintop'));
		foreach ($sqlfilteroptions as $key => $str) {
			$table_data .= \html_writer_innoverz::tag('option', $str, array('value' => $key));
		}
		$table_data .= \html_writer_innoverz::end_tag('select');
		$table_data .= '<input type="text" name="title" id="resourcetitle" />';
		$table_data .= '</label>';

		//Add By Jimmy
		$table_data .= '<label>';
		$table_data .= '<span>' . get_string('accession_number', 'local_elibrary') . '</span>';
		$table_data .= '<input type="text" name="accessno" id="resourceaccessno" />';
		$table_data .= '</label>';

		$table->data = array(array($table_data));

		$table_data = '';
		$table_data .= ' <input type="submit" name="btn_searchhistory" value="' . get_string('search') . '" class="form-submit" />';

		$table->data[] = array($table_data);

		$output .= '<div class="table_wrap">' . \html_writer_innoverz::table($table) . '</div>';

		$output .= '</form>';

		$output .= '<br>';

		$output .= '</div>';

		return $output;
	}
}
