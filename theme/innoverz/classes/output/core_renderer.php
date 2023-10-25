<?php

namespace theme_innoverz\output;

defined('MOODLE_INTERNAL') || die;

use context_system;
use moodle_url;

require_once($CFG->dirroot . '/theme/innoverz/classes/output/pageview.php');
class core_renderer extends \theme_moove\output\core_renderer
{

    public function header()
    {
        global $USER, $DB;

        $pvid = pageview::log();    //added by Tai
        $this->page->requires->data_for_js('pvid', $pvid);

        $roles = get_user_roles(context_system::instance(), $USER->id);

        foreach ($roles as $role) {
            $this->page->add_body_class('role-' . $role->shortname);
        }

        $IR_roleids = array();
        $IR_roleids[] = $DB->get_field('role', 'id', array('shortname' => 'ir_trainer'));
        $IR_roleids[] = $DB->get_field('role', 'id', array('shortname' => 'ir_trainee'));
        $IR_roleids[] = $DB->get_field('role', 'id', array('shortname' => 'ir_assessor'));
        foreach ($IR_roleids as $IR_roleid) {
            if (user_has_role_assignment($USER->id, $IR_roleid)) {
                if (
                    strpos($_SERVER['REQUEST_URI'], 'local/interrai/') == false &&
                    strpos($_SERVER['REQUEST_URI'], 'local/folder/') == false &&
                    strpos($_SERVER['REQUEST_URI'], 'pluginfile.php') == false &&
                    strpos($_SERVER['REQUEST_URI'], 'login/change_password.php') == false &&
                    strpos($_SERVER['REQUEST_URI'], "user/profile.php?id=$USER->id") == false
                ) {
                    redirect(new moodle_url('/local/interrai/'));
                }
            }
        }

        return parent::header();
    }

    //Added by Tai
    public function paging_bar_data_loop($dataset, $totalcount, $page, $perpage, $callback)
    {
        $startrow = $page * $perpage;
        $endrow = $startrow + $perpage;
        $endrow = ($endrow > $totalcount) ? $totalcount : $endrow;
        $startrow++;

        $i = 0;
        foreach ($dataset as $data) {
            $i++;
            if ($i < $startrow) {
                continue;
            }
            if ($i > $endrow) {
                break;
            }
            $callback($data);
        }
    }

    /**
     * Renders the login form. 
     * Refer from moove's core_renderer.php render_login()
     *
     * @param \core_auth\output\login $form The renderable.
     * @return string
     */
    public function render_login(\core_auth\output\login $form)
    {
        global $CFG, $SITE;

        $context = $form->export_for_template($this);
        if ($CFG->rememberusername == 0) {
            $context->cookieshelpiconformatted = $this->help_icon('cookiesenabledonlysession');
        } else {
            $context->cookieshelpiconformatted = $this->help_icon('cookiesenabled');
        }
        $context->errorformatted = $this->error_text($context->error);

        // added sub logo 'elearning center' for login form
        $context->sublogourl =  $this->image_url('login_logo', 'theme');
        $context->sublogo2url =  $this->image_url('default_course', 'theme');

        $context->sitename = format_string(
            $SITE->fullname,
            true,
            ['context' => \context_course::instance(SITEID), "escape" => false]
        );

        return $this->render_from_template('core/loginform', $context);
    }

    /**
     * Gets the logo to be rendered.
     *
     * The priority of get log is: 1st try to get the theme logo, 2st try to get the theme logo
     * If no logo was found return false
     *
     * @return mixed
     */
    public function get_logo()
    {
        if ($this->should_display_theme_logo()) {
            return $this->get_theme_logo_url();
        }

        $url = $this->get_logo_url();
        if ($url) {
            return $url->out(false);
        }

        return false;
    }

    /**
     * Outputs the pix url base
     *
     * @return string an URL.
     */
    public function get_pix_image_url_base()
    {
        global $CFG;

        return $CFG->wwwroot . "/theme/innoverz/pix";
    }
}

class core_elibrary_renderer extends \theme_innoverz\output\elibrary\renderer
{
}
