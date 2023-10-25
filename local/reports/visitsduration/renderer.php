<?php 

require_once(__DIR__ . '/../learningresources/renderer.php');

class reportvisitsduration_renderer extends reportlearningresources_renderer {
	
	var $roles;
	
	public function __construct() {
		$this->roles = get_all_roles();
		$this->tableattrs['id'] = 'reportvisitsduration';
		$this->tableattrs['rowspan'] = 2;
	}
	
	public function report_list_by_year($records) {
		global $PAGE;
		$content = '';
		$content = $this->table_start(REPORTBYYEAR);
		for($year = $records['firstyear']; $year<=$records['lastyear']; $year++) {
			$date = $year;
			$durations = $this->getNumofEachField($date, $records['visitsdurlist']);
			$content .= '
				<tr>
					<td class="completion-progresscell">'.$year.'</td>
					'.$this->print_duration_logs($durations).'
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
				$durations = $this->getNumofEachField($date, $records['visitsdurlist']);
				$content .= '
						<td class="completion-progresscell">'.$month.'</td>
						'.$this->print_duration_logs($durations).'
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
					$durations = $this->getNumofEachField($date, $records['visitsdurlist']);
					$subcontent .= '
							<td class="completion-progresscell">'.$day.'</td>
							'.$this->print_duration_logs($durations).'
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
			<th class="completion-identifyfield center" colspan="'.count($this->roles).'">'.get_string('durationhms','local_reports').'</th>
			</tr>
			<tr>
		';
		foreach($this->roles as $role) {
			$html .= '<th class="completion-identifyfield">'.$role->shortname.'</th>';
		}
		return $html;
	}
	
	private function getNumofEachField($date, $visitsdurlist) {
		$data = array();
		foreach ($this->roles as $role) {
			$key = $date. ' '.$role->shortname;
			$data[$role->shortname] = isset($visitsdurlist[$key]) ? $visitsdurlist[$key]->durationtime : 0;
		}
		return $data;
	}
	
	private function print_duration_logs($durations) {
		$html = '';
		foreach ($durations as $duration) {
			$hour = (int)($duration/60/60);
			$min = $duration/60%60;
			$second = $duration%60;
			$html .= '<td class="completion-progresscell">'.sprintf('%02d:%02d:%02d',$hour, $min, $second).'</td>';
		}
		return $html;
	}

}

?>