<?php 

require_once(__DIR__ . '/../learningresources/renderer.php');

class reportvisitorstatistics_renderer extends reportlearningresources_renderer {
	
	var $roles;
	
	public function __construct() {
		$this->roles = get_all_roles();
		$this->tableattrs['id'] = 'reportvisitorstatistics';
		$this->tableattrs['rowspan'] = 2;
	}
	
	public function report_list_by_year($records) {
		global $PAGE;
		$content = '';
		$content = $this->table_start(REPORTBYYEAR);
		for($year = $records['firstyear']; $year<=$records['lastyear']; $year++) {
			$date = $year;
			list($numofvisits,$numofunivisitors, $numoflogin) = $this->getNumofEachField($date, $records['visitlist'], $records['univisitlist'], $records['loginlist']);		
			$content .= '
				<tr>
					<td class="completion-progresscell">'.$year.'</td>
					<td class="completion-progresscell">'.$numofvisits.'</td>
					<td class="completion-progresscell">'.$numofunivisitors.'</td>
					'.$this->print_login_logs($numoflogin).'
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
				list($numofvisits,$numofunivisitors, $numoflogin) = $this->getNumofEachField($date, $records['visitlist'], $records['univisitlist'], $records['loginlist']);		
				$content .= '
						<td class="completion-progresscell">'.$month.'</td>
						<td class="completion-progresscell">'.$numofvisits.'</td>
						<td class="completion-progresscell">'.$numofunivisitors.'</td>
						'.$this->print_login_logs($numoflogin).'
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
					list($numofvisits,$numofunivisitors, $numoflogin) = $this->getNumofEachField($date, $records['visitlist'], $records['univisitlist'], $records['loginlist']);		
					$subcontent .= '
							<td class="completion-progresscell">'.$day.'</td>
							<td class="completion-progresscell">'.$numofvisits.'</td>
							<td class="completion-progresscell">'.$numofunivisitors.'</td>
							'.$this->print_login_logs($numoflogin).'
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
			<th class="completion-identifyfield" rowspan="2">'.get_string('numofvisits','local_reports').'</th>
			<th class="completion-identifyfield" rowspan="2">'.get_string('numofunivisit','local_reports').'</th>
			<th class="completion-identifyfield center" colspan="'.count($this->roles).'">'.get_string('numofuserlogin','local_reports').'</th>
			</tr>
			<tr>
		';
		foreach($this->roles as $role) {
			$html .= '<th class="completion-identifyfield fixwidth">'.$role->shortname.'</th>';
		}
		return $html;
	}
	
	private function getNumofEachField($date, $visitlist, $univisitlist, $loginlist) {
		$nums = array(0, 0, 0);
		if (isset($visitlist[$date])) {
			$nums[0] = $visitlist[$date]->recordnum;
		}
		if (isset($univisitlist[$date])) {
			$nums[1] = $univisitlist[$date]->recordnum;
		}
		$logindata = array();
		foreach ($this->roles as $role) {
			$key = $date. ' '.$role->shortname;
			$logindata[$role->shortname] = isset($loginlist[$key]) ? $loginlist[$key]->recordnum : 0;
		}
		$nums[2] = $logindata;
		return $nums;
	}
	
	private function print_login_logs($numoflogin) {
		$html = '';
		foreach ($numoflogin as $recordnum) {
			$html .= '<td class="completion-progresscell">'.$recordnum.'</td>';
		}
		return $html;
	}

}

?>