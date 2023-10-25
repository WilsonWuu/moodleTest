<?php 

require_once(__DIR__ . '/../librarypagehits/renderer.php');

class reportcoursestatistics_renderer extends reportlearningresources_renderer {
	
	var $roles;
	var $CAT_ECOURSE;
	var $CAT_CLASSROOM;
	
	public function __construct() {
		global $DB;
		$this->roles = get_all_roles();
		$this->tableattrs['id'] = 'reportcoursestatistics';
		$this->tableattrs['rowspan'] = 2;
		$table = 'course_categories';
		$select = 'id IN (?, ?)';
		$params = array(2, 3);
		$sort = 'id';
		$fields = 'id, name';
		$cats = $DB->get_records_select($table, $select, $params, $sort, $fields);
		$this->CAT_ECOURSE = $cats[2];
		$this->CAT_CLASSROOM = $cats[3];
	}
	
	//not use
	public function report_list_by_year($records) {
		global $PAGE;
		$content = '';
		$content = $this->table_start(REPORTBYYEAR);
		$content .= $this->get_all_fields_printing($records['stalist']);
		$content .= '</tbody></table>';
		
		return $content;
	}	
	
	public function report_list_by_month($records) {
		global $PAGE;
		$content = '';
		$content = $this->table_start(REPORTBYMONTH);
		$content .= $this->get_all_fields_printing($records['stalist']);
		$content .= '</tbody></table>';
		
		return $content;
	}	
	
	//not use
	public function report_list_by_day($records) {
		global $PAGE;
		$content = '';
		$content = $this->table_start(REPORTBYDAY);
		$content .= $this->get_all_fields_printing($records['stalist']);
		$content .= '</tbody></table>';		
		return $content;
	}
	
	protected function table_start($reporttypes, $table_header_fields = null) {
		return parent::table_start(-1, $this->get_table_header_fields());
	}
	
	private function get_table_header_fields() {
		$html = '
			<th class="completion-identifyfield" rowspan="2">'.get_string('ecourseclassroom','local_reports').'</th>
			<th class="completion-identifyfield" rowspan="2">'.get_string('category').'</th>
			<th class="completion-identifyfield center" colspan="2">'.get_string('numofcoursecreated','local_reports').'</th>
			</tr>
			<tr>
			<th class="completion-identifyfield fixwidth">'.get_string('onoffshelf','local_reports').'</th>
			<th class="completion-identifyfield fixwidth">'.get_string('onshelfonly','local_reports').'</th>
		';
		return $html;
	}
	
	private function get_all_fields_printing($stalist) {
		$cursupercatid = 0;
		$supercatrows = array($this->CAT_ECOURSE->id=>0, $this->CAT_CLASSROOM->id=>0);
		$supercatnames = array($this->CAT_ECOURSE->id=>$this->CAT_ECOURSE->name, $this->CAT_CLASSROOM->id=>$this->CAT_CLASSROOM->name);
		$html = '';
		foreach ($stalist as $row) {
			$supercatrows[$row->supercat]++;
			$html .= '<tr>';
			if ($cursupercatid != $row->supercat) {
				$cursupercatid = $row->supercat;
				$html .= '<td class="completion-progresscell" rowspan=":supercat'.$row->supercat.'">'.$supercatnames[$row->supercat].'</td>';
			}
			$html .= '<td class="completion-progresscell">'.$row->catname.'</td>';
			$html .= '<td class="completion-progresscell">'.$row->recordnum.'</td>';
			$html .= '<td class="completion-progresscell">'.$row->activenum .'</td>';
			$html .= '</tr>';
		}
		$html = str_replace(
			array(':supercat'.$this->CAT_ECOURSE->id, ':supercat'.$this->CAT_CLASSROOM->id),
			$supercatrows, $html
		);
		return $html;
	}				

}

?>