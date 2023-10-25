<?php
use html_writer as html_writer;

class html_writer_innoverz extends html_writer
{

	public static function new_record_widget($url, $text, $imgalt = '', $imgtitle = '', $setbottomspace = true, $class = "")
	{
		global $OUTPUT;
		$newrecordclasses = 'newrecordwidget';
		if ($setbottomspace) {
			$newrecordclasses .= ' publicmarginbottom2';
		}
		if (!empty($class)) {
			$newrecordclasses .= " $class";
		}
		$imgalt = empty($imgalt) ? $text : $imgalt;
		$imgtitle = empty($imgtitle) ? $text : $imgtitle;
		$output = '';
		$output .= html_writer_innoverz::start_tag('a', array('href' => $url));
		$output .= html_writer_innoverz::empty_tag('img', array('src' => $OUTPUT->image_url('add_btn', 'theme'), 'alt' => "$imgalt", 'title' => "$imgtitle", 'class' => 'addimage'));
		$output .= html_writer_innoverz::tag('span', $text);
		$output .= html_writer_innoverz::end_tag('a');
		$output = html_writer_innoverz::tag('div', $output, array('class' => $newrecordclasses));
		$output .= html_writer_innoverz::empty_tag('div', array('style' => 'clear:both'));
		return $output;
	}

	public static function table_paging_header($totalcount, $page, $perpage, $url)
	{
		global $OUTPUT;
		$output = '';
		$output .= '<div class="divtable">';
		$output .= '<div class="tablecell">';
		$output .= get_string("displayingrecords", "", $totalcount);
		$output .= '</div>';

		$output .= $OUTPUT->paging_bar($totalcount, $page, $perpage, "$url");
		$output .= '</div>';
		return $output;
	}
}
