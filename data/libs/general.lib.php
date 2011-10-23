<?php

function html_select($fieldname,$list,$selected=1, $extraParams='') {
	$t = "<select name='$fieldname' $extraParams>";
	foreach( $list as $slugid=>$title )
	{
		if( $slugid == $selected )
		$chk = " selected='selected' ";
		else
		$chk = '';
		$t .= "<option value='$slugid' $chk>$title</option>";
	}
	$t .= "</select>";
	return $t;
}

//savepages()
function savepages() {
	global $NANO;
	runTweak( 'on-save-pages' );
	$pagesdata = serialize( $NANO );
	if( !put2file( PAGES_DETAILS_FILE, $pagesdata ) ) {
		MsgBox( lt("File writing error"), 'redbox' );
		return false;
	}
	return true;
}

//checkBoxList()
function checkBoxList($checkList,$selected=array() ,$return=true) {
	$op='';
	foreach( $checkList as $key=>$ele ) {
		$keyname = $key;//str_replace('-','_',$key);
		$checked = ( in_array($key,$selected)?" checked=checked ":'' );
		$val = "<span class='caps'>$key</span>";
		$op.= "<input type='checkbox' id='check_$keyname' name='check_$keyname' value='$key' $checked><label for='check_$keyname'>$val</label>";
		$op.= "&nbsp; &nbsp; &nbsp;";
	}
	if($return)
	return $op;
	else echo $op;
}

function htmlentities_charset( $html ) {
	return htmlentities( $html, null, NANO_CHARSET );
}
?>