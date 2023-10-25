<?php  // Moodle configuration file

// PRODUCTION SERVER x.21 

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mariadb';
$CFG->dblibrary = 'native';
$CFG->dbhost    = '10.88.16.23';
$CFG->dbname    = 'moodle3.9.8+'; 
//$CFG->dbname    = 'moodle_20210102'; // migrated DB 20210102 for RepoID Quiz bug
//$CFG->dbname    = 'migrate39'; // migrated DB 20201128-20210102
//$CFG->dbname    = 'moodlenew';  // first Production

//20220708 git issue fix
//old
//$CFG->dbuser    = 'dbadmin';
//$CFG->dbpass    = 'Esri1234***';
//new
$CFG->dbuser    = 'delsm39admin';
$CFG->dbpass    = 'C1TuS@mOY&Jc';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array(
  'dbpersist' => 0,
  'dbport' => '',
  'dbsocket' => '',
  'dbcollation' => 'utf8mb4_unicode_ci',
);

//$CFG->wwwroot   = 'http://10.88.16.21';  
$CFG->wwwroot   = 'https://www.elc.swd.gov.hk'; 
$CFG->dataroot  = '/dataroot';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

$CFG->filedir = '/share/share';

//$CFG->repositorydir = '/share/videoshare';

$CFG->redirecttimes = 3;

$CFG->ffmpeg = '/root/bin/ffmpeg';
$CFG->thumbnailfiletype = '.jpg';

//$CFG->lang="zh_tw";

require_once(__DIR__ . '/lib/setup.php');

//E-book url, uid, password and encrypt key
$CFG->EBOOK_URL = "http://www.apabi.com/hkshfls";
$CFG->EBOOK_UID = "hkshfls1";
$CFG->EBOOK_PWD = "123456";
$CFG->EBOOK_ENKEY = "shflskey";

$CFG->EBOOK_BASEURL = "/local/ebook/";
$CFG->LIBRARY_BASEURL = "/local/elibrary/";
$CFG->RESOURCES_BASEURL = "/local/resources/";
$CFG->VIDEOS_BASEURL = "/local/videos/";
$CFG->INTERRAI_BASEURL = "/local/interrai/";
$CFG->opensslversion = '1.1.1w';

/* 
$CFG->defaultblocks_site = 'site_main_menu,course_list:course_summary,calendar_month';
$CFG->defaultblocks_social = 'search_forums,calendar_month,calendar_upcoming,social_activities,recent_activity,course_list';
$CFG->defaultblocks_topics = 'activity_modules,search_forums,course_list:news_items,calendar_upcoming,recent_activity';
$CFG->defaultblocks_weeks = 'activity_modules,search_forums,course_list:news_items,calendar_upcoming,recent_activity';
 *///
// These blocks are used when no other default setting is found.
//$CFG->defaultblocks = 'activity_modules,search_forums,course_list:news_items,calendar_upcoming,recent_activity';

//Video Streaming url
$CFG->STREAM_HTTP_URL = "http://202.128.252.81/vod/elearning/";
$CFG->STREAM_RTMP_URL = "rtmp://202.128.252.81/vod/";

//Web Accessibility Conformance Page ID
$CFG->nonconformancepageid = 1452;
$CFG->mainnonconformancepageid = 1471; //show the page by clicking link on the bottom left


// Prevent core_string_manager application caching
$CFG->langstringcache = true; // TRUE FOR PRODUCTION SERVERS!
$CFG->cachejs = true; // TRUE FOR PRODUCTION SERVERS!
$CFG->preventexecpath = true;

//for page log, including elibrary page hit report
$CFG->urlpath = str_replace(str_replace('\\', '/', dirname(__FILE__)), "", $_SERVER['SCRIPT_FILENAME']);

// image file size for elibrary
$CFG->maximagefilesize = 700 * 1024;

$CFG->debug = NONE;
$CFG->debugdisplay = 0;

//for page log, including elibrary page hit report
$CFG->urlpath = str_replace(str_replace('\\', '/', dirname(__FILE__)), "", $_SERVER['SCRIPT_FILENAME']);

$CFG->dboptions = array (
  //'logall'   => true,
  'logslow'  => 5,
  'logerrors'  => true,
);
ini_set ('display_errors', 'off'); 
ini_set ('log_errors', 'off'); 
ini_set ('display_startup_errors', 'off'); 
ini_set ('error_reporting', NONE);
ini_set('max_execution_time', 300);
ini_set('memory_limit', '512M');