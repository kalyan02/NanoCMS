<?php
/*
 NanoCMS v0.4 final © 2007-2008 Kalyan Chakravarthy ( www.KalyanChakravarthy.net )
 Default Configuration  - Use "config.php" to edit your configuration.
*/

error_reporting(E_ERROR);

$NanoCMS = array();
$NanoCMS['slug_word'] = "page";
$NanoCMS['file_extension'] = 'php';

$NanoCMS['pages_info'] = 'pagesdata.txt';
$NanoCMS['index_filename'] = 'index.php';
$NanoCMS['admin_filename'] = 'nanoadmin.php';
$NanoCMS['setting_filename'] = 'setting.php';

$NanoCMS['nanocms_files_dir'] = 'data/';
$NanoCMS['tweaks_dir'] = 'tweaks/';
$NanoCMS['pages_dir'] = 'pages/';
$NanoCMS['areas_dir'] = 'areas/';
$NanoCMS['admin_design_base'] = 'admin-design/';
$NanoCMS['nanocms_base'] = dirname($_SERVER['REQUEST_URI']);

/*	Load Custom configuration Config	*/
require_once("config.php");

if( defined('NANO_ADMIN') ) {
	$NanoCMS['index_location'] = '../';
	$NanoCMS['nanocms_files_dir'] = '';
	$NanoCMS['nanocms_base'] = dirname(dirname($_SERVER['REQUEST_URI'])."../");
}
/*
 for custom user configuration
 you can use this to hack nanocms and configure it to work with existing systems
 or make the configs changeable via external sources
*/
if( isset($NanoCMS_custom_config) )
	foreach ( (array)$NanoCMS_custom_config as $config=>$value ) 
		$NanoCMS[ $config ] = $value;
		

$NanoCMS['pages_info'] = $NanoCMS['nanocms_files_dir'].$NanoCMS['pages_info'];
$NanoCMS['tweaks_dir'] = $NanoCMS['nanocms_files_dir'].$NanoCMS['tweaks_dir'];
$NanoCMS['pages_dir'] = $NanoCMS['nanocms_files_dir'].$NanoCMS['pages_dir'];
$NanoCMS['areas_dir'] = $NanoCMS['nanocms_files_dir'].$NanoCMS['areas_dir'];

/*	Assign data into constants, for global access. Self explanatory */
define( 'NANO_CHARSET', $NanoCMS['charset'] );
define( 'NANO_SLUGWORD', $NanoCMS['slug_word'] );
define( 'NANO_CMS_PAGE' , $NanoCMS['index_location'].$NanoCMS['index_filename'] );
define( 'NANO_INDEX_LOCATION', $NanoCMS['index_location'] );
define( 'NANO_CMS_FILE_EXTENSION', $NanoCMS['file_extension'] );
define( 'NANO_CMS_EXTENSIONS_ORDER', "php,html,htm,txt" );
define( 'NANO_URL_FORMAT', NANO_CMS_PAGE.'?'.NANO_SLUGWORD.'=%s' );
define( 'NANO_PAGES_DIR', $NanoCMS['pages_dir'] );
define( 'NANO_AREAS_DIR', $NanoCMS['areas_dir'] );
define( 'NANO_CMS_ADMIN_LOGGED', 'logged');
define( 'PAGES_DETAILS_FILE', $NanoCMS['pages_info'] );
define( 'NANO_TWEAKS_DIR',  $NanoCMS['tweaks_dir'] );
define( 'NANO_MUSTHAVE_CATS', 'other-pages,sidebar' );
define( 'NANO_ADMIND_DESIGN_BASE', $NanoCMS['admin_design_base'] );
define( 'NANO_BASE', $NanoCMS['nanocms_base'] );

define( 'NANO_HTACCESS_FORMAT', "# NanoCMS v0.4 SEF url's mod_rewrite file
RewriteEngine On
RewriteBase ".NANO_BASE."
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?".NANO_SLUGWORD."=$1 [L]
" );

////////////////////////////////////////////////////////////////////////////////////////
//		do not edit anything below this
////////////////////////////////////////////////////////////////////////////////////////

function put2file($n,$d) {
	$f=@fopen($n,"w");
	if (!$f) {
		return false;
	} else {
		fwrite($f,$d);
		fclose($f);
		return true;
	}
}

function pageDataDir( $s ) {
	$ext = explode( ',', NANO_CMS_EXTENSIONS_ORDER );
	foreach( $ext as $e ) { if( file_exists( NANO_PAGES_DIR."$s.$e" ) ) return NANO_PAGES_DIR."$s.$e"; }
	return NANO_PAGES_DIR."$s.".NANO_CMS_FILE_EXTENSION;
}
function areaDataDir( $s ) {
	$ext = explode( ',', NANO_CMS_EXTENSIONS_ORDER );
	foreach( $ext as $e ) { if( file_exists( NANO_AREAS_DIR."$s.$e" ) ) return NANO_AREAS_DIR."$s.$e"; }
	return NANO_AREAS_DIR."$s.".NANO_CMS_FILE_EXTENSION;
}


/* 	TEMPLATE TAGS OR TEMPLATE FUNCTIONS - USED IN TEMPLATES FOR CONTENT DISPLAY */

function show_title()
{
	global $currentActivePage;
	runTweak('show-title');
	echo $currentActivePage->title;
}

function show_content_slug( $slug=null )
{
	global $currentActivePage;
	runTweak('content-slug',array(&$slug));
	$slug = is_null($slug) ? $currentActivePage->slug : $slug;
	$contentFile = pageDataDir( $slug );
	if( file_exists( $contentFile ) ) {
		runTweak( 'content-slug-before' );
		runTweak( 'slug_load_before' );
		require_once( $contentFile );
		runTweak( 'slug_load_after' );
		runTweak( 'content-slug-after' );
	}
	else {
		if( NANO_ADMIN ) return;
		echo "<h1>404 : File Requested was Not Found</h1>";
	}
}

function show_content_area( $areaName, $defaultContent='' )
{
	runTweak('content-area-begin',array($areaName));

	$areaFile = areaDataDir( strtolower($areaName) );
	if( file_exists( $areaFile ) )
	require( $areaFile ); // make it compulsory
	else
	echo $defaultContent;

	runTweak('content-area-end',array($areaName));

}

function makeLink($slug,$title,$class='') {
	$classText = !empty($class) ? " class='$class' " : '';
	return "<a href='$slug' $classText>$title</a>";
}

/* 	returns url for a slug  */
function slugUrl($slug) {
	$url = ( NANO_SEFURL ? $slug : sprintf(NANO_URL_FORMAT,$slug) );
	runTweak('slug-url',array($slug,&$url));
	return $url;
}

/* 	Will return array of links : use it if you are doing custom coding */

function get_links_array( $cat ) {
	$cdt = getDetails('cats');
	$scids = $cdt[$cat];
	$slugs = getDetails('slugs');
	$titles = getDetails('titles');
	$dSlugs = array();

	foreach( $scids as $sid )
		$dSlugs[ $slugs[$sid] ] = $titles[$sid];

	runTweak('get-links-array',array($cat,&$dSlugs));
	return $dSlugs;
}

/* 	Will show links in a category */
function show_links( $link_cat, $format='', $before='', $after='' )
{
	$linklist = get_links_array($link_cat);
	runTweak('show-links',array($link_cat,&$linklist));
	if( $format == '' )	$format = "<li>%s</li>";
	if( $format == 'nolist' ) $format = '%s';
	$links_html = '';
	foreach( (array)$linklist as $slug=>$title )
		$links_html.= sprintf( $format, makeLink( slugUrl($slug),$title ) );
	echo $links_html = $before.$links_html.$after;
}

/* wrapper */
function show_sidebar() { show_links('sidebar'); }

/* End of template functions */

/* sets current active page */
function nano_set_active_slug()
{
	global $NANO,$currentActivePage;
	$currentActivePage = new Page();
	if( isset( $_GET[ NANO_SLUGWORD ] ) and !empty($_GET[ NANO_SLUGWORD ]) )
	$slug = $_GET[ NANO_SLUGWORD ];
	else {
		$homePage = getDetails('homepage');
		$slugsList = getDetails('slugs');
		$slug = $slugsList[ $homePage ];
		if( empty($slug) ) {
			NanoError( 'homepage-error', 'No Home Page Found' , true );
		}
	}
	$currentActivePage->loadSlug($slug);
	$contentFile = pageDataDir( $slug );

	if( !defined('NANO_ADMIN') and !file_exists( $contentFile ) ) {
		runTweak( '404', array( $currentActivePage ) );
		header("HTTP/1.0 404 Not Found");
	}

}

// Messagebox functions  //////////////////////////////////////////////////////////

function MsgBox($msg,$class='yellowbox',$style='',$echo=true) {
	$t = "<div class='msgbox $class' style=\"$style\">$msg</div>";
	if( $echo ) echo $t;
	else return $t;
}

////////////////////////////////////////////////////////////////////////////////////////

/* Returns the configuration */
function getDetails($f) {
	global $NANO;
	if($f=='cats') return $NANO['links_cats'];
	elseif($f=='homepage') return $NANO[$f];
	elseif($f=='slugs') return $NANO[$f];
	else return $NANO[$f];
}

function setDetails($f,$v) {
	global $NANO;
	if($f=='cats') 	$NANO['links_cats'] = $v;
	else
	$NANO[$f] = $v;
}

/* makes a nicename for a slug title */
function slugify($title) {
	$pageslug = $title;
	runTweak( 'slugify', array( 'title'=>$title, 'new-slug'=>&$pageslug ) );
	$pageslug = preg_replace( array("/[^A-Za-z0-9\s\s+]/","[ +]"), array("","-"),$pageslug );
	$pageslug = trim( strtolower($pageslug), '-');
	runTweak( 'slugify-after', array( 'title'=>$title, 'new-slug'=>$pageslug ) );
	return $pageslug;
}

/* The page class */
class Page {
	var $cats = array();
	var $slug;
	var $slugId;
	var $title;
	var $isEdited=false;

	/* Initialize a blank page */
	function newPageInit() {
		$cnt = getDetails('slug_count');
		setDetails('slug_count', ++$cnt );
		$this->slugId = $cnt;
	}

	/* edit title */
	function editTitle($s) {
		if( $s != $this->slug )
		$this->isEdited=true;
		$this->title = $s;
		$this->slug = slugify( $s );
	}

	/* check if the page is present in the test category */
	function isInCat($c) {
		if( array_search($c,$this->cats) )
		return true;
		else return false;
	}
	/* add the current page to a category */
	function addToCat($cat) {
		if( !$this->isInCat($cat) )
		{
			$this->isEdited=true;
			$this->cats[] = $cat;
		}
	}
	/* reset the category listing */
	function catReset() { $this->cats = array(); }
	function removeCat($ca) {
		$catInd = array_flip( $this->cats );
		unset( $this->cats[ $catInd[$ca] ] );
	}

	function loadPageDetails() {
		if( empty($this->slug) )
		NanoError( 'code error', 'Slug is empty in loadPageDetails() method' , true );
		$tt = getDetails('titles');
		$oCats = getDetails('cats');
		$sid = $this->slugId;
		$this->title = $tt[ $this->slugId ];

		foreach( $oCats as $oCat=>$oCatC )
		if( in_array($sid,(array)$oCatC) )
		$this->cats[] = $oCat;
	}
	/* load page details from a slug */
	function loadSlug($slug) {
		$slugs = getDetails('slugs');
		$sids = array_flip($slugs);
		if( !in_array($slug,(array)$slugs) ) {
			NanoError('slug load error',' slug cannot be loaded ', true );
			return false;			//not found
		}
		$this->slug = $slug;
		$this->slugId = $sids[$slug];
		$this->loadPageDetails();
	}
	/* finalize changes */
	function commitChanges() {
		$catList = getDetails('cats');
		$sd = getDetails('slugs');
		$tt = getDetails('titles');

		$tt[ $this->slugId ] = $this->title;
		$sd[ $this->slugId ] = $this->slug;
		foreach($catList as $catName=>$catSlugs)
		{
			//the cat is there in our list and our page is not there in master list then just add/push it
			$isCategoryInOurList = in_array( $catName ,(array)$this->cats );
			$isSlugInMasterCategory = in_array( $this->slugId, (array)$catSlugs );

			if(  $isCategoryInOurList and !$isSlugInMasterCategory )
			{
				array_push($catList[$catName],$this->slugId);
				//				echo '<br>'.lt('Added');
			}

			if(  !$isCategoryInOurList and $isSlugInMasterCategory ) {
				$catSlugsIndexes = array_flip( $catList[$catName] );
				array_splice( $catList[$catName], $catSlugsIndexes[$this->slugId],1 );
				//				echo "<br>".lt('Deleted from list')." - $catName";
			}
		}

		setDetails('cats',$catList);
		setDetails('slugs',$sd);
		setDetails('titles',$tt);
	}
}

/* TWEAKER RELATED FUNCTIONS */
function registerTweak( $tLocation, $tName ) {
	global $tweakList, $tweakLocations;
	if( !isset($tweakList[ $tLocation ]) )
	$tweakList[ $tLocation ] = array();
	$tweakList[ $tLocation ] =
	array_merge( (array)$tweakList[ $tLocation ], (array)$tName );
}

function runTweak( $location, $data=array() ) {
	global $tweakList, $tweakLocations;

	if( empty($tweakList[ $location ]) ) return;
	$tweaks = $tweakList[ $location ];
	if( empty($tweaks) ) return;

	foreach( $tweaks as $tFunc ) {
		if( is_callable( $tFunc ) )
		call_user_func_array( $tFunc, (array)$data );
	}
}

function registerInterface( $title, $func ) {
	global $tweakInterfaceList;
	if( !in_array( $title, array_keys($tweakInterfaceList) ) )
	$tweakInterfaceList[$title] = $func;
}

/* Tweak Wrapper Class - use this only */
class Tweak {
	var $tweakName;
	var $Interface;
	var $isActive = false;
	function Tweak( $tN ) {
		global $tweakData;
		$activeTweaks = getDetails( 'active-tweaks' );

		$this->tweakName = slugify($tN);
		$tweakData[ $this->tweakName ][ 'name' ] = $tN;


		if( in_array( $this->tweakName, (array)$activeTweaks ) )
		$this->isActive = true;
	}
	function addTweak( $location, $callFunction ) {
		if( !$this->isActive ) return;
		if( empty($this->tweakName) )
		NanoError( 'Tweaker Error', 'Tweak Name not specified', true );

		global $tweakData;
		$tweakData[ $this->tweakName ][ 'tweaks' ][] = array( 'location'=>$location , 'callFunction'=>$callFunction );
		registerTweak( $location, $callFunction );
	}
	function Description( $desc ) {
		global $tweakData;
		$tweakData[ $this->tweakName ][ 'desc' ] = $desc;//tweak description
	}
	function Author( $auth ) {
		global $tweakData;
		$tweakData[ $this->tweakName ][ 'author' ] = $auth;//tweak author
	}
	function addInterface( $interfaceFunc ) {
		if( !$this->isActive ) return;
		global $tweakData;
		$this->Interface = $interfaceFunc;
		$tweakData[ $this->tweakName ][ 'interface' ] = $interfaceFunc;
	}
}


/* Just some debugging functions */
function NanoError( $errtype, $msg, $die=false ) {
	runTweak( 'on-error', array('message'=>$msg, 'error-type'=>$errtype) );
	if( $errtype == '404' ) die( 'file not found error' );
	else
	echo( "<br>$errtype : $msg<br>" );
	if( $die ) exit();
}

function debug($v,$exit=false) {
	echo "<pre>";
	var_dump($v);
	echo "</pre>";
	if($exit) exit();
}

/* language text */
function lt( $defaultText, $text_type='' ) {
	global $language;
	if( $text_type == '' ) $text_type = slugify( $defaultText );
	if( isset($language[$text_type]) and !empty($text_type) )
		return $language[$text_type];
	else
		return $defaultText;
}
function _lt( $defaultText, $text_type=''  ) {
	echo lt( $defaultText, $text_type );
}

////////////////////////////////////////////////////////////////////////////////////////
/* Load NanoCMS's related stuff ..... this is the startup point for data */
$NANO = array();
$editErrMsg = array();
$currentActivePage;

/* tweakList[ tweak-nice-name ] = array( name, author, desc, tweaks[array] ) */
$tweakList = array();
$tweakInterfaceList = array();
$tweakData = array();

/* Load the Configurations of NanoCMS  */
$NANO = unserialize( file_get_contents( PAGES_DETAILS_FILE ) );

/* The tweaker stuff - Tweaks are plugin like tools in NanoCMS */
$tweaksFound = glob( NANO_TWEAKS_DIR.'*.php' );
$activeTweaks = getDetails( 'active-tweaks' );

/* Include all php files in tweaks directory */
foreach( $tweaksFound as $tF )
require_once( $tF );

/* The language interface files and stuff */
$language = array(); // the array with all language words and phrases
runTweak( 'language-select', array( &$language ) );

if( !defined( 'NANO_ADMIN' ) and !defined( 'NANO_CUSTOM' ) )
	nano_set_active_slug();

define( 'NANO_SEFURL', (bool)getDetails('seourl') );
?>