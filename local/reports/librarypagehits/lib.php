<?php 
@define('REPORTBYYEAR', 2);
@define('REPORTBYMONTH', 1);
@define('REPORTBYDAY', 0);

function reportlibrarypagehits_get_records($data) {
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
	
	$params = array('startdate'=>$startdate, 'enddate'=>$enddate, 'contextid'=>1, 'page'=>'/elibrary/view_resource_detail.php');
	
	$SQL = "
		SELECT CONCAT(DATE_FORMAT(indatetime, '$sqltimeformat'), ' ', ro.shortname, ' ', objectid) as datetimeshortname, DATE_FORMAT(indatetime, '$sqltimeformat') datetime, ro.shortname, subjectid, name_eng, name_chi, COUNT(*) as recordnum 
		FROM (
			SELECT from_unixtime(time, '%Y-%m-%d') indatetime, pl.userid, objectid
			FROM mdl_pageview_log pl 
			WHERE page = :page
			AND time BETWEEN :startdate AND :enddate 
			GROUP BY indatetime, courseid, pl.userid
		) as pl
		INNER JOIN ( 
			$rolesql 
		) AS ro ON pl.userid = ro.userid 
		INNER JOIN mdl_library_resource lr ON pl.objectid = lr.id 
		INNER JOIN mdl_library_subject ls ON lr.subjectid = ls.id 
		GROUP BY datetime, ro.shortname, subjectid
		ORDER BY datetime, ro.shortname, subjectid 
	";	
	$records['hitslist'] = $DB->get_records_sql($SQL, $params);
	$records['hitslist'] = reportlibrarypagehits_reorder_records($records['hitslist']);
	//print_r($records['hitslist']); exit();
	return $records;
}

function reportlibrarypagehits_reorder_records($hitslist) {
	$resetlist = array();
	foreach ($hitslist as $row) {
		if (!isset($resetlist[$row->datetime])) {
			$resetlist[$row->datetime] = array();
		}
		$subjectid = $row->subjectid;
		if (!isset($resetlist[$row->datetime][$subjectid])) {		
			$resetlist[$row->datetime][$subjectid] = array();
			$resetlist[$row->datetime][$subjectid]['name'] = $row->name_eng;
			$resetlist[$row->datetime][$subjectid]['roles'] = array();
		}
		$resetlist[$row->datetime][$subjectid]['roles'][$row->shortname] = $row->recordnum;
	}
	return $resetlist;
}
?>