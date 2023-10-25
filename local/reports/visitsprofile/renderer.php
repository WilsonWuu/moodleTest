<?php

require_once(__DIR__ . '/../../../vendor/autoload.php');
require_once(__DIR__ . '/../learningresources/renderer.php');

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Html;

class reportvisitsprofile_renderer extends reportlearningresources_renderer
{

	var $roles;
	var $table_header_fields;
	var $tableattrs = array();

	public function __construct()
	{
		$this->roles = get_all_roles();
		$this->tableattrs['id'] = 'reportvisitsprofile';
		$this->tableattrs['rowspan'] = 2;
		$this->table_header_fields = array(
			get_string('username', 'local_reports'),
			get_string('surname', 'local_reports'),
			get_string('firstname', 'local_reports'),
			get_string('chiname', 'local_reports'),
			get_string('profession', 'local_reports'),
			get_string('posttitle', 'local_reports'),
			get_string('orgnature', 'local_reports'),
			get_string('orgname', 'local_reports'),
			get_string('orgnamechi', 'local_reports'),
			get_string('serviceuniteng', 'local_reports'),
			get_string('serviceunitchi', 'local_reports'),
			get_string('officephone', 'local_reports'),
			get_string('officefax', 'local_reports'),
			get_string('email', 'local_reports'),
			get_string('loginnum', 'local_reports'),
			get_string('loginduration', 'local_reports'),
			get_string('lastaccess', 'local_reports'),
		);
	}

	private function getProfile($profile)
	{
		return '
			<tr>
				<td class="completion-progresscell">' . $profile->username . '</td>
				<td class="completion-progresscell">' . $profile->surname . '</td>
				<td class="completion-progresscell">' . $profile->firstname . '</td>
				<td class="completion-progresscell">' . $profile->chiname . '</td>
				<td class="completion-progresscell">' . $profile->profession . '</td>
				<td class="completion-progresscell">' . $profile->posttitle . '</td>
				<td class="completion-progresscell">' . $profile->orgnature . '</td>
				<td class="completion-progresscell">' . $profile->orgname . '</td>
				<td class="completion-progresscell">' . $profile->orgnamechi . '</td>
				<td class="completion-progresscell">' . $profile->serviceuniteng . '</td>
				<td class="completion-progresscell">' . $profile->serviceunitchi . '</td>
				<td class="completion-progresscell">' . $profile->officephone . '</td>
				<td class="completion-progresscell">' . $profile->officefax . '</td>
				<td class="completion-progresscell">' . $profile->email . '</td>
				<td class="completion-progresscell">' . $profile->loginnum . '</td>
				<td class="completion-progresscell">' . $this->print_duration_logs($profile->loginduration) . '</td>
				<td class="completion-progresscell">' . date('Y-m-d H:m:s', $profile->lastaccess) . ' (' . format_time(time() - $profile->lastaccess) . ')' . '</td>
			</tr>
		';
	}

	public function report_list_by_year($records)
	{
		$content = $this->print_role_header($records['roleid']);

		$content .= $this->table_start(REPORTBYYEAR);

		foreach ($records['profile'] as $profile) {
			$content .= $this->getProfile($profile);
		}

		$content .= '</tbody></table>';

		return $content;
	}

	public function report_list_by_month($records)
	{
		$content = $this->print_role_header($records['roleid']);

		$content .= $this->table_start(REPORTBYMONTH);

		foreach ($records['profile'] as $profile) {
			$content .= $this->getProfile($profile);
		}

		$content .= '</tbody></table>';

		return $content;
	}

	public function report_list_by_day($records)
	{
		$content = $this->print_role_header($records['roleid']);

		$content .= $this->table_start(REPORTBYDAY);

		foreach ($records['profile'] as $profile) {
			$content .= $this->getProfile($profile);
		}

		$content .= '</tbody></table></div>';

		return $content;
	}


	// display table header fields
	protected function table_start($reporttype, $table_header_fields = null)
	{
		$table_header_fields = $this->get_table_header_fields();

		$content = '
		<div style="overflow-x:auto;">
			<table cellpadding="5" border="1" class="flexible boxaligncenter">
				<thead>
					<tr>
						' . $table_header_fields . '
					</tr>
				</thead>
				<tbody>
		';

		return $content;
	}

	// get table header fields
	private function get_table_header_fields()
	{
		$len = count($this->table_header_fields);

		$html = '';

		for ($i = 0; $i < $len; $i++) {
			$html .= '<th class="completion-identifyfield fixwidth">' . $this->table_header_fields[$i] . '</th>';
		}

		return $html;
	}

	// get the name of user role
	private function print_role_header($roleid)
	{
		global $OUTPUT;

		$heading = $this->roles[$roleid]->name;

		return $OUTPUT->heading($heading, 3);
	}

	/**
	 * Download report
	 */
	public function download_report($frmdata, $records, $filename = '', $heading = 'Report')
	{
		$file = $filename . '_' . date('Y_m_d') . '.xlsx';

		try {
			$reader = new Html();
			$spreadsheet = $reader->loadFromString($this->report_list_by_year($records));
			ob_end_clean();
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename=' . $file);
			header('Cache-Control: max-age=0');
			$writer = new Xlsx($spreadsheet);
			exit($writer->save('php://output'));
		} catch (Exception $e) {
			exit($e->getMessage());
		}
	}

	private function print_duration_logs($duration)
	{
		$hours = (int)($duration / 60 / 60);

		$minutes = $duration / 60 % 60;

		$seconds = $duration % 60;

		return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
	}
}
