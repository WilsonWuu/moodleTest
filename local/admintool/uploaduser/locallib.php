<?php
require_once("$CFG->dirroot/local/admintool/lib.php");
require_once("$CFG->dirroot/$CFG->admin/tool/uploaduser/locallib.php");

class uu_progress_tracker_innoverz extends uu_progress_tracker
{
    /**
     * The columns shown on the table.
     * Felix Innoverz 2.7: do not show column 'lastname' and 'firstname', 
     * as well as 'theme' that appears in 3.9
     * @var array
     */
    public $columns = array('status', 'line', 'profile_field_chiname', 'profile_field_engname', 'profile_field_joindate', 'email', 'username');

    /**
     * Print table header.
     * Felix Innoverz 2.7: do not show column 'lastname' and 'firstname', 
     * as well as 'theme' that appears in 3.9
     * @return void
     */
    public function start()
    {
        $ci = 0;
        echo '<table id="uuresults" class="generaltable boxaligncenter flexible-wrap" summary="' . get_string('uploadusersresult', 'tool_uploaduser') . '">';
        echo '<tr class="heading r0">';
        echo '<th class="header c' . $ci++ . '" scope="col">' . get_string('status') . '</th>';
        echo '<th class="header c' . $ci++ . '" scope="col">' . get_string('uucsvline', 'tool_uploaduser') . '</th>';
        echo '<th class="header c' . $ci++ . '" scope="col">' . get_string('profile_field_chiname', 'local_admintool') . '</th>';
        echo '<th class="header c' . $ci++ . '" scope="col">' . get_string('profile_field_engname', 'local_admintool') . '</th>';
        echo '<th class="header c' . $ci++ . '" scope="col">' . get_string('profile_field_joindate', 'local_admintool') . '</th>';
        echo '<th class="header c' . $ci++ . '" scope="col">' . get_string('email') . '</th>';
        echo '<th class="header c' . $ci++ . '" scope="col">' . get_string('username') . '</th>';
        echo '</tr>';
        $this->_row = null;
    }

    /**
     * Innoverz: stop displaying debugging message for not showing optional default columns or rows
     */
    public function track($col, $msg, $level = 'normal', $merge = true)
    {
        if (empty($this->_row)) {
            $this->flush(); //init arrays
        }
        if (!in_array($col, $this->columns)) {
            //debugging('Incorrect column:'.$col);
            return;
        }

        if ($merge) {
            if ($this->_row[$col][$level] != '') {
                $this->_row[$col][$level] .= '<br />';
            }
            $this->_row[$col][$level] .= $msg;
        } else {
            $this->_row[$col][$level] = $msg;
        }
    }

    /**
     * 
     * Flush previous line and start a new one.
     * @return void
     * 
     * innoverz: cloned from admin\tool\uploaduser\locallib.php, it has to be here to produce correct result
     */
    public function flush() {
        if (empty($this->_row) or empty($this->_row['line']['normal'])) {
            // Nothing to print - each line has to have at least number
            $this->_row = array();
            foreach ($this->columns as $col) {
                $this->_row[$col] = array('normal'=>'', 'info'=>'', 'warning'=>'', 'error'=>'');
            }
            return;
        }
        $ci = 0;
        $ri = 1;
        echo '<tr class="r'.$ri.'">';
        foreach ($this->_row as $key=>$field) {
            foreach ($field as $type=>$content) {
                if ($field[$type] !== '') {
                    $field[$type] = '<span class="uu'.$type.'">'.$field[$type].'</span>';
                } else {
                    unset($field[$type]);
                }
            }
            echo '<td class="cell c'.$ci++.'">';
            if (!empty($field)) {
                echo implode('<br />', $field);
            } else {
                echo '&nbsp;';
            }
            echo '</td>';
        }
        echo '</tr>';
        foreach ($this->columns as $col) {
            $this->_row[$col] = array('normal'=>'', 'info'=>'', 'warning'=>'', 'error'=>'');
        }
    }
}


/**
 * Returns mapping of all roles using short role name as index.
 * Innoverz: in case rolecache is not set, set rolecache with rname ='glswd' and its role id
 * 
 * @return array
 */
function uu_allowed_roles_cache_innoverz()
{
    $allowedroles = get_assignable_roles(context_course::instance(SITEID), ROLENAME_SHORT);
    foreach ($allowedroles as $rid => $rname) {
        $rolecache[$rid] = new stdClass();
        $rolecache[$rid]->id   = $rid;
        $rolecache[$rid]->name = $rname;
        if (!is_numeric($rname)) { // only non-numeric shortnames are supported!!!
            $rolecache[$rname] = new stdClass();
            $rolecache[$rname]->id   = $rid;
            $rolecache[$rname]->name = $rname;
        }
    }
    if (!isset($rolecache)) {
        global $CFG;
        //TODO
        //require_once($CFG->dirroot."/user/profile_lib.php");
        //$rname = 'glswd';
        //$rid = get_user_role_id($rname);
        $rolecache = array();
        $rolecache[$rid] = new stdClass();
        $rolecache[$rid]->id   = $rid;
        $rolecache[$rid]->name = $rname;
        $rolecache[$rname] = new stdClass();
        $rolecache[$rname]->id   = $rid;
        $rolecache[$rname]->name = $rname;
    }
    return $rolecache;
}


/**
 * Returns mapping of all system roles using short role name as index.
 * Innoverz: in case rolecache is not set, set rolecache with rname ='glswd' and its role id
 * 
 * @return array
 */
function uu_allowed_sysroles_cache_innoverz()
{
    $allowedroles = get_assignable_roles(context_system::instance(), ROLENAME_SHORT);
    $rolecache = [];
    foreach ($allowedroles as $rid => $rname) {
        $rolecache[$rid] = new stdClass();
        $rolecache[$rid]->id   = $rid;
        $rolecache[$rid]->name = $rname;
        if (!is_numeric($rname)) { // Only non-numeric shortnames are supported!
            $rolecache[$rname] = new stdClass();
            $rolecache[$rname]->id   = $rid;
            $rolecache[$rname]->name = $rname;
        }
    }
    if (!isset($rolecache)) {
        global $CFG;
        //TODO
        //require_once($CFG->dirroot."/user/profile_lib.php");
        //$rname = 'glswd';
        //$rid = get_user_role_id($rname);
        $rolecache = array();
        $rolecache[$rid] = new stdClass();
        $rolecache[$rid]->id   = $rid;
        $rolecache[$rid]->name = $rname;
        $rolecache[$rname] = new stdClass();
        $rolecache[$rname]->id   = $rid;
        $rolecache[$rname]->name = $rname;
    }
    return $rolecache;
}


/**
 * Checks if data provided for custom fields is correct
 * Currently checking for custom profile field or type menu
 * Innoverz: removed duplicate check
 *
 * @param array $data user profile data
 * @return bool true if no error else false
 */
function uu_check_custom_profile_data_innoverz(&$data)
{
    global $CFG, $DB;
    $noerror = true;

    // find custom profile fields and check if data needs to converted.
    foreach ($data as $key => $value) {
        if (preg_match('/^profile_field_/', $key)) {
            $shortname = str_replace('profile_field_', '', $key);
            if ($fields = $DB->get_records('user_info_field', array('shortname' => $shortname))) {
                foreach ($fields as $field) {
                    require_once($CFG->dirroot . '/user/profile/field/' . $field->datatype . '/field.class.php');
                    $newfield = 'profile_field_' . $field->datatype;
                    $formfield = new $newfield($field->id, 0);
                    if (
                        method_exists($formfield, 'convert_external_data') &&
                        is_null($formfield->convert_external_data($value))
                    ) {
                        $data['status'][] = get_string('invaliduserfield', 'error', $shortname);
                        $noerror = false;
                    }
                }
            }
        }
    }
    return $noerror;
}

function getUserSQL($where = array(), $to_select_fields = '*', $is_deleted = 0)
{
    /* $sql = " select DISTINCT t1.* from  (SELECT u.id,$to_select_fields
    FROM {user} u
    LEFT JOIN {user_info_data} d ON d.userid = u.id
    LEFT JOIN {user_info_field} f ON f.id = d.fieldid
    WHERE u.id =1 or u.id=2  or u.id=3  or u.id=4) t1
    INNER JOIN
    (SELECT u.id, $to_select_fields
    FROM {user} u
    LEFT JOIN {user_info_data} d ON d.userid = u.id
    LEFT JOIN {user_info_field} f ON f.id = d.fieldid
    WHERE u.id =3 or u.id=2) t2 on t1.id=t2.id";

    return $sql; */

    //fetch fields value into arrays
    $profile_fields_values = $user_fields_values = array();
    foreach ($where as $key => $value) {
        if (preg_match('/^profile_field_/', $key)) {
            $shortname = str_replace('profile_field_', '', $key);
            if ($shortname == 'joindate') $value = datetotime($value);
            $profile_fields_values[$shortname] = $value;
        } else {
            $user_fields_values[$key] = $value;
        }
    }
    $has_profile_fields = !empty($profile_fields_values);


    $sql = "SELECT " . ($has_profile_fields ? '' : ' DISTINCT ') .
        " u.id, $to_select_fields FROM {user} u
        LEFT JOIN {user_info_data} d ON d.userid = u.id
        LEFT JOIN {user_info_field} f ON f.id = d.fieldid
        WHERE u.deleted = $is_deleted ";

    //extend where clause for standard fields in user table
    $para_num = -1;
    foreach ($user_fields_values as $key => $value) {
        if ($para_num == -1) {
            $sql .= ' AND ( ';
            $para_num = 0;
        } elseif ($para_num > 0) {
            $sql .= ' AND ';
        }
        $sql .= " (u.$key ='$value') ";

        $para_num++;
    }
    if ($para_num != -1) $sql .= ' ) ';

    $main_sql = '';
    //pack in the sql innerjoin query (number of innerjoin depends on number of profile_field_ values)
    $i = 0;
    foreach ($profile_fields_values as $key => $value) {
        if ($main_sql == '')
            $main_sql .= "SELECT DISTINCT t0.* FROM ($sql AND ";
        else
            $main_sql .= " INNER JOIN ( $sql AND ";

        $main_sql .= " (f.shortname = '$key' and d.data ='$value') ) t$i";
        if ($i > 0)
            $main_sql .= " on t0.id=t$i.id ";
        $i++;
    }

    if (!$has_profile_fields)
        return $sql;

    return $main_sql;
}

/**
 * Innoverz: Check if user exists with same default fields in user table and profile fields
 */
function getExistingUsers($userdata, $is_deleted = 0)
{
    global $DB;

    if (count((array)$userdata) == 0) return false;

    $sql = getUserSQL($userdata, 'u.username', $is_deleted);

    return $DB->get_records_sql($sql);
}

/**
 * By default, fields profile_field_engname, profile_field_chiname and profile_field_joindate are checked for duplicate user check
 */
function filterUserData($user_data, $to_validate_fields = array('profile_field_engname', 'profile_field_chiname', 'profile_field_joindate'))
{
    return array_intersect_key((array)$user_data, array_flip($to_validate_fields));
}

/**
 * Following functions are copied from admin\tool\uploaduser\index_lib.php in 2.7
 * used in local/admintool/uploaduser/index.php only
 */

//upon submitting form data, validate fields of each user (1 user for each)
//this functions were refactored to adapt to profile_field_XXX types
function setAllValidDBFields(&$user, $optype, $notvaliduser)
{
    global $DB;

    if (!isset($user->username) or $user->username === '') {

        //it is not necessary to validate email here upon submission page
        // because duplicate email address was checked upon preview page
        $existing_user_count = count(getExistingUsers(filterUserData($user)));

        if ($existing_user_count > 0) {
            //problem user is processed in adding new user only, 
            //here we select db field so no need to specify "profile_field_"
            $sql = getUserSQL(filterUserData($user), 'username');

            if ($existing_user_count > 1) {
                if ($optype == UU_USER_ADDNEW) {
                    $result = $DB->get_records_sql($sql);
                    $user->username = current($result)->username;
                    $user->username = getFinalUsernameForNewUser($user);
                } else {
                    //problem user is ignored in other action
                    return false;
                }
            } else {
                //existing_user_count == 1
                //here we select db field so no need to specify "profile_field_"
                $result = $DB->get_record_sql($sql);

                $user->username = $result->username;
                if ($optype == UU_USER_ADDNEW && $notvaliduser) {
                    $user->username = getFinalUsernameForNewUser($user);
                }
            }
        } else {
            $user->username = getValidUsername($user);
            $user->username = getFinalUsernameForNewUser($user);
        }
    }
    /* if (!isset($user->firstname) or $user->firstname === '') {
        $firstname = explode(' ', $user->engname);
        unset($firstname[0]);
        $user->firstname = implode(' ', $firstname);
    }
    if (!isset($user->lastname) or $user->lastname === '') {
        $lastname = explode(' ', $user->engname);
        $user->lastname = $lastname[0];
    } */
    $user->profile_field_joindate = datetotime($user->profile_field_joindate);
    /*$user->department = $user->grade;
    unset($user->engname);
    //unset($user->chiname);
    unset($user->grade); */
    return true;
}

//check username exist in DB, then get a new one for new user
function getFinalUsernameForNewUser($user)
{
    $extendchar = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0, 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
    $extendindex = 0;
    $username = $user->username;
    $newusername = $user->username;
    while (true) {
        $count = count(getExistingUsers(array('username' => $newusername)));
        if ($count) {
            $newusername = $username . $extendchar[$extendindex++];
        } else {
            break;
        }
    }
    return $newusername;
}

/***
 * generate a new username for new user, 
 * format is the first letter of words of full name + joindate
 ***/
function getValidUsername($user)
{
    $delimiters = array(' ', '-', ', ');
    $ready = str_replace($delimiters, $delimiters[0], $user->profile_field_engname);
    $launch = explode(' ', $ready);
    $username = '';
    foreach ($launch as $unit) {
        $username .= strtolower(substr($unit, 0, 1));
    }
    $username = $username . str_replace('.', '', $user->profile_field_joindate);
    return $username;
}

//find SWD Users that are not found in the staff list
function getInvalidSWDUserlist($users, $previewrows)
{
    global $returnurl, $DB, $CFG;
    //TODO
    /* require_once($CFG->dirroot.'/user/profile_lib.php');
	$roleid = get_user_role_id('glngo'); */
    $error = '';
    $values = array();
    $sql = "
		SELECT * FROM {user} 
		WHERE deleted = 0
		AND id NOT IN (1, 2) 
		AND id NOT IN (
			SELECT DISTINCT userid FROM {role_assignments}
			WHERE roleid = $roleid
		)
		AND id NOT IN (
			SELECT id FROM {user}
			WHERE (fullname = ? AND joindate = ?)			
	";
    $count = count($users);
    $count = $count < $previewrows ? $count : $previewrows;
    $i = 0;
    foreach ($users as $user) {
        if (!validateDateFormat($user['joindate'])) {
            $data = new stdClass();
            $data->line = $user['line'];
            $data->joindate = $user['joindate'];
            $error .=  get_string('csvinvaliddateformatline', '', $data);
            continue;
        }
        if ($i > 0) {
            $sql .= " OR (fullname = ? AND joindate = ?)";
        }
        $values[] = $user['engname'];
        $values[] = datetotime($user['joindate']);
        $i++;
    }
    $sql .= ') LIMIT 200';
    if ($error) {
        print_error('csvloaderror', '', $returnurl, get_string('csvinvaliddateformat') . $error);
    }

    return $DB->get_records_sql($sql, $values);
}

function setUserColumns($filecolumns, $fields)
{
    $rowcols = array();
    foreach ($fields as $key => $field) {
        $rowcols[$filecolumns[$key]] = s(trim($field));
    }
    return $rowcols;
}

function printIgnoredUserList($filecolumns, $data)
{
    global $OUTPUT;

    //set checkbox for each row
    foreach ($data as $key => $value) {
        if (isset($value['rejectupload'])) {
            array_unshift($data[$key], "");
            unset($data[$key]['rejectupload']);
        } else {
            array_unshift($data[$key], "<input type='checkbox' name='ignorekeys' value='{$value['line']}' />");
        }
    }
    $table = new html_table();
    $table->id = "wupreview";
    $table->attributes['class'] = 'generaltable lefttext';
    $table->tablealign = 'center';
    $table->summary = get_string('problemuserpreview', 'local_admintool');
    $table->head = array();
    $table->data = $data;

    $table->head[] = "<input type='checkbox' id='ignorekeyall' value='all' />";
    $table->head[] = get_string('uucsvline', 'tool_uploaduser');
    foreach ($filecolumns as $column) {
        $table->head[] = $column;
    }
    $table->head[] = get_string('status');

    echo $OUTPUT->heading(get_string('problemuserpreview', 'local_admintool'), 3);
    echo html_writer::tag('div', get_string('ignoreusermessage', 'local_admintool'), array('class' => 'warning-block'));
    echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
}

function printInvalidUserList($users)
{
    global $OUTPUT, $DB, $CFG, $USER, $returnurl;

    $sort = optional_param('sort', 'name', PARAM_ALPHANUM);
    $sitecontext = context_system::instance();
    $site = get_site();

    if (empty($CFG->loginhttps)) {
        $securewwwroot = $CFG->wwwroot;
    } else {
        $securewwwroot = str_replace('http:', 'https:', $CFG->wwwroot);
    }

    $stredit   = get_string('edit');
    $strdelete = get_string('delete');
    $strdeletecheck = get_string('deletecheck');
    $strshowallusers = get_string('showallusers');
    $strsuspend = get_string('suspenduser', 'admin');
    $strunsuspend = get_string('unsuspenduser', 'admin');
    $strunlock = get_string('unlockaccount', 'admin');
    $strconfirm = get_string('confirm');

    $firstname = get_string("firstname");
    $lastname = get_string("lastname");

    $override = new stdClass();
    $override->firstname = 'firstname';
    $override->lastname = 'lastname';
    $fullnamelanguage = get_string('fullnamedisplay', '', $override);
    if (($CFG->fullnamedisplay == 'firstname lastname') or
        ($CFG->fullnamedisplay == 'firstname') or
        ($CFG->fullnamedisplay == 'language' and $fullnamelanguage == 'firstname lastname')
    ) {
        $fullnamedisplay = "$firstname / $lastname";
        if ($sort == "name") { // If sort has already been set to something else then ignore.
            $sort = "firstname";
        }
    } else { // ($CFG->fullnamedisplay == 'language' and $fullnamelanguage == 'lastname firstname').
        $fullnamedisplay = "$lastname / $firstname";
        if ($sort == "name") { // This should give the desired sorting based on fullnamedisplay.
            $sort = "lastname";
        }
    }

    $countries = get_string_manager()->get_list_of_countries(false);
    if (empty($mnethosts)) {
        $mnethosts = $DB->get_records('mnet_host', null, 'id', 'id,wwwroot,name');
    }

    foreach ($users as $key => $user) {
        if (isset($countries[$user->country])) {
            $users[$key]->country = $countries[$user->country];
        }
    }
    if ($sort == "country") {  // Need to resort by full country name, not code
        foreach ($users as $user) {
            $susers[$user->id] = $user->country;
        }
        asort($susers);
        foreach ($susers as $key => $value) {
            $nusers[] = $users[$key];
        }
        $users = $nusers;
    }

    $table = new html_table();
    $table->head = array();
    $table->colclasses = array();
    $table->head[] = $fullnamedisplay;
    $table->attributes['class'] = 'admintable generaltable lefttext';
    $table->colclasses[] = 'leftalign';
    $table->head[] = get_string('city');
    $table->colclasses[] = 'leftalign';
    $table->head[] = get_string('country');
    $table->colclasses[] = 'leftalign';
    $table->head[] = get_string('lastaccess');
    $table->colclasses[] = 'leftalign';
    $table->head[] = get_string('timecreated');
    $table->colclasses[] = 'leftalign';
    $table->head[] = get_string('edit');
    $table->colclasses[] = 'centeralign';
    $table->head[] = "";
    $table->colclasses[] = 'centeralign';

    $table->id = "users";
    foreach ($users as $user) {
        $buttons = array();
        $lastcolumn = '';

        // delete button
        if (has_capability('moodle/user:delete', $sitecontext)) {
            if (is_mnet_remote_user($user) or $user->id == $USER->id or is_siteadmin($user)) {
                // no deleting of self, mnet accounts or admins allowed
            } else {
                $buttons[] = html_writer::link(new moodle_url($returnurl, array('delete' => $user->id, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'alt' => $strdelete, 'class' => 'iconsmall')), array('title' => $strdelete));
            }
        }

        // suspend button
        if (has_capability('moodle/user:update', $sitecontext)) {
            if (is_mnet_remote_user($user)) {
                // mnet users have special access control, they can not be deleted the standard way or suspended
                $accessctrl = 'allow';
                if ($acl = $DB->get_record('mnet_sso_access_control', array('username' => $user->username, 'mnet_host_id' => $user->mnethostid))) {
                    $accessctrl = $acl->accessctrl;
                }
                $changeaccessto = ($accessctrl == 'deny' ? 'allow' : 'deny');
                $buttons[] = " (<a href=\"?acl={$user->id}&amp;accessctrl=$changeaccessto&amp;sesskey=" . sesskey() . "\">" . get_string($changeaccessto, 'mnet') . " access</a>)";
            } else {
                if ($user->suspended) {
                    $buttons[] = html_writer::link(new moodle_url($returnurl, array('unsuspend' => $user->id, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/show'), 'alt' => $strunsuspend, 'class' => 'iconsmall')), array('title' => $strunsuspend));
                } else {
                    if ($user->id == $USER->id or is_siteadmin($user)) {
                        // no suspending of admins or self!
                    } else {
                        $buttons[] = html_writer::link(new moodle_url($returnurl, array('suspend' => $user->id, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/hide'), 'alt' => $strsuspend, 'class' => 'iconsmall')), array('title' => $strsuspend));
                    }
                }

                if (login_is_lockedout($user)) {
                    $buttons[] = html_writer::link(new moodle_url($returnurl, array('unlock' => $user->id, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/unlock'), 'alt' => $strunlock, 'class' => 'iconsmall')), array('title' => $strunlock));
                }
            }
        }

        // edit button
        /*if (has_capability('moodle/user:update', $sitecontext)) {
                // prevent editing of admins by non-admins
                if (is_siteadmin($USER) or !is_siteadmin($user)) {
                    $buttons[] = html_writer::link(new moodle_url($securewwwroot.'/user/editadvanced.php', array('id'=>$user->id, 'course'=>$site->id)), html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/edit'), 'alt'=>$stredit, 'class'=>'iconsmall')), array('title'=>$stredit));
                }
            }*/

        // the last column - confirm or mnet info
        if (is_mnet_remote_user($user)) {
            // all mnet users are confirmed, let's print just the name of the host there
            if (isset($mnethosts[$user->mnethostid])) {
                $lastcolumn = get_string($accessctrl, 'mnet') . ': ' . $mnethosts[$user->mnethostid]->name;
            } else {
                $lastcolumn = get_string($accessctrl, 'mnet');
            }
        } else if ($user->confirmed == 0) {
            if (has_capability('moodle/user:update', $sitecontext)) {
                $lastcolumn = html_writer::link(new moodle_url($returnurl, array('confirmuser' => $user->id, 'sesskey' => sesskey())), $strconfirm);
            } else {
                $lastcolumn = "<span class=\"dimmed_text\">" . get_string('confirm') . "</span>";
            }
        }
        //added by Felix for NGO user approve
        if ($user->confirmed == 1 && $user->isapproved == 0) {
            if (has_capability('moodle/user:update', $sitecontext)) {
                $lastcolumn = html_writer::link(new moodle_url($returnurl, array('confirmuser' => $user->id, 'sesskey' => sesskey())), get_string('approve'));
            } else {
                $lastcolumn = "<span class=\"dimmed_text\">" . get_string('approve') . "</span>";
            }
        } else if ($user->confirmed == 0 && $user->isapproved == 0) {
            if (has_capability('moodle/user:update', $sitecontext)) {
                $lastcolumn = html_writer::link(new moodle_url($returnurl, array('confirmuser' => $user->id, 'sesskey' => sesskey())), get_string('confirmapprove'));
            } else {
                $lastcolumn = "<span class=\"dimmed_text\">" . get_string('confirmapprove') . "</span>";
            }
        }

        if ($user->lastaccess) {
            $strlastaccess = format_time(time() - $user->lastaccess);
        } else {
            $strlastaccess = get_string('never');
        }
        $fullname = fullname($user, true);

        $row = array();
        $row[] = "<a href=\"{$CFG->wwwroot}/user/view.php?id=$user->id&amp;course=$site->id\">$fullname</a>";
        $row[] = $user->city;
        $row[] = $user->country;
        $row[] = $strlastaccess;
        $row[] = date('Y-m-d', $user->joindate);
        if ($user->suspended) {
            foreach ($row as $k => $v) {
                $row[$k] = html_writer::tag('span', $v, array('class' => 'usersuspended'));
            }
        }
        $row[] = implode(' ', $buttons);
        $row[] = $lastcolumn;
        $table->data[] = $row;
    }

    if (!empty($table)) {
        echo html_writer::start_tag('div', array('class' => 'no-overflow'));
        echo html_writer::table($table);
        echo html_writer::end_tag('div');
        //echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);
    }
}


function createUploadUser($user, $updatepassword = true, $triggerevent = true)
{
    global $CFG, $DB;
    // Set the timecreate field to the current time.
    if (!is_object($user)) {
        $user = (object) $user;
    }
    $sendemail = false;

    if (empty(trim($user->password)) ||  $user->password == 'to be generated') {
        $user->isactivate = 0;
        $user->secret = random_string(15);
        $sendemail = true;
    }

    // Check username.
    if ($user->username !== core_text::strtolower($user->username)) {
        throw new moodle_exception('usernamelowercase');
    } else {
        if ($user->username !== clean_param($user->username, PARAM_USERNAME)) {
            throw new moodle_exception('invalidusername');
        }
    }

    // Save the password in a temp value for later.
    if ($updatepassword && isset($user->password)) {

        // Check password toward the password policy.
        if (!check_password_policy($user->password, $errmsg)) {
            throw new moodle_exception($errmsg);
        }

        $userpassword = $user->password;
        unset($user->password);
    }

    // Make sure calendartype, if set, is valid.
    if (!empty($user->calendartype)) {
        $availablecalendartypes = \core_calendar\type_factory::get_list_of_calendar_types();
        if (empty($availablecalendartypes[$user->calendartype])) {
            $user->calendartype = $CFG->calendartype;
        }
    } else {
        $user->calendartype = $CFG->calendartype;
    }

    if (!isset($user->timecreated)) $user->timecreated = time();
    $user->timemodified = $user->timecreated;

    // Insert the user into the database.
    $newuserid = $DB->insert_record('user', $user);

    // Create USER context for this user.
    $usercontext = context_user::instance($newuserid);

    // Update user password if necessary.
    if (isset($userpassword)) {
        // Get full database user row, in case auth is default.
        $newuser = $DB->get_record('user', array('id' => $newuserid));
        $authplugin = get_auth_plugin($newuser->auth);
        $authplugin->user_update_password($newuser, $userpassword);
    }

    // Trigger event If required.
    if ($triggerevent) {
        \core\event\user_created::create_from_userid($newuserid)->trigger();
    }

    // role assignment
    /*if (isset($user->role1)) {	
		require_once($CFG->libdir.'/accesslib.php');	
		$role = $DB->get_record_select("role", "shortname = LOWER(?)", array($user->role1), $fields='id');
		$roleid = $role->id;
		$contextid = 2;
		role_assign($roleid, $newuserid, $contextid);		
	}*/
    $user->id = $newuserid;
    if ($sendemail) {
        $data = base64_encode(json_encode($user));
        //$link = get_folder_url() . "/index_lib_background.php?data=" . $data;
        $link = new moodle_url('/local/admintool/uploaduser/index_lib_background.php?data=' . $data);
        execute_background_task($link->out());
    }

    return $newuserid;
}

function user_role_exist($userid, $roleid, $contextid = null)
{
    global $DB;
    if (!$contextid) {
        $contextid = context_course::instance(SITEID);
        $contextid = $contextid->id;
    }
    return $DB->record_exists("role_assignments", array("userid" => $userid, "roleid" => $roleid, "contextid" => $contextid));
}
