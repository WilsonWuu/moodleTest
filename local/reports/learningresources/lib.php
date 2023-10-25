<?php 
@define('REPORTBYYEAR', 2);
@define('REPORTBYMONTH', 1);
@define('REPORTBYDAY', 0);

function reportlearningresources_get_resources($data) {
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
			//$enddate = mktime(0,0,0,12,31,date('Y', $data->startdate));
			/*while ($enddate <= $finalenddate) {
				$params = array('startdate'=>$startdate, 'enddate'=>$enddate);
				$obj = new stdclass;
				$obj->year = $year;
				$SQL = "
					SELECT COUNT(*) as recordnum
					FROM {resource}
					WHERE isvideo = :isvideo
					AND timemodified BETWEEN :startdate AND :enddate
				";
				$params['isvideo'] = 1;
				$obj->numofvideos = $DB->get_field_sql($SQL, $params);
				$SQL = "
					SELECT COUNT(*) as recordnum
					FROM {resource}
					WHERE isvideo = :isvideo
					AND timemodified BETWEEN :startdate AND :enddate
				";
				$params['isvideo'] = 0;
				$obj->numofresources = $DB->get_field_sql($SQL, $params);
				$SQL = "
					SELECT COUNT(*) as recordnum
					FROM {files}
					WHERE component = :component
					AND filearea = :filearea
					AND filesize != :filesize
					AND timemodified BETWEEN :startdate AND :enddate
				";
				unset($params['isvideo']);
				$params = array_merge($params,array('component'=>'mod_folder', 'filearea'=>'content', 'filesize'=>0));
				$obj->numofresources += $DB->get_field_sql($SQL, $params);
				$records[] = $obj;
				$year++;
				$startdate = strtotime("+1 year", $startdate);
				$enddate = strtotime("+1 year", $enddate);
			}*/
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
	$params = array('startdate'=>$startdate, 'enddate'=>$enddate, 'filearea'=>'content', 'filesize'=>0, 'mimetype'=>'video%');
	$SQL = "
		SELECT from_unixtime(timemodified, '$sqltimeformat') as datetime, COUNT(*) as recordnum
		FROM {files}
		WHERE component IN ('mod_folder', 'mod_resource')
		AND filearea = :filearea
		AND filesize != :filesize
		AND mimetype LIKE :mimetype
		AND timemodified BETWEEN :startdate AND :enddate
		GROUP BY datetime
		ORDER BY datetime
	";
	$records['videolist'] = $DB->get_records_sql($SQL, $params);
	$SQL = "
		SELECT from_unixtime(timemodified, '$sqltimeformat') as datetime, COUNT(*) as recordnum
		FROM {files}
		WHERE component IN ('mod_folder', 'mod_resource')
		AND filearea = :filearea
		AND filesize != :filesize
		AND mimetype NOT LIKE :mimetype
		AND timemodified BETWEEN :startdate AND :enddate
		GROUP BY datetime
		ORDER BY datetime
	";
	$records['resourcelist'] = $DB->get_records_sql($SQL, $params);
	
	//print_r($records); exit();
	return $records;
}
?>