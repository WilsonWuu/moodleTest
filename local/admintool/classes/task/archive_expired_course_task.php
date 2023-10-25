<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A scheduled task.
 *
 * @package    core
 * @copyright  2013 onwards Martin Dougiamas  http://dougiamas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_admintool\task;

defined('MOODLE_INTERNAL') || die();

use core\task\scheduled_task;

/**
 * Simple task to run the backup cron.
 */
class archive_expired_course_task extends scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('archive_expired_course_task', 'local_admintool');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/innoverz/course/lib.php');
		$time = time();
		$courses = $DB->get_records_sql("SELECT id FROM mdl_course WHERE isarchive=0 AND enddate!=0 AND enddate<=$time AND autoarchive=1");
		foreach($courses as $course){
			course_archive($course->id);
		}
    }

}
