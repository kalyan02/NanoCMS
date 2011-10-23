<?php
//showTweaksInterface() - shows the interface for each of the Tweakers
function showTweaksInterface() {
	global $tweakInterfaceList, $tweakList, $tweakData;
	$tweakName = stripslashes($_GET[tweak]);
	$tDet = $tweakData[ $tweakName ];
	$name = lt($tDet['name']);
	$ifunc = $tDet['interface'];
	$author = $tDet['author'];

	$authTxt = lt('Author');
	$descTxt = lt('Description');
	$optdatTxt = lt('Options and Data');
	$optTxt = lt('Options');
	$nameTxt = lt('Name');

	echo "<div class='floatinfo'>";
	echo "<h2>$name</h2>";
	echo "$authTxt : $tDet[author]<br>";
	echo "$descTxt : $tDet[desc]";
	echo "</div>";

//	echo "<h2>$optdatTxt</h2>";
	if( is_callable($ifunc) )
		call_user_func( $ifunc );

	echo "<div class='break'>&nbsp;</div>";
}

//doTweakToggle() - check if any Tweaker was activated/deactivated and perform the actions...
function doTweakToggle() {
	if( isset( $_GET[ action ] ) and $_GET[ action ] == 'tweakers' ) {

		global $tweakInterfaceList, $tweakList, $tweakData,$tweak_mod_msg;
		$activeTweaks = getDetails( 'active-tweaks' );

		if( isset( $_GET['do'] ) ) {
			$do = $_GET['do'];
			$tweak = $_GET['tweak'];
			if( $do == 'activate' && !in_array( $tweak, $activeTweaks ) ) {
				$activeTweaks[] = $tweak;
				$tweak_mod_stat = true;
			}
			if( $do == 'deactivate' && in_array( $tweak, $activeTweaks ) ) {
				$activeTweaks = array_diff( $activeTweaks, (array)$tweak );
				$tweak_mod_stat = true;
			}

			if( $tweak_mod_stat ) {
				$status = $do."d";
				header( "location:$_SERVER[PHP_SELF]?action=tweakers&status=$status" );
			}

			setDetails( 'active-tweaks', $activeTweaks );
			savepages();

		}
	}
}

//showTweakers() - the Tweakers interface
function showTweakers() {
	global $tweakInterfaceList, $tweakList, $tweakData;
	$activeTweaks = getDetails( 'active-tweaks' );

	$descTxt = lt( 'Description' );
	$optTxt = lt( 'Options' );


	// just check if any tweak is modified
	// if so echo the message.
	if( $_GET['status'] == 'activated' )
		MsgBox( lt( 'Tweak Activated' ) );
	if( $_GET['status'] == 'deactivated' )
		MsgBox( lt( 'Tweak Deactivated' ) );


	$tweakerDescTxt = lt("Tweakers are plugins like tools which can be used to tweak the NanoCMS without actually touching the core files.<br>
			New functionalities can be added to the NanoCMS with ease.",'tweakers-desc');

	echo "<h2>".lt('Tweakers')."</h2>";
	echo "<p>$tweakerDescTxt<br></p>";
	echo "<h2>".lt('Active Tweakers')."</h2>";

	$t = "<table border=1 cellpadding=5 width='95%' class='pageListTable'>";
	$t.= "<tr class='th'><th>Tweaker $nameTxt</th><th>$descTxt</th><th>Tweaks</th><th>&nbsp;</th></tr>";

	$viewInterfaceLable = lt('View Interface');

	foreach( $tweakData as $tweakName=>$tweakInfo ) {

		if( in_array( $tweakName , $activeTweaks ) )
		$Lable = makeLink( "?action=tweakers&do=deactivate&tweak=$tweakName", lt('Deactivate'), 'activate_link red' );
		else
		$Lable = makeLink( "?action=tweakers&do=activate&tweak=$tweakName",lt('Activate'), 'activate_link green' );

		$desc = $tweakData[ $tweakName ][ 'desc' ];
		$opt = '';
		$name = $tweakData[ $tweakName ][ 'name' ];
		if( isset($tweakData[ $tweakName ]['interface']) ) {
			$opt = makeLink( "?tweak=$tweakName", $viewInterfaceLable );
		}

		$twk = $tweakData[ $tweakName ][ 'tweaks' ];
		$tcnt = count( $twk );
		$t.= "<tr><td><b>$name</b></td><td> $desc</td><td class='center'> $opt </td> <td class='center'>$Lable</td></tr>";
	}

	$t.= "</table>";

	runTweak( 'before-tweakerlist-display', array( &$t ) );

	echo $t;
}

function listoutInterfaces() {
	global $tweakInterfaceList, $tweakList, $tweakData;
	$activeTweaks = getDetails( 'active-tweaks' );
	foreach( $tweakData as $tweakName=>$tweakDetails ) {
		$name = $tweakData[ $tweakName ][ 'name' ];
		if( isset($tweakData[ $tweakName ]['interface']) ) {
			echo "<li>". makeLink( "?tweak=$tweakName", lt($tweakDetails['name']) ) ."</li>";
		}
	}
}

// automatically gets the content_areas present in the template ////////////////////////////////
$indexTemplateAreas = $indexTemplateLL = array();
function readIntoAreaList($l,$a='',$b='') {
	global $indexTemplateAreas;
	$indexTemplateAreas[] = $l;
}
function readIntoLinksList($cat,$f='',$a='',$b='',$c='') {
	global $indexTemplateLL;
	$indexTemplateLL[] = $cat;
}
function dummyFunction($a='',$b='',$c='',$d='',$e='',$f=''){}
function demoExecuteNanoSite() {
	global $indexTemplateAreas,$indexTemplateLL;

	$sett = getDetails('settings');
	$catt = getDetails('cats');
	$indexLastModified = filemtime(NANO_CMS_PAGE);

	if( $sett['index-last-modified'] >= $indexLastModified ) return;

	$removeFunctionList = array('show_sidebar','show_content_slug','show_title','require_once');
	$replaceFunction = 'dummyFunction';
	$demoContentToRun = file_get_contents( NANO_CMS_PAGE );
	$demoContentToRun = str_replace( 'show_content_area', 'readIntoAreaList', $demoContentToRun );
	$demoContentToRun = str_replace( 'show_links', 'readIntoLinksList', $demoContentToRun );
	$demoContentToRun = str_replace( $removeFunctionList, $replaceFunction, $demoContentToRun );

	ob_start();
	eval(" ?> ".$demoContentToRun." <?php ");
	$cont = ob_get_contents();
	ob_end_clean();

	MsgBox( lt("Template Changes Detected! Config & Settings updated!",'template-changes-detected') );
	$newcatt = array_diff( $indexTemplateLL,array_keys($catt) );

	foreach( $newcatt as $newcatname )
		$catt[$newcatname] = array();

	foreach( $indexTemplateAreas as $k=>$v ) {
		$indexTemplateAreas[$k] = strtolower($v);
	}

	$sett['index-last-modified'] = $indexLastModified;
	$sett['def-template-areas'] = array_unique( $indexTemplateAreas );
	$sett['def-template-links'] = array_unique( $indexTemplateLL );
	setDetails( 'settings', $sett );
	setDetails( 'cats', $catt );
	savepages();
}

?>