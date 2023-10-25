<?php 
@define('REPORTBYYEAR', 2);
@define('REPORTBYMONTH', 1);
@define('REPORTBYDAY', 0);

function reportvisitsfrequency_get_records($data) {
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
	
	if ($data->userrole == 'anonymous') {
		//get visitors log
		$params = array('startdate'=>$startdate, 'enddate'=>$enddate, 'page'=>'/login/index.php');
		$SQL = "
			SELECT CONCAT(from_unixtime(time, '$sqltimeformat'), ' ', from_unixtime(time,'%H')) as datetimehour, from_unixtime(time, '$sqltimeformat') as datetime, from_unixtime(time,'%H') as hour, COUNT(*) as recordnum
			FROM mdl_pageview_log pl 
			WHERE page = :page
			AND time BETWEEN :startdate AND :enddate		
			GROUP BY datetime, hour
			ORDER BY datetime, hour
		";	
		$records['freqlist'] = $DB->get_records_sql($SQL, $params);
	} else {
		//get log by the selected user role
		$params = array('startdate'=>$startdate, 'enddate'=>$enddate, 'action'=>'loggedin', 'contextid'=>1, 'roleid'=>$data->userrole);
		$SQL = "
			SELECT CONCAT(from_unixtime(timecreated, '$sqltimeformat'), ' ', from_unixtime(timecreated,'%H')) as datetimehour, from_unixtime(timecreated, '$sqltimeformat') as datetime, from_unixtime(timecreated,'%H') as hour, COUNT(*) as recordnum
			FROM {logstore_standard_log} lsl
			WHERE action = :action 
			AND userid IN (
				SELECT userid FROM {role_assignments} ra
				WHERE roleid = :roleid
				AND contextid = :contextid
			)
			AND timecreated BETWEEN :startdate AND :enddate	
			GROUP BY datetime, hour
			ORDER BY datetime, hour
		";	
		$records['freqlist'] = $DB->get_records_sql($SQL, $params);
	}
	
	//print_r($records); exit();
	return $records;
}
?>