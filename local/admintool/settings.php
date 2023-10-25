<?php

// Ensure the configurations for this site are set
if ($hassiteconfig) {
    // Add a setting field to the settings for this page
    $ADMIN->add(
        'accounts',
        new admin_externalpage(
            'tooluploaduserinnoverz',
            get_string('uploaduser', 'local_admintool'),
            "$CFG->wwwroot/local/admintool/uploaduser/index.php",
            'moodle/site:uploadusers'
        )
    );
}
