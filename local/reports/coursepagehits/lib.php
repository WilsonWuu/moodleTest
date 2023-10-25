<?php 
@define('REPORTBYYEAR', 2);
@define('REPORTBYMONTH', 1);
@define('REPORTBYDAY', 0);

function reportcoursepagehits_get_records($data) {
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
	
	$params = array('startdate'=>$startdate, 'enddate'=>$enddate, 'contextid'=>1, 'courseid'=>1, 'depth'=>1, 'object'=>'course');
	$SQL = "
		SELECT CONCAT(DATE_FORMAT(indatetime, '$sqltimeformat'), ' ', ro.shortname, ' ', courseid) as datetimeshortname, DATE_FORMAT(indatetime, '$sqltimeformat') datetime, ro.shortname, courseid, c.fullname as coursename, cc.id as catid, cc.name as catname, cc.path, COUNT(*) as recordnum 
		FROM (
			SELECT from_unixtime(time, '%Y-%m-%d') indatetime, pl.userid, courseid 
			FROM mdl_pageview_log pl 
			WHERE courseid != :courseid 
			AND ( object IN ( SELECT name FROM mdl_modules ) OR object = :object ) 
			AND time BETWEEN :startdate AND :enddate 
			GROUP BY indatetime, courseid, pl.userid
		) as pl
		INNER JOIN ( 
			$rolesql 
		) AS ro ON pl.userid = ro.userid 
		INNER JOIN mdl_course c ON pl.courseid = c.id 
		INNER JOIN mdl_course_categories cc ON c.category = cc.id 
		AND depth != :depth
		GROUP BY datetime, ro.shortname, courseid 
		ORDER BY datetime, cc.path, courseid
	";	
	$coursehitslist = $DB->get_records_sql($SQL, $params);
	$records['stalist'] = reportcoursepagehits_reorder_records($coursehitslist);
	//print_r($records['coursehitslist']); exit();
	return $records;
}

function reportcoursepagehits_reorder_records($coursehitslist) {
	$resetlist = array();
	foreach ($coursehitslist as $row) {
		if (!isset($resetlist[$row->datetime])) {
			$resetlist[$row->datetime] = array();
		}
		$supcatid = explode('/', $row->path)[1];
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