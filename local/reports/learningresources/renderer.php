<?php 

class reportlearningresources_renderer {

	var $tableattrs = array();
	
	public function report_list($frmdata, $records) {
		global $OUTPUT;
		$content = '';
		switch($frmdata->reporttype) {
			case REPORTBYYEAR:
				$startdate = date('Y', $frmdata->startdate);
				$enddate = date('Y', $frmdata->enddate);			
				$functionname = 'report_list_by_year';
				break;
			case REPORTBYMONTH:
				$startdate = date('M Y', $frmdata->startdate);
				$enddate = date('M Y', $frmdata->enddate);
				$functionname = 'report_list_by_month';
				break;
			case REPORTBYDAY:
				$startdate = date('d M Y', $frmdata->startdate);
				$enddate = date('d M Y', $frmdata->enddate);
				$functionname = 'report_list_by_day';
				break;
		}
		$content .= $OUTPUT->heading($startdate . ' - ' . $enddate, 3);
		$content .= $this->$functionname($records);
		return $content;
	}
	
	public function report_list_by_year($records) {
		global $PAGE;
		$content = '';
		$content = $this->table_start(REPORTBYYEAR, $this->get_table_header_fields());
		for($year = $records['firstyear']; $year<=$records['lastyear']; $year++) {
			$date = $year;
			list($numofvideos,$numofresources) = $this->getNumofvideosNResources($date, $records['videolist'], $records['resourcelist']);
			$content .= '
				<tr>
					<td class="completion-progresscell">'.$year.'</td>
					<td class="completion-progresscell">'.$numofvideos.'</td>
					<td class="completion-progresscell">'.$numofresources.'</td>
				</tr>
			';
		}
		$content .= '</tbody></table>';
		
		return $content;
	}	
	
	public function report_list_by_month($records) {
		global $PAGE;
		$content = '';
		$content = $this->table_start(REPORTBYMONTH, $this->get_table_header_fields());
		for($year = $records['firstyear']; $year<=$records['lastyear']; $year++) {
			$lastmonth = $year == $records['lastyear'] ? $records['lastmonth'] : 12;
			$firstmonth = $year == $records['firstyear'] ? $records['firstmonth'] : 1;
			$content .= '
				<tr>
					<td class="completion-progresscell" rowspan="'.($lastmonth-$firstmonth+1).'">'.$year.'</td>
			';
			for($month = intval($firstmonth); $month<=$lastmonth; $month++) {
				if ($month > $firstmonth) {
					$content .= '<tr>';
				}
				if ($month < 10) {
					$month = "0$month";
				}
				$date = $year . ' ' . $month;
				list($numofvideos,$numofresources) = $this->getNumofvideosNResources($date, $records['videolist'], $records['resourcelist']);
				$content .= '
						<td class="completion-progresscell">'.$month.'</td>
						<td class="completion-progresscell">'.$numofvideos.'</td>
						<td class="completion-progresscell">'.$numofresources.'</td>
					</tr>
				';
			}		
		}
		$content .= '</tbody></table>';
		
		return $content;
	}	
	
	public function report_list_by_day($records) {
		global $PAGE;
		$content = $this->table_start(REPORTBYDAY, $this->get_table_header_fields());
		for($year = $records['firstyear']; $year<=$records['lastyear']; $year++) {
			$lastmonth = $year == $records['lastyear'] ? $records['lastmonth'] : 12;
			$firstmonth = $year == $records['firstyear'] ? $records['firstmonth'] : 1;
			$subcontent = '';
			for($month = intval($firstmonth); $month<=$lastmonth; $month++) {
				if ($month > $firstmonth) {
					$subcontent .= '<tr>';
				}
				if ($month < 10) {
					$month = "0$month";
				}
				$lastday = ($year == $records['lastyear'] && $month == $records['lastmonth']) ? $records['lastday'] : cal_days_in_month(CAL_GREGORIAN, $month, $year);
				$firstday = ($year == $records['firstyear'] && $month == $records['firstmonth']) ? $records['firstday'] : 1;
				$subcontent .= '
					<td class="completion-progresscell" rowspan="'.($lastday-$firstday+1).'">'.$year.' / '.$month.'</td>
				';
				for($day = intval($firstday); $day<=$lastday; $day++) {
					if ($day > $firstday) {
						$subcontent .= '<tr>';
					}
					
					if ($day < 10) {
						$day = "0$day";
					}
					$date = $year . ' ' . $month . ' ' . $day;
					list($numofvideos,$numofresources) = $this->getNumofvideosNResources($date, $records['videolist'], $records['resourcelist']);
					$subcontent .= '
							<td class="completion-progresscell">'.$day.'</td>
							<td class="completion-progresscell">'.$numofvideos.'</td>
							<td class="completion-progresscell">'.$numofresources.'</td>
						</tr>
					';
				}
			}
			$content .= $subcontent;
		}
		$content .= '</tbody></table>';		
		return $content;
	}
	
	public function download_actions($suburl, $params = array()) {
		$params['download'] = 1;
		$params['sesskey'] = sesskey();
		$content = '<div id="printrptbtnblock">';
		$content .= '<div class="verticalspace30"></div>';
		$content .= html_writer::link(new moodle_url($suburl, $params), 
			html_writer::empty_tag('input', array('type'=>'button', 'class'=>'btn btn-primary', 'name'=>'btn_downloadexcel', 'value'=>get_string('downloadinexcel','local_reports'))));
		
		$content .= '<div class="topspacebox"></div>';
		$params['download'] = 0;
		$params['printfriendlyreport'] = 1;
		$content .= html_writer::link(new moodle_url($suburl, $params), 
			html_writer::empty_tag('input', array('type'=>'button', 'class'=>'btn btn-primary', 'name'=>'btn_printfriendly', 'value'=>get_string('printfriendlyreport','local_reports'))),
			array('target'=>'blank'));
		$content .= '</div>';
		/*$content = '
			<div class="verticalspace30"></div>
			<ul class="export-actions">
				<li>' . html_writer::link(new moodle_url($suburl, $params), get_string('downloadinexcel')) . '</li>
			</ul>
		';*/
		return $content;
	}
	
	public function start_layout($classnames = "reportmain") {
		return '<div class="'.$classnames.'">';
	}
	
	public function end_layout() {
		return '</div>';
	}
	
	public function download_report($frmdata, $records, $filename = "learningresourcesreport", $heading = 'Report') {
		$file=$filename;
		$content = '';
		//$content = "<h2>$heading</h2>";	
		$date = date('Y_m_d');
		switch($frmdata->reporttype) {
		case REPORTBYYEAR:
			$file.="byyear";
			$content .= $this->report_list_by_year($records);
			break;
		case REPORTBYMONTH:
			$file.="bymonth";
			$content .= $this->report_list_by_month($records);
			break;
		case REPORTBYDAY:
			$file.="byday";
			$content .= $this->report_list_by_day($records);
			break;
		}
		$file.=$date.'.xls';
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$file");
		echo $content;
		exit();
	}
	
	protected function table_start($reporttype, $table_header_fields) {
		$id = '';
		$rowspan = '';
		if (isset($this->tableattrs['id'])) {
			$id = 'id="'.$this->tableattrs['id'].'"';
		}
		if (isset($this->tableattrs['rowspan'])) {
			$rowspan = 'rowspan="'.$this->tableattrs['rowspan'].'"';
		}
		switch($reporttype) {
		case REPORTBYYEAR:
			$content = '
			<table '.$id.' cellpadding="5" border="1" class="flexible boxaligncenter">
				<thead>
					<tr>
						<th class="completion-identifyfield" '.$rowspan.'>'.ucfirst(get_string('year')).'</th>
						'.$table_header_fields.'
					</tr>
				</thead>
				<tbody>
			';
			break;
		case REPORTBYMONTH:
			$content = '
			<table '.$id.' cellpadding="5" border="1" class="flexible boxaligncenter">
				<thead>
					<tr>
						<th class="completion-identifyfield" '.$rowspan.'>'.ucfirst(get_string('year')).'</th>
						<th class="completion-identifyfield" '.$rowspan.'>'.get_string('month').'</th>
						'.$table_header_fields.'
					</tr>
				</thead>
				<tbody>
			';
			break;
		case REPORTBYDAY:
			$content = '
			<table '.$id.' cellpadding="5" border="1" class="flexible boxaligncenter">
				<thead>
					<tr>
						<th class="completion-identifyfield" '.$rowspan.'>'.ucfirst(get_string('year')).' / '.get_string('month').'</th>
						<th class="completion-identifyfield" '.$rowspan.'>'.ucfirst(get_string('day')).'</th>
						'.$table_header_fields.'
					</tr>
				</thead>
				<tbody>
			';
			break;
		default:
			$content = '
			<table '.$id.' cellpadding="5" border="1" class="flexible boxaligncenter">
				<thead>
					<tr>
						'.$table_header_fields.'
					</tr>
				</thead>
				<tbody>
			';
		}
		return $content;
	}
	
	private function get_table_header_fields() {
		$html = '
			<th class="completion-identifyfield">'.get_string('numofvideos','local_reports').'</th>
			<th class="completion-identifyfield">'.get_string('numofresources','local_reports').'</th>
		';	
		return $html;
	}
	
	private function getNumofvideosNResources($date, $videolist, $resourcelist) {
		$nums = array(0, 0);
		if (isset($videolist[$date])) {
			$nums[0] = $videolist[$date]->recordnum;
		}
		if (isset($resourcelist[$date])) {
			$nums[1] = $resourcelist[$date]->recordnum;
		}
		return $nums;
	}

}

?>