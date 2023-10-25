<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/course/renderer.php');

class local_course_renderer extends core_course_renderer
{

    protected function coursecat_tree(coursecat_helper $chelper, $coursecat)
    {
        /**
         * innoverz start: added viewtype param
         */
        $viewtype = optional_param('viewtype', '', PARAM_TEXT);
        $viewtype = ($viewtype != '' && $viewtype != 'archive') ? 'active' : $viewtype;
        /**
         * innoverz end
         */

        // Reset the category expanded flag for this course category tree first.
        $this->categoryexpandedonload = false;
        $categorycontent = $this->coursecat_category_content($chelper, $coursecat, 0, $viewtype); //innoverz
        if (empty($categorycontent)) {
            return '';
        }

        // Start content generation
        $content = '';
        $attributes = $chelper->get_and_erase_attributes('course_category_tree clearfix');
        $content .= html_writer::start_tag('div', $attributes);

        if ($coursecat->get_children_count()) {
            $classes = array(
                'collapseexpand', 'aabtn'
            );

            // Check if the category content contains subcategories with children's content loaded.
            if ($this->categoryexpandedonload) {
                $classes[] = 'collapse-all';
                $linkname = get_string('collapseall');
            } else {
                $linkname = get_string('expandall');
            }

            // Only show the collapse/expand if there are children to expand.
            $content .= html_writer::start_tag('div', array('class' => 'collapsible-actions'));

            $content .= html_writer::start_tag('div', array('id' => 'courselist-collapse', 'class' => 'inline-block'));


            $content .= html_writer::link('#', $linkname, array('class' => implode(' ', $classes)));

            /**
             * innoverz: add active/archive viewtype radio button start
             */
            $content .= html_writer::end_tag('div');
            /**
             * innoverz: add active/archive viewtype radio button end
             */

            $content .= html_writer::end_tag('div');

            $this->page->requires->strings_for_js(array('collapseall', 'expandall'), 'moodle');
        }

        /**
         * innoverz: add active/archive viewtype btn start
         */
        $viewtype = optional_param('viewtype', '', PARAM_TEXT);
        $viewtype = ($viewtype != '' && $viewtype != 'archive') ? 'active' : $viewtype;

        $content .= html_writer::start_tag('div', array('id' => 'courselist-viewtype', 'class' => 'inline-block'));
        if (get_class($coursecat) != 'learningplan') {
            $content .= $this->course_view_type($viewtype);
        }
        $content .= html_writer::end_tag('div');
        /**
         * innoverz: add active/archive viewtype btn end
         */

        $content .= html_writer::tag('div', $categorycontent, array('class' => 'content'));

        $content .= html_writer::end_tag('div'); // .course_category_tree

        return $content;
    }

    /**
     * innoverz: return course view type radio button
     */
    function course_view_type($viewtype)
    {
        $output = html_writer::start_tag('form');
        //$output .= html_writer::start_tag('fieldset', array('class' => 'coursesearchbox invisiblefieldset'));
        $output .= html_writer::start_tag('label');
        $output .= html_writer::empty_tag('input', array('type' => 'radio', 'id' => 'viewtype_active', 'name' => 'viewtype', 'value' => 'active', 'checked' => ((empty($viewtype) || $viewtype == 'active') ? 'checked' : null)));
        $output .= html_writer::tag('span', get_string('viewtype_active',  'local_course'));
        $output .= html_writer::end_tag('label');
        $output .= html_writer::start_tag('label');
        $output .= html_writer::empty_tag('input', array('type' => 'radio', 'id' => 'viewtype_archive', 'name' => 'viewtype', 'value' => 'archive', 'checked' => (($viewtype == 'archive') ? 'checked' : null)));
        $output .= html_writer::tag('span', get_string('viewtype_archive',  'local_course'));
        $output .= html_writer::end_tag('label');
        $output .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'btn_hiddensearch', 'value' => 'hidden', 'class' => 'accesshide'));
        $output .= html_writer::end_tag('fieldset');
        $output .= html_writer::end_tag('form');

        return $output;
    }
    public function coursecat_ajax()
    {
        global $DB, $CFG;

        $type = required_param('type', PARAM_INT);
        $viewtype = optional_param('viewtype', 'active', PARAM_RAW); //innoverz: add $viewtype param

        if ($type === self::COURSECAT_TYPE_CATEGORY) {
            // This is a request for a category list of some kind.
            $categoryid = required_param('categoryid', PARAM_INT);
            $showcourses = required_param('showcourses', PARAM_INT);
            $depth = required_param('depth', PARAM_INT);

            $category = core_course_category::get($categoryid);

            $chelper = new coursecat_helper();
            $baseurl = new moodle_url('/local/course/index.php', array('categoryid' => $categoryid));
            $coursedisplayoptions = array(
                'limit' => $CFG->coursesperpage,
                'viewmoreurl' => new moodle_url($baseurl, array('browse' => 'courses', 'page' => 1))
            );
            $catdisplayoptions = array(
                'limit' => $CFG->coursesperpage,
                'viewmoreurl' => new moodle_url($baseurl, array('browse' => 'categories', 'page' => 1))
            );
            $chelper->set_show_courses($showcourses)->set_courses_display_options($coursedisplayoptions)->set_categories_display_options($catdisplayoptions);

            return $this->coursecat_category_content($chelper, $category, $depth, $viewtype); //innoverz: add $viewtype param
        } else if ($type === self::COURSECAT_TYPE_COURSE) {
            // This is a request for the course information.
            $courseid = required_param('courseid', PARAM_INT);

            $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

            $chelper = new coursecat_helper();
            $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED);
            return $this->coursecat_coursebox_content($chelper, $course, $viewtype); //innoverz: add $viewtype param
        } else {
            throw new coding_exception('Invalid request type');
        }
    }

    protected function coursecat_category_content(coursecat_helper $chelper, $coursecat, $depth, $viewtype = 'active')
    {
        global $CFG;
        require_once $CFG->dirroot . '/innoverz/course/lib.php'; //innoverz

        $content = '';
        // Subcategories
        $content .= $this->coursecat_subcategories($chelper, $coursecat, $depth, $viewtype); //innoverz

        // AUTO show courses: Courses will be shown expanded if this is not nested category,
        // and number of courses no bigger than $CFG->courseswithsummarieslimit.
        $showcoursesauto = $chelper->get_show_courses() == self::COURSECAT_SHOW_COURSES_AUTO;
        if ($showcoursesauto && $depth) {
            // this is definitely collapsed mode
            $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_COLLAPSED);
        }

        // Courses
        if ($chelper->get_show_courses() > core_course_renderer::COURSECAT_SHOW_COURSES_COUNT) {
            $courses = array();
            if (!$chelper->get_courses_display_option('nodisplay')) {
                $courses = $coursecat->get_courses($chelper->get_courses_display_options());
                $courses = filter_course_type($courses, COURSE_TYPE_NORMAL);    //innoverz: handle which type to show
                $courses = filter_course_status($courses, ($viewtype == 'archive' ? COURSE_STATUS_ARCHIVE : COURSE_STATUS_ACTIVE));  //innoverz: handle which status to show
            }
            if ($viewmoreurl = $chelper->get_courses_display_option('viewmoreurl')) {
                // the option for 'View more' link was specified, display more link (if it is link to category view page, add category id)
                if ($viewmoreurl->compare(new moodle_url('/local/course/index.php'), URL_MATCH_BASE)) {
                    $chelper->set_courses_display_option('viewmoreurl', new moodle_url($viewmoreurl, array('categoryid' => $coursecat->id)));
                }
            }
            $content .= $this->coursecat_courses($chelper, $courses, count($courses));
        }

        if ($showcoursesauto) {
            // restore the show_courses back to AUTO
            $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_AUTO);
        }

        return $content;
    }


    protected function coursecat_category(coursecat_helper $chelper, $coursecat, $depth, $viewtype = 'active')
    {
        // open category tag
        $classes = array('category');
        if (empty($coursecat->visible)) {
            $classes[] = 'dimmed_category';
        }
        if ($chelper->get_subcat_depth() > 0 && $depth >= $chelper->get_subcat_depth()) {
            // do not load content
            $categorycontent = '';
            $classes[] = 'notloaded';
            /**
             * innoverz start: search courses condition about archive or active
             */
            if ($coursecat->id) {
                $where = $viewtype == 'archive' ? ' AND c.isarchive = 1' : ' AND c.isarchive = 0 AND enddate >= ' . time();
                /**
                 * innoverz end
                 */
                if (
                    $coursecat->get_children_count() ||
                    ($chelper->get_show_courses() >= self::COURSECAT_SHOW_COURSES_COLLAPSED && $coursecat->get_courses_count())
                ) {
                    $classes[] = 'with_children';
                    $classes[] = 'collapsed';
                }
                /**
                 * innoverz start
                 */
            }
            /**
             * innoverz end
             */
        } else {
            // load category content
            $categorycontent = $this->coursecat_category_content($chelper, $coursecat, $depth);
            $classes[] = 'loaded';
            if (!empty($categorycontent)) {
                $classes[] = 'with_children';
                // Category content loaded with children.
                $this->categoryexpandedonload = true;
            }
        }

        // Make sure JS file to expand category content is included.
        $this->coursecat_include_js();

        /**
         * innoverz start
         */
        if ($depth == 1) {
            $classes[] = 'courselist-supercat';
        }
        /**
         * innoverz end
         */

        $content = html_writer::start_tag('div', array(
            'class' => join(' ', $classes),
            'data-categoryid' => $coursecat->id,
            'data-depth' => $depth,
            'data-showcourses' => $chelper->get_show_courses(),
            'data-type' => self::COURSECAT_TYPE_CATEGORY,
        ));

        // category name
        $categoryname = $coursecat->get_formatted_name();
        /**
         * innoverz start: category link is needed only if there is no children
         */
        //if (!in_array('with_children', $classes)) {
        /**
         * innoverz end
         */
        $categoryname = html_writer::link(
            new moodle_url(
                '/local/course/index.php',
                array('categoryid' => $coursecat->id, 'viewtype' => $viewtype) // innoverz: add viewtype param
            ),
            $categoryname
        );
        /**
         * innoverz start
         */
        //}
        /**
         * innoverz end
         */
        if (
            $chelper->get_show_courses() == self::COURSECAT_SHOW_COURSES_COUNT
            && ($coursescount = $coursecat->get_courses_count())
        ) {
            $categoryname .= html_writer::tag(
                'span',
                ' (' . $coursescount . ')',
                array('title' => get_string('numberofcourses'), 'class' => 'numberofcourse')
            );
        }
        $content .= html_writer::start_tag('div', array('class' => 'info'));

        $content .= html_writer::tag(($depth > 1) ? 'h4' : 'h3', $categoryname, array('class' => 'categoryname aabtn'));
        $content .= html_writer::end_tag('div'); // .info

        // add category content to the output
        $content .= html_writer::tag('div', $categorycontent, array('class' => 'content'));

        $content .= html_writer::end_tag('div'); // .category

        // Return the course category tree HTML
        return $content;
    }


    protected function coursecat_subcategories(coursecat_helper $chelper, $coursecat, $depth, $viewtype = 'active')
    {
        global $CFG;
        $subcategories = array();
        if (!$chelper->get_categories_display_option('nodisplay')) {
            $subcategories = $coursecat->get_children($chelper->get_categories_display_options());
        }
        $totalcount = $coursecat->get_children_count();
        if (!$totalcount) {
            // Note that we call core_course_category::get_children_count() AFTER core_course_category::get_children()
            // to avoid extra DB requests.
            // Categories count is cached during children categories retrieval.
            return '';
        }

        // prepare content of paging bar or more link if it is needed
        $paginationurl = $chelper->get_categories_display_option('paginationurl');
        $paginationallowall = $chelper->get_categories_display_option('paginationallowall');
        if ($totalcount > count($subcategories)) {
            if ($paginationurl) {
                // the option 'paginationurl was specified, display pagingbar
                $perpage = $chelper->get_categories_display_option('limit', $CFG->coursesperpage);
                $page = $chelper->get_categories_display_option('offset') / $perpage;
                $pagingbar = $this->paging_bar(
                    $totalcount,
                    $page,
                    $perpage,
                    $paginationurl->out(false, array('perpage' => $perpage))
                );
                if ($paginationallowall) {
                    $pagingbar .= html_writer::tag('div', html_writer::link(
                        $paginationurl->out(false, array('perpage' => 'all')),
                        get_string('showall', '', $totalcount)
                    ), array('class' => 'paging paging-showall'));
                }
            } else if ($viewmoreurl = $chelper->get_categories_display_option('viewmoreurl')) {
                // the option 'viewmoreurl' was specified, display more link (if it is link to category view page, add category id)
                if ($viewmoreurl->compare(new moodle_url('/local/course/index.php'), URL_MATCH_BASE)) {
                    $viewmoreurl->param('categoryid', $coursecat->id);
                }
                $viewmoretext = $chelper->get_categories_display_option('viewmoretext', new lang_string('viewmore'));
                $morelink = html_writer::tag(
                    'div',
                    html_writer::link($viewmoreurl, $viewmoretext),
                    array('class' => 'paging paging-morelink')
                );
            }
        } else if (($totalcount > $CFG->coursesperpage) && $paginationurl && $paginationallowall) {
            // there are more than one page of results and we are in 'view all' mode, suggest to go back to paginated view mode
            $pagingbar = html_writer::tag('div', html_writer::link(
                $paginationurl->out(false, array('perpage' => $CFG->coursesperpage)),
                get_string('showperpage', '', $CFG->coursesperpage)
            ), array('class' => 'paging paging-showperpage'));
        }

        // display list of subcategories
        $content = html_writer::start_tag('div', array('class' => 'subcategories'));

        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }

        foreach ($subcategories as $subcategory) {
            /**
             * innoverz start: template category will be skipped
             */
            if ($subcategory->id == 1 && get_class($coursecat) != 'learningplan') {    //template category
                continue;
            }
            /**
             * innoverz end
             */
            $content .= $this->coursecat_category($chelper, $subcategory, $depth + 1, $viewtype); //innoverz
        }

        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }
        if (!empty($morelink)) {
            $content .= $morelink;
        }

        $content .= html_writer::end_tag('div');
        return $content;
    }

    protected function coursecat_include_js()
    {
        if (!$this->page->requires->should_create_one_time_item_now('moodle_local_course_categoryexpanderjsinit')) {
            return;
        }

        // We must only load this module once.
        $this->page->requires->yui_module(
            'moodle-local_course-categoryexpander',
            'Y.Moodle.local_course.categoryexpander.init'
        ); //innoverz
    }

    public function course_category($category)
    {
        global $CFG;
        $usertop = core_course_category::user_top();
        if (empty($category)) {
            $coursecat = $usertop;
        } else if (is_object($category) && $category instanceof core_course_category) {
            $coursecat = $category;
        } else {
            $coursecat = core_course_category::get(is_object($category) ? $category->id : $category);
        }
        $site = get_site();
        $output = '';

        if ($coursecat->can_create_course() || $coursecat->has_manage_capability()) {
            // Add 'Manage' button if user has permissions to edit this category.
            $managebutton = $this->single_button(new moodle_url(
                '/course/management.php',
                array('categoryid' => $coursecat->id)
            ), get_string('managecourses'), 'get');
            $this->page->set_button($managebutton);
        }

        if (core_course_category::is_simple_site()) {
            // There is only one category in the system, do not display link to it.
            $strfulllistofcourses = get_string('fulllistofcourses');
            $this->page->set_title("$site->shortname: $strfulllistofcourses");
        } else if (!$coursecat->id || !$coursecat->is_uservisible()) {
            $strcategories = get_string('categories');
            $this->page->set_title("$site->shortname: $strcategories");
        } else {
            $strfulllistofcourses = get_string('fulllistofcourses');
            $this->page->set_title("$site->shortname: $strfulllistofcourses");

            // Print the category selector
            $categorieslist = core_course_category::make_categories_list();
            if (count($categorieslist) > 1) {
                $output .= html_writer::start_tag('div', array('class' => 'categorypicker'));
                $select = new single_select(
                    new moodle_url('/local/course/index.php'),
                    'categoryid',
                    core_course_category::make_categories_list(),
                    $coursecat->id,
                    null,
                    'switchcategory'
                );
                $select->set_label(get_string('categories') . ':');
                $output .= $this->render($select);
                $output .= html_writer::end_tag('div'); // .categorypicker
            }
        }

        // Print current category description
        $chelper = new coursecat_helper();
        if ($description = $chelper->get_category_formatted_description($coursecat)) {
            $output .= $this->box($description, array('class' => 'generalbox info'));
        }

        // Prepare parameters for courses and categories lists in the tree
        $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_AUTO)
            ->set_attributes(array('class' => 'category-browse category-browse-' . $coursecat->id));

        $coursedisplayoptions = array();
        $catdisplayoptions = array();
        $browse = optional_param('browse', null, PARAM_ALPHA);
        $perpage = optional_param('perpage', $CFG->coursesperpage, PARAM_INT);
        $page = optional_param('page', 0, PARAM_INT);
        $baseurl = new moodle_url('/local/course/index.php');
        if ($coursecat->id) {
            $baseurl->param('categoryid', $coursecat->id);
        }
        if ($perpage != $CFG->coursesperpage) {
            $baseurl->param('perpage', $perpage);
        }
        $coursedisplayoptions['limit'] = $perpage;
        $catdisplayoptions['limit'] = $perpage;
        if ($browse === 'courses' || !$coursecat->get_children_count()) {
            $coursedisplayoptions['offset'] = $page * $perpage;
            $coursedisplayoptions['paginationurl'] = new moodle_url($baseurl, array('browse' => 'courses'));
            $catdisplayoptions['nodisplay'] = true;
            $catdisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'categories'));
            $catdisplayoptions['viewmoretext'] = new lang_string('viewallsubcategories');
        } else if ($browse === 'categories' || !$coursecat->get_courses_count()) {
            $coursedisplayoptions['nodisplay'] = true;
            $catdisplayoptions['offset'] = $page * $perpage;
            $catdisplayoptions['paginationurl'] = new moodle_url($baseurl, array('browse' => 'categories'));
            $coursedisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'courses'));
            $coursedisplayoptions['viewmoretext'] = new lang_string('viewallcourses');
        } else {
            // we have a category that has both subcategories and courses, display pagination separately
            $coursedisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'courses', 'page' => 1));
            $catdisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'categories', 'page' => 1));
        }
        $chelper->set_courses_display_options($coursedisplayoptions)->set_categories_display_options($catdisplayoptions);
        // Add course search form.
        $output .= $this->course_search_form();

        // Display course category tree.
        $output .= $this->coursecat_tree($chelper, $coursecat);

        // Add action buttons
        $output .= $this->container_start('buttons');
        if ($coursecat->is_uservisible()) {
            $context = get_category_or_system_context($coursecat->id);
            if (has_capability('moodle/course:create', $context)) {
                // Print link to create a new course, for the 1st available category.
                if ($coursecat->id) {
                    $url = new moodle_url('/local/course/edit.php', array('category' => $coursecat->id, 'returnto' => 'category')); //innoverz
                } else {
                    $url = new moodle_url(
                        '/local/course/edit.php', //innoverz
                        array('category' => $CFG->defaultrequestcategory, 'returnto' => 'topcat')
                    );
                }
                $output .= $this->single_button($url, get_string('addnewcourse'), 'get');
            }
            ob_start();
            print_course_request_buttons($context);
            $output .= ob_get_contents();
            ob_end_clean();
        }
        $output .= $this->container_end();

        return $output;
    }


    protected function coursecat_courses(coursecat_helper $chelper, $courses, $totalcount = null)
    {
        global $CFG;
        if ($totalcount === null) {
            $totalcount = count($courses);
        }
        /**
         * innoverz: for cat page, even without items, the viewtype option should show
         */
        /*  if (!$totalcount) {
            // Courses count is cached during courses retrieval.
            return '';
        } */

        if ($chelper->get_show_courses() == self::COURSECAT_SHOW_COURSES_AUTO) {
            // In 'auto' course display mode we analyse if number of courses is more or less than $CFG->courseswithsummarieslimit
            if ($totalcount <= $CFG->courseswithsummarieslimit) {
                $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED);
            } else {
                $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_COLLAPSED);
            }
        }

        // prepare content of paging bar if it is needed
        $paginationurl = $chelper->get_courses_display_option('paginationurl');
        $paginationallowall = $chelper->get_courses_display_option('paginationallowall');
        if ($totalcount > count($courses)) {
            // there are more results that can fit on one page
            if ($paginationurl) {
                // the option paginationurl was specified, display pagingbar
                $perpage = $chelper->get_courses_display_option('limit', $CFG->coursesperpage);
                $page = $chelper->get_courses_display_option('offset') / $perpage;
                $pagingbar = $this->paging_bar(
                    $totalcount,
                    $page,
                    $perpage,
                    $paginationurl->out(false, array('perpage' => $perpage))
                );
                if ($paginationallowall) {
                    $pagingbar .= html_writer::tag('div', html_writer::link(
                        $paginationurl->out(false, array('perpage' => 'all')),
                        get_string('showall', '', $totalcount)
                    ), array('class' => 'paging paging-showall'));
                }
            } else if ($viewmoreurl = $chelper->get_courses_display_option('viewmoreurl')) {
                // the option for 'View more' link was specified, display more link
                $viewmoretext = $chelper->get_courses_display_option('viewmoretext', new lang_string('viewmore'));
                $morelink = html_writer::tag(
                    'div',
                    html_writer::link($viewmoreurl, $viewmoretext),
                    array('class' => 'paging paging-morelink')
                );
            }
        } else if (($totalcount > $CFG->coursesperpage) && $paginationurl && $paginationallowall) {
            // there are more than one page of results and we are in 'view all' mode, suggest to go back to paginated view mode
            $pagingbar = html_writer::tag('div', html_writer::link(
                $paginationurl->out(false, array('perpage' => $CFG->coursesperpage)),
                get_string('showperpage', '', $CFG->coursesperpage)
            ), array('class' => 'paging paging-showperpage'));
        }

        // display list of courses
        $attributes = $chelper->get_and_erase_attributes('courses');
        $content = html_writer::start_tag('div', $attributes);

        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }

        $coursecount = 0;
        foreach ($courses as $course) {
            $coursecount++;
            $classes = ($coursecount % 2) ? 'odd' : 'even';
            if ($coursecount == 1) {
                $classes .= ' first';
            }
            if ($coursecount >= count($courses)) {
                $classes .= ' last';
            }
            $content .= $this->coursecat_coursebox($chelper, $course, $classes);
        }

        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }
        if (!empty($morelink)) {
            $content .= $morelink;
        }

        $content .= html_writer::end_tag('div'); // .courses
        return $content;
    }

    /**
     * referred from theme\innoverz\classes\util\extras.php
     * Returns the first course's summary issue
     *
     * @param \core_course_list_element $course
     * @param string $courselink
     *
     * @return string
     */
    public function get_course_summary_image($course)
    {
        global $CFG;

        $contentimage = '';
        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $url = moodle_url::make_file_url(
                "$CFG->wwwroot/pluginfile.php",
                '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                    $file->get_filearea() . $file->get_filepath() . $file->get_filename(),
                !$isimage
            );
            if ($isimage) {
                $contentimage = html_writer::empty_tag('img', array(
                    'src' => $url,
                    'alt' => $course->fullname,
                    'class' => 'course_img'
                ));
                break;
            }
        }

        if (empty($contentimage)) {
            $url = $CFG->wwwroot . "/theme/moove/pix/default_course.jpg";

            $contentimage = html_writer::empty_tag('img', array(
                'src' => $url,
                'alt' => $course->fullname,
                'class' => 'course_img'
            ));
        }

        return $contentimage;
    }

    protected function course_name(coursecat_helper $chelper, core_course_list_element $course): string
    {
        $content = '';
        if ($chelper->get_show_courses() >= self::COURSECAT_SHOW_COURSES_EXPANDED) {
            $nametag = 'h3';
        } else {
            $nametag = 'div';
        }
        $courseimg = $this->get_course_summary_image($course); //innoverz: added course image on course subcat list
        $coursename = $chelper->get_course_formatted_name($course);
        $coursenamelink = html_writer::link(
            new moodle_url('/course/view.php', ['id' => $course->id]),
            $courseimg . $coursename,
            ['class' => $course->visible ? 'aalink' : 'aalink dimmed']
        );
        $content .= html_writer::tag($nametag, $coursenamelink, ['class' => 'coursename']);
        // If we display course in collapsed form but the course has summary or course contacts, display the link to the info page.
        $content .= html_writer::start_tag('div', ['class' => 'moreinfo']);
        if ($chelper->get_show_courses() < self::COURSECAT_SHOW_COURSES_EXPANDED) {
            if (
                $course->has_summary() || $course->has_course_contacts() || $course->has_course_overviewfiles()
                || $course->has_custom_fields()
            ) {
                $url = new moodle_url('/course/info.php', ['id' => $course->id]);
                $image = $this->output->pix_icon('i/info', $this->strings->summary);
                $content .= html_writer::link($url, $image, ['title' => $this->strings->summary]);
                // Make sure JS file to expand course content is included.
                $this->coursecat_include_js();
            }
        }
        $content .= html_writer::end_tag('div');
        return $content;
    }
}
