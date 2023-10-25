<?php
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/lib.php');
 
// The first setting we need is the name of the theme. This should be the last part of the component name, and the same             
// as the directory name for our theme.  
 $THEME->name = 'innoverz';
 $THEME->parents = array('boost', 'moove');

 // This is the function that returns the SCSS source for the main file in our theme. We override the boost version because          
 // we want to allow presets uploaded to our own theme file area to be selected in the preset list.                                  
 $THEME->scss = function($theme) {                                                                                                   
     return theme_innoverz_get_main_scss_content($theme);                                                                               
 };
                
// This setting list the style sheets we want to include in our theme. Because we want to use SCSS instead of CSS - we won't        
// list any style sheets. If we did we would list the name of a file in the /style/ folder for our theme without any css file      
// extensions.                                                                                                   
 $THEME->sheets = [];                                                                                               
 
 // This is a setting that can be used to provide some styling to the content in the TinyMCE text editor. This is no longer the      
 // default text editor and "Atto" does not need this setting so we won't provide anything. If we did it would work the same         
 // as the previous setting - listing a file in the /styles/ folder.                                                                 
 $THEME->editor_sheets = [];                                                                                            
 
 // A dock is a way to take blocks out of the page and put them in a persistent floating area on the side of the page. Boost         
 // does not support a dock so we won't either - but look at bootstrapbase for an example of a theme with a dock.                    
 $THEME->enable_dock = false;       

// This is an old setting used to load specific CSS for some YUI JS. We don't need it in Boost based themes because Boost           
// provides default styling for the YUI modules that we use. It is not recommended to use this setting anymore.                     
$THEME->yuicssmodules = array();        


// Most themes will use this rendererfactory as this is the one that allows the theme to override any other renderer.               
$THEME->rendererfactory = 'theme_overridden_renderer_factory';      

// This is a list of blocks that are required to exist on all pages for this theme to function correctly. For example               
// bootstrap base requires the settings and navigation blocks because otherwise there would be no way to navigate to all the        
// pages in Moodle. Boost does not require these blocks because it provides other ways to navigate built into the theme.            
$THEME->requiredblocks = '';   

// This is a feature that tells the blocks library not to use the "Add a block" block. We don't want this in boost based themes
// because it forces a block region into the page when editing is enabled and it takes up too much room.
$THEME->addblockposition = BLOCK_ADDBLOCK_POSITION_FLATNAV;

// Add a custom icon system to the theme.
$THEME->iconsystem = \core\output\icon_system::FONTAWESOME;

$THEME->layouts = [
    // Most backwards compatible layout without the blocks - this is the layout used by default.
    'base' => array(
        'theme' => 'innoverz',
        'file' => 'columns2.php',
        'regions' => array(),
    ),
    // Standard layout with blocks, this is recommended for most pages with general information.
    'standard' => array(
        'theme' => 'innoverz',
        'file' => 'columns2.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    // Main course page.
    'course' => array(
        'theme' => 'innoverz',
        'file' => 'course.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
        'options' => array('nonavbar' => false, 'langmenu' => true),
    ),
    'coursecategory' => array(
        'theme' => 'innoverz',
        'file' => 'columns2.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    // Part of course, typical for modules - default page layout if $cm specified in require_login().
    'incourse' => array(
        'theme' => 'innoverz',
        'file' => 'incourse.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
        'options' => array('nonavbar' => false, 'langmenu' => true),
    ),
    // The site home page.
    'frontpage' => array(
        'theme' => 'innoverz',
        'file' => 'frontpage.php',
        'regions' => array('side-pre', 'front-center'),
        'defaultregion' => 'side-pre',
        'options' => array('nonavbar' => true),
    ),
    // Server administration scripts.
    'admin' => array(
        'theme' => 'innoverz',
        'file' => 'columns2.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    // My dashboard page.
    'mydashboard' => array(
        'theme' => 'innoverz',
        'file' => 'mydashboard.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
        'options' => array('nonavbar' => true, 'langmenu' => true),
    ),
    // My public page.
    'mypublic' => array(
        'theme' => 'innoverz',
        'file' => 'mypublic.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
        'options' => array('nonavbar' => false, 'langmenu' => true),
    ),
    'login' => array(
        'theme' => 'innoverz',
        'file' => 'login.php',
        'regions' => array(),
        'options' => array('langmenu' => true),
    ),
    'signup' => array(
        'theme' => 'innoverz',
        'file' => 'signup.php',
        'regions' => array(),
        'options' => array('langmenu' => true),
    ),

    // Pages that appear in pop-up windows - no navigation, no blocks, no header.
    'popup' => array(
        'file' => 'columns1.php',
        'regions' => array(),
        'options' => array('nofooter' => true, 'nonavbar' => true),
    ),
    // No blocks and minimal footer - used for legacy frame layouts only!
    'frametop' => array(
        'theme' => 'moove',
        'file' => 'columns1.php',
        'regions' => array(),
        'options' => array('nofooter' => true, 'nocoursefooter' => true),
    ),
    // Embeded pages, like iframe/object embeded in moodleform - it needs as much space as possible.
    'embedded' => array(
        'theme' => 'boost',
        'file' => 'embedded.php',
        'regions' => array()
    ),
    // Used during upgrade and install, and for the 'This site is undergoing maintenance' message.
    // This must not have any blocks, links, or API calls that would lead to database or cache interaction.
    // Please be extremely careful if you are modifying this layout.
    'maintenance' => array(
        'file' => 'maintenance.php',
        'regions' => array(),
    ),
    // Should display the content and basic headers only.
    'print' => array(
        'theme' => 'moove',
        'file' => 'columns1.php',
        'regions' => array(),
        'options' => array('nofooter' => true, 'nonavbar' => false),
    ),
    // The pagelayout used when a redirection is occuring.
    'redirect' => array(
        'theme' => 'boost',
        'file' => 'embedded.php',
        'regions' => array(),
    ),
    // The pagelayout used for reports.
    'report' => array(
        'theme' => 'innoverz',
        'file' => 'columns2.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    // The pagelayout used for safebrowser and securewindow.
    'secure' => array(
        'theme' => 'moove',
        'file' => 'secure.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre'
    )
];

$THEME->javascripts_footer = array('jquery-3.6.0.min','common');