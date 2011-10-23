<?php
/*
	NanoCMS v0.4 © 2007-2008 Kalyan Chakravarthy ( www.KalyanChakravarthy.net )
*/

session_start();
define('NANO_ADMIN', true);

require_once "setting.php";

runTweak('after-settings-load');

//General functions
require_once( "libs/general.lib.php" );

//General functions
require_once( "libs/template.lang.lib.php" );

//Admin Login Lib
require_once( "libs/admin.login.lib.php" );

//Pages ( create, edit, ordering etc )
require_once( "libs/admin.pages.lib.php" );

//Content areas handler
require_once( "libs/admin.contentareas.lib.php" );

//Tweaker handling functions
require_once( "libs/admin.tweakers.lib.php" );

//Settings handling functions
require_once( "libs/admin.settings.lib.php" );

performMove();
doTweakToggle();

function show_nano_updates() {
	//to provide updates in native lang
	$lang = getDetails('language');
	//to track the version being used
	$url = urlencode($_SERVER['REQUEST_URI']);
	$ver = getDetails('version');
	$query = "lang=$lang&version=$ver&url=$url&timetoken=".time()."&pg=".$_GET['action'].$_GET['tweak'].'&js=1';
	
	runTweak('show-nano-updates',array(&$query));
	
	//send the query and load updates
	if( ( isset($_GET['action']) or isset($_GET['tweak']) ) and !isset($_SESSION['nag'])) return;
	echo "<script src='http://nanocms.in/recent_updates.php?$query' language='javascript'></script>";
}
registerTweak('admin-head-content','show_nano_updates');

function do_admin_body() {
	runTweak('do-admin-body');
	
	if( $_GET['action'] == 'addpage' )
		addpage();

	elseif( $_GET['action'] == 'delete' )
		doDelete();

	elseif( $_GET['action'] == 'edit' )
		performEdit();//edit pages

	elseif( $_GET['action'] == 'showpages' )
		showpageslist();

	elseif( $_GET['action'] == 'editarea' )
		doAreaEdit();//edit content areas

	elseif( $_GET['action'] == 'showareas' )
		nanoadmin_showareas();

	elseif( $_GET['action'] == 'settings' )
		nanoadmin_showsettings();

	elseif( $_GET['action'] == 'tweakers' )
		showTweakers();

	elseif( isset($_GET[tweak]) )
		showTweaksInterface();

	elseif( !isset($_GET['action']) ) {
		$introPage = NANO_ADMIND_DESIGN_BASE."intro.php";
		runTweak( 'intro-page', array( &$introPage ) );
		require_once( $introPage );
	}
}
registerTweak('admin-body','do_admin_body');

// include the template of the admin area :)  ///////////////////////////////////////////////////

	$adminPageName = NANO_ADMIND_DESIGN_BASE."admindesign.php";
	//debug($adminPageName,0);
	runTweak( 'admin-page', array( &$adminPageName ) );
	//debug($adminPageName,1);
	require_once( $adminPageName );
?>