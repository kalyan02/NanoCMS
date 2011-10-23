<?php require_once("data/setting.php"); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN">
<html>
<head>
<title><?php show_content_area('WebSite Name'); ?> &raquo; <?php show_title(); ?></title>
<meta name="description" content="NanoCMS is the smallest text file based cms written in php. As the nano name suggests the cms is really tiny, small,elegant, easy to use interface. You can create saperate pages and also sidebar content pages. The sidebar links are added automatically" />
<meta name="keywords" content="NanoCMS, nano, cms, tiny, small, easy to use, easy, free, opensource, easy, interface, pages, static, dynamic content, beginners" />
<meta name="author" content="Kalyan Chakravarthy" />
<link rel="stylesheet" type="text/css" href="style.css" />
<?php runTweak('head'); ?>
</head>
<body>
<div id="wrapper">

  <div id="header">
    <h1><?php show_content_area('WebSite Name'); ?></h1>
    <h2><?php show_content_area('WebSite slogan'); ?></h2>
  </div>

    <div id="topnav">
      <?php
        //show_links( link_place, format[ nolist->with out <li> or default <li>%s</li>, before, after )
        show_links('top-navigation','nolist');
      ?>
    </div>

  <div id="main">
    <div id="left">
      <h2>Navigation</h2>
	  <div id="leftnav">
      	<ul>
		  <?php show_sidebar(); ?>
        </ul>
		  <?php show_content_area('Below Navigation'); ?>
	  </div>
    </div>
    <div id="right">
	  <?php show_content_slug(); ?>
    </div>
 </div>

<div class="break">&nbsp;</div>
 <div id="footer">
 	<div style="float:right"><?php show_links('Footer-Right', ' | %s'); ?></div>
	<?php show_content_area('Copyright Notice'); ?>
	powered by <a href='http://NanoCMS.in'>NanoCMS</a>
 </div>
</div>
<?php runTweak('end-body'); ?>
</body>
</html>