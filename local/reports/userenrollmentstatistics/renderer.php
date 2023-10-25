<?php 

require_once(__DIR__ . '/../coursepagehits/renderer.php');

class reportuserenrollmentstatistics_renderer extends reportcoursepagehits_renderer {
	
	public function __construct() {
		global $DB;
		parent::__construct();
		$this->tableattrs['id'] = 'reportuserenrollmentstatistics';
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
					'.$this->get_all_fields_printing($date, $records['stalist'], $yearrows).'
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
			$subcontent2 = '';
			for($month = intval($firstmonth); $month<=$lastmonth; $month++) {
				if ($month > $firstmonth) {
					$subcontent1 .= '<tr>';
				}
				if ($month < 10) {
					$month = "0$month";
				}
				$date = $year . ' ' . $month;
				$monthrows = 0;
				$subcontent2 = '
						<td class="completion-progresscell" rowspan=":monthrows">'.$month.'</td>
						'.$this->get_all_fields_printing($date, $records['stalist'], $monthrows).'
				';
				$subcontent1 .= str_replace(':monthrows', $monthrows, $subcontent2);
				$yearrows += $monthrows;
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
						'.$this->get_all_fields_printing($date, $records['stalist'], $dayrows).'
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
		return reportlearningresources_renderer::table_start($reporttypes, $this->get_table_header_fields());
	}
	
	private function get_table_header_fields() {
		$html = '
			<th class="completion-identifyfield" rowspan="2">'.get_string('ecourseclassroom','local_reports').'</th>
			<th class="completion-identifyfield" rowspan="2">'.get_string('category').'</th>
			<th class="completion-identifyfield" rowspan="2">'.get_string('course').'</th>
			<th class="completion-identifyfield center" colspan="'.count($this->roles).'">'.get_string('numofenrolleduser','local_reports').'</th>
			</tr>
			<tr>
		';
		foreach($this->roles as $role) {
			$html .= '<th class="completion-identifyfield fixwidth">'.$role->shortname.'</th>';
		}
		return $html;
	}

}

?>