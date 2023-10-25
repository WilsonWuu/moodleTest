<?php 
@define('REPORTBYYEAR', 2);
@define('REPORTBYMONTH', 1);
@define('REPORTBYDAY', 0);

function reportcoursestatistics_get_records($data) {
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
	$params = array(
		'startdate'=>$startdate, 'enddate'=>$enddate, 'siteid'=>SITEID, 'depth'=>1,
		'startdate2'=>$startdate, 'enddate2'=>$enddate, 'enddate3'=>$enddate,
		'siteid2'=>SITEID, 'depth2'=>1, 'isarchive'=>0
	);
	$approvesql = '';
	$courseconfig = get_config('moodlecourse');
	if (property_exists($courseconfig, 'enablecourseapproval') && $courseconfig->enablecourseapproval) {
		$approvesql = 'AND isapproved = 1 ';
	}
	$SQL = "
		SELECT c1.category,c1.catname, c1.supercat, c1.recordnum, IFNULL(c2.inactivenum, 0) as activenum
		FROM ( 
			SELECT c.category,cc.name as catname, SUBSTRING(cc.path,2,1) as supercat, COUNT(*) as recordnum FROM `mdl_course` c 
			JOIN mdl_course_categories cc on c.category = cc.id 
			WHERE cc.id != :siteid
			AND cc.depth != :depth
			AND timecreated BETWEEN :startdate AND :enddate 
			$approvesql
			GROUP BY c.category, catname, supercat
		) as c1
		LEFT JOIN (
			SELECT c.category,cc.name as catname, SUBSTRING(cc.path,2,1) as supercat, COUNT(*) as inactivenum FROM `mdl_course` c 
			JOIN mdl_course_categories cc on c.category = cc.id 
			WHERE cc.id != :siteid2
			AND cc.depth != :depth2
			AND timecreated BETWEEN :startdate2 AND :enddate2
			AND isarchive = :isarchive
			AND enddate >= :enddate3
			$approvesql
			GROUP BY c.category, catname, supercat		
		) as c2 on c1.category = c2.category
		ORDER BY c1.supercat     
	";	
	$records['stalist'] = $DB->get_records_sql($SQL, $params);
	
	//print_r($records); exit();
	return $records;
}
?>