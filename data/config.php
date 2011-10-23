<?php
/*
	NanoCMS ( nanocms.in ) v0.4 final © 2007-2009 Kalyan Chakravarthy ( www.KalyanChakravarthy.net )
	This software is released under GNU/GPL License ( read "gnu-license-mini.txt" )

	Developed by Kalyan Chakravarthy
	Email : kalyan@KalyanChakravarthy.net
		nvsnkalyan@gmail.com

	If you are using this, please support me by linking back :)...not a must though.
	http://NanoCMS.in
*/

/* ---------------------------------------- */
/*         Optional Configurations          */
/* ---------------------------------------- */

/* In which directory are your main NanoCMS Files present?	*/
$NanoCMS['nanocms_files_dir'] = 'data/';

/* NanoCMS charset */
$NanoCMS['charset'] = 'UTF-8';

/*
 NanoCMS page slug word .... 
 For index.php?page=something -> use $NanoCMS['slug_word'] = "page";
 For index.php?slug=something -> use $NanoCMS['slug_word'] = "slug";
*/
$NanoCMS['slug_word'] = "page";

/* Other stuff for more customization  */
$NanoCMS['file_extension'] = 'php'; //php,html,htm,txt

/* Your Main Index filename ( the filename which will launch your nanoCMS )	*/
$NanoCMS['index_filename'] = 'index.php';

/* Your admin page filename	*/
$NanoCMS['admin_filename'] = 'nanoadmin.php';

$NanoCMS['index_location'] = '';
?>