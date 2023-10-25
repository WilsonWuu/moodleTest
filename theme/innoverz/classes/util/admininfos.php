<?php
namespace theme_innoverz\util;

defined('MOODLE_INTERNAL') || die();

/**
 * Class to get some admin infos in Moodle.
 *
 * @package    theme_innoverz
 * @copyright  2020 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admininfos extends \theme_moove\util\admininfos{

    /**
     * Returns the total of disk usage
     *
     * @return string
     * @throws \coding_exception
     */
    public function get_totaldiskusage() {
        $cache = \cache::make('theme_innoverz', 'admininfos');
        $totalusagereadable = $cache->get('totalusagereadable');

        if (!$totalusagereadable) {
            return get_string('notcalculatedyet', 'theme_innoverz');
        }

        $usageunit = ' MB';
        if ($totalusagereadable > 1024) {
            $usageunit = ' GB';
        }

        return $totalusagereadable . $usageunit;
    }
}
