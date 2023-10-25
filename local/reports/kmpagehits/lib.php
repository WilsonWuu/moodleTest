<?php 
@define('REPORTBYYEAR', 2);
@define('REPORTBYMONTH', 1);
@define('REPORTBYDAY', 0);

function reportkmpagehits_get_records($data) {
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
	
	$params = array('startdate'=>$startdate, 'enddate'=>$enddate, 'contextid'=>1, 'page'=>'/km/view.php');
	
	$SQL = "
		SELECT CONCAT(DATE_FORMAT(indatetime, '$sqltimeformat'), ' ', ro.shortname, ' ', objectkey) as datetimeshortname, DATE_FORMAT(indatetime, '$sqltimeformat') datetime, ro.shortname, objectkey, COUNT(*) as recordnum 
		FROM (
			SELECT from_unixtime(time, '%Y-%m-%d') indatetime, pl.userid, objectkey
			FROM mdl_pageview_log pl 
			WHERE page = :page
			AND time BETWEEN :startdate AND :enddate 
			GROUP BY indatetime, objectkey, pl.userid
		) as pl
		INNER JOIN ( 
			$rolesql 
		) AS ro ON pl.userid = ro.userid 
		GROUP BY datetime, ro.shortname, objectkey
		ORDER BY datetime, ro.shortname, objectkey
	";	
	$records['hitslist'] = $DB->get_records_sql($SQL, $params);
	$records['hitslist'] = reportkmpagehits_reorder_records($records['hitslist']);
	//print_r($records['hitslist']); exit();
	return $records;
}

function reportkmpagehits_reorder_records($hitslist) {
	$resetlist = array();
	foreach ($hitslist as $row) {
		if (!isset($resetlist[$row->datetime])) {
			$resetlist[$row->datetime] = array();
		}
		$objectkey = $row->objectkey;
		if (!isset($resetlist[$row->datetime][$objectkey])) {		
			$resetlist[$row->datetime][$objectkey] = array();
		}
		$resetlist[$row->datetime][$objectkey][$row->shortname] = $row->recordnum;
	}
	return $resetlist;
}
?>