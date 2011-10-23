<?php
//login
runTweak( 'before-login-check' );
$loginbox_msg = "";
$admin_user = getDetails('username');
$admin_pass = getDetails('password');

//debug( dirname($_SERVER['REQUEST_URI']), 1 );

if( !isset( $_SESSION[ NANO_CMS_ADMIN_LOGGED ] ) ) {
	if( isset($_POST['user']) ) {
		if( $_POST['user'] == $admin_user and md5($_POST['pass']) == $admin_pass ) {
			$_SESSION[ LOGIN_TIME_STAMP ] = $ts = time();
			$_SESSION[ NANO_CMS_ADMIN_LOGGED ] = md5( $admin_pass . $ts . dirname($_SERVER['REQUEST_URI']) ); //die('done');
			runTweak( 'after-logged-in' );
			header("location:".$NanoCMS['admin_filename']);
		} else {
			$loginbox_msg = lt( "Error : wrong Username or Password" );
		}
	}
}

//logout
if( isset( $_GET[ 'logout' ] ) )	{
	$loginbox_msg = lt( "You were successfully logged out" );
	unset( $_SESSION[ NANO_CMS_ADMIN_LOGGED ] );
}

//the login form
if( $_SESSION[ NANO_CMS_ADMIN_LOGGED ] != md5( $admin_pass . $_SESSION[ LOGIN_TIME_STAMP ] . dirname($_SERVER['REQUEST_URI']) ) or !isset( $_SESSION[NANO_CMS_ADMIN_LOGGED] ) )
{
	session_destroy();
	runTweak( 'before-login-form' );
	$login_form = "admin-design/loginform.php";
	runTweak('loginform',array(&$login_form));
	include_once( $login_form );
	echo $form;
	exit();
}
?>