<?php 

require_once($CFG->dirroot . '/local/reports/learningresources/renderer.php');

class reportloan_renderer extends reportlearningresources_renderer {
	
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
		$content = $this->table_start(REPORTBYYEAR);
		for($year = $records['firstyear']; $year<=$records['lastyear']; $year++) {
			$date = $year;
			$numofloan = isset($records['loanlist'][$date]->recordnum) ? $records['loanlist'][$date]->recordnum : 0;
			$content .= '
				<tr>
					<td class="completion-progresscell">'.$year.'</td>
					<td class="completion-progresscell">'.$numofloan.'</td>
				</tr>
			';
		}
		$content .= '</tbody></table>';
		
		return $content;
	}	
	
	public function report_list_by_month($records) {
		global $PAGE;
		$content = '';
		$content = $this->table_start(REPORTBYMONTH);
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
				$numofloan = isset($records['loanlist'][$date]->recordnum) ? $records['loanlist'][$date]->recordnum : 0;
				$content .= '
						<td class="completion-progresscell">'.$month.'</td>
						<td class="completion-progresscell">'.$numofloan.'</td>
					</tr>
				';
			}		
		}
		$content .= '</tbody></table>';
		
		return $content;
	}	
	
	public function report_list_by_day($records) {
		global $PAGE;
		$content = '';
		$content = $this->table_start(REPORTBYDAY);
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
					$numofloan = isset($records['loanlist'][$date]->recordnum) ? $records['loanlist'][$date]->recordnum : 0;
					$subcontent .= '
							<td class="completion-progresscell">'.$day.'</td>
							<td class="completion-progresscell">'.$numofloan.'</td>
						</tr>
					';
				}
			}
			$content .= $subcontent;
		}
		$content .= '</tbody></table>';		
		return $content;
	}
	
	protected function table_start($reporttypes, $table_header_fields = null) {
		return parent::table_start($reporttypes, $this->get_table_header_fields());
	}
	
	private function get_table_header_fields() {
		$html = '
			<th class="completion-identifyfield">'.get_string('numofloan', 'local_elibrary').'</th>
			</tr>
		';
		return $html;
	}

}

?>