<?php
// referred from \admin\settings\plugins.php in 2.7


$ADMIN->add('reports', new admin_category('user_reports', new lang_string('user_reports', 'local_reports')));

$ADMIN->add('user_reports', new admin_externalpage('learningresourcesstatistics', new lang_string('learningresourcesstatistics','local_reports'), $CFG->wwwroot.'/local/reports/learningresources/index.php', 'moodle/site:viewreports'));
$ADMIN->add('user_reports', new admin_externalpage('visitorsstatisticsreport', new lang_string('visitorsstatisticsreport','local_reports'), $CFG->wwwroot.'/local/reports/visitorstatistics/index.php', 'moodle/site:viewreports'));
$ADMIN->add('user_reports', new admin_externalpage('visitsdurationreport', new lang_string('visitsdurationreport','local_reports'), $CFG->wwwroot.'/local/reports/visitsduration/index.php', 'moodle/site:viewreports'));
$ADMIN->add('user_reports', new admin_externalpage('userlastvisitsreport', new lang_string('userlastvisitsreport','local_reports'), $CFG->wwwroot.'/local/reports/userlastvisits/index.php', 'moodle/site:viewreports'));
$ADMIN->add('user_reports', new admin_externalpage('visitsfrequencyreport', new lang_string('visitsfrequencyreport','local_reports'), $CFG->wwwroot.'/local/reports/visitsfrequency/index.php', 'moodle/site:viewreports'));
$ADMIN->add('user_reports', new admin_externalpage('coursepagehitsreport', new lang_string('coursepagehitsreport','local_reports'), $CFG->wwwroot.'/local/reports/coursepagehits/index.php', 'moodle/site:viewreports'));
//$ADMIN->add('user_reports', new admin_externalpage('kmpagehitsreport', new lang_string('kmpagehitsreport','local_reports'), $CFG->wwwroot.'/local/reports/kmpagehits/index.php', 'moodle/site:viewreports'));
//$ADMIN->add('user_reports', new admin_externalpage('coursewarepagehit', new lang_string('coursewarepagehit','local_reports'), $CFG->wwwroot.'/swdreport/coursespenttimereport.php', 'moodle/site:viewreports'));
$ADMIN->add('user_reports', new admin_externalpage('coursestatisticsreport', new lang_string('coursestatisticsreport','local_reports'), $CFG->wwwroot.'/local/reports/coursestatistics/index.php', 'moodle/site:viewreports'));
$ADMIN->add('user_reports', new admin_externalpage('userenrollmentstatisticsreport', new lang_string('userenrollmentstatisticsreport','local_reports'), $CFG->wwwroot.'/local/reports/userenrollmentstatistics/index.php', 'moodle/site:viewreports'));
//$ADMIN->add('user_reports', new admin_externalpage('userenrollmentstatisticsreport', new lang_string('userenrollmentstatisticsreport','local_reports'), $CFG->wwwroot.'/swdreport/userenrollmentreport.php', 'moodle/site:viewreports'));

$ADMIN->add('user_reports', new admin_externalpage('visitsprofilereport', new lang_string('visitsprofilereport','local_reports'), $CFG->wwwroot.'/local/reports/visitsprofile/index.php', 'moodle/site:viewreports'));