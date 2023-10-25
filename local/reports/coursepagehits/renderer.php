<?php 

require_once(__DIR__ . '/../learningresources/renderer.php');

class reportcoursepagehits_renderer extends reportlearningresources_renderer {
	
	var $roles;
	var $CAT_ECOURSE;
	var $CAT_CLASSROOM;
	var $curcat; //indicate the course belong ecourse or classroom
	var $curcatrows;
	
	public function __construct() {
		global $DB;
		$this->roles = get_all_roles();
		$this->tableattrs['id'] = 'reportcoursepagehits';
		$this->tableattrs['rowspan'] = 2;
		$table = 'course_categories';
		$select = 'id IN (?, ?)';
		$params = array(2, 3);
		$sort = 'id';
		$fields = 'id, name';
		$cats = $DB->get_records_select($table, $select, $params, $sort, $fields);
		$this->CAT_ECOURSE = $cats[2];
		$this->CAT_CLASSROOM = $cats[3];
		$this->curcat = $this->CAT_ECOURSE->id;
		$this->curcatrows = 0;
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
		return parent::table_start($reporttypes, $this->get_table_header_fields());
	}
	
	private function get_table_header_fields() {
		$html = '
			<th class="completion-identifyfield" rowspan="2">'.get_string('ecourseclassroom','local_reports').'</th>
			<th class="completion-identifyfield" rowspan="2">'.get_string('category').'</th>
			<th class="completion-identifyfield" rowspan="2">'.get_string('course').'</th>
			<th class="completion-identifyfield center" colspan="'.count($this->roles).'">'.get_string('numofpagehits','local_reports').'</th>
			</tr>
			<tr>
		';
		foreach($this->roles as $role) {
			$html .= '<th class="completion-identifyfield fixwidth">'.$role->shortname.'</th>';
		}
		return $html;
	}
	
	private function getNumofEachField($date, $coursehitslist) {
		$data = array();
		foreach ($this->roles as $role) {
			$key = $date. ' '.$role->shortname;
			$data[$role->shortname] = isset($coursehitslist[$key]) ? $coursehitslist[$key]->recordnum : 0;
		}
		return $data;
	}
	
	protected function get_all_fields_printing($date, $coursehitslist, &$monthrows) {
		
		if (isset($coursehitslist[$date])) {
			$html = '';
			$ecourses = isset($coursehitslist[$date][$this->CAT_ECOURSE->id])?$coursehitslist[$date][$this->CAT_ECOURSE->id]:array();
			$classrooms = isset($coursehitslist[$date][$this->CAT_CLASSROOM->id])?$coursehitslist[$date][$this->CAT_CLASSROOM->id]:array();
			if (count($ecourses)) {
				$supercatrows = 0;
				$html .= '<td class="completion-progresscell" rowspan="%d">'.$this->CAT_ECOURSE->name.'</td>';
				$isfirst1 = true;
				foreach($ecourses as $catname=>$cats) {
					if (!$isfirst1) {
						$html .= '<tr>';
					}
					$html .= '<td class="completion-progresscell" rowspan="'.count($cats).'">'.$catname.'</td>';
					$isfirst2 = true;
					foreach ($cats as $catcourses) {
						$supercatrows++;
						$monthrows++;
						$nums = $this->getNumofEachField($date, $catcourses);
						if (!$isfirst2) {
							$html .= '<tr>';
						}
						$coursename = reset($catcourses)->coursename;
						$html .= '<td class="completion-progresscell">'.$coursename.'</td>';
						$html .= $this->print_all_roles_logs($nums);
						$html .= '</tr>';
						$isfirst2 = false;
					}		
					$isfirst1 = false;
				}
				$html = sprintf($html, $supercatrows);
			} else {
				$monthrows++;
				$html .= '
					<td class="completion-progresscell">'.$this->CAT_ECOURSE->name.'</td>
					<td class="completion-progresscell">-</td>
					<td class="completion-progresscell">-</td>
					'.$this->print_all_roles_empty().'
					</tr>
				';
			}
			if (count($classrooms)) {
				$supercatrows = 0;
				$html .= '<tr><td class="completion-progresscell" rowspan="%d">'.$this->CAT_CLASSROOM->name.'</td>';
				$isfirst1 = true;
				foreach($classrooms as $catname=>$cats) {
					if (!$isfirst1) {
						$html .= '<tr>';						
					}
					$html .= '<td class="completion-progresscell" rowspan="'.count($cats).'">'.$catname.'</td>';
					$isfirst2 = true;
					foreach ($cats as $catcourses) {
						$supercatrows++;
						$monthrows++;
						$nums = $this->getNumofEachField($date, $catcourses);
						if (!$isfirst2) {
							$html .= '<tr>';							
						}
						$coursename = reset($catcourses)->coursename;
						$html .= '<td class="completion-progresscell">'.$coursename.'</td>';
						$html .= $this->print_all_roles_logs($nums);
						$html .= '</tr>';
						$isfirst2 = false;
					}
					$isfirst1 = false;
				}
				$html = sprintf($html, $supercatrows);
			} else {
				$monthrows++;
				$html .= '
					<tr>
					<td class="completion-progresscell">'.$this->CAT_CLASSROOM->name.'</td>
					<td class="completion-progresscell">-</td>
					<td class="completion-progresscell">-</td>
					'.$this->print_all_roles_empty().'
					</tr>
				';
			}
		} else {
			$monthrows = 2;
			$html = '
				<td class="completion-progresscell">'.$this->CAT_ECOURSE->name.'</td>
				<td class="completion-progresscell">-</td>
				<td class="completion-progresscell">-</td>
				'.$this->print_all_roles_empty().'
				</tr>
				<tr>
				<td class="completion-progresscell">'.$this->CAT_CLASSROOM->name.'</td>
				<td class="completion-progresscell">-</td>
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
	
	private function print_all_roles_logs($nums) {
		$html = '';
		foreach ($nums as $num) {
			$html .= '<td class="completion-progresscell">'.$num.'</td>';
		}
		return $html;
	}

}

?>