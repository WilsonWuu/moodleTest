<?php 

require_once(__DIR__ . '/../learningresources/renderer.php');

class reportuserlastvisits_renderer extends reportlearningresources_renderer {
	
	var $roles;
	
	public function __construct() {
		$this->roles = get_all_roles();
		$this->tableattrs['id'] = 'reportuserlastvisits';
		$this->tableattrs['rowspan'] = 2;
	}
	
	//not use
	public function report_list_by_year($records) {
		global $PAGE;
		$content = '';
		$content = $this->table_start(REPORTBYYEAR);
		for($year = $records['firstyear']; $year<=$records['lastyear']; $year++) {
			$date = $year;
			
			$numoflogin = $this->getNumofEachField($date, $records['lastloginlist']);
			$content .= '
				<tr>
					<td class="completion-progresscell">'.$year.'</td>
					'.$this->print_lastlogin_logs($numoflogin).'
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
				$numoflogin = $this->getNumofEachField($date, $records['lastloginlist']);		
				$content .= '
						<td class="completion-progresscell">'.$month.'</td>
						'.$this->print_lastlogin_logs($numoflogin).'
					</tr>
				';
			}		
		}
		$content .= '</tbody></table>';
		
		return $content;
	}	
	
	//not use
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
					$lastloginlist = $this->getNumofEachField($date, $records['lastloginlist']);		
					$subcontent .= '
							<td class="completion-progresscell">'.$day.'</td>
							'.$this->print_lastlogin_logs($lastloginlist).'
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
			<th class="completion-identifyfield center" colspan="'.count($this->roles).'">'.get_string('numoflastloginusers','local_reports').'</th>
			</tr>
			<tr>
		';
		foreach($this->roles as $role) {
			$html .= '<th class="completion-identifyfield fixwidth">'.$role->shortname.'</th>';
		}
		return $html;
	}
	
	private function getNumofEachField($date, $lastvisitlist) {
		$data = array();
		foreach ($this->roles as $role) {
			$key = $date. ' '.$role->shortname;
			$data[$role->shortname] = isset($lastvisitlist[$key]) ? $lastvisitlist[$key]->recordnum : 0;
		}
		return $data;
	}
	
	private function print_lastlogin_logs($numoflogin) {
		$html = '';
		foreach ($numoflogin as $recordnum) {
			$html .= '<td class="completion-progresscell">'.$recordnum.'</td>';
		}
		return $html;
	}
	
}

?>