<?php
//Referred from lib\weblib.php class moodle_url

class moodle_url_innoverz extends moodle_url{
    /**
     * Referred from make_pluginfile_url
     * 
     * Factory method for creation of url pointing to plugin file.
     *
     * Please note this method can be used only from the plugins to
     * create urls of own files, it must not be used outside of plugins!
     *
     * @param int $contextid
     * @param string $component
     * @param string $area
     * @param int $itemid
     * @param string $pathname
     * @param string $filename
     * @param bool $forcedownload
     * @param mixed $includetoken Whether to use a user token when displaying this group image.
     *                True indicates to generate a token for current user, and integer value indicates to generate a token for the
     *                user whose id is the value indicated.
     *                If the group picture is included in an e-mail or some other location where the audience is a specific
     *                user who will not be logged in when viewing, then we use a token to authenticate the user.
     * @return moodle_url
     */
    public static function make_pluginfile_url_stream_videos_in_folder($contextid, $component, $area, $itemid, $pathname, $filename,
                                               $forcedownload = false, $includetoken = false) {
        global $CFG, $USER;

        $path = [];

        if ($includetoken) {
            $urlbase = "$CFG->wwwroot/tokenpluginfile.php";
            $userid = $includetoken === true ? $USER->id : $includetoken;
            $token = get_user_key('core_files', $userid);
            if ($CFG->slasharguments) {
                $path[] = $token;
            }
        } else {
            $urlbase = "{$CFG->wwwroot}/local/folder/view_video.php";
        }
        $path[] = $contextid;
        $path[] = $component;
        $path[] = $area;

        if ($itemid !== null) {
            $path[] = $itemid;
        }

        $path = "/" . implode('/', $path) . "{$pathname}{$filename}";

        $url = self::make_file_url($urlbase, $path, $forcedownload, $includetoken);
        if ($includetoken && empty($CFG->slasharguments)) {
            $url->param('token', $token);
        }
        return $url;
    }


    /**
     * Referred from make_pluginfile_url
     * 
     * Factory method for creation of url pointing to plugin file.
     *
     * Please note this method can be used only from the plugins to
     * create urls of own files, it must not be used outside of plugins!
     *
     * @param int $contextid
     * @param string $component
     * @param string $area
     * @param int $itemid
     * @param string $pathname
     * @param string $filename
     * @param bool $forcedownload
     * @param mixed $includetoken Whether to use a user token when displaying this group image.
     *                True indicates to generate a token for current user, and integer value indicates to generate a token for the
     *                user whose id is the value indicated.
     *                If the group picture is included in an e-mail or some other location where the audience is a specific
     *                user who will not be logged in when viewing, then we use a token to authenticate the user.
     * @return moodle_url
     */
    public static function make_pluginfile_inline_url($contextid, $component, $area, $itemid, $pathname, $filename,
                                               $forcedownload = false, $includetoken = false) {
        global $CFG, $USER;

        $path = [];

        if ($includetoken) {
            $urlbase = "$CFG->wwwroot/tokenpluginfile.php";
            $userid = $includetoken === true ? $USER->id : $includetoken;
            $token = get_user_key('core_files', $userid);
            if ($CFG->slasharguments) {
                $path[] = $token;
            }
        } else {
            $urlbase = "{$CFG->wwwroot}{$CFG->INTERRAI_BASEURL}pluginfile.php";
        }
        $path[] = $contextid;
        $path[] = $component;
        $path[] = $area;

        if ($itemid !== null) {
            $path[] = $itemid;
        }

        $path = "/" . implode('/', $path) . "{$pathname}{$filename}";

        $url = self::make_file_url($urlbase, $path, $forcedownload, $includetoken);
        if ($includetoken && empty($CFG->slasharguments)) {
            $url->param('token', $token);
        }
        return $url;
    }
    /**
     * General moodle file url.
     *
     * @param string $urlbase the script serving the file
     * @param string $path
     * @param bool $forcedownload
     * @return moodle_url
     */
    public static function make_file_url($urlbase, $path, $forcedownload = false) {
        $params = array();
        if ($forcedownload) {
            $params['forcedownload'] = 1;
        }else{
            $params['forcedownload'] = 0;
        }
        $url = new moodle_url($urlbase, $params);
        $url->set_slashargument($path);
        return $url;
    }
}