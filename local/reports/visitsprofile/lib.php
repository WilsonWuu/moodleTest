<?php
@define('REPORTBYYEAR', 2);
@define('REPORTBYMONTH', 1);
@define('REPORTBYDAY', 0);

function reportvisitsprofile_get_records($data)
{
	global $DB;
	$records = array();

	switch ($data->reporttype) {
		case REPORTBYYEAR:
			$sqltimeformat = "%Y";
			$year = date('Y', $data->startdate);
			$startdate = mktime(0, 0, 0, 1, 1, date('Y', $data->startdate));
			$enddate = mktime(0, 0, 0, 12, 31, date('Y', $data->enddate));
			$records['firstyear'] = $year;
			$records['lastyear'] =  date('Y', $data->enddate);
			break;

		case REPORTBYMONTH:
			$sqltimeformat = "%Y %m";
			$startdate = mktime(0, 0, 0, date('m', $data->startdate), 1, date('Y', $data->startdate));
			$endday = cal_days_in_month(CAL_GREGORIAN, date('m', $data->enddate), date('Y', $data->enddate));
			$enddate = mktime(0, 0, 0, date('m', $data->enddate), $endday, date('Y', $data->enddate));
			$records['firstyear'] = date('Y', $data->startdate);
			$records['firstmonth'] = date('m', $data->startdate);
			$records['lastyear'] = date('Y', $data->enddate);
			$records['lastmonth'] = date('m', $data->enddate);
			break;

		case REPORTBYDAY:
			$sqltimeformat = "%Y %m %d";
			$startdate = mktime(0, 0, 0, date('m', $data->startdate), date('d', $data->startdate), date('Y', $data->startdate));
			$enddate = mktime(0, 0, 0, date('m', $data->enddate), date('d', $data->enddate), date('Y', $data->enddate));
			$records['firstyear'] = date('Y', $data->startdate);
			$records['firstmonth'] = date('m', $data->startdate);
			$records['firstday'] = date('d', $data->startdate);
			$records['lastyear'] = date('Y', $data->enddate);
			$records['lastmonth'] = date('m', $data->enddate);
			$records['lastday'] = date('d', $data->enddate);
			break;
	}


	// get log by the selected user role
	$params = array(
		'startdate' => $startdate,
		'startdate2' => $startdate,
		'startdate3' => $startdate,
		'enddate' => $enddate,
		'enddate2' => $enddate,
		'enddate3' => $enddate,
		'contextid' => 1,
		'roleid' => $data->userrole
	);

	$SQL = "
			SELECT
				u.id AS 'ID',
				u.username AS 'username',
				u.lastname AS 'surname',
				u.firstname AS 'firstname',
				(
					SELECT uind.data
					FROM {user_info_field} uif, {user_info_data} uind
					WHERE uind.fieldid = uif.id
					AND uif.shortname = 'chiname'
					AND uind.userid = u.id
				) AS 'chiname',
				(
					SELECT uind.data
					FROM {user_info_field} uif, {user_info_data} uind
					WHERE uind.fieldid = uif.id
					AND uif.shortname = 'profession'
					AND uind.userid = u.id
				) AS 'profession',
				(
					SELECT uind.data
					FROM {user_info_field} uif, {user_info_data} uind
					WHERE uind.fieldid = uif.id
					AND uif.shortname = 'posttitle'
					AND uind.userid = u.id
				) AS 'posttitle',
				(
					SELECT uind.data
					FROM {user_info_field} uif, {user_info_data} uind
					WHERE uind.fieldid = uif.id
					AND uif.shortname = 'orgnature'
					AND uind.userid = u.id
				) AS 'orgnature',
				(
					SELECT uind.data
					FROM {user_info_field} uif, {user_info_data} uind
					WHERE uind.fieldid = uif.id
					AND uif.shortname = 'orgname'
					AND uind.userid = u.id
				) AS 'orgname',
				(
					SELECT uind.data
					FROM {user_info_field} uif, {user_info_data} uind
					WHERE uind.fieldid = uif.id
					AND uif.shortname = 'orgnamechi'
					AND uind.userid = u.id
				) AS 'orgnamechi',
				(
					SELECT uind.data
					FROM {user_info_field} uif, {user_info_data} uind
					WHERE uind.fieldid = uif.id
					AND uif.shortname = 'SU_EN'
					AND uind.userid = u.id
				) AS 'serviceuniteng',
				(
					SELECT uind.data
					FROM {user_info_field} uif, {user_info_data} uind
					WHERE uind.fieldid = uif.id
					AND uif.shortname = 'SU_CN'
					AND uind.userid = u.id
				) AS 'serviceunitchi',
				(
					SELECT uind.data
					FROM {user_info_field} uif, {user_info_data} uind
					WHERE uind.fieldid = uif.id
					AND uif.shortname = 'officephone'
					AND uind.userid = u.id
				) AS 'officephone',
				(
					SELECT uind.data
					FROM {user_info_field} uif, {user_info_data} uind
					WHERE uind.fieldid = uif.id
					AND uif.shortname = 'officefax'
					AND uind.userid = u.id
				) AS 'officefax',
				email AS 'email',
				(
					SELECT COUNT(*)
					FROM {logstore_standard_log} lsl
					WHERE lsl.userid = u.id
					AND lsl.action = 'loggedin'
					AND lsl.timecreated BETWEEN :startdate2 AND :enddate2
				) AS 'loginnum',
				(
					SELECT SUM(durationtime)
					FROM {pageview_log} pl
					WHERE pl.userid = u.id
					AND pl.time BETWEEN :startdate3 AND :enddate3
				) AS 'loginduration',
				lastaccess AS 'lastaccess'
			FROM {user} AS u
			WHERE u.id IN (
				SELECT userid
				FROM {role_assignments} ra
				WHERE roleid = :roleid
				AND contextid = :contextid
			)
			ORDER BY u.id
		";

	$records['profile'] = $DB->get_records_sql($SQL, $params);

	// echo '<pre>';
	// var_dump($records);
	// echo '</pre>';
	// exit;

	return $records;
}
