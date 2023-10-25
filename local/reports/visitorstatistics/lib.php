<?php 
@define('REPORTBYYEAR', 2);
@define('REPORTBYMONTH', 1);
@define('REPORTBYDAY', 0);

function reportvisitorstatistics_get_visits($data) {
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
	
	//get login log
	$params = array('startdate'=>$startdate, 'enddate'=>$enddate, 'action'=>'loggedin', 'contextid'=>1);
	$SQL = "
		SELECT CONCAT(from_unixtime(timecreated, '$sqltimeformat'), ' ', ro.shortname) as datetimeshortname, from_unixtime(timecreated, '$sqltimeformat') as datetime, ro.shortname, COUNT(*) as recordnum 
		FROM {logstore_standard_log} lsl
		INNER JOIN (
			$rolesql
		) AS ro ON lsl.userid = ro.userid
		WHERE action = :action 
		AND timecreated >= :startdate 
		AND timecreated <= :enddate	
		GROUP BY datetime, ro.shortname 
		ORDER BY datetime, ro.shortname
	";	
	$records['loginlist'] = $DB->get_records_sql($SQL, $params);
	
	//get visitors log
	$params = array('startdate'=>$startdate, 'enddate'=>$enddate, 'page'=>'/login/index.php');
	$SQL = "
		SELECT from_unixtime(time, '$sqltimeformat') as datetime, COUNT(*) as recordnum 
		FROM {pageview_log} pl
		WHERE page = :page 
		AND time >= :startdate 
		AND time <= :enddate	
		GROUP BY datetime
		ORDER BY datetime
	";	
	$records['visitlist'] = $DB->get_records_sql($SQL, $params);
	
	//get unique visitors log
	$SQL = "
		SELECT pl.datetime, COUNT(*) as recordnum 
		FROM (
			SELECT from_unixtime(time, '$sqltimeformat') as datetime, ip 
			FROM {pageview_log} pl 
			WHERE page = '/login/index.php' 
			AND time >= :startdate 
			AND time <= :enddate	
			GROUP BY datetime, ip
		) AS pl
		GROUP BY datetime
	";	
	$records['univisitlist'] = $DB->get_records_sql($SQL, $params);
	
	//print_r($records); exit();
	return $records;
}
?>