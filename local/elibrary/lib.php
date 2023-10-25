<?php
require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.'/innoverz/lib/moodlelib.php');
require_once($CFG->dirroot.'/innoverz/lib.php');


if (!defined('MOODLE_INTERNAL')) {
	die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

/* ---- Library Config ---- */
define('LIBRARY_NGO_GL_LOAN_QUOTA', 20);
define('LIBRARY_SWD_GL_LOAN_QUOTA', 4);
define('LIBRARY_LIBRARIAN_LOAN_QUOTA', 2);
define('LIBRARY_UNLIMITED_LOAN_QUOTA', 99999);
define('LIBRARY_ON_LOAN_DAYS', 30);
define('LIBRARY_RENEW_DAYS', 30);
define('LIBRARY_MAX_RENEW_TIMES', 1);


/* function empty_replace(&$variable, $key, $replace = '')
{
	$replace = empty($replace) ? $key : $replace;
	$variable = isset($variable) && !empty($variable) ? $variable : $replace;
} */

/* ---- Resource ---- */

function get_library_resource_info($resource_id)
{
	global $DB;

	$resource_id = trim($resource_id);
	$sql = "
		SELECT
			r.id, r.coverimage, r.title,
			r.classid, c.description_eng class_eng, c.description_chi class_chi, r.series,
			r.subjectid, s.name_eng subject_eng, s.name_chi subject_chi,
			r.publisher, r.publishyear, r.publishcountry, r.publishtype,
			r.author, r.edition, r.isbn,
			r.description, r.remark, r.frequency, r.language,
			r.supply, r.nopage, r.currencyid, r.cost, r.costhk
		FROM mdl_library_resource r
		LEFT JOIN mdl_library_subject s ON r.subjectid=s.id
		LEFT JOIN mdl_library_class c ON r.classid=c.id
		WHERE r.id=$resource_id AND r.isdelete=0
		LIMIT 0, 1
	";
	return $DB->get_record_sql($sql);
}

function get_library_resource_image_url($resourceid, $resourcepic, $size = 'f1')
{
	global $CFG;
	return new moodle_url($CFG->LIBRARY_BASEURL . "pluginfile.php/1/resource/icon/clean/{$size}?reim={$resourceid}&pic={$resourcepic}");
}

function get_library_resource_list($input_data, $detail = false)
{
	global $DB;

	function trim_value(&$value)
	{
		$value = trim($value);
	}
	array_walk($input_data, 'trim_value');

	$conditions = array();
	$params = array();
	if (!empty($input_data['title']) && confirm_sesskey()) {
		$conditions[] = "(r.title LIKE ? OR " .
			"r.title LIKE ?)";
		$params[] = "%{$input_data['title']}%";
		$params[] = "%{$input_data['title']}%";
	}
	if (!empty($input_data['author']) && confirm_sesskey()) {
		$conditions[] = "(r.author LIKE ? OR " .
			"r.author LIKE ?)";
		$params[] = "%{$input_data['author']}%";
		$params[] = "%{$input_data['author']}%";
	}
	if (!empty($input_data['publisher']) && confirm_sesskey()) {
		$conditions[] = "r.publisher LIKE ?";
		$params[] = "%{$input_data['publisher']}%";
	}
	if (!empty($input_data['subject']) && confirm_sesskey()) {
		$conditions[] = "r.subjectid = ?";
		$params[] = $input_data['subject'];
	}
	if (!empty($input_data['class']) && confirm_sesskey()) {
		$conditions[] = "r.classid = ?";
		$params[] = $input_data['class'];
	}

	$conditions[] = "r.isdelete = 0";

	$conditions = (count($conditions) > 0) ? implode(" AND ", $conditions) : "1";

	if (!empty($input_data['accessno']) && confirm_sesskey()) {
		$conditions .= " 
			AND r.id IN (
				SELECT resourceid FROM mdl_library_copy rc
				WHERE rc.accessno = ?
				AND isdelete = 0
			)
		";
		$params[] = $input_data['accessno'];
	}

	$detail_select = '';
	if ($detail) {
		$detail_select = ', r.edition, r.language, r.reserving, r.availablecopy';
	}

	$sql = "
		SELECT
			r.id, r.title, r.author, r.publisher, r.coverimage,
			s.name_eng subject_eng, s.name_chi subject_chi,
			c.description_eng class_eng, c.description_chi class_chi
			$detail_select
		FROM mdl_library_resource r
		LEFT JOIN mdl_library_subject s ON r.subjectid=s.id
		LEFT JOIN mdl_library_class c ON r.classid=c.id
		WHERE $conditions
	";
	return $DB->get_records_sql($sql, $params);
}

function get_library_resource_removed_list()
{
	global $DB;

	$sql = "
		SELECT
			r.id, r.title, GROUP_CONCAT(c.accessno SEPARATOR ', ') accessnos, r.deletetime
		FROM mdl_library_resource r
		LEFT JOIN mdl_library_copy c ON r.id=c.resourceid
		WHERE r.isdelete = 1
		GROUP BY r.id
	";
	return $DB->get_records_sql($sql);
}

function get_popular_library_resources($limit)
{
	global $DB;

	$sql = "
		SELECT mdl_library_resource.id,title,count(copyid) 
		FROM mdl_library_copy
		LEFT JOIN mdl_library_loan ON mdl_library_copy.id = mdl_library_loan.copyid
		LEFT JOIN mdl_library_resource ON mdl_library_resource.id = mdl_library_copy.resourceid
		WHERE mdl_library_resource.isdelete=0
		GROUP BY resourceid
		ORDER BY count(copyid) DESC,
		title DESC
		LIMIT 0,$limit;
	";
	return $DB->get_records_sql($sql);
}

function get_newest_library_resources($limit)
{
	global $DB;

	$sql = "
		SELECT id,title 
		FROM mdl_library_resource 
		WHERE isdelete=0
		ORDER BY id DESC, title DESC
		LIMIT 0,$limit;
	";
	return $DB->get_records_sql($sql);
}

function add_library_resource($data)
{
	global $DB;

	if (!has_capability('local/elibrary:resourceadministration', context_system::instance())) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$dataobject = filter_object_key($data, array('title', 'coverimage', 'classid', 'series', 'subjectid', 'publisher', 'publishyear', 'publishcountry', 'publishtype', 'author', 'edition', 'supply', 'isbn', 'description', 'remark', 'frequency', 'language', 'nopage', 'currencyid', 'cost'));
	$dataobject->description = $dataobject->description['text'];
	$dataobject->costhk = 0;
	if (!empty($dataobject->currencyid) && !empty($dataobject->cost)) {
		$dataobject->costhk = $dataobject->cost * get_library_currency_rate_by_id($dataobject->currencyid);
	}
	$dataobject->reserving = 0;
	$dataobject->loancount = 0;
	$dataobject->totalcopy = 0;
	$dataobject->availablecopy = 0;
	$dataobject->isdelete = 0;
	$id = $DB->insert_record('library_resource', $dataobject);

	return array(
		'status' => 'success',
		'id' => $id,
		'msg' => ''
	);
}

function update_library_resource($data)
{
	global $DB;

	if (empty($data->id) || !has_capability('local/elibrary:resourceadministration', context_system::instance())) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$dataobject = filter_object_key($data, array('id', 'title', 'coverimage', 'classid', 'series', 'subjectid', 'publisher', 'publishyear', 'publishcountry', 'publishtype', 'author', 'edition', 'supply', 'isbn', 'description', 'remark', 'frequency', 'language', 'nopage', 'currencyid', 'cost'));
	$dataobject->description = $dataobject->description['text'];
	$dataobject->costhk = 0;
	if (!empty($dataobject->currencyid) && !empty($dataobject->cost)) {
		$dataobject->costhk = $dataobject->cost * get_library_currency_rate_by_id($dataobject->currencyid);
	}
	$DB->update_record('library_resource', $dataobject);

	return array(
		'status' => 'success',
		'msg' => ''
	);
}

function update_library_resource_coverimage(stdClass $data, moodleform $mform, $filemanageroptions = array())
{
	global $CFG, $DB;
	require_once("$CFG->libdir/gdlib.php");

	$context = context_system::instance();
	$resource = $DB->get_record('library_resource', array('id' => $data->id), 'id, coverimage', MUST_EXIST);

	$newpicture = $resource->coverimage;
	// Get file_storage to process files.
	$fs = get_file_storage();
	if (!empty($data->deletepicture)) {
		// The user has chosen to delete the selected users picture.
		$fs->delete_area_files($context->id, 'resource', 'icon', $resource->id); // Drop all images in area.
		$newpicture = 0;
	} else {
		// Save newly uploaded file, this will avoid context mismatch for newly created users.
		file_save_draft_area_files($data->coverimage, $context->id, 'resource', 'newicon', 0, $filemanageroptions);
		if (($iconfiles = $fs->get_area_files($context->id, 'resource', 'newicon')) && count($iconfiles) == 2) {
			// Get file which was uploaded in draft area.
			foreach ($iconfiles as $file) {
				if (!$file->is_directory()) {
					break;
				}
			}
			// Copy file to temporary location and the send it for processing icon.
			if ($iconfile = $file->copy_content_to_temp()) {
				// There is a new image that has been uploaded.
				// Process the new image and set the user to make use of it.
				// NOTE: Uploaded images always take over Gravatar.
				$imageinfo = getimagesize($iconfile);
				$newpicture = (int)process_new_icon_innoverz($context, 'resource', 'icon', $data->id, $iconfile, array('width' => 100, 'height' => 80), array('width' => 192, 'height' => 270), array('width' => $imageinfo[0], 'height' => $imageinfo[1]), true);
				// Delete temporary file.
				@unlink($iconfile);
				// Remove uploaded file.
				$fs->delete_area_files($context->id, 'resource', 'newicon');
			} else {
				// Something went wrong while creating temp file.
				// Remove uploaded file.
				$fs->delete_area_files($context->id, 'resource', 'newicon');
				return false;
			}
		}
	}

	if ($newpicture != $resource->coverimage) {
		$DB->set_field('library_resource', 'coverimage', $newpicture, array('id' => $resource->id));
		return true;
	} else {
		return false;
	}
}

function delete_library_resource($resource_id)
{
	global $DB;

	if (empty($resource_id) || !has_capability('local/elibrary:resourceadministration', context_system::instance())) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$sql = "
		SELECT 1 FROM mdl_library_loan
		WHERE actualreturndate = 0
		AND copyid IN (
			SELECT id FROM mdl_library_copy
			WHERE resourceid = ?
		)
	";

	if ($DB->record_exists_sql($sql, array(trim($resource_id)))) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_copy_loaning', 'local_elibrary')
		);
	}

	$dataobject = new stdClass();
	$dataobject->id = trim($resource_id);
	$dataobject->isdelete = 1;
	$dataobject->deletetime = time();
	$DB->update_record('library_resource', $dataobject);

	$dataobject = new stdClass();
	$dataobject->resourceid = trim($resource_id);
	$dataobject->isdelete = 1;

	$sql = "
		UPDATE mdl_library_copy
		SET isdelete = 1
		WHERE resourceid = ?
	";
	$DB->execute($sql, array(trim($resource_id)));

	return array(
		'status' => 'success',
		'msg' => ''
	);
}

function recovery_library_resource($resource_id)
{
	global $DB;

	if (empty($resource_id) || !has_capability('local/elibrary:resourceadministration', context_system::instance())) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$sql = "
		SELECT id, accessno FROM mdl_library_copy
		WHERE resourceid = ?
	";
	$results = $DB->get_records_sql($sql, array($resource_id));

	$copyids_by_accessno = array();
	$accessnos_where = array();
	$where_sym = array();
	foreach ($results as $row) {
		$copyids_by_accessno[$row->accessno] = $row->id;
		$accessnos_where[] = $row->accessno;
		$where_sym[] = '?';
	}

	$where_sym = implode(',', $where_sym);
	$sql = "
		SELECT id, accessno FROM mdl_library_copy
		WHERE accessno IN ($where_sym)
		AND isdelete = 0
	";
	$results = $DB->get_records_sql($sql, $accessnos_where);

	foreach ($results as $row) {
		if (isset($copyids_by_accessno[$row->accessno])) {
			unset($copyids_by_accessno[$row->accessno]);
		}
	}

	$copyids = array();
	$where_sym = array();
	foreach ($copyids_by_accessno as $copyid) {
		$copyids[] = $copyid;
		$where_sym[] = '?';
	}
	$where_sym = implode(',', $where_sym);
	$sql = "
		UPDATE mdl_library_copy
		SET isdelete = 0
		WHERE id IN ($where_sym)
	";
	$DB->execute($sql, $copyids);

	$dataobject = new stdClass();
	$dataobject->id = trim($resource_id);
	$dataobject->isdelete = 0;
	$dataobject->deletetime = null;
	$DB->update_record('library_resource', $dataobject);


	return array(
		'status' => 'success',
		'msg' => ''
	);
}

function get_library_resourceid_by_accessno($accessno)
{
	global $DB;

	$accessno = trim($accessno);
	$sql = "
		SELECT r.id
		FROM mdl_library_resource r
		LEFT JOIN mdl_library_copy c ON r.id=c.resourceid
		WHERE c.accessno=? AND r.isdelete=0 AND c.isdelete=0";
	$result = $DB->get_record_sql($sql, array($accessno));
	return $result->id;
}



/* ---- Copy ---- */

function get_library_resource_copy_info($copy_id)
{
	global $DB;

	$copy_id = trim($copy_id);
	$sql = "
		SELECT
			id, accessno, resourceid, callno, locateid, isloan, remark
		FROM mdl_library_copy
		WHERE id=$copy_id AND isdelete=0
	";
	$result = $DB->get_record_sql($sql);
	return $result;
}

function get_library_resource_copy_list($resource_id, $resource_info = null)
{
	global $DB;

	$resource_id = trim($resource_id);
	$sql = "
		SELECT
			c.id, c.accessno, c.callno, c.isloan, c.remark,
			l.code locate_code, l.description locate_description,
			rs.id reserve_id, lo.returndate
		FROM mdl_library_copy c
		LEFT JOIN mdl_library_locate l ON c.locateid=l.id
		LEFT JOIN mdl_library_reserve rs ON c.resourceid=rs.resourceid AND rs.isdone=0
		LEFT JOIN mdl_library_loan lo ON c.id=lo.copyid AND lo.actualreturndate=0
		WHERE c.resourceid=$resource_id AND c.isdelete=0
		GROUP BY c.id
		ORDER BY c.accessno, c.id
	";
	$result = $DB->get_records_sql($sql);
	if ($resource_info != null) {
		foreach ($result as &$row) {
			$row->title = $resource_info->title;
		}
	}
	return $result;
}

function add_library_resource_copy($data)
{
	global $DB, $USER;

	if (empty($data->resourceid) || !has_capability('local/elibrary:resourceadministration', context_system::instance())) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$sql = "SELECT 1 FROM mdl_library_resource WHERE id={$data->resourceid}";
	if (!$DB->record_exists_sql($sql)) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$dataobject = filter_object_key($data, array('resourceid', 'accessno', 'callno', 'locateid', 'remark'));
	$dataobject->adddate = time();
	$dataobject->isloan = 0;
	$dataobject->isdelete = 0;
	$DB->insert_record('library_copy', $dataobject);

	return array(
		'status' => 'success',
		'msg' => ''
	);
}

function update_library_resource_copy($data)
{
	global $DB, $USER;

	if (empty($data->id) || !has_capability('local/elibrary:resourceadministration', context_system::instance())) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$dataobject = filter_object_key($data, array('id', 'accessno', 'callno', 'locateid', 'remark'));
	$DB->update_record('library_copy', $dataobject);

	return array(
		'status' => 'success',
		'msg' => ''
	);
}

function delete_library_resource_copy($copy_id)
{
	global $DB;

	if (empty($copy_id) || !has_capability('local/elibrary:resourceadministration', context_system::instance())) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$dataobject = new stdClass();
	$dataobject->id = trim($copy_id);
	$dataobject->isdelete = 1;
	$DB->update_record('library_copy', $dataobject);

	return array(
		'status' => 'success',
		'msg' => ''
	);
}

function get_copy_last_accessno()
{
	global $DB;

	$sql = "SELECT max(CONVERT(accessno, UNSIGNED INTEGER)) last_accessno FROM `mdl_library_copy` WHERE accessno REGEXP '^[0-9]+$'";
	return $DB->get_record_sql($sql);
}

function check_copy_accessno_exist($accessno)
{
	global $DB;

	$sql = "SELECT 1 FROM mdl_library_copy WHERE accessno=? AND isdelete=0";
	if ($DB->record_exists_sql($sql, array($accessno))) {
		return true;
	}
	return false;
}

function get_copy_loan_status($accessno)
{
	global $DB;

	$accessno = trim($accessno);
	$sql = "
		SELECT c.isloan, c.id
		FROM mdl_library_copy c
		LEFT JOIN mdl_library_resource r ON r.id=c.resourceid
		WHERE c.accessno='$accessno' AND c.isdelete=0 AND r.isdelete=0
	";
	$result = $DB->get_record_sql($sql);
	return $result;
}

function get_library_resourceid_by_copyid($copyid)
{
	global $DB;

	$copyid = trim($copyid);
	$sql = "SELECT resourceid FROM mdl_library_copy WHERE id='$copyid' AND isdelete=0";
	$result = $DB->get_record_sql($sql);
	return $result->resourceid;
}



/* ---- Review ---- */

function get_all_review_list()
{
	global $DB;

	$sql = "
		SELECT
			r.id, r.userid, u.username, r.resourceid, rs.title, r.message, r.adddate, r.ishide
		FROM mdl_library_review r
		LEFT JOIN mdl_user u ON r.userid=u.id
		LEFT JOIN mdl_library_resource rs ON r.resourceid=rs.id
		ORDER BY adddate DESC
	";
	return $DB->get_records_sql($sql);
}

function get_library_resource_review_list($resource_id)
{
	global $DB;

	$resource_id = trim($resource_id);
	$sql = "
		SELECT
			r.id, r.userid, u.username, r.message, r.adddate, r.ishide,
			u.firstname, u.lastname, u.lastnamephonetic, u.firstnamephonetic, u.middlename, u.alternatename
		FROM mdl_library_review r
		LEFT JOIN mdl_user u ON r.userid=u.id
		WHERE r.resourceid='$resource_id'
		ORDER BY adddate DESC
	";
	return $DB->get_records_sql($sql);
}

function add_library_resource_review($review_content, $resource_id)
{
	global $DB, $USER;

	if (empty($review_content)) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_empty_review_content', 'local_elibrary')
		);
	}
	if (empty($resource_id) || ($USER->id <= 1)) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$dataobject = new stdClass();
	$dataobject->userid = $USER->id;
	$dataobject->resourceid = trim($resource_id);
	$dataobject->message = trim($review_content);
	$dataobject->adddate = time();
	$dataobject->ishide = 0;
	$DB->insert_record('library_review', $dataobject);

	return array(
		'status' => 'success',
		'msg' => ''
	);
}

function hide_library_resource_review($review_id)
{
	global $DB;

	if (empty($review_id) || !has_capability('local/elibrary:manageresourcereview', context_system::instance())) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$review_id = trim($review_id);

	$sql = "SELECT ishide FROM mdl_library_review WHERE id=$review_id";
	if (!$DB->record_exists_sql($sql)) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}
	$result = $DB->get_record_sql($sql);

	$dataobject = new stdClass();
	$dataobject->id = $review_id;
	$dataobject->ishide = ($result->ishide == 1) ? 0 : 1;
	$DB->update_record('library_review', $dataobject);

	return array(
		'status' => 'success',
		'msg' => ''
	);
}

function delete_library_resource_review($review_id)
{
	global $DB;

	if (empty($review_id) || !has_capability('local/elibrary:manageresourcereview', context_system::instance())) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$review_id = trim($review_id);

	$sql = "SELECT 1 FROM mdl_library_review WHERE id=$review_id";
	if (!$DB->record_exists_sql($sql)) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$DB->delete_records('library_review', array('id' => $review_id));

	return array(
		'status' => 'success',
		'msg' => ''
	);
}



/* ---- Class ---- */

function get_class_info($class_id)
{
	global $DB;

	$class_id = trim($class_id);
	$sql = "
		SELECT
			description_eng, description_chi
		FROM mdl_library_class
		WHERE id=$class_id
		LIMIT 0, 1
	";
	return $DB->get_record_sql($sql);
}

function get_class_list($input_data)
{
	global $DB;

	function trim_value(&$value)
	{
		$value = trim($value);
	}
	array_walk($input_data, 'trim_value');

	$sql = "
		SELECT
			id, description_eng, description_chi
		FROM mdl_library_class
		ORDER BY id
	";
	return $DB->get_records_sql($sql);
}

function add_class($data)
{
	global $DB;

	if (!has_capability('local/elibrary:resourceadministration', context_system::instance())) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$dataobject = filter_object_key($data, array('description_eng', 'description_chi'));
	$id = $DB->insert_record('library_class', $dataobject);

	return array(
		'status' => 'success',
		'id' => $id,
		'msg' => ''
	);
}

function update_class($data)
{
	global $DB;

	if (empty($data->id) || !has_capability('local/elibrary:resourceadministration', context_system::instance())) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$dataobject = filter_object_key($data, array('id', 'description_eng', 'description_chi'));

	$DB->update_record('library_class', $dataobject);

	return array(
		'status' => 'success',
		'msg' => ''
	);
}

function delete_class($class_id)
{
	global $DB;

	if (empty($class_id) || !has_capability('local/elibrary:resourceadministration', context_system::instance())) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$DB->delete_records('library_class', array('id' => $class_id));

	return array(
		'status' => 'success',
		'msg' => ''
	);
}



/* ---- Currency ---- */

function get_currency_info($currency_id)
{
	global $DB;

	$currency_id = trim($currency_id);
	$sql = "
		SELECT
			code, rate
		FROM mdl_library_currency
		WHERE id=$currency_id
		LIMIT 0, 1
	";
	return $DB->get_record_sql($sql);
}

function get_currency_list($input_data)
{
	global $DB;

	function trim_value(&$value)
	{
		$value = trim($value);
	}
	array_walk($input_data, 'trim_value');

	$sql = "
		SELECT
			id, code, rate
		FROM mdl_library_currency
		ORDER BY id
	";
	return $DB->get_records_sql($sql);
}

function add_currency($data)
{
	global $DB;

	if (!has_capability('local/elibrary:resourceadministration', context_system::instance())) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$dataobject = filter_object_key($data, array('code', 'rate'));
	$id = $DB->insert_record('library_currency', $dataobject);

	return array(
		'status' => 'success',
		'id' => $id,
		'msg' => ''
	);
}

function update_currency($data)
{
	global $DB;

	if (empty($data->id) || !has_capability('local/elibrary:resourceadministration', context_system::instance())) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$dataobject = filter_object_key($data, array('id', 'code', 'rate'));

	$DB->update_record('library_currency', $dataobject);

	return array(
		'status' => 'success',
		'msg' => ''
	);
}

function delete_currency($currency_id)
{
	global $DB;

	if (empty($currency_id) || !has_capability('local/elibrary:resourceadministration', context_system::instance())) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$DB->delete_records('library_currency', array('id' => $currency_id));

	return array(
		'status' => 'success',
		'msg' => ''
	);
}

function get_library_currency_rate_by_id($id)
{
	global $DB;

	$result = $DB->get_record_sql("SELECT rate FROM mdl_library_currency WHERE id=$id");
	return $result->rate;
}



/* ---- Locate ---- */

function get_locate_info($locate_id)
{
	global $DB;

	$locate_id = trim($locate_id);
	$sql = "
		SELECT
			code, description
		FROM mdl_library_locate
		WHERE id=$locate_id
		LIMIT 0, 1
	";
	return $DB->get_record_sql($sql);
}

function get_locate_list($input_data)
{
	global $DB;

	function trim_value(&$value)
	{
		$value = trim($value);
	}
	array_walk($input_data, 'trim_value');

	$sql = "
		SELECT
			id, code, description
		FROM mdl_library_locate
		ORDER BY id
	";
	return $DB->get_records_sql($sql);
}

function add_locate($data)
{
	global $DB;

	if (!has_capability('local/elibrary:resourceadministration', context_system::instance())) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$dataobject = filter_object_key($data, array('code', 'description'));
	$id = $DB->insert_record('library_locate', $dataobject);

	return array(
		'status' => 'success',
		'id' => $id,
		'msg' => ''
	);
}

function update_locate($data)
{
	global $DB;

	if (empty($data->id) || !has_capability('local/elibrary:resourceadministration', context_system::instance())) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$dataobject = filter_object_key($data, array('id', 'code', 'description'));

	$DB->update_record('library_locate', $dataobject);

	return array(
		'status' => 'success',
		'msg' => ''
	);
}

function delete_locate($locate_id)
{
	global $DB;

	if (empty($locate_id) || !has_capability('local/elibrary:resourceadministration', context_system::instance())) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$DB->delete_records('library_locate', array('id' => $locate_id));

	return array(
		'status' => 'success',
		'msg' => ''
	);
}



/* ---- Subject ---- */

function get_subject_info($subject_id)
{
	global $DB;

	$subject_id = trim($subject_id);
	$sql = "
		SELECT
			parent_id, name_eng, name_chi
		FROM mdl_library_subject
		WHERE id=$subject_id AND isdelete=0
		LIMIT 0, 1
	";
	return $DB->get_record_sql($sql);
}

function get_subject_list($input_data)
{
	global $DB;

	function trim_value(&$value)
	{
		$value = trim($value);
	}
	array_walk($input_data, 'trim_value');

	$sql = "
		SELECT
			id, parent_id, name_eng, name_chi
		FROM mdl_library_subject
		WHERE isdelete=0
		ORDER BY id
	";
	return $DB->get_records_sql($sql);
}

function add_subject($data)
{
	global $DB;

	if (!has_capability('local/elibrary:resourceadministration', context_system::instance())) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$dataobject = filter_object_key($data, array('parent_id', 'name_eng', 'name_chi'));
	$dataobject->isdelete = 0;
	$id = $DB->insert_record('library_subject', $dataobject);

	return array(
		'status' => 'success',
		'id' => $id,
		'msg' => ''
	);
}

function update_subject($data)
{
	global $DB;

	if (empty($data->id) || !has_capability('local/elibrary:resourceadministration', context_system::instance())) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$dataobject = filter_object_key($data, array('id', 'parent_id', 'name_eng', 'name_chi'));

	$DB->update_record('library_subject', $dataobject);

	return array(
		'status' => 'success',
		'msg' => ''
	);
}

function delete_subject($subject_id)
{
	global $DB;

	if (empty($subject_id) || !has_capability('local/elibrary:resourceadministration', context_system::instance())) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$dataobject = new stdClass();
	$dataobject->id = trim($subject_id);
	$dataobject->isdelete = 1;
	$DB->update_record('library_subject', $dataobject);

	return array(
		'status' => 'success',
		'msg' => ''
	);
}



/* ---- Loan ---- */

function check_is_ngo_user($userid)
{
	global $DB;

	$userid = trim($userid);
	$sql = "SELECT 1 FROM mdl_user WHERE id={$userid}";
	if (!$DB->record_exists_sql($sql)) {
		return array(
			'status' => 'fail',
			'msg' => 'msg_user_not_found'
		);
	}

	$sql = "
		SELECT 1
		FROM mdl_role_assignments ra
		LEFT JOIN mdl_role r ON ra.roleid=r.id
		WHERE ra.userid={$userid} AND ra.contextid=1 AND r.shortname='glngo'
	";
	if ($DB->record_exists_sql($sql)) {	//check is ngo user
		return true;
	}
	return false;
}

function check_is_unlimited_loan_quota($userid)
{
	return has_capability('local/elibrary:unlimitedloanquota', context_user::instance($userid));
}

function get_user_number_of_loan($userid)
{
	global $DB;

	$userid = trim($userid);
	$sql = "
		SELECT count(*) number_of_loan
		FROM mdl_library_loan
		WHERE userid=$userid AND IFNULL(actualreturndate,0)=0
	";
	$result = $DB->get_record_sql($sql);
	return $result->number_of_loan;
}

function get_user_number_of_loan_by_borrowerid($borrowerid)
{
	global $DB;

	$borrowerid = trim($borrowerid);
	$sql = "
		SELECT count(*) number_of_loan
		FROM mdl_library_loan
		WHERE borrowerid=$borrowerid AND IFNULL(actualreturndate,0)=0
	";
	$result = $DB->get_record_sql($sql);
	return $result->number_of_loan;
}

function add_loan_record($userid, $resourceid, $copyid, $duedate, $is_ngo, $contactperson = '', $contactnumber = '', $contactemail = '', $borrowerid = '')
{
	global $DB;

	$dataobject = new stdClass();
	$dataobject->userid = $userid;
	$dataobject->copyid = $copyid;
	$dataobject->loandate = time();
	$dataobject->duedate = $duedate;
	$dataobject->returndate = $duedate;
	$dataobject->actualreturndate = 0;
	$dataobject->islibrarian = 0;
	if ((!empty($contactperson) || !empty($contactnumber) || !empty($contactemail) || !empty($borrowerid)) && !$is_ngo) {
		$dataobject->islibrarian = 1;
	}
	$dataobject->contactperson = $contactperson;
	$dataobject->contactnumber = $contactnumber;
	$dataobject->contactemail = $contactemail;
	$dataobject->borrowerid = $borrowerid;
	$DB->insert_record('library_loan', $dataobject);

	$dataobject = new stdClass();
	$dataobject->id = $copyid;
	$dataobject->isloan = 1;
	$DB->update_record('library_copy', $dataobject);

	$sql = "
		UPDATE mdl_library_reserve
		SET isdone=1
		WHERE userid=? AND resourceid=? AND isdone=0
	";
	$DB->execute($sql, array($userid, $resourceid));

	return array(
		'status' => 'success',
		'msg' => ''
	);
}

function get_library_resource_copy_info_for_loan_list($copy_id)
{
	global $DB;

	$copy_id = trim($copy_id);
	$sql = "
		SELECT
			c.accessno, r.title,
			r.author, r.publisher
		FROM mdl_library_copy c
		LEFT JOIN mdl_library_resource r ON c.resourceid=r.id
		WHERE c.id=$copy_id AND c.isdelete=0 AND r.isdelete=0
		LIMIT 0, 1
	";
	return $DB->get_record_sql($sql);
}

function get_user_current_loan_list($user_id)
{	//$user_id = -1:all, -2:librarian
	global $DB;

	if ($user_id < 0) {
		$islibrarian = ($user_id == -2) ? 1 : 0;
		$sql = "
			SELECT
				l.id,
				r.title,
				r.author, c.accessno,
				c.callno,
				l.loandate, l.returndate,
				rn.id renew_id, rs.id reserve_id,
				l.userid, u.username, l.islibrarian,
				l.contactperson, l.contactnumber, l.contactemail, l.borrowerid
			FROM mdl_library_loan l
			LEFT JOIN mdl_library_copy c ON l.copyid=c.id
			LEFT JOIN mdl_library_resource r ON c.resourceid=r.id
			LEFT JOIN mdl_library_renew rn ON l.id=rn.loanid
			LEFT JOIN mdl_library_reserve rs ON r.id=rs.resourceid AND rs.isdone=0
			LEFT JOIN mdl_user u ON u.id=l.userid
			WHERE l.actualreturndate=0 AND l.islibrarian=$islibrarian
		";
		return $DB->get_records_sql($sql);
	} else {
		$is_ngo = check_is_ngo_user($user_id);
		$sql_select_contact = '';
		if ($is_ngo) {
			$sql_select_contact = ', l.contactperson, l.contactnumber, l.contactemail, l.borrowerid';
		}

		$sql = "
			SELECT
				l.id,
				r.title,
				r.author, c.accessno,
				c.callno,
				l.loandate, l.returndate,
				rn.id renew_id, rs.id reserve_id, l.islibrarian
				$sql_select_contact
			FROM mdl_library_loan l
			LEFT JOIN mdl_library_copy c ON l.copyid=c.id
			LEFT JOIN mdl_library_resource r ON c.resourceid=r.id
			LEFT JOIN mdl_library_renew rn ON l.id=rn.loanid
			LEFT JOIN mdl_library_reserve rs ON r.id=rs.resourceid AND rs.isdone=0
			WHERE l.userid=? AND l.actualreturndate=0 AND l.islibrarian=0
		";
		return $DB->get_records_sql($sql, array($user_id));
	}
}

function get_user_loan_history($user_id, $title = '', $operation = -1, $accessno = 0)
{	//$user_id = -1:all, -2:librarian
	global $DB;

	$where = "AND r.title ";
	$params = array();
	if ($user_id > 0) {
		$params[] = $user_id;
	}
	switch ($operation) {
		case 0:
			$where .= "LIKE ?";
			$params[] = "%$title%";
			break;
		case 1:
			$where .= "NOT LIKE ?";
			$params[] = "%$title%";
			break;
		case 2:
			$where .= "= ?";
			$params[] = $title;
			break;
		case 3:
			$where .= "LIKE ?";
			$params[] = "$title%";
			break;
		case 4:
			$where .= "LIKE ?";
			$params[] = "%$title";
			break;
		default:
			$where = "";
	}

	//Add By Jimmy
	if ($accessno != 0) {
		$where .= "AND accessno = ?";
		$params[] = $accessno;
	}

	if ($user_id < 0) {
		$islibrarian = ($user_id == -2) ? 1 : 0;
		$sql = "
			SELECT
				l.id,
				r.title,
				r.author, c.accessno,
				c.callno,
				l.loandate, l.actualreturndate,
				l.userid, u.username, l.islibrarian,
				l.contactperson, l.contactnumber, l.contactemail, l.borrowerid
			FROM mdl_library_loan l
			LEFT JOIN mdl_library_copy c ON l.copyid=c.id
			LEFT JOIN mdl_library_resource r ON c.resourceid=r.id
			LEFT JOIN mdl_user u ON u.id=l.userid
			WHERE l.actualreturndate!=0 AND l.islibrarian=$islibrarian
			$where
			order by id desc
		";
	} else {
		$is_ngo = check_is_ngo_user($user_id);
		$sql_select_contact = '';
		if ($is_ngo) {
			$sql_select_contact = ', l.contactperson, l.contactnumber, l.contactemail, l.borrowerid';
		}

		$sql = "
			SELECT
				l.id,
				r.title,
				r.author, c.accessno,
				c.callno,
				l.loandate, l.actualreturndate, l.islibrarian
				$sql_select_contact
			FROM mdl_library_loan l
			LEFT JOIN mdl_library_copy c ON l.copyid=c.id
			LEFT JOIN mdl_library_resource r ON c.resourceid=r.id
			WHERE l.userid=? AND l.actualreturndate!=0 AND l.islibrarian=0
			$where
			order by id desc
		";
	}

	return $DB->get_records_sql($sql, $params);
}

function check_library_resource_is_reserved($accessno)
{
	global $DB;

	$accessno = trim($accessno);
	$userids = array();

	$sql = "
		SELECT
			rs.userid
		FROM mdl_library_reserve rs
		LEFT JOIN mdl_library_resource r ON rs.resourceid=r.id
		LEFT JOIN mdl_library_copy c ON r.id=c.resourceid
		WHERE c.accessno=? AND rs.isdone=0 AND c.isdelete=0
	";
	$result = $DB->get_records_sql($sql, array($accessno));
	foreach ($result as $row) {
		$userids[] = $row->userid;
	}

	return $userids;
}

function count_available_copy_by_resourceid($resourceid)
{
	global $DB;

	$sql = "
		SELECT COUNT(*) as num FROM mdl_library_copy
		WHERE resourceid = ?
		AND isloan = 0
		AND isdelete = 0
	";
	$record = $DB->get_record_sql($sql, array($resourceid));
	return $record->num;
}

function get_available_copy_by_resourceid($resourceid)
{
	global $DB;

	$sql = "
		SELECT id, accessno, callno FROM mdl_library_copy
		WHERE resourceid = ?
		AND isloan = 0
		AND isdelete = 0
	";
	$record = $DB->get_records_sql($sql, array($resourceid));
	return $record;
}

function get_loan_userid_by_copyid($copyid)
{
	global $DB;

	$copyid = trim($copyid);

	$sql = "
		SELECT userid
		FROM mdl_library_loan
		WHERE copyid=$copyid AND actualreturndate=0
	";
	$result = $DB->get_record_sql($sql);
	if ($result === false) {
		return false;
	} else {
		return $result->userid;
	}
}

function validate_borrower_encoded_string($borrower)
{
	if (!isset($borrower) || empty($borrower)) {
		return false;
	}
	$borrower = json_decode(base64_decode($borrower));
	if (isset($borrower->contactperson) && isset($borrower->contactnumber) && isset($borrower->contactemail) && isset($borrower->borrowerid)) {
		return true;
	}
	return false;
}



/* ---- Return ---- */

function add_return_record($copyid)
{
	global $DB;

	$time = time();
	$DB->execute("UPDATE mdl_library_loan SET actualreturndate=$time WHERE copyid=$copyid AND actualreturndate=0");

	$dataobject = new stdClass();
	$dataobject->id = $copyid;
	$dataobject->isloan = 0;
	$DB->update_record('library_copy', $dataobject);

	$copy = $DB->get_record_sql("SELECT resourceid FROM mdl_library_copy WHERE id='$copyid'");

	$reserve = $DB->get_record_sql("SELECT * FROM mdl_library_reserve WHERE isdone=0 AND resourceid='{$copy->resourceid}' ORDER BY requestdate LIMIT 0, 1");
	if ($reserve) {
		$reserve->availabletime = $time;
		$DB->update_record('library_reserve', $reserve);
		send_reserve_email($reserve);
	}

	return array(
		'status' => 'success',
		'msg' => ''
	);
}

function get_library_resource_copy_info_for_return_list($copy_id)
{
	global $DB;

	$copy_id = trim($copy_id);
	$sql = "
		SELECT
			c.accessno, r.title,
			l.loandate, l.returndate
		FROM mdl_library_loan l
		LEFT JOIN mdl_library_copy c ON l.copyid=c.id
		LEFT JOIN mdl_library_resource r ON c.resourceid=r.id
		WHERE l.copyid=$copy_id AND actualreturndate=0
		LIMIT 0, 1
	";
	return $DB->get_record_sql($sql);
}



/* ---- Renew ---- */

function check_is_loan_overdue($copyid)
{
	global $DB;

	$time = time();

	$sql = "
		SELECT id FROM mdl_library_loan
		WHERE
			copyid=$copyid AND
			actualreturndate=0 AND
			returndate < $time
	";
	if ($DB->record_exists_sql($sql)) {
		return true;
	}
	return false;
}

function get_loanid_by_copyid($copyid)
{
	global $DB;

	$time = time();

	$sql = "
		SELECT id FROM mdl_library_loan
		WHERE
			copyid=$copyid AND
			actualreturndate=0 AND
			returndate >= $time
	";
	$result = $DB->get_record_sql($sql);
	if (isset($result)) {
		return $result->id;
	}
	return false;
}

function get_renew_count($loanid)
{
	global $DB;

	$sql = "SELECT count(*) renew_count FROM mdl_library_renew WHERE loanid=$loanid";
	$result = $DB->get_record_sql($sql);
	return $result->renew_count;
}

function check_is_library_resource_reserving($resourceid)
{
	global $DB;

	$time = time();

	$sql = "SELECT id FROM mdl_library_reserve WHERE resourceid=$resourceid AND isdone=0";
	if ($DB->record_exists_sql($sql)) {
		return true;
	}
	return false;
}

function add_renew_record($loanid, $new_return_date)
{
	global $DB;

	$time = time();

	$dataobject = new stdClass();
	$dataobject->loanid = $loanid;
	$dataobject->renewdate = $time;
	$dataobject->newreturndate = $new_return_date;
	$DB->insert_record('library_renew', $dataobject);

	$dataobject = new stdClass();
	$dataobject->id = $loanid;
	$dataobject->returndate = $new_return_date;
	$DB->update_record('library_loan', $dataobject);

	return array(
		'status' => 'success',
		'msg' => ''
	);
}

function get_library_resource_copy_info_for_renew_list($loan_id)
{
	global $DB;

	$loan_id = trim($loan_id);
	$sql = "
		SELECT
			c.accessno, r.title,
			l.returndate oldreturndate
		FROM mdl_library_loan l
		LEFT JOIN mdl_library_copy c ON l.copyid=c.id
		LEFT JOIN mdl_library_resource r ON c.resourceid=r.id
		WHERE l.id=$loan_id
		LIMIT 0, 1
	";

	$result = $DB->get_record_sql($sql);
	$result->newreturndate = time() + (3600 * 24 * LIBRARY_RENEW_DAYS);
	$result->newreturndate = date('Y-m-d', $result->newreturndate) . ' 00:00:00';
	$result->newreturndate = strtotime($result->newreturndate) - 1;
	return $result;
}



/* ---- Reserve ---- */

function get_reserve_queue_count($resourceid)
{
	global $DB;

	$resourceid = trim($resourceid);
	$sql = "
		SELECT count(*) number_of_reserve
		FROM mdl_library_reserve
		WHERE resourceid=$resourceid AND IFNULL(isdone,0)=0
	";
	$result = $DB->get_record_sql($sql);
	return $result->number_of_reserve;
}

function get_user_own_reserve_info($resourceid)
{
	global $DB, $USER;

	if (empty($resourceid) || ($USER->id <= 1)) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$sql = "
		SELECT requestdate
		FROM mdl_library_reserve
		WHERE resourceid=? AND userid=? AND IFNULL(isdone,0)=0
	";
	$params = array($resourceid, $USER->id);
	return $DB->get_record_sql($sql, $params);
}

function get_user_reserve_rank_in_queue($resourceid, $userid)
{
	global $DB;

	if (empty($resourceid) || empty($userid)) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}
	/*
	if(get_user_own_reserve_info($resourceid) == null){
		return array(
			'status' => 'error',
			'msg' => get_string('msg_not_already_reserved_resource', 'local_elibrary')
		);
	}
	*/
	$sql = "
		SELECT userid
		FROM mdl_library_reserve
		WHERE resourceid=$resourceid AND IFNULL(isdone,0)=0
	";
	$result = $DB->get_records_sql($sql);
	$i = 1;
	foreach ($result as $user) {
		if ($user->userid == $userid) {
			return $i;
		}
		$i++;
	}
}

function add_reserve($resourceid)
{
	global $DB, $USER;

	if (empty($resourceid) || ($USER->id <= 1)) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$dataobject = new stdClass();
	$dataobject->userid = $USER->id;
	$dataobject->resourceid = trim($resourceid);
	$dataobject->requestdate = time();
	$dataobject->isdone = 0;
	$reserveid = $DB->insert_record('library_reserve', $dataobject);

	$reserve = $DB->get_record_sql("SELECT * FROM mdl_library_reserve WHERE isdone=0 AND resourceid='{$resourceid}' ORDER BY requestdate LIMIT 0, 1");
	if (!$reserve) {
		$reserve = $DB->get_record_sql("SELECT * FROM mdl_library_reserve WHERE id='$reserveid'");
		$reserve->availabletime = $reserve->requestdate;
		send_reserve_email($reserve);
	}

	return array(
		'status' => 'success',
		'msg' => ''
	);
}

function delete_own_reserve($resourceid)
{
	global $DB, $USER;

	if (empty($resourceid) || ($USER->id <= 1)) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$resourceid = trim($resourceid);

	$sql = "DELETE FROM mdl_library_reserve WHERE userid={$USER->id} AND resourceid=$resourceid AND isdone=0";
	$DB->execute($sql);

	$reserve = $DB->get_record_sql("SELECT * FROM mdl_library_reserve WHERE isdone=0 AND resourceid='{$resourceid}' ORDER BY requestdate LIMIT 0, 1");
	if ($reserve) {
		$reserve->availabletime = time();
		$DB->update_record('library_reserve', $reserve);
		send_reserve_email($reserve);
	}

	return array(
		'status' => 'success',
		'msg' => ''
	);
}

function delete_reserve($reserveid)
{
	global $DB;

	if (empty($reserveid)) {
		return array(
			'status' => 'error',
			'msg' => get_string('msg_unknown_error', 'local_elibrary')
		);
	}

	$reserveid = trim($reserveid);

	$reserve = $DB->get_record_sql("SELECT * FROM mdl_library_reserve WHERE id='$reserveid'");

	$sql = "DELETE FROM mdl_library_reserve WHERE id=$reserveid";
	$DB->execute($sql);

	$reserve = $DB->get_record_sql("SELECT * FROM mdl_library_reserve WHERE isdone=0 AND resourceid='{$reserve->resourceid}' ORDER BY requestdate LIMIT 0, 1");
	if ($reserve) {
		$reserve->availabletime = time();
		$DB->update_record('library_reserve', $reserve);
		send_reserve_email($reserve);
	}

	return array(
		'status' => 'success',
		'msg' => ''
	);
}

function get_user_current_reservation_list($user_id)
{
	global $DB;

	if ($user_id == -1) {
		$sql = "
			SELECT
				rs.id, r.id resource_id,
				r.title,
				r.author, r.isbn,
				rs.requestdate, rs.userid, u.username
			FROM mdl_library_reserve rs
			LEFT JOIN mdl_library_resource r ON rs.resourceid=r.id
			LEFT JOIN mdl_user u ON rs.userid=u.id
			WHERE rs.isdone=0
			order by rs.id desc
		";
		$result = $DB->get_records_sql($sql);
	} else {
		$sql = "
			SELECT
				rs.id, r.id resource_id,
				r.title,
				r.author, r.isbn,
				rs.requestdate
			FROM mdl_library_reserve rs
			LEFT JOIN mdl_library_resource r ON rs.resourceid=r.id
			WHERE rs.userid=? AND rs.isdone=0
			order by rs.id desc
		";
		$result = $DB->get_records_sql($sql, array($user_id));
	}

	foreach ($result as &$row) {
		if ($user_id == -1) {
			$rank = get_user_reserve_rank_in_queue($row->resource_id, $row->userid);
		} else {
			$rank = get_user_reserve_rank_in_queue($row->resource_id, $user_id);
		}
		if (!is_int($rank)) {
			var_dump($rank);
			$row->reserve_rank_in_queue = false;
		} else {
			$row->reserve_rank_in_queue = $rank;
		}

		$copy_count = count_available_copy_by_resourceid($row->resource_id);
		if ($rank > $copy_count) {
			$sql = "
				SELECT
					min(l.returndate) recent_return_date
				FROM mdl_library_loan l
				LEFT JOIN mdl_library_copy c ON c.id=l.copyid
				WHERE c.resourceid={$row->resource_id} AND l.actualreturndate=0
			";
			$loan_result = $DB->get_record_sql($sql);

			if ($loan_result->recent_return_date !== null) {
				$row->isloan = true;
				$row->recent_return_date = $rank == 1 ? $loan_result->recent_return_date : 0;
			} else {
				$row->isloan = false;
				$row->recent_return_date = 0;
			}
		} else {
			$row->isloan = false;
			$row->recent_return_date = 0;
		}
	}

	return $result;
}

function get_user_reservation_history($user_id, $title = '', $operation = -1, $accessno = 0)
{
	global $DB;

	$where = "AND r.title ";
	$params = array();

	if ($user_id != -1) {
		$params[] = $user_id;
	}

	switch ($operation) {
		case 0:
			$where .= "LIKE ?";
			$params[] = "%$title%";
			break;
		case 1:
			$where .= "NOT LIKE ?";
			$params[] = "%$title%";
			break;
		case 2:
			$where .= "= ?";
			$params[] = $title;
			break;
		case 3:
			$where .= "LIKE ?";
			$params[] = "$title%";
			break;
		case 4:
			$where .= "LIKE ?";
			$params[] = "%$title";
			break;
		default:
			$where = "";
	}

	//Add By Jimmy
	if ($accessno != 0) {
		$where .= "AND accessno = ?";
		$params[] = $accessno;
	}

	if ($user_id == -1) {
		$sql = "
			SELECT
				rs.id,
				r.title,
				r.author, r.isbn,
				rs.requestdate, rs.userid, u.username
			FROM mdl_library_reserve rs
			LEFT JOIN mdl_library_resource r ON rs.resourceid=r.id
			LEFT JOIN mdl_library_copy c ON rs.resourceid=c.id
			LEFT JOIN mdl_user u ON rs.userid=u.id
			WHERE rs.isdone=1
			$where
			order by id desc
		";
	} else {
		$sql = "
			SELECT
				rs.id,
				r.title,
				r.author, r.isbn,
				rs.requestdate
			FROM mdl_library_reserve rs
			LEFT JOIN mdl_library_resource r ON rs.resourceid=r.id
			LEFT JOIN mdl_library_copy c ON rs.resourceid=c.id
			WHERE rs.userid=? AND rs.isdone=1
			$where
			order by id desc
		";
	}

	return $DB->get_records_sql($sql, $params);
}

function get_reserve_userid($reserveid)
{
	global $DB;

	$reserveid = trim($reserveid);

	$sql = "
		SELECT userid
		FROM mdl_library_reserve
		WHERE id=$reserveid
	";

	$result = $DB->get_record_sql($sql);
	if ($result === false) {
		return false;
	} else {
		return $result->userid;
	}
}

function send_reserve_email($reserve)
{
	global $DB, $CFG;

	require_once($CFG->dirroot . '/user/profile_lib.php');

	if (!isset($reserve->userid) || !isset($reserve->resourceid)) {
		return false;
	}

	$site = get_site();
	$sitename = format_string($site->fullname);
	$supportuser = core_user::get_support_user();
	$admin = generate_email_signoff();

	$user = $DB->get_record_sql("SELECT * FROM mdl_user WHERE id={$reserve->userid}");
	$resource = $DB->get_record_sql("SELECT title FROM mdl_library_resource WHERE id={$reserve->resourceid}");

	$data = new stdClass();
	$data->firstname = $user->firstname;
	$data->resourcename = $resource->title;
	$data->resourceurl = (new moodle_url($CFG->LIBRARY_BASEURL . 'view_resource_detail.php', array('id' => $reserve->resourceid)))->__toString();
	$data->sitename = $sitename;
	$data->admin = $admin;

	$subject = get_string('reservation_become_available_email_subject', 'local_elibrary', $data);
	$messagetext = get_string('reservation_become_available_email_message', 'local_elibrary', $data);
	$messagehtml = text_to_html($messagetext, false, false, true);

	//Send email
	$user->mailformat = 1;  // Always send HTML version as well.
	email_to_user($user, $supportuser, $subject, $messagetext, $messagehtml, null, null, null, null, null, null, 'reservation_become_available_email');

	$sas = get_role_users(get_user_role_id('sa'), context_system::instance());
	foreach ($sas as $sa) {
		$data = new stdClass();
		$data->username = $user->firstname;
		$data->firstname = $sa->firstname;
		$data->resourcename = $resource->title;
		$data->resourceurl = (new moodle_url($CFG->LIBRARY_BASEURL . 'user_reservation_history.php'))->__toString();
		$data->sitename = $sitename;
		$data->admin = $admin;

		$subject = get_string('reservation_become_available_sa_email_subject', 'local_elibrary', $data);
		$messagetext = get_string('reservation_become_available_sa_email_message', 'local_elibrary', $data);
		$messagehtml = text_to_html($messagetext, false, false, true);

		//Send email
		$sa->mailformat = 1;  // Always send HTML version as well.
		email_to_user($sa, $supportuser, $subject, $messagetext, $messagehtml, null, null, null, null, null, null, 'reservation_become_available_sa_email');
	}
}



/* ---- Other ---- */

function get_library_selector_data($selector)
{
	global $DB;

	switch ($selector) {
		case 'subject':
			$return = array();
			$data = $DB->get_records_sql("SELECT id, name_eng, name_chi FROM mdl_library_subject WHERE isdelete=0 ORDER BY name_eng");
			foreach ($data as $row) {
				$return[$row->id] = $row->name_eng;
			}
			return $return;
		case 'subject_chi':
			$return = array();
			$data = $DB->get_records_sql("SELECT id, name_eng, name_chi FROM mdl_library_subject WHERE isdelete=0 ORDER BY name_eng");
			foreach ($data as $row) {
				$return[$row->id] = $row->name_chi;
			}
			return $return;
		case 'class':
			$return = array();
			$data = $DB->get_records_sql("SELECT id, description_eng, description_chi FROM mdl_library_class ORDER BY description_eng");
			foreach ($data as $row) {
				$return[$row->id] = $row->description_eng;
			}
			return $return;
		case 'class_chi':
			$return = array();
			$data = $DB->get_records_sql("SELECT id, description_eng, description_chi FROM mdl_library_class ORDER BY description_eng");
			foreach ($data as $row) {
				$return[$row->id] = $row->description_chi;
			}
			return $return;
		case 'currency':
			$return = array();
			$data = $DB->get_records_sql("SELECT id, code FROM mdl_library_currency ORDER BY code");
			foreach ($data as $row) {
				$return[$row->id] = $row->code;
			}
			return $return;
		case 'locate':
			$return = array();
			$data = $DB->get_records_sql("SELECT id, code, description FROM mdl_library_locate ORDER BY code");
			foreach ($data as $row) {
				$return[$row->id] = "{$row->code} - {$row->description}";
			}
			return $return;
	}
}

function get_userdata($username, $firstname, $lastname, $chinesename)
{
	global $DB;

	$username = trim($username);
	$firstname = trim($firstname);
	$lastname = trim($lastname);
	$chinesename = trim($chinesename);

	$conditions = array();
	$params = array();

	if ($username != '') {
		$conditions[] = "username LIKE ?";
		$params[] = "%$username%";
	}

	if ($firstname != '') {
		$conditions[] = "firstname LIKE ?";
		$params[] = "%$firstname%";
	}

	if ($lastname != '') {
		$conditions[] = "lastname LIKE ?";
		$params[] = "%$lastname%";
	}

	if ($chinesename != '') {
		$conditions[] = "chinesename LIKE ?";
		$params[] = "%$chinesename%";
	}

	$where = "	
		WHERE deleted = 0
		AND suspended = 0
		AND confirmed = 1
		AND isactivate = 1
		AND isapproved = 1
	";

	if ($username != '' || $firstname != '' || $lastname != '' || $chinesename != '') {
		$where .= " AND (" . implode(" AND ", $conditions) . ")";
	}

	$sql = "
		SELECT id, shortname
		FROM mdl_user_info_field
		WHERE shortname IN ('orgname', 'chiname')
	";
	$data = $DB->get_records_sql($sql);

	$useraddifields = array();
	foreach ($data as $row) {
		$useraddifields[$row->shortname] = $row->id;
	}

	$sql = "
		SELECT mdl_user.id, username,firstname,lastname,chiname,phone1,phone2,email,orgname
		FROM mdl_user
		LEFT JOIN (
			SELECT data as orgname, userid
			FROM mdl_user_info_data
			WHERE fieldid = {$useraddifields['orgname']}
		) userfield1 ON userfield1.userid = mdl_user.id
		LEFT JOIN (
			SELECT data as chiname, userid
			FROM mdl_user_info_data
			WHERE fieldid = {$useraddifields['chiname']}
		) userfield2 ON userfield2.userid = mdl_user.id
		$where
		LIMIT 100
	";

	return $DB->get_records_sql($sql, $params);
}

function get_userid_by_username($username)
{
	global $DB;

	$username = trim($username);
	return $DB->get_record_sql("SELECT id FROM mdl_user WHERE username='$username'");
}

function get_username_by_userid($userid)
{
	global $DB;

	$userid = trim($userid);
	return $DB->get_record_sql("SELECT username FROM mdl_user WHERE id=?", array($userid));
}