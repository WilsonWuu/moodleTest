<?php 
@define('REPORTBYYEAR', 2);
@define('REPORTBYMONTH', 1);
@define('REPORTBYDAY', 0);

function reportuserlastvisits_get_records($data) {
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
	
	$params = array('startdate'=>$startdate, 'enddate'=>$enddate, 'action'=>'loggedin', 'contextid'=>1);
	$SQL = "
		SELECT CONCAT(lsl.datetime, ' ', ro.shortname) as datetimeshortname, lsl.datetime, ro.shortname, COUNT(*) as recordnum 
		FROM (
			SELECT from_unixtime(MAX(timecreated), '$sqltimeformat') as datetime, userid
			FROM {logstore_standard_log}
			WHERE action = :action
			AND timecreated >= :startdate 
			AND timecreated <= :enddate	
			GROUP BY userid
		) AS lsl
		INNER JOIN (
			$rolesql
		) AS ro ON lsl.userid = ro.userid
		GROUP BY datetime, shortname
	";	
	$records['lastloginlist'] = $DB->get_records_sql($SQL, $params);
	
	//print_r($records); exit();
	return $records;
}
?>