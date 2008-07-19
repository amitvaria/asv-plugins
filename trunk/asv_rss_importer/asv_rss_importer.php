<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'asv_rss_importer';

$plugin['version'] = '0.1';
$plugin['author'] = 'Amit Varia';
$plugin['author_uri'] = 'http://www.amitvaria.com/';
$plugin['description'] = 'Import RSS feeds in to Textpattern';

// Plugin types:
// 0 = regular plugin; loaded on the public web side only
// 1 = admin plugin; loaded on both the public and admin side
// 2 = library; loaded only when include_plugin() or require_plugin() is called
$plugin['type'] = 1; 


@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---
h1. asv_rss_importer

h2. the easiest way to import RSS feeds into Textpattern

p(announcement). Before activatin the plugin, read the documentation below and perform the necessary steps to install

h3. Overview

p. asv_rss_importer spwaned from the original asv_tumblelog plugin because the codebase grew too large to support all the great features of 

h3. Installation

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

#setup hooks
if (@txpinterface == 'admin') {

	add_privs('asv_rss_importer','1,2,3,4'); // Allow only userlevels 1,2,3,4 acess to this plugin.

	register_tab('extensions', 'asv_rss_importer', "Import RSS");

	register_callback("asv_rss_importer", "asv_rss_importer");

}


#main admin page
function asv_rss_importer($event, $step){

	pagetop('RSS Importer');
	
	if(in_array($step, array('asv_rss_importer_create', 'asv_rss_importer_delete', 'asv_rss_importer_update', 'asv_rss_importer_setup', 'asv_rss_importer_edit')))
		$step();
	else
		asv_rss_importer_list();
}

//output a list of all feeds
function asv_rss_importer_list() {
	
	//get data from table
	$rs = safe_rows_start("*", "asv_tumblelog_feeds", "1=1");
	
	//allow the user to add a feed
	echo n.n.'<form name="asv_rss_importer" method="post" action="index.php?event=asv_rss_importer&step=asv_rss_importer_edit">'.
		fInput('text', 'asv_rss_importer_url', '').
		fInput('submit','save_feeds', 'add',"publish", '', '', '', 4);
	
	//list the data
	if($rs)
	{
		while($a = nextRow($rs))
		{
			extract($a);
			
			echo "<p>".n.			
					(($Favicon)? "<img src='$Favicon' />":'').'<br />'.n.
					"<a href=\"index.php?event=asv_rss_importer&step=asv_rss_importer_edit&asv_rss_importer_id=".$ID."\">$Title</a>".n.
				"</p>";
		}						
	}
	
	
}

//List a single feed for editing
function asv_rss_importer_edit(){
	
	//check to see if we have an id to edit
	$asv_rss_importer_id = gps('asv_rss_importer_id');
	
	if(!$asv_rss_importer_id)
	{
		//otherwise we should be to create a new feed and get the id
		$asv_rss_importer_id = asv_rss_importer_create();
	}
	
	//if we got an id lets build
	if(assert_int($asv_rss_importer_id))
	{
		$rs = safe_row("*", "asv_tumblelog_feeds", "ID=".$asv_rss_importer_id);
		
		if($rs)
		{
			extract($rs);	
		}	
	}
	else
	{
		asv_rss_importer_list();
	}
}

//called during asv_rss_importer_edit to create a new feed
function asv_rss_importer_create() 
{
	global $txpcfg;
	
	$asv_rss_importer_url = gps('asv_rss_importer_url');
	
	//check to see if there really is a url
	if($asv_rss_importer_url)
	{
		//now let's see if this points to a real feed
		$contents = asv_rss_importer_verifyfeed($asv_rss_importer_url, $txpcfg['txpath'].'/lib/simplepie.inc');
		
		if($contents)
		{
			extract($contents);
			
			safe_insert('asv_tumblelog_feeds', 
				"Feed = '$asv_rss_importer_url',
				 Title = '".doSlash($title)."',
				 Image = '$logo'
			");
		
			//return the ID
			return mysql_insert_id();			
		}
	}
	
	//it wasn't a legit call to create so return null
	return null;
}

function asv_rss_importer_delete() {
	//if data is provided delete a feed and then list the feeds
}

function asv_rss_importer_update() {
	//if data is provided then update a feed and list the feeds
}

function asv_rss_importer_setup() {
	//update settings
	
	//list settings
}


class asv_rss{
	
}

// ================
// Helper Functions
// ================

#Verify a feed really exists
function asv_rss_importer_verifyfeed($feed, $simplepie)
{
	//Get SimplePie
	require_once($simplepie);
	
	//Create and setup SimplePie Instance
	$thefeed = new SimplePie();
	$thefeed->set_feed_url($feed);
	$thefeed->enable_cache(false);
	$thefeed->handle_content_type();
	
	//Get the feed
	$success = $thefeed->init();
	
	$title = $thefeed->get_title();
	$logo = ($thefeed->get_image_url())? $thefeed->get_image_url() : $thefeed->get_favicon();
	$permalink = $thefeed->get_permalink();
	
	return ($success)? array("title" => $title, "logo" => $logo, $permalink) :  false;

}

# --- END PLUGIN CODE ---

?>
