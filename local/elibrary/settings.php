<?php

// * Miscellaneous settings

if ($hassiteconfig  && has_capability('local/elibrary:resourceadministration', context_system::instance())) { // speedup for non-admins, add all caps used on this page

	$ADMIN->add('root', new admin_category('elibrary', new lang_string('library', 'local_elibrary')));

	$ADMIN->add('elibrary', new admin_category('elibrarycirculationadmin', new lang_string('circulationadmin', 'local_elibrary')));
	$ADMIN->add('elibrarycirculationadmin', new admin_externalpage('elibraryloanresource', new lang_string('loan_resource', 'local_elibrary'), new moodle_url('/local/elibrary/loan_resource.php')));
	$ADMIN->add('elibrarycirculationadmin', new admin_externalpage('elibraryrenewresource', new lang_string('renew_resource', 'local_elibrary'), new moodle_url('/local/elibrary/renew_resource.php')));
	$ADMIN->add('elibrarycirculationadmin', new admin_externalpage('elibraryreturnresource', new lang_string('return_resource', 'local_elibrary'), new moodle_url('/local/elibrary/return_resource.php')));

	$ADMIN->add('elibrary', new admin_category('elibraryreport', new lang_string('report', 'local_elibrary')));
	$ADMIN->add('elibraryreport', new admin_externalpage('elibraryloanreport', new lang_string('loanreport', 'local_elibrary'), new moodle_url('/local/elibrary/user_loan_history.php', array('userid' => -1))));
	$ADMIN->add('elibraryreport', new admin_externalpage('elibraryreservationreport', new lang_string('reservationreport', 'local_elibrary'), new moodle_url('/local/elibrary/user_reservation_history.php', array('userid' => -1))));
	$ADMIN->add('elibraryreport', new admin_externalpage('elibraryreviewreport', new lang_string('reviewreport', 'local_elibrary'), new moodle_url('/local/elibrary/review_report.php')));
	$ADMIN->add('elibraryreport', new admin_externalpage('elibraryresourceremovedreport', new lang_string('resourceremovedreport', 'local_elibrary'), new moodle_url('/local/elibrary/resource_removed_list.php')));
	$ADMIN->add('elibraryreport', new admin_externalpage('elibraryuserborrowstatisticsreport', new lang_string('userborrowstatisticsreport', 'local_elibrary'), new moodle_url('/local/elibrary/userborrowstat/index.php')));
	$ADMIN->add('elibraryreport', new admin_externalpage('elibraryloanstatisticsreport', new lang_string('loanstatisticsreport', 'local_elibrary'), new moodle_url('/local/elibrary/loanstat/index.php')));
	$ADMIN->add('elibraryreport', new admin_externalpage('librarypagehitsreport', new lang_string('librarypagehitsreport', 'local_elibrary'), $CFG->wwwroot . '/local/reports/librarypagehits/index.php', 'moodle/site:viewreports'));

	$ADMIN->add('elibrary', new admin_category('elibraryresourceadmin', new lang_string('resourceadmin', 'local_elibrary')));
	$ADMIN->add('elibraryresourceadmin', new admin_externalpage('elibrarymaintainresource', new lang_string('resource', 'local_elibrary'), new moodle_url('/local/elibrary/resource_list.php')));
	$ADMIN->add('elibraryresourceadmin', new admin_externalpage('elibrarymaintainsubject', new lang_string('subject', 'local_elibrary'), new moodle_url('/local/elibrary/subject_list.php')));
	$ADMIN->add('elibraryresourceadmin', new admin_externalpage('elibrarymaintainclass', new lang_string('class', 'local_elibrary'), new moodle_url('/local/elibrary/class_list.php')));
	//$ADMIN->add('elibraryresourceadmin', new admin_externalpage('elibrarymaintaincurrency', new lang_string('currency', 'local_elibrary'), new moodle_url('/local/elibrary/currency_list.php')));
	$ADMIN->add('elibraryresourceadmin', new admin_externalpage('elibrarymaintainlocate', new lang_string('locate', 'local_elibrary'), new moodle_url('/local/elibrary/locate_list.php')));
} // end of speedup
