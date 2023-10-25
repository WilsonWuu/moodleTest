<?php 
@define('REPORTBYYEAR', 2);
@define('REPORTBYMONTH', 1);
@define('REPORTBYDAY', 0);

function reportuserenrollmentstatistics_get_records($data) {
	global $DB;
	$records = array();
	
	switch ($data->reporttype) {	
		
		case REPORTBYYEAR:
			$year = date('Y', $data->startdate);
			$startdate = mktime(0,0,0,1,1,date('Y', $data->startdate));
			$enddate = mktime(0,0,0,12,31,date('Y', $data->enddate));
			$sqltimeformat = "%Y";
			$records['firstyear'] = $year;
			$records['lastyear'] =  date('Y', $data->enddate);
			break;
		
		case REPORTBYMONTH:
			$sqltimeformat = "%Y %m";
			$startdate = mktime(0,0,0,date('m', $data->startdate),1,date('Y', $data->startdate));
			$endday = cal_days_in_month(CAL_GREGORIAN, date('m', $data->enddate), date('Y', $data->enddate));
			$enddate = mktime(0,0,0,date('m', $data->enddate),$endday,date('Y', $data->enddate));
			$records['firstyear'] = date('Y', $data->startdate);
			$records['firstmonth'] = date('m', $data->startdate);
			$records['lastyear'] = date('Y', $data->enddate);
			$records['lastmonth'] = date('m', $data->enddate);
			break;
		case REPORTBYDAY:
			$sqltimeformat = "%Y %m %d";
			$startdate = mktime(0,0,0,date('m', $data->startdate),date('d', $data->startdate),date('Y', $data->startdate));
			$enddate = mktime(0,0,0,date('m', $data->enddate),date('d', $data->enddate),date('Y', $data->enddate));
			$records['firstyear'] = date('Y', $data->startdate);
			$records['firstmonth'] = date('m', $data->startdate);
			$records['firstday'] = date('d', $data->startdate);
			$records['lastyear'] = date('Y', $data->enddate);
			$records['lastmonth'] = date('m', $data->enddate);
			$records['lastday'] = date('d', $data->enddate);
			break;
	}
	
	$rolesql = "
		SELECT shortname, userid from {role} r, {role_assignments} ra
		WHERE r.id = ra.roleid
		AND contextid = :contextid
	";
	
	$params = array(
		'startdate'=>$startdate, 'enddate'=>$enddate, 
		'startdate2'=>$startdate, 'enddate2'=>$enddate, 
		'contextid'=>1, 'activeenrol'=>0
	);
	$SQL = "
		SELECT CONCAT(datetime, ' ', ue.shortname, ' ', courseid) as datetimeshortname, datetime, ue.shortname, ue.courseid, c.fullname as coursename, cc.id as catid, cc.name as catname, SUBSTRING(cc.path,2,1) as supercat, recordnum 
		FROM (
			SELECT from_unixtime(ue.timecreated, '$sqltimeformat') datetime, shortname, courseid, COUNT(*) as recordnum
			FROM mdl_user_enrolments ue
			INNER JOIN (
				$rolesql
			) AS ro ON ue.userid = ro.userid 
			INNER JOIN mdl_enrol en on ue.enrolid = en.id
			WHERE (
				ue.timecreated BETWEEN :startdate AND :enddate 
				OR ue.timestart BETWEEN :startdate2 AND :enddate2
			) AND ue.status = :activeenrol
			GROUP BY datetime, shortname, courseid
		) AS ue
		INNER JOIN mdl_course c ON ue.courseid = c.id 
		INNER JOIN mdl_course_categories cc ON c.category = cc.id 
		ORDER BY datetime, cc.path, courseid
	";	
	$stalist = $DB->get_records_sql($SQL, $params);
	$records['stalist'] = reportuserenrollmentstatistics_reorder_records($stalist);
	//print_r($records['stalist']); exit();
	return $records;
}

function reportuserenrollmentstatistics_reorder_records($stalist) {
	$resetlist = array();
	foreach ($stalist as $row) {
		if (!isset($resetlist[$row->datetime])) {
			$resetlist[$row->datetime] = array();
		}
		$supcatid = $row->supercat;
		if (!isset($resetlist[$row->datetime][$supcatid])) {		
			$resetlist[$row->datetime][$supcatid] = array();
		}
		$catname = $row->catname;
		if (!isset($resetlist[$row->datetime][$supcatid][$catname])) {
			$resetlist[$row->datetime][$supcatid][$catname] = array();
		}
		$resetlist[$row->datetime][$supcatid][$catname][$row->courseid][$row->datetime.' '.$row->shortname] = $row;
	}
	return $resetlist;
}
?>