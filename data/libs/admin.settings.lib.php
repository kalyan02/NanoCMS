<?php
function nanoadmin_showsettings() {
	$home = getDetails('homepage');
	$pages = getDetails('titles');
	$slugs = getDetails('slugs');
	$username = getDetails('username');
	$seourl_stat = (bool)getDetails('seourl');
	$seourl = array( lt('Disabled'), lt('Enabled') );

	$is_modrewrite_available = false;
	if( function_exists('apache_get_modules') ){
		if(in_array('mod_rewrite',apache_get_modules()))
			$is_modrewrite_available = true;
	}

	if( isset($_POST['save']) ) {
		runTweak('save-settings');
		$_POST =  array_map( 'stripslashes', $_POST );
		$home = $_POST['homepage'];
		$seourl_stat = $_POST['seourls'];
		$seourl_stat = $is_modrewrite_available ? $seourl_stat : 0;

		if( $seourl_stat==1 )
			file_put_contents( NANO_INDEX_LOCATION . '.htaccess', NANO_HTACCESS_FORMAT );
		else
			unlink( NANO_INDEX_LOCATION . '.htaccess' );

		$username = $_POST['username'];
		$password = $_POST['password'];
		setDetails('homepage',$home);
		setDetails('seourl',$seourl_stat);
		if(!empty($username)) setDetails('username',$username);
		if(!empty($password)) {
			setDetails('password',md5($password));
			//reset the logged session variable
			$_SESSION[ NANO_CMS_ADMIN_LOGGED ] = md5( md5($password) . $_SESSION[ LOGIN_TIME_STAMP ] );
		}

		if( savepages() )
			MsgBox( lt('Settings were saved successfully'), 'greenbox' );
	}

	$word_homepage = lt('Home Page');
	$word_sefurl = lt('Search Engine Friendly URL\'s');
	$word_new = lt('New');
	$word_username = lt('Username');
	$word_password = lt('Password');
	$word_leaveitemtpy = lt("Leave empty if you don't want to change",'leave-empty-for-no-change');
	$word_loginsettings = lt("Login Settings");
	$word_save = lt("Save Changes");
	$word_settings = lt("NanoCMS Settings");

	if( $is_modrewrite_available ) {
		$select_seourl = html_select('seourls', $seourl, $seourl_stat );
		$word_modrewrite = lt("mod_rewrite is required and is available");
	}
	else {
		$select_seourl = html_select('seourls', $seourl, $seourl_stat, ' disabled="disabled"' );
		$word_modrewrite = lt("mod_rewrite is <b>not available</b>, please contact your host or enable it via httpd.conf",'modrewrite-not-available');
	}

	$select_homepage = html_select('homepage', $pages, $home);


	echo $output =<<<NANO_SETTINGS
	<h2>$word_settings</h2>
	<form action="#" method="POST" accept-charset="utf-8">
		<table width="100%" cellpadding="5">
			<tr>
				<td>$word_homepage</td><td>$select_homepage</td>
			</tr>
			<tr>
				<td>$word_sefurl <br /><small>[ $word_modrewrite ]</small></td><td>$select_seourl</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td colspan="2"><h2>$word_loginsettings</h2></td>
			</tr>
			<tr>
				<td colspan="2">$word_leaveitemtpy</td>
			</tr>
			<tr>
				<td>$word_new $word_username</td><td><input type="text" value="$username" name="username" /></td>
			</tr>
			<tr>
				<td>$word_new $word_password</td><td><input type="text" name="password" value="" /></td>
			</tr>
			<tr>
				<td><br /><input type="submit" value="$word_save" name="save" /></td>
			</tr>
NANO_SETTINGS;
			runTweak('admin-settings');
	echo "
		</table>
	</form>";
}
?>