<?php

//performMove()
function performMove() {
	if( $_GET[action]=='reorder' ) {
		$par = explode(',',$_GET[param]);
		$par[0] = (int)$par[0];
		$par[1] = (int)$par[1];

		reorder( $_GET[cat], $par[0], $par[1] );

		savepages();
		$_SESSION[opencat]=$_GET[cat];
		header( "Location:nanoadmin.php?action=showpages" );
	}
}

//reorder()
function reorder($cat,$pos1,$pos2) {
	$cats = getDetails('cats');
	$cc = $cats[$cat][$pos1];
	$cats[$cat][$pos1] = $cats[$cat][$pos2];
	$cats[$cat][$pos2] = $cc;
	setDetails('cats',$cats);
}

//performEdit()
function performEdit() {
	if( $_GET[action] != 'edit' ) return;
	global $editErrMsg;

	$slug = $_GET[slug];
	$editErrMsg = array();
	$catlist = getDetails('cats');

	if( isset( $_POST[save] ) )
	{
		//declare a new page class
		$EditPageObj = new Page();
		$EditPageObj->loadSlug($slug);

		$oldSlug = $slug;
		$newSlug = slugify( $_POST[title] ); //newp->slug;

		$fileName = pageDataDir($slug);
		$newFileName = $newName = pageDataDir( $newSlug );

		if( $slug=='' )
			$editErrMsg[]= lt("Title cannot be empty");

//	::	New procedure	:: Fix
		$postVariables = $_POST;
		$selectedCats = array();

		foreach( $postVariables as $catKey=>$categoryName ) {
			if( strpos( $catKey, 'check_' ) !== false ) {
				$selectedCats[] = $categoryName;
			}
		}
		// the default one os 'other-pages' section if nothing is selected...
		if( count($selectedCats) == 0 )
			$selectedCats[] = 'other-pages';

		$EditPageObj->cats = $selectedCats;
		// $EditPageObj->commitChanges(); // dont commit yet
//	::	fix complete


		if( $newSlug!=$oldSlug ) // if a slug change has occured
		{
			$renameErr = false;
			if( file_exists( $newName ) ) {
				$editErrMsg[] = lt("Cannot Rename Title: Another page with similar title already exists",'cannot-rename-title');
				$renameErr = true;
			}else
			if( !rename($fileName,$newName) ) {
				$editErrMsg[]= lt("Error Renaming");
				$renameErr = true;
			}

			if( !$renameErr ) //edit title only ifrename error has not occured
				$EditPageObj->editTitle( stripslashes($_POST[title]) );

			$fileName = $newName;
		}

		runTweak( 'file-contents-edit', array( &$_POST[content] ) );

		if( !put2file( $fileName, stripslashes( $_POST[content] ) ) )
			$editErrMsg[]=lt("Error writing to file",'file-write-error');
	}

	if( count($addtErrMsg)!=0 and isset($EditPageObj) ) { //error occured
		$em = '';
		foreach( $addtErrMsg as $msg ) $em .= "<li>$msg</li>";
		MsgBox( lt('Errors Occured')." : <ul>$em</ul>", 'redbox' );
	}
//	:: Success : No error so proceed to saving pages
	elseif( isset($EditPageObj) ) {
		$EditPageObj->commitChanges();
		savepages();

		$em = ''; $gmsg = array();
		$gmsg[] = lt("Content Edited Successfully. You can continue editing",'content-edit-success-msg');
		foreach( $gmsg as $msg ) $em .= "<li>$msg</li>";
		MsgBox( "<ul>$em</ul>" );
		$slug = $newSlug;
//		return;
	}

	$ep = new Page();
	$ep->loadSlug($slug);

	$fileName = pageDataDir( $slug );
	$formAction = "?action=edit&slug=$slug";
	$fileContent = file_exists( $fileName )? htmlentities_charset( file_get_contents( $fileName ) ) : lt("no content yet");
	$submitButton = lt('Save Page Content');

	$editLabel = lt( 'Edit Content' );
	$catLabel = lt('Categories');
	$contentLabel = lt('Content');
	$pagetitle_l = lt('Page Title');


	$title = $ep->title;
	$chkboxs = checkBoxList( getDetails('cats'), $ep->cats );

	runTweak( 'edit-form-display' );

	echo $te=<<<TET
	<form action='$formAction' method=post class='pagemod_form' accept-charset='utf-8'>
		<h2>$editLabel : $ep->title</h2>
		<table width='98%' border='0'>
		  <tr>
			<td width='20%'>$pagetitle_l</td>
			<td><input type='text' value='$title' name='title'></td>
			<td align='right'><input type='submit' value='$submitButton' name='save'></td>
		  </tr>
		  <tr>
			<td>$catLabel</td>
			<td colspan=2>$chkboxs</td>
		  </tr>
		</table>
		<h2>$contentLabel</h2>
		<table width="98%">
		  <tr><td colspan=2>
			<textarea name='content' rows=20 cols=70 id='editbox' class='editbox'>$fileContent</textarea>
		  </td></tr>
		  <tr><td colspan=2><br /><input type='submit' value='$submitButton' name='save'></td></tr>
		</table>
	</form>
TET;
	runTweak( 'after-edit-form-display' );
}

//addpage()
function addpage()
{
	$addtErrMsg = array();
	$catlist = getDetails('cats');

	$newp = new Page();
	$newp->newPageInit();
	$newp->addToCat( 'sidebar' );

	if( isset( $_POST[save] ) )
	{
		$isFormSubmitted = true;
		$content = stripslashes($_POST[content]);
		$title = stripslashes($_POST[title]);

		$newp->editTitle( $title );
		$slug = $newp->slug;

		//	Fixed Category adding
		$postVariables = $_POST;
		$selectedCats = array();

		foreach( $postVariables as $catKey=>$categoryName ) {
			if( strpos( $catKey, 'check_' ) !== false ) {
				$selectedCats[] = $categoryName;
			}
		}
		if( count($selectedCats) == 0 )
			$selectedCats[] = 'sidebar';

		$newp->cats = $selectedCats;
		// $newp->commitChanges(); // dont commit yet
		// fix complete


		if( $title != '' and $content !='' )
		{
			$newPageFile = pageDataDir($slug);
			if( !file_exists($newPageFile)  )
			{
				//view all cats
				$newp->commitChanges();
				//debug($newp);

				runTweak( 'new-page', array( 'page-obj'=>&$newp, 'page-content'=> &$content ) );

				if( put2file( $newPageFile, $content ) )
					savepages();
				$m = lt(sprintf("The page was created successfully. <a href='%s'>Continue Editing</a>","?action=edit&slug=$slug"),'page-create-success'). '<br />';
				$m .= lt("File Created") ." : ".$newPageFile."<br />";
				$m .= lt("Content")." : ".substr( strip_tags($content), 0, 100 ).( strlen($content)>100 ? '...' : '' )."<br />";

				MsgBox( $m );

				return; // success
			}
			else
			{
				$addtErrMsg[] = lt("Save Failed");
				if( file_exists($newPageFile) )
					$addtErrMsg[] = lt("A page with similar Title already exists!!",'similar-page-title-exists');
			}
		}
		else
		$addtErrMsg[] = lt("Either the title or the content is/are empty!!! Please check your input!!<br />",'title-or-content-empty');

		//savepages();
	}

	if( count($addtErrMsg)!=0 and isset($title) ) { //error occured
		$em = '';
		foreach( $addtErrMsg as $msg ) $em .= "<li>$msg</li>";
		MsgBox( lt("Errors Occured")." : <ul>$em</ul>", 'background:#FFE8E8;border:1px solid #AE0000' );
	}

	//debug($addtErrMsg);

	$formAction = "?action=addpage";
	$submitButton = lt('Add Page');
	$chkboxs = checkBoxList( getDetails('cats'), $newp->cats );

	$pageTitleLabel = lt('Page Title');
	$catsLabel = lt('Categories');
	$contentLabel = lt('Content');
	$addnewpageLabel = lt('Add new Page');

	runTweak('add-form-display');
	echo $te=<<<TET
	<form action='$formAction' method=post class='pagemod_form' accept-charset='utf-8'>
		<h2>$addnewpageLabel</h2>
		<table width='98%' border='0'>
		  <tr>
			<td width='20%'>$pageTitleLabel</td>
			<td><input type='text' value='$title' name='title'></td>
			<td align='right'><input type='submit' value='$submitButton' name='save'></td>
		  </tr>
		  <tr>
			<td>$catsLabel</td>
			<td colspan=2>$chkboxs</td>
		  </tr>
		</table>
		<h2>$contentLabel</h2>
		<table width="98%">
		  <tr><td colspan=2>
			<textarea name='content' rows=20 cols=70 id='editbox' class='editbox'>$content</textarea>
		  </td></tr>
		  <tr><td colspan=2><br /><input type='submit' value='$submitButton' name='save'></td></tr>
		</table>
	</form>
TET;
	runTweak( 'after-add-form-display' );
}

//doDelete()
function doDelete() {
	$slug = $_GET[slug];
	$slugList = getDetails('slugs');
	$titleList = getDetails('titles');
	$homepage = getDetails('homepage');
	$delpg = new Page();
	$delpg->loadSlug($slug);

	$title = $titleList[ $delpg->slugId ];

	if( $delpg->slugId == $homepage ) {
		$msg = sprintf( lt("Cannot Delete <b>%s</b> : <b>Your homepage cannot be deleted</b>",'cannot-delete-homepage'),$title );
		MsgBox( $msg, 'redbox' );
		return;
	}

	runTweak( 'on-delete-page', array('page'=>$delpg) );

	unset( $slugList[ $delpg->slugId ] );
	unset( $titleList[ $delpg->slugId ] );

	if( unlink( pageDataDir( $delpg->slug ) ) ) {
		$delpg->catReset();
		$delpg->commitChanges();
		setDetails('slugs',$slugList);
		setDetails('titles',$titleList);
		savepages();
		$msg = sprintf( "<strong>".lt("Page '%s' was Successfully Deleted",'page-delete-success').'</strong>',"<b>$title</b>" );
	} else {
		$msg = sprintf( "<strong>".lt("Error : '%s' could not be deleted",'page-delete-error').'</strong>',"<b>$title</b>" );
	}

	echo '<br />';
	MsgBox( $msg );
}

//showpageslist()
function showpageslist() {
	global $NANO;

	demoExecuteNanoSite();

	runTweak('show-pages-list');
	
	$cdt = getDetails('cats');
	$sett = getDetails('settings');
	$slugs = getDetails('slugs');
	$titles = getDetails('titles');
	$templateCats = $sett['def-template-links'];
	$defaultCats = explode( ',', NANO_MUSTHAVE_CATS );
	$musthaveCats = array_unique( array_merge( $templateCats, $defaultCats ) );

	$selectedCat = 1;
	$toggStat = 'false';

	if( isset($_GET[addcat]) ) {
		$newCatName = strtolower( stripslashes($_POST[catname]) );
		if( in_array( $newCatName, array_keys($cdt) ) ) {
			$msg = sprintf( lt( "Cannot add new Links Category : %s already exists",'cat-add-fail-already-exists'), "<b>$newCatName</b>" );
			MsgBox( $msg );
		}
		else {
			$cdt[$newCatName] = array();

			$msg = sprintf( lt( "Pages Category %s Added Successfully",'cat-add-success'), "<b>$newCatName</b>" );
			MsgBox( $msg,'greenbox' );
			setDetails( 'cats', $cdt );
			savepages();
		}
	}

	if( isset($_GET[removecat]) ) {
		$catN = $_GET[removecat];
		if( !in_array($catN,array_keys($cdt) ) )
			MsgBox( lt("Category to be deleted does not exist",'cat-to-del-not-exists'), 'redbox' );
		else
		if( in_array( $catN, $musthaveCats ) )
			MsgBox( "<b>$catN</b> : ".lt('Cannot be deleted'), 'redbox' );
		else {
			unset( $cdt[ $catN ] );
			$msg = sprintf( lt( "Pages Category %s was removed Successfully",'cat-remove-success'), "<b>$catN</b>" );
			MsgBox( $msg,'greenbox' );
			setDetails( 'cats', $cdt );
			savepages();
		}
	}

	if( isset( $_GET[addtocat] ) ) {
		$slug2add = $_POST[page];
		$cat2add = $_POST[cat];
		if( in_array( $slug2add, $cdt[$cat2add] ) ) {
			$msg = sprintf( lt( "The page %s is already listed in %s",'page-already-listed'), "<b>$titles[$slug2add]</b>" , "<b>$cat2add</b>");
			MsgBox( $msg );
		}
		else {
			array_push( $cdt[$cat2add],$slug2add );
			$msg = sprintf( lt( "The page %s was added successfully under %s",'page-to-cat-add-success'), "<b>$titles[$slug2add]</b>" , "<b>$cat2add</b>");
			MsgBox( $msg );
			setDetails( 'cats', $cdt );
			savepages();

			$selectedCat = $cat2add;
			$toggStat = 'true';
		}
	}

	$catSelectList = array();
	foreach( $cdt as $cN=>$cSC ) $catSelectList[$cN] = $cN;

	runTweak('show-pages-list-cat-select',array(&$cat));
	
	$pagesAndOpt = lt( 'Pages & Category Options','page-and-cat-opt' );
	$pagesListing = lt( 'Pages & Category Listing','page-and-cat-list' );
	$addNewCat = lt( 'Add new Category' );
	$addToAnotherCat = lt( 'Add page to another Category', 'add-page-to-another-cat' );
	$addLabel = lt( 'Add' );
	$useUrlLabel = lt( 'Url you can use' );
	$moveLabel = lt( 'Move' );
	$optLabel = lt( 'Options' );
	$pageLabel = lt( 'Page' );

	echo "<h2 id='cat_anchor' class='cattitle'><span id='toggCon'>&raquo; </span><a href='#nogo' class='nodeco'>$pagesAndOpt</a></h2>
			<table id='cat_options'>
			 <tr>
			 	<form action='?action=showpages&addcat=true' method='post' accept-charset='utf-8'>
				<td>$addNewCat : </td><td><input type='text' name='catname'> <input type='submit' value='$addLabel'></td>
				</form>
			 </tr>
			 <tr>
				<form action='?action=showpages&addtocat=true' method='post' accept-charset='utf-8'>
				<td>$addToAnotherCat</td><td>".html_select( 'page', $titles, 0 )." to ".html_select( 'cat',$catSelectList,$selectedCat  )."
					 <input type='submit' value='$addLabel'>
				</td>
				</form>
			 </tr>
			</table>";
	$js = "
			is_{$v}_open = $toggStat;
			if( is_{$v}_open )
				\$('#cat_options').show()
			else
				\$('#cat_options').hide()

			\$('#cat_anchor').click(
				function() {
					if( is_{$v}_open )	{
						\$('#cat_options').fadeOut('fast');
						is_{$v}_open = false;
					} else {
						\$('#cat_options').fadeIn('fast');
						is_{$v}_open = true;
					}
				}
			);
			\$('.cattitle').hover(
				function() {
					\$(this).toggleClass('cathoverclass');
				},
				function() {
					\$(this).toggleClass('cathoverclass');
				}
			);
	";
	$v=0;
	echo "<h2>&raquo; $pagesListing</h2>";
	echo "<div class='linkcats-div'>";

	foreach($cdt as $catname=>$catslugs)
	{
		$v++;
		$slugids = array_values($catslugs);
		$n = count($slugids)-1;

		if( !in_array( $catname, $musthaveCats ) )
			$removeOpt = "( <a href='?action=showpages&removecat=$catname' class='removecat'>".lt("remove")."</a> )";
		else
			$removeOpt = '';

		//just user interface stuff
		$toggStat = ($catname==$_SESSION[opencat])?'true':'false';
		if( !isset($_SESSION[opencat]) and $catname=='sidebar' ){ $toggStat = true; }
		if( $catname==$_SESSION[opencat] ) { $toggStat='true'; unset($_SESSION[opencat]); }
		else $toggStat=='false';

		$js .= "
			is_{$v}_open = $toggStat;
			if( is_{$v}_open )
				$('#t$v').show()
			else
				$('#t$v').hide()

			$('#h2$v').click(
				function() {
					if( is_{$v}_open )	{
						$('#t$v').fadeOut('fast');
						is_{$v}_open = false;
					} else {
						$('#t$v').fadeIn('fast');
						is_{$v}_open = true;
					}
				}
			);
		";

		echo "<h2 class='cattitle noborder' id='h2$v' class='togg'><span>&raquo;</span> <a href='#nogo'>$catname</a> $removeOpt</h2>";
//		echo "<h2 class='cattitle noborder' id='h2$v'><a href='#nogo'><span id='co$v' class='togg'>&raquo;</span> $catname</a> $removeOpt</h2>";
		echo "<div class='borderWrap'>";
		echo "<table cellpadding='5px' cellspacing='2px'  width='100%' id='t$v' class='pageListTable'>";
		echo "<tr class='th'><th>$pageLabel</th><th colspan='2' class='center'>$optLabel</th><th colspan='2' class='center'>$moveLabel</th><th>$useUrlLabel</th></tr>";

		if( count($slugids) == 0 ) {
			echo "<tr><td colspan='10' class='center'>".lt('No pages are added under this Category','no-pages-added')."</td></tr></table>";
			continue;
		}

		foreach($slugids as $pos=>$ids) {

			$delTxt = lt('Delete');
			$editTxt = lt('Edit');
			$upTxt = lt('Move Up');
			$downTxt = lt('Move Down');

			$ul = makeLink("?action=reorder&cat=$catname&param=".$pos.','.($pos-1),"<img src='".NANO_ADMIND_DESIGN_BASE."stuff/icons/arrow_up.png' alt='$upTxt' title='$upTxt'/>");
			$dl = makeLink("?action=reorder&cat=$catname&param=".$pos.','.($pos+1),"<img src='".NANO_ADMIND_DESIGN_BASE."stuff/icons/arrow_down.png' alt='$downTxt' title='$downTxt' />");

			if( $pos==0 ) $ul="<img src='".NANO_ADMIND_DESIGN_BASE."stuff/icons/arrow_up_d.png' alt='$upTxt' />";
			if( $pos==$n ) $dl="<img src='".NANO_ADMIND_DESIGN_BASE."stuff/icons/arrow_down_d.png' alt='$downTxt' />";

			$deleteConfirmMsg = lt( "Are you sure you want to delete this page!! Remember, Once you delete you cannot retrieve again!! Proceed???", 'page-delete-confirm-msg' );

			$s = "<tr>
					<td><b>".$titles[$ids]."</b></td>
					<td class='center' width='10px'><a href='?action=edit&slug=".$slugs[$ids]."'><img src='".NANO_ADMIND_DESIGN_BASE."stuff/icons/page_edit.png' alt='$editTxt' title='$editTxt' /></a></td>
					<td class='center' width='10px'><a href='?action=delete&slug=".$slugs[$ids]."' onclick='return confirm(\"$deleteConfirmMsg\");'><img src='".NANO_ADMIND_DESIGN_BASE."stuff/icons/cross.png' alt='$delTxt' title='$delTxt' /></a>
					</td>
					<td class='center' width='10px'>$ul</td>
					<td class='center' width='10px'>$dl</td>
					<td>".makeLink( (NANO_SEFURL?'../':'').slugUrl($slugs[$ids]), slugUrl($slugs[$ids]) )."</td>
				  </tr>";
			echo $s;

		}
		echo "</table>";
		echo "</div>";

	}
	echo "</div>";

	echo "	<script language='javascript'>
				\$(document).ready(function(){
					\$('.pageListTable tr').hover( function() {
						\$(this).css('backgroundColor','#ebf0f8');
					},
					function() {
						\$(this).css('backgroundColor','#fff');
					});
				});
				$js
			</script>";

	runTweak('show-pages-list-end');
}

?>