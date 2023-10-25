<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_login();
$PAGE->set_context(context_system::instance());
require_capability('local/elibrary:resourceadministration', context_system::instance());

$PAGE->set_url(new moodle_url($CFG->LIBRARY_BASEURL . 'admin.php'));
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add(get_string('elibrary:resourceadministration', 'local_elibrary'));
$PAGE->set_title(get_string('elibrary:resourceadministration', 'local_elibrary'));
$PAGE->set_heading(get_string('elibrary:resourceadministration', 'local_elibrary'));

echo $OUTPUT->header();
?>
<div class="tab-pane" id="linkelibrary" role="tabpanel">
	<div class="container">
		<div class="row">
			<div class="col-sm-3">
				<h4>Circulation administration<h4>

			</div>
			<div class="col-sm-9">
				<ul class="list-unstyled">
					<li><a href="<?php echo new moodle_url($CFG->LIBRARY_BASEURL . 'loan_resource.php');?>">Borrowing Resource</a></li>
					<li><a href="<?php echo new moodle_url($CFG->LIBRARY_BASEURL . 'renew_resource.php');?>">Renew Resource</a></li>
					<li><a href="<?php echo new moodle_url($CFG->LIBRARY_BASEURL . 'return_resource.php');?>">Return Resource</a></li>
				</ul>
			</div>
		</div>
		<hr>
		<div class="row">
			<div class="col-sm-3">
				<h4>Report<h4>

			</div>
			<div class="col-sm-9">
				<ul class="list-unstyled">
					<li><a href="<?php echo new moodle_url($CFG->LIBRARY_BASEURL . 'user_loan_history.php?userid=-1');?>">Borrowing report</a></li>
					<li><a href="<?php echo new moodle_url($CFG->LIBRARY_BASEURL . 'user_reservation_history.php?userid=-1');?>">Reservation report</a></li>
					<li><a href="<?php echo new moodle_url($CFG->LIBRARY_BASEURL . 'review_report.php');?>">Resource review report</a></li>
					<li><a href="<?php echo new moodle_url($CFG->LIBRARY_BASEURL . 'resource_removed_list.php');?>">Resource remove report</a></li>
					<li><a href="<?php echo new moodle_url($CFG->LIBRARY_BASEURL . 'userborrowstat/index.php');?>">User borrow statistics report</a></li>
					<li><a href="<?php echo new moodle_url($CFG->LIBRARY_BASEURL . 'loanstat/index.php');?>">Borrowing statistics report</a></li>
					<li><a href="<?php echo new moodle_url('/local/reports/librarypagehits/index.php');?>">Library page hits report</a></li>
				</ul>
			</div>
		</div>
		<hr>
		<div class="row">
			<div class="col-sm-3">
				<h4>Resource administration<h4>

			</div>
			<div class="col-sm-9">
				<ul class="list-unstyled" style="margin-bottom:0;">
					<li><a href="<?php echo new moodle_url($CFG->LIBRARY_BASEURL . 'resource_list.php');?>">Resource</a></li>
					<li><a href="<?php echo new moodle_url($CFG->LIBRARY_BASEURL . 'subject_list.php');?>">Subject</a></li>
					<li><a href="<?php echo new moodle_url($CFG->LIBRARY_BASEURL . 'class_list.php');?>">Class</a></li>
					<li><a href="<?php echo new moodle_url($CFG->LIBRARY_BASEURL . 'locate_list.php');?>">Location</a></li>
				</ul>
			</div>
		</div>
	</div>
</div>
<?php
echo $OUTPUT->footer();
