<?php 

require_once(__DIR__ . '/../learningresources/renderer.php');

class reportvisitsfrequency_renderer extends reportlearningresources_renderer {
	
	var $roles;
	var $timeslots;
	
	public function __construct() {
		$this->roles = get_all_roles();
		$this->tableattrs['id'] = 'reportvisitsfrequency';
		$this->tableattrs['rowspan'] = 2;
		$this->timeslots = array(9, 13, 14, 18, 24);
	}
	
	public function report_list_by_year($records) {
		global $PAGE;
		$content = $this->print_role_header($records['roleid']);
		$content .= $this->table_start(REPORTBYYEAR);
		for($year = $records['firstyear']; $year<=$records['lastyear']; $year++) {
			$date = $year;
			$data = $this->getNumofEachField($date, $records['freqlist']);		
			$content .= '
				<tr>
					<td class="completion-progresscell">'.$year.'</td>
					'.$this->print_all_fields($data).'
				</tr>
			';
		}
		$content .= '</tbody></table>';
		
		return $content;
	}	
	
	public function report_list_by_month($records) {
		global $PAGE;
		$content = $this->print_role_header($records['roleid']);
		$content .= $this->table_start(REPORTBYMONTH);
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
				$data = $this->getNumofEachField($date, $records['freqlist']);		
				$content .= '
						<td class="completion-progresscell">'.$month.'</td>
						'.$this->print_all_fields($data).'
					</tr>
				';
			}		
		}
		$content .= '</tbody></table>';
		
		return $content;
	}	
	
	public function report_list_by_day($records) {
		global $PAGE;
		$content = $this->print_role_header($records['roleid']);
		$content .= $this->table_start(REPORTBYDAY);
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
					$data = $this->getNumofEachField($date, $records['freqlist']);		
					$subcontent .= '
							<td class="completion-progresscell">'.$day.'</td>
							'.$this->print_all_fields($data).'
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
		$len = count($this->timeslots);
		$html = '
			<th class="completion-identifyfield center" colspan="'.$len.'">'.get_string('timeslots','local_reports').'</th>
			</tr>
			<tr>
		';
		for($i=0; $i<$len; $i++) {
			$html .= '<th class="completion-identifyfield fixwidth">'.get_string("timeslot$i",'local_reports').'</th>';
		}
		return $html;
	}
	
	private function getNumofEachField($date, $freqlist) {
		$nums = array(0, 0, 0, 0, 0);
		$tltindex = 0;
		$controls = array(1, 1, 1, 1, 1);
		for ($hour = 0; $hour<24; $hour++) {
			if ($hour >= $this->timeslots[$tltindex] && $controls[$tltindex]) {
				$controls[$tltindex] = 0;
				$tltindex++;
			}
			$hour = str_pad($hour,2,'0',STR_PAD_LEFT);
			$key = $date.' '.$hour; 
			if (isset($freqlist[$key])) {
				$nums[$tltindex] += $freqlist[$key]->recordnum;
			}
		}
		return $nums;
	}
	
	private function print_all_fields($data) {
		$html = '';
		foreach ($data as $val) {
			$html .= '<td class="completion-progresscell">'.$val.'</td>';
		}
		return $html;
	}
	
	private function print_role_header($roleid) {
		global $OUTPUT;
		$rolename = $roleid == 'anonymous' ? get_string('anonymous','local_reports') : $this->roles[$roleid]->name;
		return $OUTPUT->heading($rolename, 3);
	}

}

?>