<?php

//doAreaEdit()
function doAreaEdit() {
	if( isset($_GET['do']) and $_GET['do']=='editarea' ) {
		$areaCount = stripslashes($_POST[areaCount]);
		$errCnt = 0;
		for( $i=1; $i<=$areaCount; $i++ )
		{
			$areaNamei = stripslashes($_POST["areaName$i"]);
			$areaContenti = stripslashes($_POST["areaContent$i"]);
			$areaFilei = areaDataDir( "$areaNamei" );
			$areaFilei = strtolower($areaFilei);
			if( !put2file( $areaFilei, $areaContenti ) ) {
				$errCnt++;
				$msg = sprintf( lt("Error saving area : <b>%s</b> to file <b>%s</b>",'error-saving-area-to-file'), $areaNamei, $areaFilei );
				MsgBox( $msg );
			}
		}
		if( $errCnt == 0 )
			MsgBox( lt("All Area contents saved Successfully",'areas-save-success' ) );
		//header("location:?action=showareas");
	}
}


function nanoadmin_showareas() {

	doAreaEdit();

	global $indexTemplateAreas;
	//execute the nano site in demo to read the content areas
	demoExecuteNanoSite();
	$sett = getDetails('settings');
	$contents = $sett['def-template-areas'];

	$areaInfo = array();
	foreach( $contents as $areaName )
	{
		$areaFile = areaDataDir( "$areaName" );
		$fileContent = file_exists($areaFile) ? file_get_contents( $areaFile ) : '';
		$areaInfo[ $areaName ] = $fileContent;
	}

	$saveAllTxt = lt('Save all Areas');
	$biggerInp = lt('Bigger Input Box');
	$smallerInp = lt('Smaller Input Box');

	echo "<form action='?action=showareas&do=editarea' method='post' accept-charset='utf-8'>";
	echo "<input type='submit' value='+ $saveAllTxt +' class='floatright'>";
	echo "<input type='hidden' name='areaCount' value='".count($areaInfo)."'>";

	$cnt = 1;
	foreach( $areaInfo as $areaName=>$areaContents )
	{
		$boxId = "box$cnt";//md5($areaName);

		echo "<h2>&raquo; $areaName</h2>
			    <input type='hidden' name='areaName$cnt' value='$areaName'>
				<table><tr valign='top'><td>
				<textarea name='areaContent$cnt' rows='2' cols='60' id='$boxId' class='areabox'>".htmlentities_charset( $areaContents )."</textarea>
				</td><td>
				<input type='button' onclick='makesmall(\"$boxId\")' value='-' title='$smallerInp' class='isizeh'>
				<input type='button' onclick='makebig(\"$boxId\")' value='+' title='$biggerInp' class='isizeh'>
				</td></tr></table>
			 ";
		$cnt++;
	}
	echo "<input type='submit' value='+ $saveAllTxt +' class='floatright'>";

	echo "</form>";

	echo "<script language='javascript'>
			function makebig(id) {
			obj = document.getElementById(id);
			if( obj.rows < 30 ) obj.rows+= 5;
			}
			function makesmall(id) {
			obj = document.getElementById(id);
			if( obj.rows > 5 ) obj.rows-= 5;
			}
		  </script>";
}

?>