<?php 

require_once(__DIR__ . '/../learningresources/renderer.php');
require_once($CFG->dirroot . '/km/lib.php');

class reportkmpagehits_renderer extends reportlearningresources_renderer {
	
	var $roles;
	
	public function __construct() {
		$this->roles = get_all_roles();
		$this->tableattrs['id'] = 'reportkmpagehits';
		$this->tableattrs['rowspan'] = 2;
	}
	
	public function report_list_by_year($records) {
		global $PAGE;
		$content = '';
		$content = $this->table_start(REPORTBYYEAR);
		for($year = $records['firstyear']; $year<=$records['lastyear']; $year++) {
			$date = $year;
			$yearrows = 0;
			$subcontent1 = '
				<tr>
					<td class="completion-progresscell" rowspan=":yearrows">'.$year.'</td>
					'.$this->get_all_fields_printing($date, $records['hitslist'], $yearrows).'
			';
			$content .= str_replace(':yearrows', $yearrows, $subcontent1);		
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
			$yearrows = 0;
			$subcontent1 = '
				<tr>
					<td class="completion-progresscell" rowspan=":yearrows">'.$year.'</td>
			';
			for($month = intval($firstmonth); $month<=$lastmonth; $month++) {
				if ($month > $firstmonth) {
					$subcontent1 .= '<tr>';
				}
				if ($month < 10) {
					$month = "0$month";
				}
				$date = $year . ' ' . $month;
				$monrows = 0;
				$subcontent2 = '
						<td class="completion-progresscell" rowspan=":month">'.$month.'</td>
						'.$this->get_all_fields_printing($date, $records['hitslist'], $monrows).'
				';
				$subcontent1 .= str_replace(':month', $monrows, $subcontent2);
				$yearrows += $monrows;
			}		
			$subcontent1 = str_replace(':yearrows', $yearrows, $subcontent1);
			$content .= $subcontent1;
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
				$yearmonrows = 0;
				$subcontent1 = '
					<td class="completion-progresscell" rowspan=":yearmonrows">'.$year.' / '.$month.'</td>
				';
				$subcontent2 = '';
				for($day = intval($firstday); $day<=$lastday; $day++) {
					if ($day > $firstday) {
						$subcontent2 .= '<tr>';
					}
					
					if ($day < 10) {
						$day = "0$day";
					}
					$date = $year . ' ' . $month . ' ' . $day;
					$dayrows = 0;
					$subcontent2 = '
						<td class="completion-progresscell" rowspan=":dayrows">'.$day.'</td>
						'.$this->get_all_fields_printing($date, $records['hitslist'], $dayrows).'
					';
					$subcontent1 .= str_replace(':dayrows', $dayrows, $subcontent2);
					$yearmonrows += $dayrows;
				}
				$subcontent1 = str_replace(':yearmonrows', $yearmonrows, $subcontent1);
				$subcontent .= $subcontent1;
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
			<th class="completion-identifyfield" rowspan="2">'.get_string('branch').'</th>
			<th class="completion-identifyfield center" colspan="'.count($this->roles).'">'.get_string('numofpagehits','local_reports').'</th>
			</tr>
			<tr>
		';
		foreach($this->roles as $role) {
			$html .= '<th class="completion-identifyfield fixwidth">'.$role->shortname.'</th>';
		}
		return $html;
	}
	
	private function getNumofEachField($rolesnum) {  
		$nums = array();
		foreach ($this->roles as $role) { 
			$nums[$role->shortname] = isset($rolesnum[$role->shortname]) ? $rolesnum[$role->shortname] : 0;
		}		
		return $nums;
	}
	
	private function get_all_fields_printing($date, $hitslist, &$rowcount) {
		global $SP_BRANCHS;
		$html = '';
		$thislist = isset($hitslist[$date]) ? $hitslist[$date] : null;
		if ($thislist) {
			foreach ($thislist as $key=>$row) {
				if ($rowcount++) {
					$html .= '<tr>';
				}
				$html .= '<td class="completion-progresscell">'.$SP_BRANCHS[$key].'</td>';
				$nums = $this->getNumofEachField($row);
				$html .= $this->print_roles_nums($nums);
				$html .= '</tr>';
			}
		} else {
			$rowcount = 1;
			$html .= '
				<td class="completion-progresscell">-</td>
				'.$this->print_all_roles_empty().'
				</tr>
			';
		}
		return $html;
	}
	
	private function print_all_roles_empty() {
		$html = '';
		$len = count($this->roles);
		for($i=0; $i<$len; $i++) {
			$html .= '<td class="completion-progresscell">-</td>';
		}
		return $html;
	}
	
	private function print_roles_nums($nums) {
		$html = '';
		foreach ($nums as $recordnum) {
			$html .= '<td class="completion-progresscell">'.$recordnum.'</td>';
		}
		return $html;
	}

}

?>